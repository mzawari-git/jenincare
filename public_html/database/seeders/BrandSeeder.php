<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name_ar' => 'أول搁', 'name_en' => 'Olaplex', 'slug' => 'olaplex', 'is_active' => true, 'sort_order' => 1],
            ['name_ar' => 'مورoccان', 'name_en' => 'Moroccanoil', 'slug' => 'moroccanoil', 'is_active' => true, 'sort_order' => 2],
            ['name_ar' => 'كريستاست', 'name_en' => 'Christophe Robin', 'slug' => 'christophe-robin', 'is_active' => true, 'sort_order' => 3],
            ['name_ar' => 'أفروجا', 'name_en' => 'Avorga', 'slug' => 'avorga', 'is_active' => true, 'sort_order' => 4],
        ];

        foreach ($brands as $brand) {
            Brand::create(array_merge($brand, ['tenant_id' => 1]));
        }
    }
}
