<?php

namespace Modules\CustomAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\IdentityEvent;
use App\Services\AttributionService;
use App\Services\EventSourcingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoasDashboardController extends Controller
{
    public function __construct(
        private AttributionService $attributionService,
        private EventSourcingService $eventSourcingService,
    ) {}

    public function index()
    {
        return view('admin.roas.index');
    }

    public function data(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $source = $request->get('source');

        $ordersQuery = Order::where('created_at', '>=', now()->subDays($days))
            ->where('status', '!=', 'cancelled');

        $totalRevenue = (clone $ordersQuery)->sum('total_amount');
        $totalOrders = (clone $ordersQuery)->count();

        $attributedOrders = 0;
        $attributedRevenue = 0;
        $sourceBreakdown = [];

        $orders = (clone $ordersQuery)->select('id', 'user_id', 'customer_email', 'total_amount', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(500)
            ->get();

        foreach ($orders as $order) {
            $attr = $this->attributionService->attributeOrderToSource($order);
            $orderSource = $attr['source'] ?? 'direct';

            if ($orderSource !== 'unattributed' && $orderSource !== null) {
                $attributedOrders++;
                $attributedRevenue += (float) $order->total_amount;
            }

            if (!isset($sourceBreakdown[$orderSource])) {
                $sourceBreakdown[$orderSource] = [
                    'source' => $orderSource,
                    'orders' => 0,
                    'revenue' => 0,
                    'ad_spend' => 0,
                ];
            }
            $sourceBreakdown[$orderSource]['orders']++;
            $sourceBreakdown[$orderSource]['revenue'] += (float) $order->total_amount;
        }

        $topSources = $this->attributionService->getTopSources($days);

        $dailyStats = $this->eventSourcingService->getDailyStats($days);

        $campaignPerformance = $this->attributionService->getCampaignPerformance($days);

        $roas = $attributedRevenue > 0
            ? round($attributedRevenue / max(array_sum(array_column($sourceBreakdown, 'ad_spend')), 1), 2)
            : 0;

        return response()->json([
            'summary' => [
                'total_orders' => $totalOrders,
                'total_revenue' => round($totalRevenue, 2),
                'aov' => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0,
                'attributed_orders' => $attributedOrders,
                'attributed_revenue' => round($attributedRevenue, 2),
                'attribution_rate' => $totalOrders > 0 ? round(($attributedOrders / $totalOrders) * 100, 1) : 0,
                'roas' => $roas,
            ],
            'source_breakdown' => array_values($sourceBreakdown),
            'top_sources' => $topSources,
            'daily_stats' => $dailyStats,
            'campaign_performance' => $campaignPerformance,
        ]);
    }
}
