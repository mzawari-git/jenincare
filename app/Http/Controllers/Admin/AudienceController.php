<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Audience\AudienceBuilderService;
use App\Models\CustomAudience;
use Illuminate\Http\Request;

class AudienceController extends Controller
{
    public function __construct(
        private AudienceBuilderService $audienceBuilder,
    ) {}

    public function index(Request $request)
    {
        $query = CustomAudience::query();

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $audiences = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.audiences.index', compact('audiences'));
    }

    public function create()
    {
        return view('admin.audiences.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|in:meta,google',
            'source' => 'required|in:website,lookalike,engagement,lead_form,capi',
            'country' => 'nullable|string|max:2',
        ]);

        $result = $this->audienceBuilder->createCustomAudience($request->all());

        if ($result['success']) {
            return redirect()->route('admin.audiences.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function show(CustomAudience $audience)
    {
        $insights = $audience->insights()->orderByDesc('date')->limit(30)->get();
        $fatigue = $this->audienceBuilder->detectFatigue($audience->id);

        return view('admin.audiences.show', compact('audience', 'insights', 'fatigue'));
    }

    public function sync(CustomAudience $audience)
    {
        $result = $this->audienceBuilder->createCustomAudience([
            'platform' => $audience->platform,
            'name' => $audience->name,
            'source' => $audience->source_type,
        ]);

        return response()->json($result);
    }

    public function pushToPlatform(CustomAudience $audience)
    {
        $result = $this->audienceBuilder->createCustomAudience([
            'platform' => $audience->platform,
            'name' => $audience->name,
            'source' => $audience->source_type,
            'seed_source' => $audience->seed_source,
        ]);

        return response()->json($result);
    }

    public function createLookalike(Request $request)
    {
        $request->validate([
            'seed_audience_id' => 'required|integer',
            'ratio' => 'required|integer|min:1|max:10',
            'country' => 'nullable|string|max:2',
            'name' => 'nullable|string|max:255',
        ]);

        $result = $this->audienceBuilder->createLookalike($request->all());

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 422);
    }

    public function overlapAnalysis(Request $request)
    {
        $request->validate([
            'audience_ids' => 'required|array|min:2',
        ]);

        $result = $this->audienceBuilder->getOverlapAnalysis($request->audience_ids);

        return response()->json($result);
    }

    public function destroy(CustomAudience $audience)
    {
        $audience->delete();

        return redirect()->route('admin.audiences.index')
            ->with('success', 'تم حذف الجمهور بنجاح');
    }
}
