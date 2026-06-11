<?php

namespace App\Services\Meta;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbTestService
{
    public function createTest(array $params): ?int
    {
        return DB::table('ab_tests')->insertGetId([
            'name' => $params['name'],
            'platform' => $params['platform'] ?? 'meta',
            'campaign_id' => $params['campaign_id'] ?? null,
            'test_type' => $params['test_type'] ?? 'headline',
            'variant_a_id' => $params['variant_a_id'] ?? null,
            'variant_b_id' => $params['variant_b_id'] ?? null,
            'status' => 'running',
            'confidence_level' => 0,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function recordImpression(int $testId, string $variant): void
    {
        $today = today()->toDateString();

        DB::table('ab_test_results')->updateOrInsert(
            ['ab_test_id' => $testId, 'variant' => $variant, 'date' => $today],
            [
                'impressions' => DB::raw('impressions + 1'),
                'updated_at' => now(),
            ]
        );
    }

    public function recordClick(int $testId, string $variant): void
    {
        $today = today()->toDateString();

        DB::table('ab_test_results')->updateOrInsert(
            ['ab_test_id' => $testId, 'variant' => $variant, 'date' => $today],
            [
                'clicks' => DB::raw('clicks + 1'),
                'updated_at' => now(),
            ]
        );
    }

    public function recordConversion(int $testId, string $variant, float $value = 0): void
    {
        $today = today()->toDateString();

        DB::table('ab_test_results')->updateOrInsert(
            ['ab_test_id' => $testId, 'variant' => $variant, 'date' => $today],
            [
                'conversions' => DB::raw('conversions + 1'),
                'spend' => DB::raw("spend + {$value}"),
                'updated_at' => now(),
            ]
        );
    }

    public function analyzeTest(int $testId): array
    {
        $test = DB::table('ab_tests')->where('id', $testId)->first();
        if (!$test) return ['error' => 'Test not found'];

        $results = DB::table('ab_test_results')
            ->where('ab_test_id', $testId)
            ->select('variant', DB::raw('SUM(impressions) as impressions, SUM(clicks) as clicks, SUM(conversions) as conversions, SUM(spend) as spend'))
            ->groupBy('variant')
            ->get();

        $dataA = $results->where('variant', 'a')->first();
        $dataB = $results->where('variant', 'b')->first();

        $ctrA = ($dataA->impressions ?? 0) > 0 ? ($dataA->clicks ?? 0) / ($dataA->impressions ?? 1) * 100 : 0;
        $ctrB = ($dataB->impressions ?? 0) > 0 ? ($dataB->clicks ?? 0) / ($dataB->impressions ?? 1) * 100 : 0;

        $conversionRateA = ($dataA->clicks ?? 0) > 0 ? ($dataA->conversions ?? 0) / ($dataA->clicks ?? 1) * 100 : 0;
        $conversionRateB = ($dataB->clicks ?? 0) > 0 ? ($dataB->conversions ?? 0) / ($dataB->clicks ?? 1) * 100 : 0;

        $confidence = $this->calculateConfidence(
            $dataA->conversions ?? 0, $dataA->clicks ?? 1,
            $dataB->conversions ?? 0, $dataB->clicks ?? 1
        );

        $winner = null;
        if ($confidence >= 95) {
            $winner = $ctrB > $ctrA ? 'b' : 'a';
        }

        return [
            'test_id' => $testId,
            'variant_a' => [
                'impressions' => $dataA->impressions ?? 0,
                'clicks' => $dataA->clicks ?? 0,
                'conversions' => $dataA->conversions ?? 0,
                'ctr' => round($ctrA, 2),
                'conversion_rate' => round($conversionRateA, 2),
            ],
            'variant_b' => [
                'impressions' => $dataB->impressions ?? 0,
                'clicks' => $dataB->clicks ?? 0,
                'conversions' => $dataB->conversions ?? 0,
                'ctr' => round($ctrB, 2),
                'conversion_rate' => round($conversionRateB, 2),
            ],
            'confidence' => round($confidence, 1),
            'winner' => $winner,
            'lift' => $ctrA > 0 ? round(($ctrB - $ctrA) / $ctrA * 100, 1) : 0,
        ];
    }

    public function declareWinner(int $testId, string $winner): bool
    {
        $analysis = $this->analyzeTest($testId);
        if (!$analysis['winner']) return false;

        DB::table('ab_tests')->where('id', $testId)->update([
            'winner_variant' => $winner,
            'status' => 'completed',
            'confidence_level' => $analysis['confidence'],
            'ended_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    public function getActiveTests(): array
    {
        return DB::table('ab_tests')->where('status', 'running')->orderByDesc('created_at')->get()->toArray();
    }

    private function calculateConfidence(int $convA, int $totalA, int $convB, int $totalB): float
    {
        if ($totalA < 30 || $totalB < 30) return 0;

        $rateA = $convA / $totalA;
        $rateB = $convB / $totalB;
        $pooledRate = ($convA + $convB) / ($totalA + $totalB);

        if ($pooledRate == 0 || $pooledRate == 1) return 0;

        $se = sqrt($pooledRate * (1 - $pooledRate) * (1/$totalA + 1/$totalB));
        if ($se == 0) return 0;

        $z = abs($rateB - $rateA) / $se;
        $confidence = (1 - 2 * (1 - $this->normalCDF($z))) * 100;

        return min(99.9, max(0, $confidence));
    }

    private function normalCDF(float $z): float
    {
        $a1 = 0.254829592;
        $a2 = -0.284496736;
        $a3 = 1.421413741;
        $a4 = -1.453152027;
        $a5 = 1.061405429;
        $p = 0.3275911;

        $sign = $z < 0 ? -1 : 1;
        $z = abs($z) / sqrt(2);

        $t = 1.0 / (1.0 + $p * $z);
        $y = 1.0 - (((((($a5 * $t + $a4) * $t) + $a3) * $t + $a2) * $t + $a1) * $t * exp(-$z * $z));

        return 0.5 * (1.0 + $sign * $y);
    }
}
