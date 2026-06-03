<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::active();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('skin_type')) {
            $query->where('skin_type', $request->input('skin_type'));
        }

        if ($request->filled('concern')) {
            $query->whereJsonContains('concerns', $request->input('concern'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy($request->input('sort_by', 'name'))
            ->paginate($request->input('per_page', 20));

        return response()->json($products);
    }

    public function recommended(int $scanId, Request $request): JsonResponse
    {
        $scan = \App\Models\SkinAnalysis::with('recommendedProducts')
            ->where('user_id', $request->user()->id)
            ->findOrFail($scanId);

        $products = $scan->recommendedProducts->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'name_ar' => $p->name_ar,
            'description' => $p->description,
            'price' => $p->price,
            'image_url' => $p->image_url,
            'matching_reason' => $p->pivot->matching_reason ?? null,
            'in_stock' => $p->is_active ?? true,
        ]);

        return response()->json([
            'products' => $products,
            'scan_id' => $scanId,
        ]);
    }
}
