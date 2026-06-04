<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function index()
    {
        $affiliates = Affiliate::withCount(['clicks', 'commissions'])
            ->withSum('commissions as total_commission', 'commission_amount')
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Affiliate::count(),
            'active' => Affiliate::active()->count(),
            'pending' => Affiliate::pending()->count(),
            'total_earned' => AffiliateCommission::where('status', 'approved')->sum('commission_amount'),
        ];

        return view('admin.affiliates.index', compact('affiliates', 'stats'));
    }

    public function show(Affiliate $affiliate)
    {
        $affiliate->loadCount(['clicks', 'commissions', 'payouts']);
        $affiliate->load(['commissions' => fn($q) => $q->latest()->limit(20), 'payouts' => fn($q) => $q->latest()->limit(20)]);

        return view('admin.affiliates.show', compact('affiliate'));
    }

    public function updateStatus(Request $request, Affiliate $affiliate)
    {
        $request->validate(['status' => 'required|in:active,inactive,suspended,pending']);
        $affiliate->update(['status' => $request->status]);
        return back()->with('success', 'تم تحديث حالة المسوّق بنجاح.');
    }

    public function updateCommission(Request $request, Affiliate $affiliate)
    {
        $request->validate([
            'commission_type' => 'required|in:percentage,fixed,hybrid',
            'commission_value' => 'required|numeric|min:0',
        ]);
        $affiliate->update([
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
        ]);
        return back()->with('success', 'تم تحديث إعدادات العمولة بنجاح.');
    }

    public function updateTier(Request $request, Affiliate $affiliate)
    {
        $request->validate(['tier_level' => 'required|in:bronze,silver,gold,platinum']);
        $affiliate->update(['tier_level' => $request->tier_level]);
        return back()->with('success', 'تم تحديث مستوى المسوّق بنجاح.');
    }

    public function commissions()
    {
        $commissions = AffiliateCommission::with('affiliate', 'order')
            ->latest()
            ->paginate(30);

        $stats = [
            'total' => AffiliateCommission::sum('commission_amount'),
            'pending' => AffiliateCommission::pending()->sum('commission_amount'),
            'approved' => AffiliateCommission::approved()->sum('commission_amount'),
            'paid' => AffiliateCommission::paid()->sum('commission_amount'),
        ];

        return view('admin.affiliates.commissions', compact('commissions', 'stats'));
    }

    public function approveCommission(AffiliateCommission $commission)
    {
        $commission->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $commission->affiliate->increment('wallet_balance', $commission->commission_amount);
        $commission->affiliate->increment('total_earned', $commission->commission_amount);

        return back()->with('success', 'تمت الموافقة على العمولة بنجاح.');
    }

    public function rejectCommission(Request $request, AffiliateCommission $commission)
    {
        $commission->update([
            'status' => 'rejected',
            'notes' => $request->notes,
        ]);
        return back()->with('success', 'تم رفض العمولة.');
    }

    public function payouts()
    {
        $payouts = AffiliatePayout::with('affiliate')
            ->latest()
            ->paginate(30);

        $stats = [
            'total' => AffiliatePayout::sum('amount'),
            'pending' => AffiliatePayout::pending()->count(),
            'paid' => AffiliatePayout::paid()->sum('amount'),
        ];

        return view('admin.affiliates.payouts', compact('payouts', 'stats'));
    }

    public function processPayout(Request $request, AffiliatePayout $payout)
    {
        $payout->update([
            'status' => $request->action === 'approve' ? 'paid' : 'rejected',
            'admin_notes' => $request->notes,
            'processed_at' => now(),
        ]);

        if ($request->action === 'approve') {
            $payout->affiliate->increment('total_paid', $payout->amount);
        } else {
            $payout->affiliate->increment('wallet_balance', $payout->amount);
        }

        return back()->with('success', $request->action === 'approve' ? 'تمت معالجة الدفع بنجاح.' : 'تم رفض الدفع.');
    }

    public function notes(Request $request, Affiliate $affiliate)
    {
        $affiliate->update(['notes' => $request->notes]);
        return back()->with('success', 'تم حفظ الملاحظات.');
    }
}
