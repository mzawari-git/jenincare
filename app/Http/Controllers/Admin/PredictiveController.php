<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\PosSale;
use App\Services\LtvMultiplierService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PredictiveController extends Controller
{
    public function __construct(
        private LtvMultiplierService $ltvService,
    ) {}

    public function index()
    {
        return view('admin.predictive.index');
    }

    public function data(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $orders = Order::where('created_at', '>=', now()->subDays($days))
            ->select('id', 'user_id', 'customer_email', 'total_amount', 'created_at')
            ->where('status', '!=', 'cancelled')
            ->take(200)
            ->get();

        $segments = ['b2b' => 0, 'b2c' => 0, 'one_time' => 0];
        $totalLtv = 0;
        $segmentedOrders = [];

        foreach ($orders as $order) {
            $aov = (float) $order->total_amount;
            $prediction = $this->ltvService->predictLtv($aov);
            $segment = $prediction['segment'];

            $segments[$segment]++;
            $totalLtv += $prediction['ltv_365d'];

            $segmentedOrders[] = [
                'id' => $order->id,
                'email' => $order->customer_email,
                'aov' => $aov,
                'ltv_30d' => $prediction['ltv_30d'],
                'ltv_365d' => $prediction['ltv_365d'],
                'segment' => $segment,
            ];
        }

        $posStats = [
            'total_sales' => PosSale::where('created_at', '>=', now()->subDays($days))->count(),
            'matched_sales' => PosSale::where('created_at', '>=', now()->subDays($days))->where('matched_to_online', true)->count(),
            'total_revenue' => PosSale::where('created_at', '>=', now()->subDays($days))->sum('order_total'),
        ];

        return response()->json([
            'segments' => $segments,
            'total_ltv_365d' => round($totalLtv, 2),
            'total_orders' => $orders->count(),
            'average_ltv' => $orders->count() > 0 ? round($totalLtv / $orders->count(), 2) : 0,
            'orders' => $segmentedOrders,
            'pos_stats' => $posStats,
            'multipliers' => [
                'default' => config('tracking.ltv.multipliers', []),
                'facebook' => config('tracking.ltv.platform_multipliers.facebook', []),
                'tiktok' => config('tracking.ltv.platform_multipliers.tiktok', []),
                'google' => config('tracking.ltv.platform_multipliers.google', []),
            ],
        ]);
    }
}
