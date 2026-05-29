<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosSale;
use App\Services\OfflineConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PosBridgeController extends Controller
{
    public function __construct(
        private OfflineConversionService $offlineConversion,
    ) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'pos_sale_id' => 'required|string|max:100',
            'store_id' => 'nullable|string|max:100',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'order_total' => 'required|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'items' => 'nullable|array',
            'items.*.name' => 'required_with:items|string',
            'items.*.price' => 'required_with:items|numeric',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'payment_method' => 'nullable|string|max:50',
            'sale_at' => 'nullable|date',
        ]);

        if (PosSale::where('pos_sale_id', $data['pos_sale_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate pos_sale_id',
            ], 409);
        }

        $sale = PosSale::create([
            'pos_sale_id' => $data['pos_sale_id'],
            'store_id' => $data['store_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'order_total' => $data['order_total'],
            'subtotal' => $data['subtotal'] ?? $data['order_total'],
            'currency' => $data['currency'] ?? 'ILS',
            'items' => $data['items'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'sale_at' => $data['sale_at'] ?? now(),
        ]);

        $uuid = $this->offlineConversion->matchCustomer($sale);

        if ($request->input('send_offline', true)) {
            if (config('tracking.platforms.facebook.enabled', false)) {
                $this->offlineConversion->sendToMetaOffline($sale);
            }
            if (config('tracking.platforms.tiktok.enabled', false)) {
                $this->offlineConversion->sendToTikTokOffline($sale);
            }
        }

        Log::info('POS sale recorded', [
            'pos_sale_id' => $sale->pos_sale_id,
            'matched' => $uuid !== null,
            'total' => $sale->order_total,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $sale->id,
                'pos_sale_id' => $sale->pos_sale_id,
                'matched_to_online' => $uuid !== null,
            ],
        ], 201);
    }

    public function stats(Request $request)
    {
        $days = (int) $request->get('days', 30);

        $query = PosSale::where('created_at', '>=', now()->subDays($days));

        return response()->json([
            'total_sales' => (clone $query)->count(),
            'total_revenue' => (clone $query)->sum('order_total'),
            'matched_sales' => (clone $query)->where('matched_to_online', true)->count(),
            'match_rate' => (function () use ($query) {
                $total = (clone $query)->count();
                if ($total === 0) return 0;
                $matched = (clone $query)->where('matched_to_online', true)->count();
                return round(($matched / $total) * 100, 1);
            })(),
        ]);
    }
}
