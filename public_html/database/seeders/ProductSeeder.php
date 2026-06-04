<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $shampoo = Category::where('slug', 'shampoo')->first();
        $conditioner = Category::where('slug', 'conditioner')->first();
        $hairOil = Category::where('slug', 'hair-oil')->first();
        $hairMask = Category::where('slug', 'hair-mask')->first();

        $olaplex = Brand::where('slug', 'olaplex')->first();
        $moroccanoil = Brand::where('slug', 'moroccanoil')->first();
        $avorga = Brand::where('slug', 'avorga')->first();

        $products = [
            [
                'name_ar' => 'شامبوOlaplex No.3',
                'name_en' => 'Olaplex No.3 Shampoo',
                'slug' => 'olaplex-no-3-shampoo',
                'sku' => 'OLA-003-S',
                'category_id' => $shampoo->id,
                'brand_id' => $olaplex->id,
                'description_ar' => 'شامبو علاجي لإعادة بناء الشعر التالف',
                'base_price' => 180,
                'b2c_price' => 180,
                'b2b_price' => 150,
                'stock_quantity' => 50,
                'status' => 'active',
                'is_featured' => true,
                'show_in_b2c' => true,
                'show_in_b2b' => true,
            ],
            [
                'name_ar' => 'زيت الأرغان الذهبي',
                'name_en' => 'Golden Argan Oil',
                'slug' => 'golden-argan-oil',
                'sku' => 'MOR-ARG-01',
                'category_id' => $hairOil->id,
                'brand_id' => $moroccanoil->id,
                'description_ar' => 'زيت أرغان مغذي للشعر الجاف والمتضرر',
                'base_price' => 150,
                'b2c_price' => 150,
                'b2b_price' => 120,
                'stock_quantity' => 40,
                'status' => 'active',
                'is_featured' => true,
                'show_in_b2c' => true,
                'show_in_b2b' => true,
            ],
            [
                'name_ar' => 'بلسمAvorga المخصص',
                'name_en' => 'Avorga Custom Conditioner',
                'slug' => 'avorga-custom-conditioner',
                'sku' => 'AVR-CON-01',
                'category_id' => $conditioner->id,
                'brand_id' => $avorga->id,
                'description_ar' => 'بلسم ترطيب عميق لجميع أنواع الشعر',
                'base_price' => 120,
                'b2c_price' => 120,
                'b2b_price' => 95,
                'stock_quantity' => 60,
                'status' => 'active',
                'is_featured' => true,
                'show_in_b2c' => true,
                'show_in_b2b' => true,
            ],
            [
                'name_ar' => 'ماسك复兴 للشعر',
                'name_en' => 'Recovery Hair Mask',
                'slug' => 'recovery-hair-mask',
                'sku' => 'OLA-MSK-01',
                'category_id' => $hairMask->id,
                'brand_id' => $olaplex->id,
                'description_ar' => 'ماسك علاجي للشعر التالف والهايش',
                'base_price' => 220,
                'b2c_price' => 220,
                'b2b_price' => 180,
                'stock_quantity' => 30,
                'status' => 'active',
                'is_featured' => true,
                'show_in_b2c' => true,
                'show_in_b2b' => true,
                'is_new' => true,
            ],
            [
                'name_ar' => 'شامبو منشط',
                'name_en' => 'Activating Shampoo',
                'slug' => 'activating-shampoo',
                'sku' => 'AVR-SHP-01',
                'category_id' => $shampoo->id,
                'brand_id' => $avorga->id,
                'description_ar' => 'شامبو منعش لتنشيط بصيلات الشعر',
                'base_price' => 90,
                'b2c_price' => 90,
                'b2b_price' => 70,
                'stock_quantity' => 45,
                'status' => 'active',
                'is_featured' => false,
                'show_in_b2c' => true,
                'show_in_b2b' => true,
                'is_new' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create(array_merge($product, [
                'tenant_id' => 1,
                'slug' => $product['slug'] . '-' . rand(1000, 9999),
            ]));
        }
    }
}
