<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateClick;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    public function landing()
    {
        $affiliate = null;
        if (auth()->check()) {
            $affiliate = Affiliate::where('user_id', auth()->id())->first();
        }
        return view('frontend.affiliate.landing', compact('affiliate'));
    }

    public function dashboard()
    {
        if (!auth()->check()) {
            return view('frontend.affiliate.guest');
        }

        $affiliate = $this->autoCreateAffiliate();

        if ($affiliate->status !== 'active') {
            return view('frontend.affiliate.pending', compact('affiliate'));
        }

        $todayClicks = AffiliateClick::where('affiliate_id', $affiliate->id)
            ->whereDate('created_at', today())
            ->count();

        $monthClicks = AffiliateClick::where('affiliate_id', $affiliate->id)
            ->whereMonth('created_at', now()->month)
            ->count();

        $totalConversions = AffiliateClick::where('affiliate_id', $affiliate->id)
            ->where('converted', true)
            ->count();

        $totalClicks = AffiliateClick::where('affiliate_id', $affiliate->id)->count();

        $pendingCommissions = AffiliateCommission::where('affiliate_id', $affiliate->id)
            ->where('status', 'pending')
            ->sum('commission_amount');

        $approvedCommissions = AffiliateCommission::where('affiliate_id', $affiliate->id)
            ->where('status', 'approved')
            ->sum('commission_amount');

        $recentCommissions = AffiliateCommission::where('affiliate_id', $affiliate->id)
            ->with('order')
            ->latest()
            ->limit(10)
            ->get();

        $recentPayouts = AffiliatePayout::where('affiliate_id', $affiliate->id)
            ->latest()
            ->limit(5)
            ->get();

        $topProducts = AffiliateCommission::where('affiliate_id', $affiliate->id)
            ->where('status', 'approved')
            ->with('order')
            ->latest()
            ->limit(5)
            ->get();

        return view('frontend.affiliate.dashboard', compact(
            'affiliate', 'todayClicks', 'monthClicks', 'totalConversions', 'totalClicks',
            'pendingCommissions', 'approvedCommissions',
            'recentCommissions', 'recentPayouts', 'topProducts'
        ));
    }

    public function register(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('message', 'يرجى تسجيل الدخول أولا للانضمام إلى برنامج التسويق.');
        }

        $existing = Affiliate::where('user_id', auth()->id())->first();
        if ($existing) {
            return redirect()->route('affiliate.dashboard');
        }

        $request->validate(['phone' => 'nullable|string|max:30']);

        $phone = $request->phone ?: (auth()->user()->phone ?? '0590000000');

        Affiliate::create([
            'user_id' => auth()->id(),
            'name' => auth()->user()->name ?? 'مسوّق',
            'email' => auth()->user()->email ?? (auth()->user()->phone . '@user.jenincare.ps'),
            'phone' => $phone,
            'referral_code' => $this->generateReferralCode(auth()->user()->name ?? 'user'),
            'status' => 'active',
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
        ]);

        return redirect()->route('affiliate.dashboard')
            ->with('success', 'مرحباً بك في برنامج التسويق بالعمولة! تم تفعيل حسابك.');
    }

    public function requestPayout(Request $request)
    {
        $affiliate = $this->autoCreateAffiliate();

        $request->validate([
            'amount' => 'required|numeric|min:50|max:' . $affiliate->wallet_balance,
            'method' => 'required|in:bank_transfer,paypal,mobile_wallet',
            'iban' => 'required_if:method,bank_transfer|nullable|string|max:50',
            'paypal_email' => 'required_if:method,paypal|nullable|email|max:100',
            'mobile_wallet' => 'required_if:method,mobile_wallet|nullable|string|max:30',
        ]);

        AffiliatePayout::create([
            'affiliate_id' => $affiliate->id,
            'amount' => $request->amount,
            'method' => $request->method,
            'iban' => $request->iban,
            'paypal_email' => $request->paypal_email,
            'mobile_wallet' => $request->mobile_wallet,
            'status' => 'pending',
        ]);

        $affiliate->decrement('wallet_balance', $request->amount);

        return back()->with('success', 'تم تقديم طلب السحب بنجاح. سنقوم بمراجعته قريباً.');
    }

    public function marketingTools()
    {
        if (!auth()->check()) {
            return view('frontend.affiliate.guest');
        }
        $affiliate = $this->autoCreateAffiliate();

        $products = Product::active()->showInB2C()
            ->with('category')
            ->latest()
            ->limit(30)
            ->get();

        $categories = Category::active()
            ->withCount(['products' => fn($q) => $q->active()->showInB2C()])
            ->having('products_count', '>', 0)
            ->get();

        return view('frontend.affiliate.tools', compact('affiliate', 'products', 'categories'));
    }

    private function autoCreateAffiliate(): Affiliate
    {
        $affiliate = Affiliate::where('user_id', auth()->id())->first();

        if (!$affiliate) {
            $affiliate = Affiliate::create([
                'user_id' => auth()->id(),
                'name' => auth()->user()->name ?? 'مسوّق',
                'email' => auth()->user()->email ?? (auth()->user()->phone . '@user.jenincare.ps'),
                'phone' => auth()->user()->phone ?? '',
                'referral_code' => $this->generateReferralCode(auth()->user()->name ?? 'user'),
                'status' => 'active',
                'commission_type' => 'percentage',
                'commission_value' => 10.00,
            ]);
        }

        return $affiliate;
    }

    private function generateReferralCode(string $name): string
    {
        $base = Str::slug(Str::limit($name, 8, ''), '');
        $base = $base ?: 'jenin';
        $code = $base . rand(100, 999);

        while (Affiliate::where('referral_code', $code)->exists()) {
            $code = $base . rand(100, 999);
        }

        return $code;
    }
}
