<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;

class LandingController extends Controller
{
    public function index()
    {
        $products = Product::showOnLanding()
            ->active()
            ->showInB2C()
            ->with(['category', 'brand'])
            ->latest()
            ->get();

        $categories = Category::active()
            ->whereIn('id', $products->pluck('category_id')->unique())
            ->get();

        return view('frontend.pages.products-landing', compact('products', 'categories'));
    }
}