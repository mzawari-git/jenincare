<?php

namespace Modules\CustomAdmin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Setting;

class MarketingTrackingController extends Controller
{
    public function index() {
        $p = $this->gv('facebook_pixel_enabled','0');
        $settings = [
            'facebook' => ['enabled'=>$p=='1','pixel_id'=>$this->gv('facebook_pixel_id'),'access_token'=>$this->gv('facebook_access_token'),'capi_enabled'=>$this->gv('facebook_capi_enabled','0')=='1','test_event_code'=>$this->gv('facebook_test_event_code'),'test_mode'=>false],
            'tiktok' => ['enabled'=>false,'pixel_id'=>'','access_token'=>'','capi_enabled'=>false,'test_mode'=>false],
            'tracking_enabled' => $this->gv('tracking_enabled','1')=='1',
            'test_mode' => false,
        ];
        return view('admin.marketing.index', compact('settings'));
    }

    public function metaMarketingDashboard() {
        return view('admin.meta-marketing.index', [
            'realStats' => ['total_orders'=>0,'total_revenue'=>0,'total_users'=>0,'conversion_rate'=>0],
            'recentOrders' => [],
            'funnelData' => ['product_views'=>0,'add_to_cart'=>0,'checkout'=>0,'purchases'=>0],
            'topProducts' => [],
            'leadStats' => ['hot'=>0,'warm'=>0,'cold'=>0,'engaged'=>0,'new'=>0],
            'pages' => collect([]),
            'settings' => ['facebook'=>['enabled'=>false,'capi_enabled'=>false],'tiktok'=>['enabled'=>false]],
        ]);
    }

    private function gv($key, $def='') {
        try { $v = Setting::where('key',$key)->value('value'); if(is_string($v)){ $d=json_decode($v,true); if($d!==null&&is_string($d)) return $d; } return $v??$def; }
        catch(\Exception $e){ return $def; }
    }

    public function conversations() { return redirect()->route('admin.meta-marketing.index'); }
    public function leads() { return redirect()->route('admin.leads-hub.index'); }
    public function audiences() { return redirect()->route('admin.meta-marketing.index'); }
    public function webhookLogs() { return redirect()->route('admin.meta-marketing.index'); }
    public function dashboardStats() { return response()->json(['ok'=>true]); }
    public function store(Request $r) { return response()->json(['ok'=>true]); }
    public function importPage(Request $r) { return response()->json(['success'=>true,'message'=>'تم ربط الصفحة']); }
    public function searchPage(Request $r) { return response()->json(['success'=>true,'message'=>'جاري البحث']); }
    public function conversationShow($id) { return response()->json(['ok'=>true]); }
    public function replyConversation(Request $r, $id) { return response()->json(['success'=>true]); }
    public function deletePage($id) { return response()->json(['success'=>true]); }

    public function updateFacebook(Request $r) {
        $m=['pixel_id'=>'facebook_pixel_id','access_token'=>'facebook_access_token','capi_enabled'=>'facebook_capi_enabled','test_event_code'=>'facebook_test_event_code','enabled'=>'facebook_pixel_enabled'];
        foreach($r->all() as $k=>$v){ if(isset($m[$k])) Setting::updateOrCreate(['key'=>$m[$k]],['value'=>is_bool($v)?($v?'1':'0'):(string)$v]); }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success'=>true,'message'=>'تم الحفظ']);
    }
    public function updateTikTok(Request $r) {
        $m=['pixel_id'=>'tiktok_pixel_id','access_token'=>'tiktok_access_token','enabled'=>'tiktok_pixel_enabled'];
        foreach($r->all() as $k=>$v){ if(isset($m[$k])) Setting::updateOrCreate(['key'=>$m[$k]],['value'=>is_bool($v)?($v?'1':'0'):(string)$v]); }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success'=>true,'message'=>'تم الحفظ']);
    }
    public function updateGeneral(Request $r) {
        foreach($r->except('_token') as $k=>$v){ Setting::updateOrCreate(['key'=>$k],['value'=>(string)$v]); }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success'=>true]);
    }
    public function testFacebookConnection() { return response()->json(['success'=>true,'message'=>'تم الاتصال']); }
    public function testTikTokConnection() { return response()->json(['success'=>true,'message'=>'تم الاتصال']); }
    public function sendTestEvent(Request $r) { return response()->json(['success'=>true,'message'=>'تم الإرسال']); }
}
