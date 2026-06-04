<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use App\Exports\ProductsExport;
use App\Exports\UsersExport;

class ReportController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts = Product::count();
        $pendingOrders = Order::where('status', 'pending')->count();

        $monthlyRevenue = Order::selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->where('payment_status', 'paid')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        $recentOrders = Order::with('user')->latest()->limit(5)->get();

        $topProducts = OrderItem::selectRaw('product_name, SUM(quantity) as total_qty, SUM(total) as total_revenue')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('admin.reports.index', compact(
            'totalOrders', 'totalRevenue', 'totalCustomers', 'totalProducts',
            'pendingOrders', 'monthlyRevenue', 'recentOrders', 'topProducts'
        ));
    }

    public function sales(Request $request)
    {
        $query = Order::with(['user', 'items'])
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status));

        $orders = $query->latest()->paginate(30)->appends($request->all());

        $totals = Order::selectRaw('COUNT(*) as count, COALESCE(SUM(subtotal),0) as subtotal, COALESCE(SUM(discount_amount),0) as discount, COALESCE(SUM(shipping_cost),0) as shipping, COALESCE(SUM(tax_amount),0) as tax, COALESCE(SUM(total_amount),0) as total')
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status))
            ->first();

        return view('admin.reports.sales', compact('orders', 'totals'));
    }

    public function products(Request $request)
    {
        $query = OrderItem::with('order')
            ->when($request->date_from, fn($q) => $q->whereHas('order', fn($o) => $o->whereDate('created_at', '>=', $request->date_from)))
            ->when($request->date_to, fn($q) => $q->whereHas('order', fn($o) => $o->whereDate('created_at', '<=', $request->date_to)));

        $products = $query->selectRaw(
                'product_name, product_sku, SUM(quantity) as total_qty, SUM(total) as total_revenue, COUNT(DISTINCT order_id) as order_count'
            )
            ->groupBy('product_name', 'product_sku')
            ->orderByDesc('total_qty')
            ->paginate(30)
            ->appends($request->all());

        $lowStock = Product::where('quantity', '>', 0)
            ->where('quantity', '<=', 10)
            ->orderBy('quantity')
            ->limit(10)
            ->get();

        return view('admin.reports.products', compact('products', 'lowStock'));
    }

    public function users(Request $request)
    {
        $query = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total_amount')
            ->when($request->search, fn($q) => $q->where(fn($sub) => $sub->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%")));

        $users = $query->orderByDesc('orders_count')->paginate(30)->appends($request->all());

        return view('admin.reports.users', compact('users'));
    }

    public function invoice(Order $order)
    {
        $order->load(['items.product', 'user']);
        return view('admin.reports.invoice', compact('order'));
    }

    public function invoicePdf(Order $order, string $size = 'a4')
    {
        $order->load(['items.product', 'user']);

        $view = match ($size) {
            'a6' => 'admin.reports.invoice-a6',
            'a5' => 'admin.reports.invoice-a5',
            default => 'admin.reports.invoice-a4',
        };

        $paperSize = match ($size) {
            'a6' => [0, 0, 297.64, 419.53],
            'a5' => [0, 0, 419.53, 595.28],
            default => [0, 0, 595.28, 841.89],
        };

        $pdf = Pdf::loadView($view, compact('order'))
            ->setPaper($paperSize, 'portrait')
            ->setOptions(['defaultFont' => 'DejaVu Sans']);

        return $pdf->download('invoice-' . $order->order_number . '-' . $size . '.pdf');
    }

    public function exportSalesExcel(Request $request)
    {
        $orders = Order::with('user')
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->get();

        return Excel::download(new OrdersExport($orders), 'sales-report.xlsx');
    }

    public function exportProductsExcel(Request $request)
    {
        $products = OrderItem::with('order')
            ->when($request->date_from, fn($q) => $q->whereHas('order', fn($o) => $o->whereDate('created_at', '>=', $request->date_from)))
            ->when($request->date_to, fn($q) => $q->whereHas('order', fn($o) => $o->whereDate('created_at', '<=', $request->date_to)))
            ->selectRaw('product_name, product_sku, SUM(quantity) as total_qty, SUM(total) as total_revenue, COUNT(DISTINCT order_id) as order_count')
            ->groupBy('product_name', 'product_sku')
            ->orderByDesc('total_qty')
            ->get();

        return Excel::download(new ProductsExport($products), 'products-report.xlsx');
    }

    public function exportUsersExcel(Request $request)
    {
        $users = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total_amount')
            ->when($request->search, fn($q) => $q->where(fn($sub) => $sub->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%")))
            ->orderByDesc('orders_count')
            ->get();

        return Excel::download(new UsersExport($users), 'users-report.xlsx');
    }

    public function delivery(Request $request)
    {
        $query = Delivery::with(['order.user', 'order.items'])
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->driver, fn($q) => $q->where('driver_name', 'like', "%{$request->driver}%"))
            ->when($request->city, fn($q) => $q->where('delivery_city', 'like', "%{$request->city}%"));

        $deliveries = $query->latest()->paginate(30)->appends($request->all());

        $summary = Delivery::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status IN ('assigned','picked_up','in_transit','out_for_delivery') THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
            SUM(CASE WHEN status IN ('failed','attempted','returned') THEN 1 ELSE 0 END) as failed,
            COALESCE(SUM(delivery_cost), 0) as total_cost,
            COALESCE(SUM(cod_amount), 0) as total_cod,
            COALESCE(AVG(estimated_delivery_days), 0) as avg_days
        ")
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->driver, fn($q) => $q->where('driver_name', 'like', "%{$request->driver}%"))
            ->first();

        $deliveryByCity = Delivery::select('delivery_city', DB::raw('COUNT(*) as count'))
            ->whereNotNull('delivery_city')
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->groupBy('delivery_city')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'delivery_city');

        $deliveryByDriver = Delivery::select('driver_name', DB::raw('COUNT(*) as count'))
            ->whereNotNull('driver_name')
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->groupBy('driver_name')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'driver_name');

        $drivers = Delivery::distinct()->whereNotNull('driver_name')->pluck('driver_name');

        return view('admin.reports.delivery', compact(
            'deliveries', 'summary', 'deliveryByCity', 'deliveryByDriver', 'drivers'
        ));
    }

    public function exportDeliveryExcel(Request $request)
    {
        $deliveries = Delivery::with('order')
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->driver, fn($q) => $q->where('driver_name', 'like', "%{$request->driver}%"))
            ->latest()
            ->get();

        $data = $deliveries->map(fn($d) => [
            'رقم التوصيل' => $d->delivery_number,
            'رقم الطلب' => $d->order->order_number ?? '',
            'الحالة' => $d->status_label,
            'السائق' => $d->driver_name,
            'المدينة' => $d->delivery_city,
            'تكلفة التوصيل' => $d->delivery_cost,
            'الدفع عند الاستلام' => $d->cod_amount,
            'تاريخ الإنشاء' => $d->created_at->format('Y-m-d'),
            'تاريخ التوصيل' => $d->delivered_at?->format('Y-m-d'),
        ]);

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() { return $this->data; }
            public function headings(): array { return array_keys($this->data->first() ?? []); }
        }, 'delivery-report.xlsx');
    }
}
