<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $products = $query->with(['category', 'brand'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $product = Product::with(['category', 'brand', 'reviews'])->findOrFail($id);
        return response()->json(['data' => $product]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'sku' => 'nullable|string|unique:products,sku',
            'barcode' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'base_price' => 'nullable|numeric|min:0',
            'b2c_price' => 'required|numeric|min:0',
            'b2b_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'stock_status' => 'nullable|in:in_stock,low_stock,out_of_stock,pre_order',
            'status' => 'nullable|in:draft,active,inactive',
            'main_image' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            'is_bestseller' => 'nullable|boolean',
            'show_in_b2c' => 'nullable|boolean',
            'show_in_b2b' => 'nullable|boolean',
        ]);

        $product = Product::create($data);

        return response()->json(['data' => $product, 'message' => 'تم إنشاء المنتج بنجاح'], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'sku' => 'nullable|string|unique:products,sku,' . $id,
            'barcode' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'base_price' => 'nullable|numeric|min:0',
            'b2c_price' => 'sometimes|numeric|min:0',
            'b2b_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'stock_status' => 'nullable|in:in_stock,low_stock,out_of_stock,pre_order',
            'status' => 'nullable|in:draft,active,inactive',
            'main_image' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            'is_bestseller' => 'nullable|boolean',
            'show_in_b2c' => 'nullable|boolean',
            'show_in_b2b' => 'nullable|boolean',
        ]);

        $product->update($data);

        return response()->json(['data' => $product, 'message' => 'تم تحديث المنتج بنجاح']);
    }

    public function destroy($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'تم حذف المنتج بنجاح']);
    }

    public function recommendationRules(): JsonResponse
    {
        $rules = cache()->remember('product_recommendation_rules', 3600, function () {
            return \App\Models\Setting::where('key', 'product_recommendation_rules')->first()?->value ?? [];
        });

        return response()->json(['data' => $rules]);
    }

    public function updateRecommendationRules(Request $request): JsonResponse
    {
        $rules = $request->validate([
            'rules' => 'required|array',
        ]);

        \App\Models\Setting::updateOrCreate(
            ['key' => 'product_recommendation_rules'],
            ['value' => $rules['rules']]
        );

        cache()->forget('product_recommendation_rules');

        return response()->json(['message' => 'تم تحديث قواعد التوصية بنجاح']);
    }
}
