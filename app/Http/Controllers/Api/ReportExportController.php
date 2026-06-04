<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SkinScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportExportController extends Controller
{
    public function generateQr(Request $request, string $scanId): JsonResponse
    {
        $scan = SkinScan::findOrFail($scanId);

        $payload = [
            'scan_id' => $scan->id,
            'expires_at' => now()->addHour()->timestamp,
        ];
        $token = Crypt::encryptString(json_encode($payload));
        $url = url("/report/{$token}");

        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->color(0, 0, 0)
            ->generate($url);

        return response()->json([
            'qr_svg' => $qrSvg,
            'url' => $url,
            'scan_id' => $scan->id,
            'expires_at' => now()->addHour()->toIso8601String(),
        ]);
    }

    public function viewReport(string $token): JsonResponse
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid or expired report link'], 404);
        }

        if (!isset($payload['scan_id']) || ($payload['expires_at'] ?? 0) < now()->timestamp) {
            return response()->json(['message' => 'Report link has expired'], 410);
        }

        $scan = SkinScan::with(['heatmapPoints', 'defects.products', 'generalTips'])->findOrFail($payload['scan_id']);
        $analysisData = $scan->analysis_data ?? [];

        return response()->json([
            'scan' => [
                'id' => $scan->id,
                'image_url' => $scan->image_url,
                'overall_score' => (int) ($analysisData['overall_health_score'] ?? $scan->overall_health_score),
                'confidence' => $scan->confidence_score,
                'analyzed_by' => $scan->analyzed_by_provider,
                'analyzed_at' => $scan->analyzed_at?->toISOString(),
            ],
            'metrics' => $analysisData['radar_metrics'] ?? [
                'hydration' => $scan->hydration,
                'sebum' => $scan->sebum,
                'pigmentation' => $scan->pigmentation,
                'pores' => $scan->pores,
                'elasticity' => $scan->elasticity,
            ],
            'advanced_metrics' => $analysisData['advanced_metrics'] ?? [],
            'spectral_analysis' => $analysisData['spectral_analysis'] ?? [],
            'defects' => $analysisData['defects'] ?? [],
            'heatmap_points' => $analysisData['heatmap_coordinates'] ?? [],
            'custom_arabic_analysis' => $analysisData['custom_arabic_analysis_text'] ?? '',
            'expert_free_tips' => $analysisData['expert_free_tips'] ?? [],
        ]);
    }
}
