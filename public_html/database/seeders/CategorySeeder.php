<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name_ar' => 'شامبو', 'name_en' => 'Shampoo', 'slug' => 'shampoo', 'is_active' => true, 'is_featured' => true, 'sort_order' => 1],
            ['name_ar' => 'بلسم', 'name_en' => 'Conditioner', 'slug' => 'conditioner', 'is_active' => true, 'is_featured' => true, 'sort_order' => 2],
            ['name_ar' => 'زيت للشعر', 'name_en' => 'Hair Oil', 'slug' => 'hair-oil', 'is_active' => true, 'is_featured' => true, 'sort_order' => 3],
            ['name_ar' => 'ماسك', 'name_en' => 'Hair Mask', 'slug' => 'hair-mask', 'is_active' => true, 'is_featured' => true, 'sort_order' => 4],
            ['name_ar' => 'سبراي', 'name_en' => 'Spray', 'slug' => 'spray', 'is_active' => true, 'is_featured' => false, 'sort_order' => 5],
            ['name_ar' => 'جل', 'name_en' => 'Gel', 'slug' => 'gel', 'is_active' => true, 'is_featured' => false, 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            Category::create(array_merge($category, ['tenant_id' => 1]));
        }
    }
}
