<?php

namespace Modules\CustomAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Meta\Services\LeadSyncService;
use Modules\Meta\Models\MetaLead;
use Maatwebsite\Excel\Facades\Excel;

class MetaLeadHubController extends Controller
{
    public function __construct(
        private LeadSyncService $leadSync,
    ) {}

    public function index(Request $request)
    {
        $query = MetaLead::query();

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('intent')) {
            $query->where('intent', $request->intent);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sender_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('source_campaign', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_score')) {
            $query->where('lead_score', '>=', $request->min_score);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $leads = $query->orderByDesc('created_at')->paginate(25);
        $stats = $this->leadSync->getLeadStats();

        return view('admin.meta-marketing.leads-hub', compact('leads', 'stats'));
    }

    public function filter(Request $request)
    {
        return $this->index($request);
    }

    public function stats()
    {
        return response()->json($this->leadSync->getLeadStats());
    }

    public function show($leadId)
    {
        $lead = MetaLead::findOrFail($leadId);
        return view('admin.meta-marketing.lead-show', compact('lead'));
    }

    public function sync(Request $request)
    {
        $result = $this->leadSync->syncLeadsFromFacebook();

        if ($request->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function syncFromFacebook(Request $request)
    {
        $result = $this->leadSync->syncLeadsFromFacebook();

        if ($request->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function exportExcel(Request $request)
    {
        $query = MetaLead::query();

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $leads = $query->orderByDesc('created_at')->get();

        $exportData = $leads->map(function ($lead) {
            return [
                'المعرف' => $lead->id,
                'الاسم' => $lead->sender_name,
                'البريد الإلكتروني' => $lead->email,
                'الهاتف' => $lead->phone,
                'المدينة' => $lead->city,
                'الدولة' => $lead->country,
                'المصدر' => $lead->source,
                'الحملة' => $lead->source_campaign,
                'التقييم' => $lead->lead_score,
                'المراحل' => $lead->stage,
                'النية' => $lead->intent,
                'التفاعلات' => $lead->total_interactions,
                'تاريخ الإنشاء' => $lead->created_at?->format('Y-m-d H:i'),
                'آخر نشاط' => $lead->last_activity_at?->format('Y-m-d H:i'),
            ];
        });

        return response()->json(['data' => $exportData, 'count' => $exportData->count()]);
    }

    public function exportSelected(Request $request)
    {
        $ids = $request->input('lead_ids', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'لم يتم تحديد عملاء محتملين']);
        }

        $leads = MetaLead::whereIn('id', $ids)->get();

        $exportData = $leads->map(function ($lead) {
            return [
                'المعرف' => $lead->id,
                'الاسم' => $lead->sender_name,
                'البريد الإلكتروني' => $lead->email,
                'الهاتف' => $lead->phone,
                'المدينة' => $lead->city,
                'التقييم' => $lead->lead_score,
                'المراحل' => $lead->stage,
                'الحملة' => $lead->source_campaign,
            ];
        });

        return response()->json(['data' => $exportData, 'count' => $exportData->count()]);
    }

    public function bulkMessage(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array',
            'message' => 'required|string|max:1000',
        ]);

        $leads = MetaLead::whereIn('id', $request->lead_ids)->get();
        $sentCount = 0;

        foreach ($leads as $lead) {
            $lead->update([
                'stage' => 'engaged',
                'last_activity_at' => now(),
                'meta_data' => array_merge($lead->meta_data ?? [], [
                    'bulk_message_sent' => now()->toIso8601String(),
                ]),
            ]);
            $sentCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "تم إرسال الرسالة إلى {$sentCount} عميل محتمل",
            'sent' => $sentCount,
        ]);
    }

    public function bulkCampaigns()
    {
        $campaigns = MetaLead::whereNotNull('source_campaign')
            ->selectRaw('source_campaign, COUNT(*) as lead_count, AVG(lead_score) as avg_score')
            ->groupBy('source_campaign')
            ->orderByDesc('lead_count')
            ->get();

        return view('admin.meta-marketing.bulk-campaigns', compact('campaigns'));
    }

    public function bulkCampaignShow($campaignName)
    {
        $leads = MetaLead::where('source_campaign', $campaignName)
            ->orderByDesc('lead_score')
            ->paginate(50);

        return view('admin.meta-marketing.bulk-campaign-show', [
            'campaign' => $campaignName,
            'leads' => $leads,
        ]);
    }

    public function updateScore(Request $request, $leadId)
    {
        $lead = MetaLead::findOrFail($leadId);

        $lead->update([
            'lead_score' => $request->input('score', $lead->lead_score),
            'stage' => $this->leadSync->getStageFromScore($request->input('score', $lead->lead_score)),
        ]);

        return response()->json(['success' => true, 'message' => 'تم تحديث التقييم']);
    }

    public function updateStage(Request $request, $leadId)
    {
        $lead = MetaLead::findOrFail($leadId);

        $lead->update([
            'stage' => $request->input('stage'),
        ]);

        return response()->json(['success' => true, 'message' => 'تم تحديث المراحل']);
    }

    public function addTag(Request $request, $leadId)
    {
        $lead = MetaLead::findOrFail($leadId);
        $tags = $lead->tags ?? [];
        $tags[] = $request->input('tag');
        $lead->update(['tags' => array_unique($tags)]);

        return response()->json(['success' => true, 'message' => 'تمت إضافة العلامة']);
    }

    public function removeTag(Request $request, $leadId)
    {
        $lead = MetaLead::findOrFail($leadId);
        $tags = array_diff($lead->tags ?? [], [$request->input('tag')]);
        $lead->update(['tags' => array_values($tags)]);

        return response()->json(['success' => true, 'message' => 'تمت إزالة العلامة']);
    }
}
