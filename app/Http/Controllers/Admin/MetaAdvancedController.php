<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Meta\MetaAnalyticsService;
use App\Services\Meta\MetaAutomationService;
use App\Services\Meta\MetaCreativeOptimizationService;
use App\Services\Meta\MetaComplianceService;
use App\Services\Meta\MetaLeadManagementService;
use App\Services\Meta\MetaAdvancedTargetingService;
use App\Models\Meta\MetaAutomationRule;
use App\Models\Meta\MetaScheduledCampaign;
use App\Models\Meta\MetaAutomatedReport;
use App\Models\Meta\MetaComplianceLog;
use App\Models\Meta\MetaSpendingLimit;
use App\Models\Meta\MetaAdAccount;
use Illuminate\Http\Request;

class MetaAdvancedController extends Controller
{
    protected $analytics;
    protected $automation;
    protected $creative;
    protected $compliance;
    protected $leads;
    protected $targeting;

    public function __construct(
        MetaAnalyticsService $analytics,
        MetaAutomationService $automation,
        MetaCreativeOptimizationService $creative,
        MetaComplianceService $compliance,
        MetaLeadManagementService $leads,
        MetaAdvancedTargetingService $targeting
    ) {
        $this->analytics = $analytics;
        $this->automation = $automation;
        $this->creative = $creative;
        $this->compliance = $compliance;
        $this->leads = $leads;
        $this->targeting = $targeting;
    }

    public function dashboard()
    {
        $accounts = MetaAdAccount::where('is_active', true)->get();
        
        $summary = [
            'automation_rules' => MetaAutomationRule::where('status', 'active')->count(),
            'scheduled_actions' => MetaScheduledCampaign::where('status', 'pending')->count(),
            'compliance_issues' => MetaComplianceLog::where('status', 'open')->count(),
            'active_reports' => MetaAutomatedReport::where('status', 'active')->count(),
        ];

        return view('admin.meta-advanced.dashboard', compact('accounts', 'summary'));
    }

    public function analyticsIndex(Request $request)
    {
        $campaignId = $request->input('campaign_id');
        $days = $request->input('days', 30);

        $funnel = $this->analytics->getFunnelData($campaignId, $days);
        $attribution = $this->analytics->getAttributionReport($campaignId, $days);
        $topCampaigns = $this->analytics->getTopPerformingCampaigns($days);

        $comparisons = [
            'purchases' => $this->analytics->getDateComparison('purchases', 7, 7),
            'revenue' => $this->analytics->getDateComparison('revenue', 7, 7),
            'clicks' => $this->analytics->getDateComparison('clicks', 7, 7),
        ];

        return view('admin.meta-advanced.analytics', compact(
            'funnel', 'attribution', 'topCampaigns', 'comparisons', 'days', 'campaignId'
        ));
    }

    public function automationIndex()
    {
        $rules = MetaAutomationRule::with('adAccount')->orderByDesc('created_at')->get();
        $scheduled = MetaScheduledCampaign::with('campaign')
            ->where('status', 'pending')
            ->orderBy('scheduled_at')
            ->get();

        return view('admin.meta-advanced.automation', compact('rules', 'scheduled'));
    }

