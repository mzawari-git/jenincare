<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
            ->get();

        $catIds = $categories->pluck('id');
        $sampleProducts = [];
        $priceRanges = [];

        if ($catIds->isNotEmpty()) {
            $allSamples = Product::active()->showInB2C()
                ->whereIn('category_id', $catIds)
                ->select('id', 'category_id', 'name_ar', 'b2c_price', 'main_image', 'main_image_webp', 'slug', 'status')
                ->get()
                ->groupBy('category_id');

            foreach ($catIds as $cid) {
                $prods = $allSamples->get($cid, collect());
                $sampleProducts[$cid] = $prods->sortByDesc('created_at')->first();
                $prices = $prods->pluck('b2c_price')->filter();
                $priceRanges[$cid] = [
                    'min' => $prices->min(),
                    'max' => $prices->max(),
                ];
            }
        }

        $categories = $categories->map(function($cat) use ($sampleProducts, $priceRanges) {
            $sample = $sampleProducts[$cat->id] ?? null;
            $cat->min_price = $priceRanges[$cat->id]['min'] ?? null;
            $cat->max_price = $priceRanges[$cat->id]['max'] ?? null;
            $cat->sample_image = $sample?->main_image_url;
            $cat->display_name = preg_replace('/^[^\x{0600}-\x{06FF}\s]+/u', '', $cat->name_ar);
            $cat->display_name = trim($cat->display_name) ?: $cat->name_ar;
            return $cat;
        });

        $heroData = $this->buildHeroSlidesData($categories, $featuredProducts);

        return view("frontend.home.index", array_merge(
            compact("slides", "featuredProducts", "newProducts", "categories"),
            $heroData,
            [
                'allPhrases' => config('hero-content.phrases', []),
                'heroHeadlines' => config('hero-content.headlines', []),
            ]
        ));
    }

    private function buildHeroSlidesData($categories, $featuredProducts): array
    {
        $catIds = $categories->filter(fn($c) => $c->products_count > 0)->shuffle()->take(8);
        $catIdList = $catIds->pluck('id');

        $slideProducts = collect();
        $slideProductIds = [];

        if ($catIdList->isNotEmpty()) {
            $allSlideProducts = Product::whereIn('category_id', $catIdList)
                ->where('status', 'active')
                ->where('show_in_b2c', true)
                ->inRandomOrder()
                ->get()
                ->groupBy('category_id');

            foreach ($catIds as $cat) {
                $prods = $allSlideProducts->get($cat->id);
                if (!$prods || $prods->isEmpty()) continue;
                $main = $prods->shift();
                $slideProductIds[$cat->id] = $main->id;
                $slideProducts->push(['product' => $main, 'category' => $cat, 'subs' => $prods->take(2)]);
            }
        }

        if ($slideProducts->isEmpty() && $featuredProducts->isNotEmpty()) {
            $slideProducts->push([
                'product' => $featuredProducts->first(),
                'category' => null,
                'subs' => collect(),
            ]);
        }

        $slidesData = $slideProducts->map(function($item) {
            $cat = $item['category'];
            $p = $item['product'];
            $catName = $cat ? ($cat->display_name ?? $cat->name_ar) : '';
            $safeDeviceTerms = ['جهاز', 'أجهزة', 'تقنية', 'تكنولوجيا', 'نبض', 'ضوئي', 'متقدم', 'advanced', 'device', 'technology'];
            $isDevices = false;
            foreach ($safeDeviceTerms as $term) {
                if (str_contains($catName, $term)) { $isDevices = true; break; }
            }
            $isSalon = str_contains($catName, 'صالون') || str_contains($catName, 'تجهيز');
            return [
                'product' => $p,
                'category' => $cat,
                'subs' => $item['subs'],
                'title_line1' => $isDevices ? 'تقنيات متطورة' : ($isSalon ? 'صالونك المثالي' : 'منتجات أصلية'),
                'title_line2' => $isDevices ? 'نتائج احترافية.' : ($isSalon ? 'فخامة متناهية.' : 'جمال لا يُقاوم.'),
                'color' => $isDevices ? '#06b6d4' : ($isSalon ? '#d4af37' : '#ec4899'),
            ];
        })->toArray();

        return [
            'slidesData' => $slidesData,
            'slideProductIds' => $slideProductIds,
        ];
    }
}
