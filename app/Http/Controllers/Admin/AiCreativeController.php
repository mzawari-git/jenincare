<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AI\CreativeGeneratorService;
use Illuminate\Http\Request;

class AiCreativeController extends Controller
{
    public function __construct(
        private CreativeGeneratorService $generator,
    ) {}

    public function index(Request $request)
    {
        $variations = session('generated_variations', []);

        return view('admin.ai-creative.index', compact('variations'));
    }

    public function generateForm()
    {
        return view('admin.ai-creative.generate');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'platform' => 'required|in:meta,google',
            'objective' => 'required|in:conversions,traffic,awareness,leads,engagement',
            'product_name' => 'required|string|max:255',
            'service_description' => 'nullable|string|max:1000',
            'tone' => 'required|in:professional,friendly,luxury,urgent,educational,emotional',
            'num_variations' => 'required|integer|min:1|max:10',
            'audience' => 'nullable|string|max:500',
        ]);

        $result = $this->generator->generateVariations($request->all());

        if ($result['success']) {
            session(['generated_variations' => $result['variations']]);

            return view('admin.ai-creative.generate', [
                'variations' => $result['variations'],
                'platform' => $request->platform,
                'provider' => $result['provider'] ?? 'unknown',
                'success' => true,
            ]);
        }

        return view('admin.ai-creative.generate', [
            'error' => $result['message'] ?? 'فشل توليد الإعلانات',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'variations' => 'required|array',
        ]);

        foreach ($request->variations as $variation) {
            \App\Models\Meta\MetaAdCreative::create([
                'ad_account_id' => $request->ad_account_id,
                'name' => 'AI: ' . ($variation['headline'] ?? 'Creative'),
                'type' => $request->platform === 'google' ? 'responsive_search' : 'standard',
                'body' => $variation['primary_text'] ?? '',
                'title' => $variation['headline'] ?? '',
                'description' => $variation['description'] ?? '',
                'call_to_action_type' => $variation['cta'] ?? 'BOOK_TRAVEL',
                'status' => 'DRAFT',
                'settings' => [
                    'ai_generated' => true,
                    'platform' => $request->platform,
                    'quality_score' => $variation['quality_score'] ?? 0,
                    'compliance_score' => $variation['compliance_score'] ?? 0,
                ],
            ]);
        }

        return redirect()->route('admin.ai-creative.index')
            ->with('success', 'تم حفظ ' . count($request->variations) . ' نسخة إعلانية');
    }

    public function destroy($id)
    {
        $creative = \App\Models\Meta\MetaAdCreative::findOrFail($id);
        $creative->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف النسخة الإعلانية']);
    }
}
