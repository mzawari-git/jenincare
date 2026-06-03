<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\SkinAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateProductRecommendations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $backoff = 10;

    public function __construct(
        public readonly int $scanId,
    ) {}

    public function handle(): void
    {
        $scan = SkinAnalysis::with('recommendedProducts')->findOrFail($this->scanId);

        $defects = $this->extractDefectsFromScan($scan);

        if (empty($defects)) {
            Log::info('GenerateProductRecommendations: No defects found for scan.', [
                'scan_id' => $scan->id,
            ]);
            return;
        }

        $products = Product::active()
            ->where(function ($query) use ($defects) {
                foreach ($defects as $defect) {
                    $query->orWhereJsonContains('concerns', $defect);
                }
            })
            ->limit(10)
            ->get();

        if ($products->isEmpty()) {
            Log::info('GenerateProductRecommendations: No matching products found.', [
                'scan_id' => $scan->id,
                'defects' => $defects,
            ]);
            return;
        }

        $existingProductIds = $scan->recommendedProducts->pluck('id')->toArray();

        foreach ($products as $product) {
            if (in_array($product->id, $existingProductIds)) {
                continue;
            }

            $matchingConcerns = array_intersect(
                $defects,
                $product->concerns ?? []
            );

            $reason = ! empty($matchingConcerns)
                ? 'Matches: ' . implode(', ', $matchingConcerns)
                : 'General recommendation for ' . ($defects[0] ?? 'skin health');

            $scan->recommendedProducts()->attach($product->id, [
                'matching_reason' => $reason,
            ]);
        }

        Log::info('GenerateProductRecommendations complete.', [
            'scan_id' => $scan->id,
            'products_matched' => $products->count(),
            'defects_found' => count($defects),
        ]);
    }

    private function extractDefectsFromScan(SkinAnalysis $scan): array
    {
        $defects = [];

        if ($scan->radar_metrics) {
            $metrics = $scan->radar_metrics;

            $lowScores = array_filter($metrics, fn ($v) => $v < 50);
            $defects = array_merge($defects, array_keys($lowScores));
        }

        if ($scan->raw_vendor_response) {
            $vendorDefects = $scan->raw_vendor_response['defects']
                ?? $scan->raw_vendor_response['concerns']
                ?? $scan->raw_vendor_response['skin_issues']
                ?? [];

            if (is_array($vendorDefects)) {
                $defects = array_merge($defects, $vendorDefects);
            }
        }

        return array_values(array_unique($defects));
    }
}
