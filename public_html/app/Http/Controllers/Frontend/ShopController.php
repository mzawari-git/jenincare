<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()->showInB2C()->with(['category', 'brand']);

        $selectedCategory = null;
        if ($request->has('category')) {
            $selectedCategory = Category::where('slug', $request->category)->first();
            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            }
        }

        if ($request->has('brand')) {
            $query->where('brand_id', $request->brand);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name_ar', 'like', '%' . $request->search . '%')
                  ->orWhere('name_en', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_low':
                    $query->orderBy('b2c_price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('b2c_price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('sales_count', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->get();
        $categories = Category::active()
            ->withCount(['products' => function($q) {
                $q->active()->showInB2C();
            }])
            ->having('products_count', '>', 0)
            ->get();
        $brands = Brand::active()->get();

        return view('frontend.shop.index', compact('products', 'categories', 'brands', 'selectedCategory'));
    }

    public function searchAjax(Request $request)
    {
        $term = $request->get('q', '');
        if (mb_strlen($term) < 2) return response()->json([]);

        $products = Product::active()->showInB2C()
            ->where(function($q) use ($term) {
                $q->where('name_ar', 'like', "%{$term}%")
                  ->orWhere('name_en', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%");
            })
            ->select('id', 'name_ar', 'slug', 'b2c_price', 'main_image')
            ->limit(8)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name_ar,
                'slug' => $p->slug,
                'price' => number_format($p->b2c_price, 2) . ' ₪',
                'image' => $p->main_image_url,
                'url' => route('product.show', $p->slug),
            ]);

        return response()->json($products);
    }
}
