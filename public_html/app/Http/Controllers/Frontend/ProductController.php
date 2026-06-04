<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->showInB2C()
            ->with(['category', 'brand', 'reviews' => function($q) {
                $q->where('is_approved', true)->approved();
            }])
            ->firstOrFail();

        $product->increment('views_count');

        $relatedProducts = Product::active()
            ->showInB2C()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('frontend.product.show', compact('product', 'relatedProducts'));
    }

    public function quickView($id)
    {
        $product = Product::active()->showInB2C()->findOrFail($id);
        return response()->json([
            'id' => $product->id,
            'name' => $product->name_ar,
            'price' => number_format($product->final_b2c_price, 2),
            'image' => $product->main_image_url,
            'description' => $product->description_ar ? \Illuminate\Support\Str::limit(strip_tags($product->description_ar), 200) : null,
            'category' => $product->category?->name_ar,
            'stock' => $product->stock_quantity > 10 ? 'متوفر' : ($product->stock_quantity > 0 ? 'تبقى '.$product->stock_quantity.' فقط' : 'نفذ المخزون'),
            'url' => route('product.show', $product->slug),
            'add_url' => route('cart.add'),
        ]);
    }
}
