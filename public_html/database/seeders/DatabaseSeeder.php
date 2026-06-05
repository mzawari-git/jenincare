<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            BlogPostSeeder::class,
            AIProviderSeeder::class,
            WarehouseSeeder::class,
            TriggerWordSeeder::class,
            DefaultUserSeeder::class,
        ]);
    }
}
