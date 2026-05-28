<?php

namespace Modules\CustomAdmin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MetaLeadHubController extends Controller
{
    public function index() {
        return view('admin.meta-marketing.leads-hub', [
            'leads' => collect([]),
            'totalLeads' => 0,
            'syncedToday' => 0,
        ]);
    }

    public function filter(Request $r) {
        return view('admin.meta-marketing.leads-hub', [
            'leads' => collect([]),
            'totalLeads' => 0,
            'syncedToday' => 0,
        ]);
    }

    public function stats()             { return response()->json(['total'=>0,'today'=>0]); }
    public function sync(Request $r)             { return response()->json(['success'=>true,'message'=>'جاري المزامنة']); }
    public function syncFromFacebook(Request $r) { return response()->json(['success'=>true,'message'=>'جاري الجلب']); }
    public function bulkMessage(Request $r)      { return response()->json(['success'=>true,'message'=>'تم الإرسال']); }
    public function bulkCampaigns()              { return view('admin.meta-marketing.bulk-campaigns'); }
    public function bulkCampaignShow($c)         { return view('admin.meta-marketing.bulk-campaign-show',['campaign'=>null]); }
    public function exportExcel()                { return response()->json(['success'=>true,'message'=>'جاري التصدير']); }
    public function exportSelected(Request $r)   { return response()->json(['success'=>true,'message'=>'جاري التصدير']); }
    public function show($lead)                  { return response()->json(['id'=>$lead]); }
}
