<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Meta\AdPreviewService;
use App\Services\Meta\AdCopyGeneratorService;
use App\Services\Meta\BudgetOptimizerService;
use App\Services\Meta\PerformanceForecastService;
use App\Services\Meta\AdPlacementRecommendationService;
use App\Services\Meta\AdScheduleOptimizerService;
use App\Services\Meta\AdLibraryService;
use App\Services\Meta\PreFlightComplianceService;
use App\Models\Meta\MetaAdCreative;
use App\Models\Meta\MetaCampaign;
use App\Models\Meta\MetaAdAccount;
use Illuminate\Http\Request;

class MetaProToolsController extends Controller
{
    public function __construct(
        private AdPreviewService $preview,
        private AdCopyGeneratorService $copyGenerator,
        private BudgetOptimizerService $budgetOptimizer,
        private PerformanceForecastService $forecast,
        private AdPlacementRecommendationService $placement,
        private AdScheduleOptimizerService $schedule,
        private AdLibraryService $adLibrary,
        private PreFlightComplianceService $compliance,
    ) {}

    public function index()
    {
        $accounts = MetaAdAccount::where('is_active', true)->get();
        $campaigns = MetaCampaign::with('adAccount')
            ->whereHas('adAccount', fn($q) => $q->where('is_active', true))
            ->latest('updated_at')
            ->take(20)
            ->get();
        $creatives = MetaAdCreative::whereIn('status', ['active', 'draft'])
            ->latest()
            ->take(20)
            ->get();

        return view('admin.meta-pro-tools.index', compact('accounts', 'campaigns', 'creatives'));
    }

    public function adPreview(MetaAdCreative $creative)
    {
        $placement = request('placement', 'feed');
        $preview = $this->preview->generatePreview($creative, $placement);
        $allPlacements = $this->preview->getAllPlacements();

        if (request()->wantsJson()) {
            return response()->json($preview);
        }

        return view('admin.meta-pro-tools.ad-preview', compact('creative', 'preview', 'allPlacements', 'placement'));
    }

    public function adPreviewAll(MetaAdCreative $creative)
    {
        $previews = $this->preview->getCreativePreview($creative);

        return response()->json($previews);
    }

