<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdAlert;
use App\Models\AdAutoPauseLog;
use App\Models\Meta\MetaCampaign;
use App\Services\AdAccountHealthService;
use Illuminate\Http\Request;

class AdAlertController extends Controller
{
    public function index(Request $request)
    {
        $query = AdAlert::query();

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->boolean('unresolved')) {
            $query->unresolved();
        }

        if ($request->boolean('unacknowledged')) {
            $query->unacknowledged();
        }

        $alerts = $query->latest()->paginate(25);

        $stats = [
            'total' => AdAlert::count(),
            'unresolved' => AdAlert::unresolved()->count(),
            'critical' => AdAlert::where('severity', 'critical')->unresolved()->count(),
            'warning' => AdAlert::where('severity', 'warning')->unresolved()->count(),
        ];

        $platforms = ['facebook', 'tiktok', 'google', 'snapchat', 'pinterest', 'twitter', 'linkedin'];
        $types = AdAlert::select('type')->distinct()->pluck('type');

        return view('admin.ad-alerts.index', compact('alerts', 'stats', 'platforms', 'types'));
    }

    public function pauseLog(Request $request)
    {
        $query = AdAutoPauseLog::query();

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->boolean('failed')) {
            $query->where('success', false);
        }

        $logs = $query->latest()->paginate(25);

        $platforms = ['facebook', 'tiktok', 'google', 'snapchat', 'pinterest', 'twitter', 'linkedin'];

        $stats = [
            'total' => AdAutoPauseLog::count(),
            'paused_today' => AdAutoPauseLog::where('action', 'paused')->whereDate('created_at', today())->count(),
            'successful' => AdAutoPauseLog::where('success', true)->count(),
            'failed' => AdAutoPauseLog::where('success', false)->count(),
        ];

        return view('admin.ad-alerts.pause-log', compact('logs', 'stats', 'platforms'));
    }

    public function acknowledge(AdAlert $alert)
    {
        $alert->update([
            'acknowledged' => true,
            'acknowledged_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function resolve(AdAlert $alert)
    {
        $alert->update(['resolved_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function destroy(AdAlert $alert)
    {
        $alert->delete();

        return redirect()->back()->with('success', 'تم حذف التنبيه');
    }

    public function healthSummary(AdAccountHealthService $health)
    {
        $scores = $health->getAllScores();

        return response()->json($scores);
    }

    public function activeAlertsCount()
    {
        $count = AdAlert::unresolved()->unacknowledged()->count();

        return response()->json(['count' => $count]);
    }
}
