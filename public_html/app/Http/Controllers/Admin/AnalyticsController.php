<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays((int) $period);
        $endDate = Carbon::now();

        $overview = $this->getOverview($startDate, $endDate);
        $salesData = $this->getSalesData($startDate, $endDate, $period);
        $productAnalytics = $this->getProductAnalytics($startDate, $endDate);
        $customerAnalytics = $this->getCustomerAnalytics($startDate, $endDate);
        $trafficSources = $this->getTrafficSources($startDate, $endDate);

        return view('admin.analytics.index', compact(
            'overview', 'salesData', 'productAnalytics', 'customerAnalytics', 
            'trafficSources', 'period', 'startDate', 'endDate'
        ));
    }

    private function getOverview($startDate, $endDate): array
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate]);
        $previousPeriodOrders = Order::whereBetween('created_at', [
            $startDate->copy()->subDays($endDate->diffInDays($startDate)),
            $startDate
        ]);

        $revenue = (clone $orders)->where('status', '!=', 'cancelled')->sum('total_amount');
        $previousRevenue = (clone $previousPeriodOrders)->where('status', '!=', 'cancelled')->sum('total_amount');

        $ordersCount = (clone $orders)->count();
        $previousOrdersCount = (clone $previousPeriodOrders)->count();

        $avgOrderValue = $ordersCount > 0 ? $revenue / $ordersCount : 0;
        $previousAvgOrderValue = $previousOrdersCount > 0 ? $previousRevenue / $previousOrdersCount : 0;

        return [
            'revenue' => $revenue,
            'revenueGrowth' => $this->calculateGrowth($revenue, $previousRevenue),
            'orders' => $ordersCount,
            'ordersGrowth' => $this->calculateGrowth($ordersCount, $previousOrdersCount),
            'avgOrderValue' => $avgOrderValue,
            'avgOrderValueGrowth' => $this->calculateGrowth($avgOrderValue, $previousAvgOrderValue),
            'customers' => User::where('role', 'customer')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'conversionRate' => $this->calculateConversionRate($startDate, $endDate),
        ];
    }

    private function getSalesData($startDate, $endDate, $period): array
    {
        $interval = $period <= 7 ? 'hour' : ($period <= 30 ? 'day' : 'week');
        
        $format = $interval === 'hour' ? '%Y-%m-%d %H:00:00' : ($interval === 'day' ? '%Y-%m-%d' : '%Y-%u');
        
        $sales = Order::select(
            DB::raw("DATE_FORMAT(created_at, '{$format}') as date"),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('COUNT(*) as orders')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $hourlyDistribution = Order::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour');

        $dailyRevenue = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as total')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'timeline' => $sales,
            'hourly' => $hourlyDistribution,
            'daily' => $dailyRevenue,
            'totalRevenue' => $sales->sum('revenue'),
            'totalOrders' => $sales->sum('orders'),
        ];
    }

    private function getProductAnalytics($startDate, $endDate): array
    {
        $topProducts = OrderItem::select(
            'products.id',
            'products.name_ar',
            'products.main_image',
            DB::raw('SUM(order_items.quantity) as total_sold'),
            DB::raw('SUM(order_items.total) as total_revenue'),
            DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.name_ar', 'products.main_image')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $categorySales = OrderItem::select(
            'categories.name_ar as category',
            DB::raw('SUM(order_items.quantity) as total_sold'),
            DB::raw('SUM(order_items.total) as total_revenue')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('categories.id', 'categories.name_ar')
            ->orderByDesc('total_sold')
            ->get();

        $lowPerformers = Product::where('sales_count', '<', 5)
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->orderBy('sales_count')
            ->limit(10)
            ->get(['id', 'name_ar', 'sales_count', 'views_count', 'stock_quantity']);

        return [
            'topProducts' => $topProducts,
            'categorySales' => $categorySales,
            'lowPerformers' => $lowPerformers,
        ];
    }

    private function getCustomerAnalytics($startDate, $endDate): array
    {
        $newCustomers = User::where('role', 'customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $activeCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->distinct('user_id')
            ->count('user_id');

        $customerSegments = [
            'new' => User::where('role', 'customer')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'returning' => Order::select('user_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) > 1')
                ->count(),
            'vip' => User::whereHas('orders', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', '!=', 'cancelled')
                  ->havingRaw('SUM(total_amount) > 1000');
            })->count(),
        ];

        $topCustomers = User::select(
            'users.id',
            'users.name',
            'users.email',
            DB::raw('COUNT(orders.id) as order_count'),
            DB::raw('SUM(orders.total_amount) as total_spent'),
            DB::raw('MAX(orders.created_at) as last_order')
        )
            ->leftJoin('orders', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'orders.user_id')
                    ->whereBetween('orders.created_at', [$startDate, $endDate])
                    ->where('orders.status', '!=', 'cancelled');
            })
            ->where('users.role', 'customer')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->havingRaw('COUNT(orders.id) > 0')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        $cityDistribution = Order::select(
            'shipping_city as city',
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total_amount) as total_revenue')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('shipping_city')
            ->groupBy('shipping_city')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get();

        return [
            'newCustomers' => $newCustomers,
            'activeCustomers' => $activeCustomers,
            'segments' => $customerSegments,
            'topCustomers' => $topCustomers,
            'cityDistribution' => $cityDistribution,
        ];
    }

    private function getTrafficSources($startDate, $endDate): array
    {
        $utmSources = Order::select(
            DB::raw('COALESCE(utm_source, "direct") as source'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as revenue')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy(DB::raw('COALESCE(utm_source, "direct")'))
            ->orderByDesc('count')
            ->get();

        $deviceStats = Order::select(
            DB::raw('CASE 
                WHEN user_agent LIKE "%Mobile%" THEN "mobile"
                WHEN user_agent LIKE "%Tablet%" THEN "tablet"
                ELSE "desktop"
            END as device'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('CASE 
                WHEN user_agent LIKE "%Mobile%" THEN "mobile"
                WHEN user_agent LIKE "%Tablet%" THEN "tablet"
                ELSE "desktop"
            END'))
            ->orderByDesc('count')
            ->get();

        return [
            'sources' => $utmSources,
            'devices' => $deviceStats,
        ];
    }

    private function calculateGrowth($current, $previous): float
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function calculateConversionRate($startDate, $endDate): float
    {
        $visitors = max(User::whereBetween('created_at', [$startDate->subDays(30), $endDate])->count(), 1);
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->count();
        return round(($orders / $visitors) * 100, 2);
    }

    public function export(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays((int) $period);
        $endDate = Carbon::now();

        $data = [
            'overview' => $this->getOverview($startDate, $endDate),
            'topProducts' => $this->getProductAnalytics($startDate, $endDate)['topProducts'],
            'customers' => $this->getCustomerAnalytics($startDate, $endDate)['topCustomers'],
        ];

        return response()->json($data);
    }
}
