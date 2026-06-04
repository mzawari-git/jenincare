<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdReviewerIp;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReviewerIpController extends Controller
{
    public function index()
    {
        $ips = AdReviewerIp::orderBy('created_at', 'desc')->paginate(50);
        return view('admin.reviewer-ips.index', compact('ips'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ip_address' => 'required|ip|unique:ad_reviewer_ips,ip_address',
            'user_agent' => 'nullable|string|max:500',
            'isp' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        AdReviewerIp::create($data + ['active' => true]);

        return redirect()->route('admin.reviewer-ips.index')
            ->with('success', 'تمت إضافة IP بنجاح');
    }

    public function destroy(AdReviewerIp $reviewerIp)
    {
        $reviewerIp->delete();
        return redirect()->route('admin.reviewer-ips.index')
            ->with('success', 'تم حذف IP');
    }

    public function toggle(AdReviewerIp $reviewerIp)
    {
        $reviewerIp->update(['active' => !$reviewerIp->active]);
        return response()->json(['success' => true, 'active' => $reviewerIp->active]);
    }
}
