<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Company;
use App\Models\Delivery;
use App\Models\SkinAnalysis;
use App\Models\AIProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $orderStats = Order::selectRaw("
            COUNT(*) as total_orders,
            COALESCE(SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END), 0) as total_revenue,
            COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END), 0) as today_orders,
            COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() AND status != 'cancelled' THEN total_amount ELSE 0 END), 0) as today_revenue,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_orders,
            COALESCE(SUM(CASE WHEN status IN ('confirmed','processing','shipped') THEN 1 ELSE 0 END), 0) as processing_orders,
            COALESCE(SUM(CASE WHEN status IN ('completed','delivered') THEN 1 ELSE 0 END), 0) as completed_orders,
            COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled_orders
        ")->first();

        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts = Product::count();

        $lowStockProducts = Product::where('track_inventory', true)->whereBetween('stock_quantity', [1, 10])->count();
        $outOfStockProducts = Product::where('track_inventory', true)->where('stock_quantity', 0)->count();

        $recentOrders = Order::with('user')->latest()->take(8)->get();

        $topProducts = Product::orderBy('sales_count', 'desc')->take(5)->get();

        $monthlyRevenue = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COALESCE(SUM(CASE WHEN status != \'cancelled\' THEN total_amount ELSE 0 END), 0) as total')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        $chartData = $this->getChartData();
        $analytics = $this->getAnalyticsData();
        $b2bStats = $this->getB2BStats();
        $deliveryStats = $this->getDeliveryStats();

        return view('admin.dashboard', compact(
            'totalCustomers', 'totalProducts',
            'lowStockProducts', 'outOfStockProducts',
            'recentOrders', 'topProducts', 'monthlyRevenue',
            'chartData', 'analytics', 'b2bStats', 'deliveryStats'
        ) + [
            'totalOrders' => $orderStats->total_orders,
            'totalRevenue' => $orderStats->total_revenue,
            'todayOrders' => $orderStats->today_orders,
            'todayRevenue' => $orderStats->today_revenue,
            'pendingOrders' => $orderStats->pending_orders,
            'processingOrders' => $orderStats->processing_orders,
            'completedOrders' => $orderStats->completed_orders,
            'cancelledOrders' => $orderStats->cancelled_orders,
        ]);
    }

    private function getChartData(): array
    {
        $last30Days = collect(range(0, 29))->map(function ($days) {
            return Carbon::now()->subDays($days)->format('Y-m-d');
        })->reverse()->values();

        $dailyRevenue = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $dailyOrders = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $revenueData = $last30Days->map(fn($date) => $dailyRevenue[$date] ?? 0);
        $ordersData = $last30Days->map(fn($date) => $dailyOrders[$date] ?? 0);

        $weekDays = $last30Days->map(fn($date) => Carbon::parse($date)->format('d/m'));

        $statusCounts = Order::selectRaw("
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending,
            COALESCE(SUM(CASE WHEN status IN ('confirmed','processing') THEN 1 ELSE 0 END), 0) as processing,
            COALESCE(SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END), 0) as shipped,
            COALESCE(SUM(CASE WHEN status IN ('completed','delivered') THEN 1 ELSE 0 END), 0) as completed,
            COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled
        ")->first();
        $statusDistribution = [
            'pending' => $statusCounts->pending ?? 0,
            'processing' => $statusCounts->processing ?? 0,
            'shipped' => $statusCounts->shipped ?? 0,
            'completed' => $statusCounts->completed ?? 0,
            'cancelled' => $statusCounts->cancelled ?? 0,
        ];

        $paymentMethods = Order::select('payment_method', DB::raw('COUNT(*) as count'))
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->pluck('count', 'payment_method');

        $hourlyOrders = Order::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour');

        $categorySales = OrderItem::select(
            'categories.name_ar as category',
            DB::raw('SUM(order_items.quantity) as total_sold')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('categories.id', 'categories.name_ar')
            ->orderByDesc('total_sold')
            ->limit(6)
            ->pluck('total_sold', 'category');

        return [
            'labels' => $weekDays,
            'revenue' => $revenueData,
            'orders' => $ordersData,
            'status' => $statusDistribution,
            'paymentMethods' => $paymentMethods,
            'hourly' => $hourlyOrders,
            'categories' => $categorySales,
        ];
    }

    private function getAnalyticsData(): array
    {
        $revenuePeriods = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->selectRaw("
                COALESCE(SUM(CASE WHEN created_at >= ? THEN total_amount ELSE 0 END), 0) as last_week_revenue,
                COALESCE(SUM(CASE WHEN created_at < ? AND created_at >= ? THEN total_amount ELSE 0 END), 0) as prev_week_revenue,
                COALESCE(AVG(total_amount), 0) as avg_order_value
            ", [Carbon::now()->subDays(7), Carbon::now()->subDays(7), Carbon::now()->subDays(14)])
            ->first();

        $lastWeekRevenue = $revenuePeriods->last_week_revenue;
        $previousWeekRevenue = $revenuePeriods->prev_week_revenue;
        $revenueGrowth = $previousWeekRevenue > 0
            ? round((($lastWeekRevenue - $previousWeekRevenue) / $previousWeekRevenue) * 100, 1)
            : 0;

        $totalOrders = Order::where('status', '!=', 'cancelled')->count();
        $totalUsers = User::count();
        $conversionRate = $totalOrders > 0
            ? round(($totalOrders / max($totalUsers, 1)) * 100, 2)
            : 0;

        $newCustomers = User::where('role', 'customer')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $returningCustomers = Order::select('user_id')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $cartAbandonment = Order::where('status', 'pending')
            ->where('created_at', '<=', Carbon::now()->subHours(24))
            ->count();

        $topCities = Order::select('shipping_city', DB::raw('COUNT(*) as count'))
            ->whereNotNull('shipping_city')
            ->groupBy('shipping_city')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'shipping_city');

        return [
            'revenueGrowth' => $revenueGrowth,
            'avgOrderValue' => round($revenuePeriods->avg_order_value, 2),
            'conversionRate' => $conversionRate,
            'newCustomers' => $newCustomers,
            'returningCustomers' => $returningCustomers,
            'cartAbandonment' => $cartAbandonment,
            'topCities' => $topCities,
            'customerLifetimeValue' => round($revenuePeriods->avg_order_value, 2),
        ];
    }

    private function getB2BStats(): array
    {
        $companyStats = Company::selectRaw("
            COUNT(*) as total_companies,
            COALESCE(SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END), 0) as active_companies,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_companies,
            COALESCE(SUM(credit_limit), 0) as total_credit,
            COALESCE(SUM(current_balance), 0) as used_credit
        ")->first();

        $availableCredit = $companyStats->total_credit - $companyStats->used_credit;

        $b2bStats = Order::where('order_type', 'b2b')
            ->selectRaw("
                COUNT(*) as b2b_orders,
                COALESCE(SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END), 0) as b2b_revenue
            ")->first();

        $topCompanies = Company::orderByDesc('lifetime_value')
            ->limit(5)
            ->get(['company_name_ar', 'lifetime_value', 'total_orders']);

        return [
            'totalCompanies' => $companyStats->total_companies,
            'activeCompanies' => $companyStats->active_companies,
            'pendingCompanies' => $companyStats->pending_companies,
            'totalCredit' => $companyStats->total_credit,
            'usedCredit' => $companyStats->used_credit,
            'availableCredit' => $availableCredit,
            'b2bOrders' => $b2bStats->b2b_orders,
            'b2bRevenue' => $b2bStats->b2b_revenue,
            'topCompanies' => $topCompanies,
        ];
    }

    private function getDeliveryStats(): array
    {
        $deliveryStats = Delivery::selectRaw("
            COUNT(*) as total_deliveries,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_deliveries,
            COALESCE(SUM(CASE WHEN status IN ('assigned','picked_up','in_transit','out_for_delivery') THEN 1 ELSE 0 END), 0) as active_deliveries,
            COALESCE(SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END), 0) as completed_deliveries,
            COALESCE(SUM(CASE WHEN status IN ('failed','attempted','returned') THEN 1 ELSE 0 END), 0) as failed_deliveries,
            COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END), 0) as today_deliveries,
            COALESCE(SUM(CASE WHEN DATE(delivered_at) = CURDATE() THEN 1 ELSE 0 END), 0) as today_completed
        ")->first();

        $successRate = $deliveryStats->total_deliveries > 0
            ? round(($deliveryStats->completed_deliveries / $deliveryStats->total_deliveries) * 100, 1)
            : 0;

        $recentDeliveries = Delivery::with('order')
            ->latest()
            ->take(5)
            ->get();

        $drivers = Delivery::select('driver_name', DB::raw('COUNT(*) as count'))
            ->whereNotNull('driver_name')
            ->groupBy('driver_name')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'driver_name');

        $deliveryByStatus = [
            'pending' => $deliveryStats->pending_deliveries,
            'active' => $deliveryStats->active_deliveries,
            'completed' => $deliveryStats->completed_deliveries,
            'failed' => $deliveryStats->failed_deliveries,
        ];

        return compact(
            'successRate', 'recentDeliveries', 'drivers', 'deliveryByStatus'
        ) + [
            'totalDeliveries' => $deliveryStats->total_deliveries,
            'pendingDeliveries' => $deliveryStats->pending_deliveries,
            'activeDeliveries' => $deliveryStats->active_deliveries,
            'completedDeliveries' => $deliveryStats->completed_deliveries,
            'failedDeliveries' => $deliveryStats->failed_deliveries,
            'todayDeliveries' => $deliveryStats->today_deliveries,
            'todayCompleted' => $deliveryStats->today_completed,
        ];
    }

    public function skinAnalyzerStats()
    {
        $totalScans = SkinAnalysis::count();
        $pendingScans = SkinAnalysis::where('status', 'pending')->count();
        $approvedToday = SkinAnalysis::where('status', 'approved')->whereDate('approved_at', today())->count();
        $activeProvider = AIProvider::where('is_active', true)->first();
        $providers = AIProvider::all()->map(fn($p) => [
            'id' => $p->id, 'name' => $p->name, 'driver_key' => $p->driver_key,
            'is_active' => $p->is_active, 'quota_used' => $p->quota_used, 'quota_limit' => $p->quota_limit,
        ]);

        return response()->json([
            'total_scans' => $totalScans,
            'pending_scans' => $pendingScans,
            'approved_today' => $approvedToday,
            'active_provider' => $activeProvider?->name ?? 'Native Engine',
            'providers' => $providers,
        ]);
    }

    public function pendingSkinScans()
    {
        $scans = SkinAnalysis::with(['user', 'aiProvider', 'accessPin'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($scans);
    }

    public function allSkinScans(Request $request)
    {
        $query = SkinAnalysis::with(['user', 'aiProvider']);

        if ($request->status) $query->where('status', $request->status);
        if ($request->user_id) $query->where('user_id', $request->user_id);
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);

        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function skinScanDetail($id)
    {
        $scan = SkinAnalysis::with(['user', 'aiProvider', 'accessPin', 'recommendedProducts'])
            ->findOrFail($id);

        return response()->json($scan);
    }
}
