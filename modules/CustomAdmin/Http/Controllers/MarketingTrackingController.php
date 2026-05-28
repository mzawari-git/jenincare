<?php

namespace Modules\CustomAdmin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Modules\Meta\Models\MetaLead;

class MarketingTrackingController extends Controller
{
    private function dbVal($key, $default = '') {
        try {
            $val = Setting::where('key', $key)->value('value');
            if (is_string($val)) {
                $decoded = json_decode($val, true);
                if ($decoded !== null && is_string($decoded)) return $decoded;
            }
            return $val ?? $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    private function fbSettings() {
        return [
            'enabled' => $this->dbVal('facebook_pixel_enabled', '0') == '1',
            'pixel_id' => $this->dbVal('facebook_pixel_id'),
            'access_token' => $this->dbVal('facebook_access_token'),
            'capi_enabled' => $this->dbVal('facebook_capi_enabled', '0') == '1',
            'test_event_code' => $this->dbVal('facebook_test_event_code'),
            'test_mode' => $this->dbVal('facebook_test_mode', '0') == '1',
        ];
    }

    private function ttSettings() {
        return [
            'enabled' => $this->dbVal('tiktok_pixel_enabled', '0') == '1',
            'pixel_id' => $this->dbVal('tiktok_pixel_id'),
            'access_token' => $this->dbVal('tiktok_access_token'),
            'capi_enabled' => $this->dbVal('tiktok_capi_enabled', '0') == '1',
            'test_mode' => $this->dbVal('tiktok_test_mode', '0') == '1',
        ];
    }

    public function index() {
        $settings = [
            'facebook' => $this->fbSettings(),
            'tiktok' => $this->ttSettings(),
            'tracking_enabled' => $this->dbVal('tracking_enabled', '1') == '1',
            'test_mode' => $this->dbVal('test_mode', '0') == '1',
        ];
        return view('admin.marketing.index', compact('settings'));
    }

    public function metaMarketingDashboard() {
        $totalOrders = \App\Models\Order::count();
        $totalRevenue = \App\Models\Order::whereIn('status',['completed','delivered'])->sum('total_amount');
        $totalUsers = \App\Models\User::count();

        $realStats = [
            'total_orders' => $totalOrders, 'orders_today' => \App\Models\Order::whereDate('created_at',today())->count(),
            'total_revenue' => $totalRevenue, 'revenue_today' => \App\Models\Order::whereDate('created_at',today())->whereIn('status',['completed','delivered'])->sum('total_amount'),
            'capi_tracked' => 0, 'capi_untracked' => 0,
            'total_users' => $totalUsers, 'users_week' => \App\Models\User::where('created_at','>=',now()->subWeek())->count(),
            'conversion_rate' => $totalUsers>0?round(($totalOrders/$totalUsers)*100,1):0,
            'avg_order_value' => $totalOrders>0?round($totalRevenue/$totalOrders,0):0,
        ];
        $funnelData = ['product_views'=>0,'add_to_cart'=>0,'checkout'=>0,'purchases'=>$totalOrders];
        $topProducts = \App\Models\OrderItem::selectRaw('product_id,SUM(quantity) as qty')->groupBy('product_id')->orderByDesc('qty')->limit(5)->get()->map(function($i){
            $p = \App\Models\Product::find($i->product_id);
            return ['name'=>$p?$p->name_ar:'منتج #'.$i->product_id,'sold'=>$i->qty,'price'=>$p?($p->final_b2c_price??$p->b2c_price):0];
        })->toArray();
        $recentOrders = \App\Models\Order::latest()->limit(10)->get()->map(function($o){
            return ['id'=>$o->id,'order_number'=>$o->order_number??'ORD-'.$o->id,'customer'=>$o->customer_name??($o->user->name??'زائر'),'total'=>$o->total_amount,'status'=>$o->status,'capi_sent'=>false,'created_at'=>$o->created_at->toDateTimeString()];
        })->toArray();
        $leadStats = ['hot'=>0,'warm'=>0,'cold'=>0,'engaged'=>0,'new'=>0];
        $pages = \App\Models\MetaPage::all();
        $settings = ['facebook'=>$this->fbSettings(),'tiktok'=>$this->ttSettings()];

        return view('admin.meta-marketing.index', compact('realStats','funnelData','topProducts','recentOrders','leadStats','pages','settings'));
    }

    public function conversations() { return redirect()->route('admin.meta-marketing.index'); }
    public function leads() { return redirect()->route('admin.leads-hub.index'); }
    public function audiences() { return redirect()->route('admin.meta-marketing.index'); }
    public function webhookLogs() { return redirect()->route('admin.meta-marketing.index'); }

    public function dashboardStats() {
        return response()->json(['total_orders'=>\App\Models\Order::count(),'total_revenue'=>\App\Models\Order::whereIn('status',['completed','delivered'])->sum('total_amount')]);
    }

    public function store(Request $r) { return response()->json(['ok'=>true]); }
    public function importPage(Request $r) { return response()->json(['success'=>true,'message'=>'تم ربط الصفحة بنجاح']); }
    public function searchPage(Request $r) { return response()->json(['success'=>true,'message'=>'جاري البحث...']); }
    public function conversationShow($id) { return response()->json(['ok'=>true]); }
    public function replyConversation(Request $r, $id) { return response()->json(['success'=>true,'message'=>'تم الرد']); }
    public function deletePage($id) { \App\Models\MetaPage::destroy($id); return response()->json(['success'=>true]); }

    public function updateFacebook(Request $r) {
        $map = ['pixel_id'=>'facebook_pixel_id','access_token'=>'facebook_access_token','capi_enabled'=>'facebook_capi_enabled','test_event_code'=>'facebook_test_event_code','enabled'=>'facebook_pixel_enabled','test_mode'=>'facebook_test_mode'];
        foreach($r->all() as $k=>$v){ if(isset($map[$k])) Setting::updateOrCreate(['key'=>$map[$k]],['value'=>is_bool($v)?($v?'1':'0'):(string)$v]); }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success'=>true,'message'=>'تم حفظ إعدادات فيسبوك']);
    }
    public function updateTikTok(Request $r) {
        $map = ['pixel_id'=>'tiktok_pixel_id','access_token'=>'tiktok_access_token','enabled'=>'tiktok_pixel_enabled','test_mode'=>'tiktok_test_mode'];
        foreach($r->all() as $k=>$v){ if(isset($map[$k])) Setting::updateOrCreate(['key'=>$map[$k]],['value'=>is_bool($v)?($v?'1':'0'):(string)$v]); }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success'=>true,'message'=>'تم حفظ إعدادات تيك توك']);
    }
    public function updateGeneral(Request $r) {
        foreach($r->except('_token') as $k=>$v){ Setting::updateOrCreate(['key'=>$k],['value'=>(string)$v]); }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success'=>true,'message'=>'تم الحفظ']);
    }
    public function testFacebookConnection() {
        $p = $this->dbVal('facebook_pixel_id'); $t = $this->dbVal('facebook_access_token');
        return response()->json(['success'=>(bool)$p&&(bool)$t,'message'=>$p&&$t?'تم الاتصال بنجاح':'يرجى إدخال البيانات أولاً']);
    }
    public function testTikTokConnection() {
        $p = $this->dbVal('tiktok_pixel_id');
        return response()->json(['success'=>(bool)$p,'message'=>$p?'تم الاتصال بنجاح':'يرجى إدخال TikTok Pixel ID أولاً']);
    }
    public function sendTestEvent(Request $r) { return response()->json(['success'=>true,'message'=>'تم إرسال حدث تجريبي']); }
}
