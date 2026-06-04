<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\HeroSlide;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::active()->ordered()->with("product")->get();

        $featuredProducts = Product::featured()
            ->showInB2C()
            ->with(["category", "brand"])
            ->limit(8)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::active()->showInB2C()
                ->with(["category", "brand"])
                ->inRandomOrder()->limit(8)->get();
        }

        $newProducts = Product::active()
            ->showInB2C()
            ->where("is_new", true)
            ->with(["category", "brand"])
            ->limit(8)
            ->get();

        if ($newProducts->isEmpty()) {
            $newProducts = Product::active()->showInB2C()
                ->with(["category", "brand"])
                ->latest()->limit(8)->get();
        }

        $categories = Category::active()
            ->withCount(['products' => function($q) {
                $q->active()->showInB2C();
            }])
            ->having('products_count', '>', 0)
            ->get()
            ->map(function($cat) {
                $sample = Product::active()->showInB2C()
                    ->where('category_id', $cat->id)
                    ->latest()
                    ->first();
                $prices = Product::active()->showInB2C()
                    ->where('category_id', $cat->id)
                    ->pluck('b2c_price')->filter();
                $cat->min_price = $prices->min();
                $cat->max_price = $prices->max();
                $cat->sample_image = $sample?->main_image_url;
                $cat->display_name = preg_replace('/^[^\x{0600}-\x{06FF}\s]+/u', '', $cat->name_ar);
                $cat->display_name = trim($cat->display_name) ?: $cat->name_ar;
                return $cat;
            });

        return view("frontend.home.index", compact(
            "slides", "featuredProducts", "newProducts", "categories"
        ));
    }
}
