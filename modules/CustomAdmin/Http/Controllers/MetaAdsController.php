<?php

namespace Modules\CustomAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Meta\MetaAdAccount;
use App\Models\Meta\MetaCampaign;
use App\Models\Meta\MetaAdSet;
use App\Models\Meta\MetaAdCreative;
use App\Models\Meta\MetaAd;
use App\Services\Meta\FacebookGraphService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MetaAdsController extends Controller
{
    public function __construct(
        private FacebookGraphService $graph,
    ) {}

    public function dashboard()
    {
        $accounts = MetaAdAccount::where('is_active', true)->get();
        $campaigns = MetaCampaign::with('adAccount')->latest('updated_at')->take(50)->get();
        $tokenManager = app(\App\Services\TokenManagerService::class);

        return view('admin.ads.index', [
            'accounts' => $accounts,
            'campaigns' => $campaigns,
            'creatives' => MetaAdCreative::where('status', 'active')->take(20)->get(),
            'pages' => \Modules\Meta\Models\MetaLead::selectRaw('DISTINCT source_campaign')->get()->pluck('source_campaign'),
            'activeCount' => $campaigns->where('status', 'ACTIVE')->count(),
            'pausedCount' => $campaigns->where('status', 'PAUSED')->count(),
            'totalCampaigns' => $campaigns->count(),
            'platforms' => $this->getPlatformConnectionStatus($tokenManager),
            'connectedCount' => $accounts->count(),
            'autoSync' => $accounts->isNotEmpty() && $campaigns->isEmpty() && $tokenManager->isConnected('meta'),
        ]);
    }

    public function connectAccount(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        $token = $request->input('access_token');

        $debug = $this->graph->debugToken($token);
        if (!$debug || ($debug['type'] ?? '') !== 'USER') {
            return response()->json([
                'success' => false,
                'message' => 'رمز الوصول غير صالح أو منتهي الصلاحية',
            ], 400);
        }

        $users = $this->graph->getAdAccounts($token);
        if (empty($users)) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على حسابات إعلانية لهذا المستخدم',
            ], 404);
        }

        $imported = 0;
        foreach ($users as $acc) {
            $adAccountId = str_replace('act_', '', $acc['id'] ?? $acc['account_id'] ?? '');

            MetaAdAccount::updateOrCreate(
                ['ad_account_id' => $adAccountId],
                [
                    'name' => $acc['name'] ?? 'Unnamed',
                    'currency' => $acc['currency'] ?? 'ILS',
                    'timezone' => $acc['timezone_name'] ?? 'Asia/Jerusalem',
                    'access_token' => $token,
                    'business_id' => $acc['business_id'] ?? null,
                    'spend_cap' => ($acc['spend_cap'] ?? 0) / 100,
                    'amount_spent' => ($acc['amount_spent'] ?? 0) / 100,
                    'account_status' => $this->mapAccountStatus($acc['account_status'] ?? 1),
                    'is_active' => true,
                    'last_synced_at' => now(),
                ]
            );
            $imported++;
        }

        return response()->json([
            'success' => true,
            'message' => "تم ربط {$imported} حساب (حسابات) إعلانية بنجاح",
            'count' => $imported,
        ]);
    }

    public function deleteAdAccount($id)
    {
        $account = MetaAdAccount::findOrFail($id);

        MetaCampaign::where('ad_account_id', $account->id)->delete();
        MetaAdSet::where('ad_account_id', $account->id)->delete();
        MetaAdCreative::where('ad_account_id', $account->id)->delete();
        $account->delete();

        return response()->json(['success' => true]);
    }

    public function createCampaign(Request $request)
    {
        $data = $request->validate([
            'ad_account_id' => 'required|exists:meta_ad_accounts,id',
            'name' => 'required|string|max:255',
            'objective' => 'required|string|in:OUTCOME_AWARENESS,OUTCOME_TRAFFIC,OUTCOME_ENGAGEMENT,OUTCOME_LEADS,OUTCOME_SALES,OUTCOME_APP_PROMOTION',
            'status' => 'string|in:ACTIVE,PAUSED',
            'daily_budget' => 'nullable|numeric|min:1',
            'lifetime_budget' => 'nullable|numeric|min:1',
            'bid_strategy' => 'string|in:LOWEST_COST_WITHOUT_CAP,LOWEST_COST_WITH_BID_CAP,COST_CAP,TARGET_COST',
            'start_time' => 'nullable|date',
        ]);

        $account = MetaAdAccount::findOrFail($data['ad_account_id']);
        $this->graph->setUserAccessToken($account->access_token);

        $response = $this->graph->createCampaign(
            $account->ad_account_id,
            $data
        );

        if (!empty($response['error'])) {
            return response()->json([
                'success' => false,
                'message' => $response['error']['message'] ?? 'فشل إنشاء الحملة في فيسبوك',
            ], 400);
        }

        $campaign = MetaCampaign::create([
            'ad_account_id' => $account->id,
            'campaign_id' => $response['id'],
            'name' => $data['name'],
            'objective' => $data['objective'],
            'status' => $data['status'] ?? 'PAUSED',
            'buying_type' => 'AUCTION',
            'daily_budget' => $data['daily_budget'] ?? null,
            'lifetime_budget' => $data['lifetime_budget'] ?? null,
            'bid_strategy' => $data['bid_strategy'] ?? 'LOWEST_COST_WITHOUT_CAP',
            'start_time' => $data['start_time'] ?? null,
            'last_synced_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الحملة بنجاح',
            'campaign' => $campaign,
        ]);
    }

    public function toggleCampaign(Request $request, $id)
    {
        $campaign = MetaCampaign::findOrFail($id);
        $newStatus = $campaign->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';

        $account = $campaign->adAccount;
        if ($account) {
            $this->graph->setUserAccessToken($account->access_token);
            $this->graph->updateCampaignStatus($campaign->campaign_id, $newStatus);
        }

        $campaign->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status' => $newStatus,
        ]);
    }

    public function deleteCampaign($id)
    {
        $campaign = MetaCampaign::findOrFail($id);

        $account = $campaign->adAccount;
        if ($account) {
            $this->graph->setUserAccessToken($account->access_token);
            $this->graph->deleteCampaign($campaign->campaign_id);
        }

        $campaign->delete();

        return response()->json(['success' => true]);
    }

    public function getInsights(Request $request, $id)
    {
        $campaign = MetaCampaign::findOrFail($id);

        $cacheKey = "meta_insights_{$campaign->campaign_id}";
        $ttl = config('meta.insights_cache_ttl', 900);

        $insights = Cache::remember($cacheKey, $ttl, function () use ($campaign) {
            $account = $campaign->adAccount;
            if (!$account) return [];

            $this->graph->setUserAccessToken($account->access_token);
            return $this->graph->getInsights($campaign->campaign_id, 'campaign', [
                'date_preset' => $request->get('date_preset', 'last_30d'),
            ]);
        });

        $campaign->update(['insights' => $insights, 'last_synced_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $insights,
        ]);
    }

    public function createAdSet(Request $request)
    {
        $data = $request->validate([
            'campaign_id' => 'required|exists:meta_campaigns,id',
            'name' => 'required|string|max:255',
            'optimization_goal' => 'required|string',
            'billing_event' => 'string|in:IMPRESSIONS,CLICKS,APP_INSTALLS,THRUPLAYS',
            'daily_budget' => 'nullable|numeric|min:1',
            'lifetime_budget' => 'nullable|numeric|min:1',
            'bid_amount' => 'nullable|numeric|min:0.01',
            'targeting' => 'nullable|json',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'promoted_object' => 'nullable|string',
        ]);

        $campaign = MetaCampaign::findOrFail($data['campaign_id']);
        $account = $campaign->adAccount;

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'حساب الإعلانات غير موجود',
            ], 400);
        }

        $this->graph->setUserAccessToken($account->access_token);

        $targeting = null;
        if (!empty($data['targeting'])) {
            $targeting = is_string($data['targeting']) ? json_decode($data['targeting'], true) : $data['targeting'];
        }

        $response = $this->graph->createAdSet($account->ad_account_id, [
            'name' => $data['name'],
            'campaign_id' => $campaign->campaign_id,
            'optimization_goal' => $data['optimization_goal'],
            'billing_event' => $data['billing_event'] ?? 'IMPRESSIONS',
            'daily_budget' => $data['daily_budget'] ?? null,
            'lifetime_budget' => $data['lifetime_budget'] ?? null,
            'bid_amount' => $data['bid_amount'] ?? null,
            'targeting' => $targeting,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'promoted_object' => $data['promoted_object'] ?? null,
        ]);

        if (!empty($response['error'])) {
            return response()->json([
                'success' => false,
                'message' => $response['error']['message'] ?? 'فشل إنشاء مجموعة إعلانية',
            ], 400);
        }

        $adSet = MetaAdSet::create([
            'campaign_id' => $campaign->id,
            'ad_account_id' => $account->id,
            'ad_set_id' => $response['id'],
            'name' => $data['name'],
            'status' => 'PAUSED',
            'optimization_goal' => $data['optimization_goal'],
            'billing_event' => $data['billing_event'] ?? 'IMPRESSIONS',
            'daily_budget' => $data['daily_budget'] ?? null,
            'lifetime_budget' => $data['lifetime_budget'] ?? null,
            'bid_amount' => $data['bid_amount'] ?? null,
            'targeting' => $targeting,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'promoted_object' => $data['promoted_object'] ?? null,
            'last_synced_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء مجموعة الإعلانات',
            'ad_set' => $adSet,
        ]);
    }

    public function toggleAdSet(Request $request, $id)
    {
        $adSet = MetaAdSet::findOrFail($id);
        $newStatus = $adSet->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';

        $account = $adSet->adAccount;
        if ($account) {
            $this->graph->setUserAccessToken($account->access_token);
            $this->graph->updateAdSetStatus($adSet->ad_set_id, $newStatus);
        }

        $adSet->update(['status' => $newStatus]);

        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    public function uploadCreative(Request $request)
    {
        $data = $request->validate([
            'ad_account_id' => 'required|exists:meta_ad_accounts,id',
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:40',
            'body' => 'nullable|string',
            'description' => 'nullable|string|max:255',
            'link_url' => 'nullable|url|max:500',
            'display_link' => 'nullable|string|max:500',
            'call_to_action' => 'nullable|string',
            'page_id' => 'nullable|string',
            'instagram_actor_id' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $account = MetaAdAccount::findOrFail($data['ad_account_id']);
        $this->graph->setUserAccessToken($account->access_token);

        $imageHash = null;
        if ($request->hasFile('image')) {
            $uploaded = $this->graph->uploadImage(
                $account->ad_account_id,
                $request->file('image')->getPathname(),
                $request->file('image')->getClientOriginalName()
            );
            if ($uploaded && !empty($uploaded['images'])) {
                $imageHash = array_key_first($uploaded['images']);
            }
        }

        $creative = MetaAdCreative::create([
            'ad_account_id' => $account->id,
            'name' => $data['name'],
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'description' => $data['description'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'display_link' => $data['display_link'] ?? null,
            'call_to_action' => $data['call_to_action'] ?? null,
            'page_id' => $data['page_id'] ?? null,
            'instagram_actor_id' => $data['instagram_actor_id'] ?? null,
            'image_hash' => $imageHash,
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الإعلان الإبداعي',
            'creative' => $creative,
        ]);
    }

    public function saveCreative(Request $request)
    {
        $data = $request->validate([
            'creative_id' => 'required|exists:meta_ad_creatives,id',
        ]);

        $creative = MetaAdCreative::findOrFail($data['creative_id']);
        $account = $creative->adAccount;

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'حساب الإعلانات غير موجود'], 400);
        }

        $this->graph->setUserAccessToken($account->access_token);

        $creativeData = [
            'name' => $creative->name,
            'object_story_spec' => [
                'page_id' => $creative->page_id,
                'link_data' => [
                    'link' => $creative->link_url,
                    'message' => $creative->body,
                    'name' => $creative->title,
                    'description' => $creative->description,
                    'call_to_action' => ['type' => $creative->call_to_action ?? 'LEARN_MORE'],
                ],
            ],
        ];

        if ($creative->image_hash) {
            $creativeData['object_story_spec']['link_data']['image_hash'] = $creative->image_hash;
        }

        $response = $this->graph->createCreative($account->ad_account_id, $creativeData);

        if (!empty($response['error'])) {
            return response()->json([
                'success' => false,
                'message' => $response['error']['message'] ?? 'فشل إنشاء الإعلان الإبداعي في فيسبوك',
            ], 400);
        }

        $creative->update([
            'creative_id' => $response['id'],
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم نشر الإعلان الإبداعي على فيسبوك',
            'creative' => $creative,
        ]);
    }

    public function createAd(Request $request)
    {
        $data = $request->validate([
            'ad_set_id' => 'required|exists:meta_ad_sets,id',
            'creative_id' => 'required|exists:meta_ad_creatives,id',
            'name' => 'required|string|max:255',
            'status' => 'string|in:ACTIVE,PAUSED',
        ]);

        $adSet = MetaAdSet::findOrFail($data['ad_set_id']);
        $account = $adSet->adAccount;
        $creative = MetaAdCreative::findOrFail($data['creative_id']);

        if (!$account || !$creative->creative_id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إنشاء الإعلان: تأكد من نشر الإعلان الإبداعي',
            ], 400);
        }

        $this->graph->setUserAccessToken($account->access_token);

        $response = $this->graph->createAd($account->ad_account_id, [
            'name' => $data['name'],
            'adset_id' => $adSet->ad_set_id,
            'creative' => ['creative_id' => $creative->creative_id],
            'status' => $data['status'] ?? 'PAUSED',
        ]);

        if (!empty($response['error'])) {
            return response()->json([
                'success' => false,
                'message' => $response['error']['message'] ?? 'فشل إنشاء الإعلان',
            ], 400);
        }

        $ad = MetaAd::create([
            'ad_set_id' => $adSet->id,
            'creative_id' => $creative->id,
            'ad_account_id' => $account->id,
            'ad_id' => $response['id'],
            'name' => $data['name'],
            'status' => $data['status'] ?? 'PAUSED',
            'last_synced_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الإعلان بنجاح',
            'ad' => $ad,
        ]);
    }

    public function toggleAd(Request $request, $id)
    {
        $ad = MetaAd::findOrFail($id);
        $newStatus = $ad->status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';

        $account = $ad->adAccount;
        if ($account && $ad->ad_id) {
            $this->graph->setUserAccessToken($account->access_token);
            $this->graph->updateAdStatus($ad->ad_id, $newStatus);
        }

        $ad->update(['status' => $newStatus]);

        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    public function refreshInsights(Request $request)
    {
        $accounts = MetaAdAccount::where('is_active', true)->get();
        $count = 0;

        foreach ($accounts as $account) {
            $this->graph->setUserAccessToken($account->access_token);

            $campaigns = $this->graph->getCampaigns($account->ad_account_id);
            foreach ($campaigns as $fbCamp) {
                MetaCampaign::updateOrCreate(
                    ['campaign_id' => $fbCamp['id']],
                    [
                        'ad_account_id' => $account->id,
                        'name' => $fbCamp['name'],
                        'objective' => $fbCamp['objective'] ?? '',
                        'status' => $fbCamp['status'] ?? 'PAUSED',
                        'buying_type' => $fbCamp['buying_type'] ?? 'AUCTION',
                        'daily_budget' => isset($fbCamp['daily_budget']) ? (int) $fbCamp['daily_budget'] / 100 : null,
                        'lifetime_budget' => isset($fbCamp['lifetime_budget']) ? (int) $fbCamp['lifetime_budget'] / 100 : null,
                        'bid_strategy' => $fbCamp['bid_strategy'] ?? 'LOWEST_COST_WITHOUT_CAP',
                        'start_time' => $fbCamp['start_time'] ?? null,
                        'stop_time' => $fbCamp['stop_time'] ?? null,
                        'last_synced_at' => now(),
                    ]
                );
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "تم تحديث {$count} حملة",
            'count' => $count,
        ]);
    }

    public function syncCampaigns(Request $request)
    {
        return $this->refreshInsights($request);
    }

    // ──────────────────────────────────────────────
    //  New: Get Ad Sets for a Campaign
    // ──────────────────────────────────────────────
    public function getCampaignAdSets($id)
    {
        $campaign = MetaCampaign::with('adSets.adAccount')->findOrFail($id);
        $adSets = $campaign->adSets;

        return response()->json([
            'success' => true,
            'data' => $adSets->map(fn($s) => [
                'id' => $s->id,
                'ad_set_id' => $s->ad_set_id,
                'name' => $s->name,
                'status' => $s->status,
                'optimization_goal' => $s->optimization_goal,
                'daily_budget' => $s->daily_budget,
                'lifetime_budget' => $s->lifetime_budget,
                'bid_amount' => $s->bid_amount,
                'billing_event' => $s->billing_event,
                'start_time' => $s->start_time?->toIso8601String(),
                'end_time' => $s->end_time?->toIso8601String(),
                'promoted_object' => $s->promoted_object,
                'targeting' => $s->targeting,
                'insights' => $s->insights,
                'ads_count' => $s->ads()->count(),
                'last_synced_at' => $s->last_synced_at?->diffForHumans(),
            ]),
        ]);
    }

    // ──────────────────────────────────────────────
    //  New: Get Ads for an Ad Set
    // ──────────────────────────────────────────────
    public function getAdSetAds($id)
    {
        $adSet = MetaAdSet::with('ads.creative', 'ads.adAccount')->findOrFail($id);
        $ads = $adSet->ads;

        return response()->json([
            'success' => true,
            'data' => $ads->map(fn($a) => [
                'id' => $a->id,
                'ad_id' => $a->ad_id,
                'name' => $a->name,
                'status' => $a->status,
                'creative_name' => $a->creative?->name,
                'creative_id' => $a->creative_id,
                'insights' => $a->insights,
                'tracking_specs' => $a->tracking_specs,
                'last_synced_at' => $a->last_synced_at?->diffForHumans(),
            ]),
        ]);
    }

    // ──────────────────────────────────────────────
    //  New: List all Creatives
    // ──────────────────────────────────────────────
    public function getCreatives()
    {
        $creatives = MetaAdCreative::with('adAccount')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $creatives->map(fn($c) => [
                'id' => $c->id,
                'creative_id' => $c->creative_id,
                'name' => $c->name,
                'title' => $c->title,
                'body' => $c->body,
                'description' => $c->description,
                'image_hash' => $c->image_hash,
                'image_url' => $c->image_url,
                'link_url' => $c->link_url,
                'call_to_action' => $c->call_to_action,
                'page_id' => $c->page_id,
                'status' => $c->status,
                'account_name' => $c->adAccount?->name,
            ]),
            'current_page' => $creatives->currentPage(),
            'last_page' => $creatives->lastPage(),
            'total' => $creatives->total(),
        ]);
    }

    // ──────────────────────────────────────────────
    //  New: Update Campaign
    // ──────────────────────────────────────────────
    public function updateCampaign(Request $request, $id)
    {
        $campaign = MetaCampaign::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:ACTIVE,PAUSED',
            'daily_budget' => 'nullable|numeric|min:1',
            'lifetime_budget' => 'nullable|numeric|min:1',
            'bid_strategy' => 'sometimes|string|in:LOWEST_COST_WITHOUT_CAP,LOWEST_COST_WITH_BID_CAP,COST_CAP,TARGET_COST',
            'start_time' => 'nullable|date',
        ]);

        $account = $campaign->adAccount;
        if ($account) {
            $this->graph->setUserAccessToken($account->access_token);

            $fbPayload = [];
            if (isset($data['name'])) $fbPayload['name'] = $data['name'];
            if (isset($data['status'])) $fbPayload['status'] = $data['status'];
            if (array_key_exists('daily_budget', $data)) {
                $fbPayload['daily_budget'] = $data['daily_budget'] ? (int) ($data['daily_budget'] * 100) : null;
            }
            if (array_key_exists('lifetime_budget', $data)) {
                $fbPayload['lifetime_budget'] = $data['lifetime_budget'] ? (int) ($data['lifetime_budget'] * 100) : null;
            }
            if (isset($data['bid_strategy'])) $fbPayload['bid_strategy'] = $data['bid_strategy'];
            if (array_key_exists('start_time', $data)) $fbPayload['start_time'] = $data['start_time'];

            if (!empty($fbPayload)) {
                $response = $this->graph->post($campaign->campaign_id, $fbPayload);
                if (!empty($response['error'])) {
                    return response()->json([
                        'success' => false,
                        'message' => $response['error']['message'] ?? 'فشل تحديث الحملة في فيسبوك',
                    ], 400);
                }
            }
        }

        $campaign->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الحملة بنجاح',
            'campaign' => $campaign->fresh(),
        ]);
    }

    // ──────────────────────────────────────────────
    //  New: Update Ad Set
    // ──────────────────────────────────────────────
    public function updateAdSet(Request $request, $id)
    {
        $adSet = MetaAdSet::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:ACTIVE,PAUSED',
            'daily_budget' => 'nullable|numeric|min:1',
            'lifetime_budget' => 'nullable|numeric|min:1',
            'bid_amount' => 'nullable|numeric|min:0.01',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
        ]);

        $account = $adSet->adAccount;
        if ($account) {
            $this->graph->setUserAccessToken($account->access_token);

            $fbPayload = [];
            if (isset($data['name'])) $fbPayload['name'] = $data['name'];
            if (isset($data['status'])) $fbPayload['status'] = $data['status'];
            if (array_key_exists('daily_budget', $data)) {
                $fbPayload['daily_budget'] = $data['daily_budget'] ? (int) ($data['daily_budget'] * 100) : null;
            }
            if (array_key_exists('lifetime_budget', $data)) {
                $fbPayload['lifetime_budget'] = $data['lifetime_budget'] ? (int) ($data['lifetime_budget'] * 100) : null;
            }
            if (array_key_exists('bid_amount', $data)) {
                $fbPayload['bid_amount'] = $data['bid_amount'] ? (int) ($data['bid_amount'] * 100) : null;
            }

            if (!empty($fbPayload)) {
                $response = $this->graph->post($adSet->ad_set_id, $fbPayload);
                if (!empty($response['error'])) {
                    return response()->json([
                        'success' => false,
                        'message' => $response['error']['message'] ?? 'فشل تحديث المجموعة في فيسبوك',
                    ], 400);
                }
            }
        }

        $adSet->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المجموعة الإعلانية بنجاح',
            'ad_set' => $adSet->fresh(),
        ]);
    }

    // ──────────────────────────────────────────────
    //  New: Update Creative (local only)
    // ──────────────────────────────────────────────
    public function updateCreative(Request $request, $id)
    {
        $creative = MetaAdCreative::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'title' => 'nullable|string|max:40',
            'body' => 'nullable|string',
            'description' => 'nullable|string|max:255',
            'link_url' => 'nullable|url|max:500',
            'display_link' => 'nullable|string|max:500',
            'call_to_action' => 'nullable|string',
            'page_id' => 'nullable|string',
        ]);

        $creative->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث التصميم الإبداعي',
            'creative' => $creative->fresh(),
        ]);
    }

    // ──────────────────────────────────────────────
    //  New: Update Ad
    // ──────────────────────────────────────────────
    public function updateAd(Request $request, $id)
    {
        $ad = MetaAd::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:ACTIVE,PAUSED',
        ]);

        $account = $ad->adAccount;
        if ($account && $ad->ad_id) {
            $this->graph->setUserAccessToken($account->access_token);

            $fbPayload = [];
            if (isset($data['name'])) $fbPayload['name'] = $data['name'];
            if (isset($data['status'])) $fbPayload['status'] = $data['status'];

            if (!empty($fbPayload)) {
                $response = $this->graph->post($ad->ad_id, $fbPayload);
                if (!empty($response['error'])) {
                    return response()->json([
                        'success' => false,
                        'message' => $response['error']['message'] ?? 'فشل تحديث الإعلان في فيسبوك',
                    ], 400);
                }
            }
        }

        $ad->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الإعلان',
            'ad' => $ad->fresh(),
        ]);
    }

    // ──────────────────────────────────────────────
    //  New: Delete Creative
    // ──────────────────────────────────────────────
    public function deleteCreative($id)
    {
        $creative = MetaAdCreative::findOrFail($id);

        MetaAd::where('creative_id', $creative->id)->update(['creative_id' => null]);
        $creative->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف التصميم']);
    }

    // ──────────────────────────────────────────────
    //  New: Duplicate Campaign
    // ──────────────────────────────────────────────
    public function duplicateCampaign(Request $request, $id)
    {
        $campaign = MetaCampaign::with('adAccount')->findOrFail($id);
        $account = $campaign->adAccount;

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'حساب الإعلانات غير موجود'], 400);
        }

        $newName = ($request->input('name') ?? $campaign->name) . ' (نسخة)';

        $this->graph->setUserAccessToken($account->access_token);

        $response = $this->graph->createCampaign(
            $account->ad_account_id,
            [
                'name' => $newName,
                'objective' => $campaign->objective,
                'status' => 'PAUSED',
                'daily_budget' => $campaign->daily_budget,
                'bid_strategy' => $campaign->bid_strategy,
            ]
        );

        if (!empty($response['error'])) {
            return response()->json([
                'success' => false,
                'message' => $response['error']['message'] ?? 'فشل نسخ الحملة',
            ], 400);
        }

        $newCampaign = MetaCampaign::create([
            'ad_account_id' => $account->id,
            'campaign_id' => $response['id'],
            'name' => $newName,
            'objective' => $campaign->objective,
            'status' => 'PAUSED',
            'buying_type' => $campaign->buying_type,
            'daily_budget' => $campaign->daily_budget,
            'lifetime_budget' => $campaign->lifetime_budget,
            'bid_strategy' => $campaign->bid_strategy,
            'last_synced_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم نسخ الحملة بنجاح',
            'campaign' => $newCampaign,
        ]);
    }

    // ──────────────────────────────────────────────
    //  New: Campaign Insights (POST)
    // ──────────────────────────────────────────────
    public function getCampaignInsights(Request $request, $id)
    {
        $campaign = MetaCampaign::findOrFail($id);
        $account = $campaign->adAccount;
        if (!$account) {
            return response()->json(['success' => false, 'data' => []]);
        }

        $this->graph->setUserAccessToken($account->access_token);
        $insights = $this->graph->getInsights($campaign->campaign_id, 'campaign', [
            'date_preset' => $request->get('date_preset', 'last_30d'),
        ]);

        $campaign->update(['insights' => $insights, 'last_synced_at' => now()]);

        return response()->json(['success' => true, 'data' => $insights]);
    }

    // ──────────────────────────────────────────────
    //  New: Ad Set Insights (POST)
    // ──────────────────────────────────────────────
    public function getAdSetInsights(Request $request, $id)
    {
        $adSet = MetaAdSet::findOrFail($id);
        $account = $adSet->adAccount;
        if (!$account) {
            return response()->json(['success' => false, 'data' => []]);
        }

        $this->graph->setUserAccessToken($account->access_token);
        $insights = $this->graph->getInsights($adSet->ad_set_id, 'adset', [
            'date_preset' => $request->get('date_preset', 'last_30d'),
        ]);

        $adSet->update(['insights' => $insights, 'last_synced_at' => now()]);

        return response()->json(['success' => true, 'data' => $insights]);
    }

    // ──────────────────────────────────────────────
    //  New: Ad Insights (POST)
    // ──────────────────────────────────────────────
    public function getAdInsights(Request $request, $id)
    {
        $ad = MetaAd::findOrFail($id);
        $account = $ad->adAccount;
        if (!$account) {
            return response()->json(['success' => false, 'data' => []]);
        }

        $this->graph->setUserAccessToken($account->access_token);
        $insights = $this->graph->getInsights($ad->ad_id, 'ad', [
            'date_preset' => $request->get('date_preset', 'last_30d'),
        ]);

        $ad->update(['insights' => $insights, 'last_synced_at' => now()]);

        return response()->json(['success' => true, 'data' => $insights]);
    }

    private function mapAccountStatus(int $status): string
    {
        return match ($status) {
            1 => 'active',
            2 => 'disabled',
            3 => 'unsettled',
            7 => 'pending_risk_review',
            9 => 'in_grace_period',
            100 => 'pending_closed',
            101 => 'closed',
            201 => 'any_active',
            default => 'unknown',
        };
    }

    private function getPlatformConnectionStatus(\App\Services\TokenManagerService $tokens): array
    {
        $platforms = [];
        $oauthPlatforms = config('oauth', []);

        foreach ($oauthPlatforms as $key => $config) {
            if (!is_array($config) || empty($config['scopes']) && empty($config['install_mode'])) {
                continue;
            }
            $configured = !empty($config['client_id']);
            $connected = $tokens->isConnected($key);
            $platforms[$key] = [
                'name' => $config['name'] ?? ucfirst($key),
                'icon' => $config['icon'] ?? 'fas fa-plug',
                'color' => $config['color'] ?? '#6c757d',
                'configured' => $configured,
                'connected' => $connected,
                'connected_at' => $tokens->getConnectedAt($key),
                'has_oauth' => !empty($config['auth_url']) && !empty($config['token_url']),
                'install_mode' => !empty($config['install_mode']),
            ];
        }

        return $platforms;
    }
}