    public function validateAd(Request $request)
    {
        $content = $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'description' => 'nullable|string|max:255',
            'link_url' => 'nullable|url|max:500',
        ]);

        $validation = $this->preview->validateAdContent($content);
        $compliance = $this->compliance->checkAdContent($content);

        return response()->json([
            'content_validation' => $validation,
            'compliance_check' => $compliance,
        ]);
    }

    public function copyGeneratorIndex()
    {
        $industries = $this->copyGenerator->getIndustries();
        $tones = $this->copyGenerator->getToneOptions();
        $ctas = $this->copyGenerator->getCtaOptions();
        $objectives = $this->copyGenerator->getObjectiveOptions();

        return view('admin.meta-pro-tools.copy-generator', compact('industries', 'tones', 'ctas', 'objectives'));
    }

    public function copyGeneratorGenerate(Request $request)
    {
        $request->validate([
            'industry' => 'required|string',
            'tone' => 'required|string',
            'objective' => 'required|string',
            'product_name' => 'nullable|string|max:255',
            'service_description' => 'nullable|string|max:1000',
            'audience' => 'nullable|string|max:500',
            'count' => 'required|integer|min:1|max:10',
        ]);

        $result = $this->copyGenerator->generateVariations($request->all());

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        $industries = $this->copyGenerator->getIndustries();
        $tones = $this->copyGenerator->getToneOptions();
        $ctas = $this->copyGenerator->getCtaOptions();
        $objectives = $this->copyGenerator->getObjectiveOptions();

        return view('admin.meta-pro-tools.copy-generator', compact(
            'industries', 'tones', 'ctas', 'objectives', 'result'
        ));
    }

    public function budgetOptimizerIndex()
    {
        $accounts = MetaAdAccount::where('is_active', true)->get();

        return view('admin.meta-pro-tools.budget-optimizer', compact('accounts'));
    }

    public function budgetOptimizerAnalyze(Request $request, int $accountId)
    {
        $account = MetaAdAccount::findOrFail($accountId);
        $recommendations = $this->budgetOptimizer->getAccountBudgetRecommendations($accountId);
        $distribution = $this->budgetOptimizer->getBudgetDistributionRecommendation($accountId);
        $performance = $this->budgetOptimizer->getPerformanceScore($accountId);

        if ($request->wantsJson()) {
            return response()->json(compact('recommendations', 'distribution', 'performance'));
        }

        return view('admin.meta-pro-tools.budget-optimizer', compact('account', 'recommendations', 'distribution', 'performance'));
    }

    public function performanceForecastIndex()
    {
        $campaigns = MetaCampaign::with('adAccount')
            ->whereHas('adAccount', fn($q) => $q->where('is_active', true))
            ->latest('updated_at')
            ->get();
        $accounts = MetaAdAccount::where('is_active', true)->get();

        return view('admin.meta-pro-tools.performance-forecast', compact('campaigns', 'accounts'));
    }

    public function performanceForecastCampaign(Request $request, int $campaignId)
    {
        $scenarios = $request->input('scenarios', []);
        $forecast = $this->forecast->forecastCampaignPerformance($campaignId, $scenarios);

        if ($request->wantsJson()) {
            return response()->json($forecast);
        }

        return view('admin.meta-pro-tools.performance-forecast', compact('forecast', 'campaignId'));
    }

    public function performanceForecastAccount(int $accountId)
    {
        $forecast = $this->forecast->getAccountForecast($accountId);

        return response()->json($forecast);
    }

    public function placementRecommendations()
    {
        $matrix = $this->placement->getObjectivePlacementMatrix();
        $costComparison = $this->placement->getCostComparison();
        $allPlacements = $this->placement->getAllPlacements();

        return view('admin.meta-pro-tools.placement-recommendations', compact('matrix', 'costComparison', 'allPlacements'));
    }

    public function placementForObjective(string $objective)
    {
        $placements = $this->placement->getRecommendationsForObjective($objective);
        $industry = request('industry', '');
        $industryAdvice = $industry ? $this->placement->getBestPlacementForIndustry($industry) : null;

        return response()->json([
            'objective' => $objective,
            'recommended_placements' => $placements,
            'industry_advice' => $industryAdvice,
        ]);
    }

    public function scheduleOptimizer()
    {
        $bestTimes = $this->schedule->getBestTimes();
        $weeklySchedule = $this->schedule->generateWeeklySchedule();
        $campaigns = MetaCampaign::with('adAccount')
            ->whereHas('adAccount', fn($q) => $q->where('is_active', true))
            ->latest('updated_at')
            ->take(20)
            ->get();

        return view('admin.meta-pro-tools.schedule-optimizer', compact('bestTimes', 'weeklySchedule', 'campaigns'));
    }

    public function scheduleForCampaign(int $campaignId)
    {
        $recommendations = $this->schedule->getCampaignScheduleRecommendations($campaignId);

        return response()->json($recommendations);
    }

    public function adLibraryIndex()
    {
        return view('admin.meta-pro-tools.ad-library');
    }

    public function adLibrarySearch(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:100',
            'platform' => 'nullable|string',
            'country' => 'nullable|string|max:2',
        ]);

        $params = $request->only(['limit', 'platform', 'country']);
        $results = $this->adLibrary->searchCompetitorAds($request->query, $params);

        if ($request->wantsJson()) {
            return response()->json($results);
        }

        return view('admin.meta-pro-tools.ad-library', compact('results'));
    }

    public function adLibraryByPage(Request $request)
    {
        $request->validate(['page_name' => 'required|string|min:2']);

        $results = $this->adLibrary->searchByPage($request->page_name, $request->limit ?? 20);

        return response()->json($results);
    }

    public function adLibraryIndustry(Request $request)
    {
        $request->validate(['industry' => 'required|string']);

        $results = $this->adLibrary->getCommonAdsInIndustry($request->industry, $request->limit ?? 30);

        return response()->json($results);
    }

    public function preFlightCheck(Request $request)
    {
        $content = $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'description' => 'nullable|string|max:255',
            'link_url' => 'nullable|url|max:500',
            'image_text' => 'nullable|string|max:500',
        ]);

        $complianceCheck = $this->compliance->checkAdContent($content);
        $contentValidation = $this->preview->validateAdContent($content);

        return response()->json([
            'compliance' => $complianceCheck,
            'content' => $contentValidation,
            'sanitized' => $this->compliance->sanitizeContent($content),
        ]);
    }

    public function complianceRules()
    {
        $rules = $this->compliance->getComplianceRules();

        return response()->json($rules);
    }
}
