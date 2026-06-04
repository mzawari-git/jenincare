<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\SkinScan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateProductRecommendations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public int $tries = 2;

    protected SkinScan $skinScan;

    public function __construct(SkinScan $skinScan)
    {
        $this->skinScan = $skinScan;
    }

    public function handle(): void
    {
        try {
            Log::info('GenerateProductRecommendations started', [
                'scan_id' => $this->skinScan->id,
            ]);

            $analysisData = $this->skinScan->analysis_data;

            if (empty($analysisData) || !is_array($analysisData)) {
                Log::warning('No analysis data for product recommendations', [
                    'scan_id' => $this->skinScan->id,
                ]);
                return;
            }

            $defects = $analysisData['defects'] ?? [];
            $radarMetrics = $analysisData['radar_metrics'] ?? [];

            $conditionTypes = [];
            foreach ($defects as $defect) {
                $type = $defect['type'] ?? '';
                if ($type && $defect['severity'] >= 20) {
                    $conditionTypes[] = $type;
                }
            }

            if (empty($conditionTypes)) {
                Log::info('No significant defects for product recommendations', [
                    'scan_id' => $this->skinScan->id,
                ]);
                return;
            }

            $recommendedProducts = Product::where(function ($query) use ($conditionTypes) {
                foreach ($conditionTypes as $i => $type) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $query->{$method}('conditions_handled', 'like', "%{$type}%");
                }
            })
                ->where('is_active', true)
                ->take(10)
                ->get();

            $mapped = $recommendedProducts->map(function (Product $product) {
                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'name_ar' => $product->name_ar,
                    'category' => $product->category,
                    'price' => $product->price,
                    'image' => $product->image_url,
                    'conditions_handled' => $product->conditions_handled,
                    'recommendation_reason' => $this->generateReason($product, $defects),
                    'recommendation_reason_ar' => $this->generateReasonAr($product, $defects),
                ];
            })->toArray();

            $this->skinScan->updateQuietly([
                'recommended_products' => $mapped,
            ]);

            Log::info('GenerateProductRecommendations completed', [
                'scan_id' => $this->skinScan->id,
                'products' => count($mapped),
            ]);

        } catch (\Throwable $e) {
            Log::error('GenerateProductRecommendations failed', [
                'scan_id' => $this->skinScan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function generateReason(Product $product, array $defects): string
    {
        $conditions = $product->conditions_handled ?? [];

        if (empty($conditions) || !is_array($conditions)) {
            return 'Recommended based on your skin analysis.';
        }

        $matched = array_intersect(
            $conditions,
            array_column($defects, 'type')
        );

        if (!empty($matched)) {
            $matchedNames = implode(', ', array_slice($matched, 0, 3));
            return "Targets: {$matchedNames}.";
        }

        return 'Recommended based on your skin profile.';
    }

    protected function generateReasonAr(Product $product, array $defects): string
    {
        $conditions = $product->conditions_handled ?? [];

        if (empty($conditions) || !is_array($conditions)) {
            return 'موصى به بناءً على تحليل بشرتك.';
        }

        $matched = array_intersect(
            $conditions,
            array_column($defects, 'type')
        );

        if (!empty($matched)) {
            return 'مناسب لعلاج مشاكل البشرة المكتشفة.';
        }

        return 'موصى به بناءً على ملف بشرتك.';
    }
}
