<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\SkinScan;
use Illuminate\Support\Facades\Log;

class CartIntegrationService
{
    public function addRecommendedProducts(int $scanId, int $userId): array
    {
        $scan = SkinScan::with('defects.products')->findOrFail($scanId);
        $analysisData = $scan->analysis_data ?? [];
        $recommendedProducts = $analysisData['recommended_products'] ?? [];

        if (empty($recommendedProducts) && $scan->defects->isNotEmpty()) {
            foreach ($scan->defects as $defect) {
                foreach ($defect->products as $product) {
                    $recommendedProducts[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'defect' => $defect->name_en,
                    ];
                }
            }
        }

        $cart = Cart::firstOrCreate(['user_id' => $userId]);
        $added = [];

        foreach ($recommendedProducts as $product) {
            $productId = is_array($product) ? ($product['id'] ?? null) : $product;
            if (!$productId) continue;

            $cartItem = $cart->items()->updateOrCreate(
                ['product_id' => $productId],
                ['quantity' => \DB::raw('quantity + 1')]
            );
            $added[] = $productId;
        }

        Log::info('CartIntegrationService: added products', [
            'scan_id' => $scanId,
            'user_id' => $userId,
            'products_added' => count($added),
        ]);

        return [
            'success' => true,
            'cart_id' => $cart->id,
            'products_added' => count($added),
            'product_ids' => $added,
        ];
    }
}
