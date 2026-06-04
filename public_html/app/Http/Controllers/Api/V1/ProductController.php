<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SkinScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function recommended(Request $request, string $scanId): JsonResponse
    {
        $user = $request->user();
        $scan = SkinScan::where('user_id', $user->id)->findOrFail($scanId);

        $products = Product::whereHas('defects', function ($q) use ($scan) {
            $q->whereIn('scan_defects.id', $scan->defects()->pluck('id'));
        })->get();

        if ($products->isEmpty()) {
            $products = Product::inRandomOrder()->take(6)->get();
        }

        return response()->json([
            'products' => $products->map(fn($p) => [
                'id' => (string) $p->id,
                'name' => $p->name,
                'name_ar' => $p->name_ar ?? $p->name,
                'price' => (float) $p->price,
                'image_url' => $p->image_url ? url('/storage/' . $p->image_url) : null,
                'description' => $p->description,
                'matching_reason' => 'Recommended for your skin type',
            ]),
        ]);
    }
}
