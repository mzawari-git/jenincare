<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreScene;
use App\Models\Scene3dObject;
use App\Models\Product;
use Illuminate\Http\Request;

class Store3dController extends Controller
{
    public function scenes()
    {
        $scenes = StoreScene::where('is_active', true)
            ->where('3d_enabled', true)
            ->orderBy('sort_order')
            ->get(['id', 'name_ar', 'name_en', 'slug', 'section', 'aisle', 'description_ar', 'ground_plane_url', 'skybox_url']);

        return response()->json(['data' => $scenes]);
    }

    public function objects($sceneId)
    {
        $objects = Scene3dObject::where('scene_id', $sceneId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $objects]);
    }

    public function products()
    {
        $products = Product::where('status', 'active')
            ->select('id', 'name_ar', 'name_en', 'slug', 'base_price', 'b2c_price', 'average_rating', 'main_image', 'thumbnail')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name_ar' => $p->name_ar,
                    'name_en' => $p->name_en,
                    'slug' => $p->slug,
                    'price' => $p->getCurrentPrice(),
                    'rating' => $p->average_rating ?? 0,
                    'image' => $p->main_image_url ?? '',
                    'thumbnail' => $p->thumbnail ?? '',
                ];
            });

        return response()->json(['data' => $products]);
    }

    protected function getSectionKeywords(): array
    {
        return [
            'skincare' => ['بشرة', 'عناية', 'تنظيف', 'face', 'skin', 'wash', 'toner'],
            'devices' => ['جهاز', 'ليزر', 'مساج', 'device', 'laser', 'massage', 'ion'],
            'creams' => ['كريم', 'سيروم', 'cream', 'serum', 'مرطب', 'مقشر'],
            'salon' => ['صالون', 'تجهيز', 'salon', 'chair', 'dryer', 'مشط', 'مجفف'],
        ];
    }

    /**
     * Return products grouped by 3D store section.
     * Maps real products to sections based on category name keywords.
     */
    public function shelves()
    {
        $products = Product::where('status', 'active')
            ->where('show_in_b2c', true)
            ->with('category:id,name_ar,name_en')
            ->select('id', 'category_id', 'name_ar', 'name_en', 'slug', 'b2c_price', 'discount_percentage', 'discount_amount', 'discount_starts_at', 'discount_ends_at', 'average_rating', 'reviews_count', 'main_image', 'thumbnail', 'is_featured', 'is_bestseller')
            ->get();

        $keywords = $this->getSectionKeywords();
        $grouped = array_fill_keys(array_keys($keywords), []);
        $grouped['offers'] = [];

        foreach ($products as $p) {
            $catName = $p->category?->name_ar ?? '';
            $assigned = false;

            foreach ($keywords as $section => $kwList) {
                foreach ($kwList as $kw) {
                    if (mb_stripos($catName, $kw) !== false) {
                        $grouped[$section][] = $p;
                        $assigned = true;
                        break 2;
                    }
                }
            }

            if (!$assigned) {
                $grouped['offers'][] = $p;
            }
        }

        // Also prepare a flat all-products list for the wall display
        $allFlat = $products->map(function ($p) {
            $price = $p->getCurrentPrice();
            return [
                'id' => $p->id,
                'name_ar' => $p->name_ar,
                'name_en' => $p->name_en,
                'slug' => $p->slug,
                'price' => $price,
                'rating' => (float) ($p->average_rating ?? 0),
                'reviews_count' => $p->reviews_count ?? 0,
                'image' => $p->main_image_url ?? '',
                'thumbnail' => $p->thumbnail ?? '',
                'is_featured' => $p->is_featured,
                'is_on_sale' => $p->isDiscountActive(),
            ];
        })->values();

        // Return all products grouped by section AND a flat list
        foreach ($grouped as $section => $items) {
            $grouped[$section] = collect($items)->map(function ($p) {
                $price = $p->getCurrentPrice();
                return [
                    'id' => $p->id,
                    'name_ar' => $p->name_ar,
                    'name_en' => $p->name_en,
                    'slug' => $p->slug,
                    'price' => $price,
                    'rating' => (float) ($p->average_rating ?? 0),
                    'reviews_count' => $p->reviews_count ?? 0,
                    'image' => $p->main_image_url ?? '',
                    'thumbnail' => $p->thumbnail ?? '',
                    'is_featured' => $p->is_featured,
                    'is_on_sale' => $p->isDiscountActive(),
                ];
            })->values()->all();
        }

        $grouped['all'] = $allFlat->all();

        return response()->json(['data' => $grouped]);
    }

    public function objectTypes()
    {
        return response()->json([
            'data' => [
                ['id' => 'product_display', 'name_ar' => 'عرض منتج', 'name_en' => 'Product Display'],
                ['id' => 'shelf', 'name_ar' => 'رف', 'name_en' => 'Shelf'],
                ['id' => 'wall', 'name_ar' => 'جدار', 'name_en' => 'Wall'],
                ['id' => 'floor', 'name_ar' => 'أرضية', 'name_en' => 'Floor'],
                ['id' => 'sign', 'name_ar' => 'لافتة', 'name_en' => 'Sign'],
                ['id' => 'decor', 'name_ar' => 'ديكور', 'name_en' => 'Decor'],
                ['id' => 'lighting', 'name_ar' => 'إضاءة', 'name_en' => 'Lighting'],
            ],
        ]);
    }
}