    public function createAutomationRule(Request $request)
    {
        $validated = $request->validate([
            'ad_account_id' => 'required|exists:meta_ad_accounts,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:auto_pause,budget_scale,bid_adjust,schedule,alert',
            'conditions' => 'required|array',
            'actions' => 'required|array',
            'scope' => 'required|in:all_campaigns,specific_campaigns',
            'campaign_ids' => 'nullable|array',
        ]);

        $rule = $this->automation->createAutomationRule($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء القاعدة بنجاح',
            'data' => $rule,
        ]);
    }

    public function updateAutomationRule(Request $request, $id)
    {
        $rule = MetaAutomationRule::findOrFail($id);
        $rule->update($request->only(['name', 'status', 'conditions', 'actions']));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث القاعدة بنجاح',
        ]);
    }

    public function deleteAutomationRule($id)
    {
        MetaAutomationRule::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف القاعدة بنجاح',
        ]);
    }

    public function executeAutomationRules()
    {
        $results = $this->automation->executeRules();

        return response()->json([
            'success' => true,
            'message' => 'تم تنفيذ القواعد بنجاح',
            'results' => $results,
        ]);
    }

    public function scheduleCampaignAction(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:meta_campaigns,id',
            'action' => 'required|in:activate,pause,budget_change,bid_change',
            'scheduled_at' => 'required|date|after:now',
            'parameters' => 'nullable|array',
        ]);

        $validated['created_by'] = auth()->id();
        $scheduled = $this->automation->scheduleCampaignAction($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم جدولة الإجراء بنجاح',
            'data' => $scheduled,
        ]);
    }

    public function cancelScheduledAction($id)
    {
        $scheduled = MetaScheduledCampaign::findOrFail($id);
        $scheduled->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الإجراء المجدول',
        ]);
    }

    public function creativeIndex(Request $request)
    {
        $accountId = $request->input('account_id');
        $threshold = $request->input('threshold', 'warning');

        $fatiguedCreatives = $this->creative->getFatiguedCreatives($accountId, $threshold);

        return view('admin.meta-advanced.creative', compact('fatiguedCreatives', 'accountId', 'threshold'));
    }

    public function analyzeCreativeFatigue($creativeId)
    {
        $data = $this->creative->analyzeCreativeFatigue($creativeId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getCreativeSuggestions($creativeId)
    {
        $suggestions = $this->creative->getCreativeSuggestions($creativeId);

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    public function compareCreatives(Request $request)
    {
        $creativeIds = $request->input('creative_ids', []);
        $comparison = $this->creative->compareCreatives($creativeIds);

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }

    public function complianceIndex(Request $request)
    {
        $accountId = $request->input('account_id');
        
        $issues = MetaComplianceLog::with(['campaign', 'ad'])
            ->when($accountId, fn($q) => $q->where('ad_account_id', $accountId))
            ->orderByDesc('created_at')
            ->paginate(20);

        $summary = $accountId 
            ? $this->compliance->getComplianceSummary($accountId)
            : null;

        $spendingLimits = MetaSpendingLimit::when($accountId, fn($q) => $q->where('ad_account_id', $accountId))
            ->get();

        return view('admin.meta-advanced.compliance', compact('issues', 'summary', 'spendingLimits', 'accountId'));
    }

    public function resolveComplianceIssue(Request $request, $id)
    {
        $validated = $request->validate([
            'resolution_notes' => 'nullable|string',
        ]);

        $log = $this->compliance->resolveIssue($id, $validated['resolution_notes'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'تم حل المشكلة بنجاح',
            'data' => $log,
        ]);
    }

    public function checkAccountHealth($accountId)
    {
        $health = $this->compliance->checkAccountHealth($accountId);

        return response()->json([
            'success' => true,
            'data' => $health,
        ]);
    }

    public function createSpendingLimit(Request $request)
    {
        $validated = $request->validate([
            'ad_account_id' => 'required|exists:meta_ad_accounts,id',
            'scope' => 'required|in:account,campaign,ad_set',
            'entity_id' => 'nullable|integer',
            'daily_limit' => 'nullable|numeric|min:0',
            'lifetime_limit' => 'nullable|numeric|min:0',
            'alert_threshold' => 'numeric|min:0|max:100',
            'action_on_limit' => 'required|in:pause,alert_only,reduce_budget',
        ]);

        $limit = $this->compliance->createSpendingLimit($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء حد الإنفاق بنجاح',
            'data' => $limit,
        ]);
    }

    public function checkSpendingLimits()
    {
        $results = $this->compliance->checkSpendingLimits();

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    public function leadsIndex(Request $request)
    {
        $campaignId = $request->input('campaign_id');
        $days = $request->input('days', 30);

        $stats = $this->leads->getConversionStats($campaignId, $days);
        $funnel = $this->leads->getLeadFunnel($days);
        $topCampaigns = $this->leads->getTopConvertingCampaigns($days);

        return view('admin.meta-advanced.leads', compact('stats', 'funnel', 'topCampaigns', 'days', 'campaignId'));
    }

    public function trackLeadConversion(Request $request, $leadId)
    {
        $validated = $request->validate([
            'conversion_type' => 'required|in:booking,purchase,signup,call,form_submit',
            'value' => 'nullable|numeric|min:0',
            'campaign_id' => 'nullable|exists:meta_campaigns,id',
            'booking_id' => 'nullable|integer',
            'order_id' => 'nullable|integer',
        ]);

        $conversion = $this->leads->trackConversion($leadId, $validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تتبع التحويل بنجاح',
            'data' => $conversion,
        ]);
    }

    public function autoScoreLeads()
    {
        $results = $this->leads->autoScoreAllLeads();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث نقاط العملاء المحتملين',
            'results' => $results,
        ]);
    }

    public function targetingIndex()
    {
        $accounts = MetaAdAccount::where('is_active', true)->get();

        return view('admin.meta-advanced.targeting', compact('accounts'));
    }

    public function createLookalikeAudience(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:meta_ad_accounts,id',
            'source_audience_id' => 'required|string',
            'country' => 'string|max:2',
            'percentage' => 'numeric|min:1|max:10',
        ]);

        try {
            $audience = $this->targeting->createLookalikeAudience(
                $validated['account_id'],
                $validated['source_audience_id'],
                $validated['country'] ?? 'PS',
                $validated['percentage'] ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الجمهور المشابه بنجاح',
                'data' => $audience,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function createRetargetingAudience(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:meta_ad_accounts,id',
            'name' => 'required|string|max:255',
            'page_visitors' => 'nullable|string',
            'cart_abandoners' => 'nullable|boolean',
            'time_on_site' => 'nullable|integer|min:10',
            'retention_days' => 'integer|min:1|max:180',
        ]);

        try {
            $audience = $this->targeting->buildRetargetingAudience(
                $validated['account_id'],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء جمهور إعادة الاستهداف بنجاح',
                'data' => $audience,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getAudienceSuggestions($campaignId)
    {
        $accountId = request()->input('account_id');
        $suggestions = $this->targeting->suggestAudienceExpansions($accountId, $campaignId);

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    public function reportsIndex()
    {
        $reports = MetaAutomatedReport::orderByDesc('created_at')->get();

        return view('admin.meta-advanced.reports', compact('reports'));
    }

    public function createAutomatedReport(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:daily,weekly,monthly,custom',
            'metrics' => 'required|array',
            'filters' => 'nullable|array',
            'recipients' => 'required|array',
            'format' => 'required|in:email,pdf,csv,excel',
            'send_time' => 'nullable|date_format:H:i',
        ]);

        $validated['created_by'] = auth()->id();
        $report = MetaAutomatedReport::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء التقرير الآلي بنجاح',
            'data' => $report,
        ]);
    }

    public function generateReport($reportId)
    {
        $data = $this->analytics->generateReport($reportId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function deleteAutomatedReport($id)
    {
        MetaAutomatedReport::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التقرير بنجاح',
        ]);
    }
}
