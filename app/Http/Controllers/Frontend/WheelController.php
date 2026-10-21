<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\WheelPrize;
use App\Models\Setting;

class WheelController extends Controller
{
    public function index()
    {
        $prizes = WheelPrize::active()->orderBy('sort_order')->get();

        if ($prizes->isEmpty()) {
            $defaults = [
                ['type' => 'product', 'name' => 'مسكرة', 'discount_percent' => null, 'color' => '#FF6B6B', 'sort_order' => 0, 'weight' => 9900],
                ['type' => 'discount', 'name' => 'خصم 10%', 'discount_percent' => 10, 'color' => '#FECA57', 'sort_order' => 1, 'weight' => 3],
                ['type' => 'discount', 'name' => 'خصم 15%', 'discount_percent' => 15, 'color' => '#48DBFB', 'sort_order' => 2, 'weight' => 3],
                ['type' => 'discount', 'name' => 'خصم 20%', 'discount_percent' => 20, 'color' => '#FF9FF3', 'sort_order' => 3, 'weight' => 3],
                ['type' => 'product', 'name' => 'شامبو', 'discount_percent' => null, 'color' => '#54A0FF', 'sort_order' => 4, 'weight' => 2],
                ['type' => 'product', 'name' => 'كريم بشره', 'discount_percent' => null, 'color' => '#5F27CD', 'sort_order' => 5, 'weight' => 4],
                ['type' => 'product', 'name' => '10 اقنعه للبشره', 'discount_percent' => null, 'color' => '#FF6348', 'sort_order' => 6, 'weight' => 2],
                ['type' => 'product', 'name' => 'بكج فرموني', 'discount_percent' => null, 'color' => '#2ED573', 'sort_order' => 7, 'weight' => 1],
                ['type' => 'product', 'name' => 'نبه عطر', 'discount_percent' => null, 'color' => '#A29BFE', 'sort_order' => 8, 'weight' => 3],
                ['type' => 'product', 'name' => 'هديه بقيمه 100شيكل مختاره', 'discount_percent' => null, 'color' => '#FD79A8', 'sort_order' => 9, 'weight' => 1],
                ['type' => 'product', 'name' => 'جهاز كهربائي', 'discount_percent' => null, 'color' => '#00CEC9', 'sort_order' => 10, 'weight' => 1],
            ];
            foreach ($defaults as $data) {
                WheelPrize::create($data);
            }
            $prizes = WheelPrize::active()->orderBy('sort_order')->get();
        }

        $siteSettings = Setting::pluck('value', 'key')->all();

        return view('frontend.pages.spin-wheel', compact('prizes', 'siteSettings'));
    }
}
