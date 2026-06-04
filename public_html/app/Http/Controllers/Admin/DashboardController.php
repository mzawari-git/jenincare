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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts = Product::count();

        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total_amount');

        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::whereIn('status', ['confirmed', 'processing', 'shipped'])->count();
        $completedOrders = Order::whereIn('status', ['completed', 'delivered'])->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        $lowStockProducts = Product::where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10)->count();
        $outOfStockProducts = Product::where('stock_quantity', 0)->count();

        $recentOrders = Order::with('user')->latest()->take(8)->get();

        $topProducts = Product::orderBy('sales_count', 'desc')->take(5)->get();

        $monthlyRevenue = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('status', '!=', 'cancelled')
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
            'totalOrders', 'totalRevenue', 'totalCustomers', 'totalProducts',
            'todayOrders', 'todayRevenue',
            'pendingOrders', 'processingOrders', 'completedOrders', 'cancelledOrders',
            'lowStockProducts', 'outOfStockProducts',
            'recentOrders', 'topProducts', 'monthlyRevenue',
            'chartData', 'analytics', 'b2bStats', 'deliveryStats'
        ));
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

        $statusDistribution = [
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::whereIn('status', ['confirmed', 'processing'])->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'completed' => Order::whereIn('status', ['completed', 'delivered'])->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
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
        $lastWeekRevenue = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->sum('total_amount');

        $previousWeekRevenue = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [Carbon::now()->subDays(14), Carbon::now()->subDays(7)])
            ->sum('total_amount');

        $revenueGrowth = $previousWeekRevenue > 0
            ? round((($lastWeekRevenue - $previousWeekRevenue) / $previousWeekRevenue) * 100, 1)
            : 0;

        $avgOrderValue = Order::where('status', '!=', 'cancelled')
            ->avg('total_amount') ?? 0;

        $conversionRate = Order::where('status', '!=', 'cancelled')->count() > 0
            ? round((Order::where('status', '!=', 'cancelled')->count() / max(User::count(), 1)) * 100, 2)
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
            'avgOrderValue' => round($avgOrderValue, 2),
            'conversionRate' => $conversionRate,
            'newCustomers' => $newCustomers,
            'returningCustomers' => $returningCustomers,
            'cartAbandonment' => $cartAbandonment,
            'topCities' => $topCities,
            'customerLifetimeValue' => Order::where('status', '!=', 'cancelled')->avg('total_amount') ?? 0,
        ];
    }

    private function getB2BStats(): array
    {
        $totalCompanies = Company::count();
        $activeCompanies = Company::where('status', 'active')->count();
        $pendingCompanies = Company::where('status', 'pending')->count();

        $totalCredit = Company::sum('credit_limit');
        $usedCredit = Company::sum('current_balance');
        $availableCredit = $totalCredit - $usedCredit;

        $b2bOrders = Order::where('order_type', 'b2b')->count();
        $b2bRevenue = Order::where('order_type', 'b2b')->where('status', '!=', 'cancelled')->sum('total_amount');

        $topCompanies = Company::orderByDesc('lifetime_value')
            ->limit(5)
            ->get(['company_name_ar', 'lifetime_value', 'total_orders']);

        return [
            'totalCompanies' => $totalCompanies,
            'activeCompanies' => $activeCompanies,
            'pendingCompanies' => $pendingCompanies,
            'totalCredit' => $totalCredit,
            'usedCredit' => $usedCredit,
            'availableCredit' => $availableCredit,
            'b2bOrders' => $b2bOrders,
            'b2bRevenue' => $b2bRevenue,
            'topCompanies' => $topCompanies,
        ];
    }

    private function getDeliveryStats(): array
    {
        $totalDeliveries = Delivery::count();
        $pendingDeliveries = Delivery::where('status', 'pending')->count();
        $activeDeliveries = Delivery::whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])->count();
        $completedDeliveries = Delivery::where('status', 'delivered')->count();
        $failedDeliveries = Delivery::whereIn('status', ['failed', 'attempted', 'returned'])->count();

        $todayDeliveries = Delivery::whereDate('created_at', today())->count();
        $todayCompleted = Delivery::whereDate('delivered_at', today())->count();

        $successRate = $totalDeliveries > 0 ? round(($completedDeliveries / $totalDeliveries) * 100, 1) : 0;

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
            'pending' => $pendingDeliveries,
            'active' => $activeDeliveries,
            'completed' => $completedDeliveries,
            'failed' => $failedDeliveries,
        ];

        return compact(
            'totalDeliveries', 'pendingDeliveries', 'activeDeliveries',
            'completedDeliveries', 'failedDeliveries', 'todayDeliveries',
            'todayCompleted', 'successRate', 'recentDeliveries', 'drivers', 'deliveryByStatus'
        );
    }
}
