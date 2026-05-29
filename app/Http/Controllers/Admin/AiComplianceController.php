<?php

namespace App\Http\Controllers\Admin;

use App\Models\TriggerWord;
use App\Models\CapiEventLog;
use App\Services\AdAccountHealthService;
use App\Services\AISanitizerService;
use App\Services\Sanitization\SanitizationPipeline;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AiComplianceController extends Controller
{
    public function __construct(
        private AdAccountHealthService $healthService,
        private AISanitizerService $aiSanitizer,
        private SanitizationPipeline $pipeline,
    ) {}

    public function index()
    {
        $healthScores = $this->healthService->getAllScores();
        $aiProviders = $this->aiSanitizer->getAvailableProviders();
        $triggerWordCount = TriggerWord::count();
        $activeTriggerWordCount = TriggerWord::where('active', true)->count();

        $sanitizationLogs = CapiEventLog::where('response', 'LIKE', '%sanitized%')
            ->orWhere('response', 'LIKE', '%_sanitized%')
            ->latest()
            ->take(20)
            ->get();

        $recentBlocks = CapiEventLog::where('error_message', 'LIKE', '%blocked%')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.ai-compliance.index', compact(
            'healthScores', 'aiProviders', 'triggerWordCount',
            'activeTriggerWordCount', 'sanitizationLogs', 'recentBlocks'
        ));
    }

    public function refreshHealth()
    {
        $scores = $this->healthService->getAllScores();
        return response()->json(['scores' => $scores]);
    }

    public function testSanitization(Request $request)
    {
        $text = $request->input('text', '');
        $platform = $request->input('platform', 'facebook');

        $result = $this->pipeline->process(
            ['data' => ['description' => $text]],
            ['platform' => $platform]
        );

        return response()->json([
            'original' => $text,
            'sanitized' => $result['data']['description'] ?? $text,
            'blocked' => $result['_blocked'] ?? false,
            'reason' => $result['_block_reason'] ?? null,
            'log' => $result['_sanitization_log'] ?? [],
        ]);
    }
}
