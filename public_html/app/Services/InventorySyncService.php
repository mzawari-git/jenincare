<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PosSale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InventorySyncService
{
    /**
     * مزامنة مخزون المتجر مع نظام POS/ERP
     * تقوم بتحديث الكميات لحظياً عند استقبال مبيعات من نقاط البيع
     */
    public function syncFromPosSale(PosSale $posSale): array
    {
        $results = [
            'updated' => 0,
            'skipped' => 0,
            'not_found' => 0,
            'errors' => [],
        ];

        if (empty($posSale->items) || !is_array($posSale->items)) {
            return $results;
        }

        DB::beginTransaction();
        try {
            foreach ($posSale->items as $item) {
                $productName = $item['name'] ?? '';
                $quantity = (int) ($item['quantity'] ?? 0);
                $itemPrice = (float) ($item['price'] ?? 0);

                if ($quantity <= 0) {
                    $results['skipped']++;
                    continue;
                }

                // محاولة مطابقة المنتج بالباركود أولاً ثم بالاسم
                $product = $this->findProductByPosItem($item);

                if (!$product) {
                    $results['not_found']++;
                    $results['errors'][] = "المنتج غير موجود: {$productName}";
                    continue;
                }

                // تحديث المخزون
                if ($product->track_inventory) {
                    $product->confirmSale($quantity);
                    $results['updated']++;

                    Log::info('POS inventory sync: stock reduced', [
                        'product_id' => $product->id,
                        'product_name' => $product->name_ar,
                        'pos_sale_id' => $posSale->pos_sale_id,
                        'quantity_sold' => $quantity,
                        'remaining_stock' => $product->available_quantity,
                    ]);
                } else {
                    $results['skipped']++;
                }
            }

            // تحديث حالة المخزون لجميع المنتجات المتأثرة
            $this->refreshLowStockAlerts();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS inventory sync failed', [
                'pos_sale_id' => $posSale->pos_sale_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $results;
    }

    /**
     * مطابقة منتج POS مع منتج المتجر
     */
    private function findProductByPosItem(array $item): ?Product
    {
        $barcode = $item['barcode'] ?? null;
        $sku = $item['sku'] ?? null;
        $name = $item['name'] ?? '';

        // البحث بالباركود (الأكثر دقة)
        if ($barcode) {
            $product = Product::where('barcode', $barcode)->first();
            if ($product) return $product;
        }

        // البحث بالSKU
        if ($sku) {
            $product = Product::where('sku', $sku)->first();
            if ($product) return $product;
        }

        // البحث بالاسم (مطابقة جزئية)
        if ($name) {
            $product = Product::where('name_ar', 'like', "%{$name}%")
                ->orWhere('name_en', 'like', "%{$name}%")
                ->first();
            if ($product) return $product;
        }

        return null;
    }

    /**
     * تحديث تنبيهات المخزون المنخفض
     */
    private function refreshLowStockAlerts(): void
    {
        Product::where('track_inventory', true)
            ->where('stock_status', 'in_stock')
            ->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    $product->updateStockStatus();
                }
            });
    }

    /**
     * دفع تحديث المخزون إلى ERP خارجي
     */
    public function pushToErp(Product $product): bool
    {
        $erpConfig = config('erp');

        if (empty($erpConfig['enabled']) || empty($erpConfig['webhook_url'])) {
            return false;
        }

        $payload = [
            'event' => 'stock_updated',
            'product' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'name_ar' => $product->name_ar,
                'stock_quantity' => $product->stock_quantity,
                'reserved_quantity' => $product->reserved_quantity,
                'available_quantity' => $product->available_quantity,
                'stock_status' => $product->stock_status,
                'updated_at' => $product->updated_at->toIso8601String(),
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'X-ERP-API-Key' => $erpConfig['api_key'] ?? '',
                'Content-Type' => 'application/json',
            ])->post($erpConfig['webhook_url'], $payload);

            if ($response->successful()) {
                Log::info('ERP sync: stock pushed successfully', [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                ]);
                return true;
            }

            Log::warning('ERP sync: push failed', [
                'product_id' => $product->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('ERP sync: exception', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * استيراد مخزون من ERP
     */
    public function importFromErp(array $erpProducts): array
    {
        $results = ['updated' => 0, 'created' => 0, 'failed' => 0];

        foreach ($erpProducts as $erpProduct) {
            try {
                $product = Product::where('sku', $erpProduct['sku'] ?? '')->first();

                if ($product) {
                    $product->update([
                        'stock_quantity' => $erpProduct['stock_quantity'] ?? $product->stock_quantity,
                        'b2c_price' => $erpProduct['retail_price'] ?? $product->b2c_price,
                        'b2b_price' => $erpProduct['wholesale_price'] ?? $product->b2b_price,
                        'barcode' => $erpProduct['barcode'] ?? $product->barcode,
                    ]);
                    $product->updateStockStatus();
                    $results['updated']++;
                } else {
                    // يمكن إنشاء منتج جديد هنا إذا لزم الأمر
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error('ERP import failed for SKU', [
                    'sku' => $erpProduct['sku'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }
}
