<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Google\GoogleAdsCampaignService;
use App\Models\MarketingSetting;
use Illuminate\Http\Request;

class GoogleAdsController extends Controller
{
    public function __construct(
        private GoogleAdsCampaignService $googleAds,
    ) {}

    public function index(Request $request)
    {
        $campaigns = [];
        if ($this->googleAds->isEnabled()) {
            $campaigns = $this->googleAds->getCampaigns();
        }

        $connectionStatus = $this->googleAds->isEnabled();

        return view('admin.google-ads.index', compact('campaigns', 'connectionStatus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'budget_amount' => 'required|numeric|min:1',
            'bidding_strategy' => 'required|in:MAXIMIZE_CONVERSIONS,TARGET_CPA,MANUAL_CPC',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $campaignId = $this->googleAds->createCampaign([
            'name' => $request->name,
            'budget_amount' => $request->budget_amount,
            'budget_currency' => 'ILS',
            'bidding_strategy' => $request->bidding_strategy,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        if ($campaignId) {
            return redirect()->route('admin.google-ads.index')
                ->with('success', 'تم إنشاء الحملة بنجاح');
        }

        return redirect()->back()->with('error', 'فشل إنشاء الحملة');
    }

    public function toggle($campaignId)
    {
        $campaign = $this->googleAds->getCampaign($campaignId);

        if (!$campaign) {
            return response()->json(['success' => false, 'message' => 'الحملة غير موجودة']);
        }

        $newStatus = $campaign['status'] === 'ENABLED' ? 'PAUSED' : 'ENABLED';
        $success = $this->googleAds->updateCampaign($campaignId, ['status' => $newStatus]);

        return response()->json([
            'success' => $success,
            'message' => $success ? "تم {$newStatus} الحملة" : 'فشل تحديث الحملة',
            'status' => $newStatus,
        ]);
    }

    public function update(Request $request, $campaignId)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'budget_amount' => 'sometimes|numeric|min:1',
        ]);

        $data = [];
        if ($request->has('name')) $data['name'] = $request->name;
        if ($request->has('budget_amount')) $data['budget_amount'] = $request->budget_amount;

        $success = $this->googleAds->updateCampaign($campaignId, $data);

        if ($success) {
            return redirect()->route('admin.google-ads.index')
                ->with('success', 'تم تحديث الحملة بنجاح');
        }

        return redirect()->back()->with('error', 'فشل تحديث الحملة');
    }

    public function destroy($campaignId)
    {
        $success = $this->googleAds->removeCampaign($campaignId);

        if ($success) {
            return redirect()->route('admin.google-ads.index')
                ->with('success', 'تم حذف الحملة بنجاح');
        }

        return redirect()->back()->with('error', 'فشل حذف الحملة');
    }

    public function insights(Request $request, $campaignId)
    {
        $dateRange = $request->input('date_range', 'last_30d');
        $metrics = $this->googleAds->getCampaignMetrics($campaignId, $dateRange);

        return response()->json($metrics ?? []);
    }

    public function adGroups($campaignId)
    {
        $adGroups = $this->googleAds->getAdGroups($campaignId);

        return response()->json($adGroups);
    }

    public function createAdGroup(Request $request, $campaignId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cpc_bid' => 'nullable|numeric|min:0',
        ]);

        $adGroupId = $this->googleAds->createAdGroup($campaignId, [
            'name' => $request->name,
            'cpc_bid' => $request->cpc_bid,
        ]);

        if ($adGroupId) {
            return response()->json(['success' => true, 'ad_group_id' => $adGroupId, 'message' => 'تم إنشاء المجموعة الإعلانية بنجاح']);
        }

        return response()->json(['success' => false, 'message' => 'فشل إنشاء المجموعة الإعلانية']);
    }

    public function keywords($adGroupId)
    {
        $keywords = $this->googleAds->getKeywords($adGroupId);

        return response()->json($keywords);
    }

    public function addKeyword(Request $request, $adGroupId)
    {
        $request->validate([
            'text' => 'required|string|max:80',
            'match_type' => 'required|in:BROAD,PHRASE,EXACT',
        ]);

        $success = $this->googleAds->addKeyword($adGroupId, [
            'text' => $request->text,
            'match_type' => $request->match_type,
        ]);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'تمت إضافة الكلمة المفتاحية بنجاح' : 'فشل إضافة الكلمة المفتاحية',
        ]);
    }

    public function createResponsiveAd(Request $request, $adGroupId)
    {
        $request->validate([
            'headlines' => 'required|array|min:3|max:15',
            'headlines.*' => 'string|max:30',
            'descriptions' => 'required|array|min:2|max:4',
            'descriptions.*' => 'string|max:90',
            'final_url' => 'required|url',
        ]);

        $adId = $this->googleAds->createResponsiveSearchAd($adGroupId, [
            'headlines' => $request->headlines,
            'descriptions' => $request->descriptions,
            'final_url' => $request->final_url,
        ]);

        if ($adId) {
            return response()->json(['success' => true, 'ad_id' => $adId, 'message' => 'تم إنشاء الإعلان بنجاح']);
        }

        return response()->json(['success' => false, 'message' => 'فشل إنشاء الإعلان']);
    }

    public function testConnection()
    {
        return response()->json($this->googleAds->testConnection());
    }

    public function getMetrics(Request $request)
    {
        $dateRange = $request->input('date_range', 'last_30d');
        $campaigns = $this->googleAds->getCampaigns();

        $metrics = [];
        foreach ($campaigns as $campaign) {
            $campaignMetrics = $this->googleAds->getCampaignMetrics($campaign['campaign_id'], $dateRange);
            if ($campaignMetrics) {
                $metrics[] = array_merge($campaign, $campaignMetrics);
            }
        }

        return response()->json($metrics);
    }
}
