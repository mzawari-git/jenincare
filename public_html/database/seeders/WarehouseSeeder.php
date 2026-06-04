<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Warehouse;
use Modules\Commerce\Models\Product;
use Modules\Commerce\Models\WarehouseStock;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // Create main warehouse in Ramallah
        $mainWarehouse = Warehouse::create([
            'code' => 'WH-RAM-MAIN',
            'name' => 'المستودع الرئيسي - رام الله',
            'name_en' => 'Main Warehouse - Ramallah',
            'address' => 'شارع القدس، رام الله',
            'city' => 'رام الله',
            'country' => 'PS',
            'latitude' => 31.9522,
            'longitude' => 35.2332,
            'phone' => '+972 2 123 4567',
            'email' => 'warehouse.ramallah@jenincare.com',
            'manager_name' => 'أحمد محمد',
            'type' => 'main',
            'is_active' => true,
            'capacity' => 10000,
            'operating_hours' => [
                'sunday' => ['8:00', '20:00'],
                'monday' => ['8:00', '20:00'],
                'tuesday' => ['8:00', '20:00'],
                'wednesday' => ['8:00', '20:00'],
                'thursday' => ['8:00', '20:00'],
                'friday' => ['closed'],
                'saturday' => ['9:00', '16:00']
            ],
            'settings' => [
                'auto_reorder' => true,
                'priority_shipping' => true,
                'temperature_controlled' => true
            ]
        ]);

        // Create branch warehouse in Gaza
        $gazaWarehouse = Warehouse::create([
            'code' => 'WH-GAZ-001',
            'name' => 'فرع غزة',
            'name_en' => 'Gaza Branch',
            'address' => 'شارع عمر المختار، غزة',
            'city' => 'غزة',
            'country' => 'PS',
            'latitude' => 31.3885,
            'longitude' => 34.3446,
            'phone' => '+972 8 234 5678',
            'email' => 'warehouse.gaza@jenincare.com',
            'manager_name' => 'فاطمة أحمد',
            'type' => 'branch',
            'is_active' => true,
            'capacity' => 5000,
            'operating_hours' => [
                'sunday' => ['9:00', '18:00'],
                'monday' => ['9:00', '18:00'],
                'tuesday' => ['9:00', '18:00'],
                'wednesday' => ['9:00', '18:00'],
                'thursday' => ['9:00', '18:00'],
                'friday' => ['closed'],
                'saturday' => ['10:00', '15:00']
            ],
            'settings' => [
                'auto_reorder' => false,
                'priority_shipping' => false,
                'temperature_controlled' => true
            ]
        ]);

        // Create pickup point in Nablus
        $nablusWarehouse = Warehouse::create([
            'code' => 'WH-NAB-PICKUP',
            'name' => 'نقطة استلام نابلس',
            'name_en' => 'Nablus Pickup Point',
            'address' => 'شارع رفيديا، نابلس',
            'city' => 'نابلس',
            'country' => 'PS',
            'latitude' => 32.2133,
            'longitude' => 35.2848,
            'phone' => '+972 9 345 6789',
            'email' => 'pickup.nablus@jenincare.com',
            'manager_name' => 'محمد علي',
            'type' => 'pickup',
            'is_active' => true,
            'capacity' => 1000,
            'operating_hours' => [
                'sunday' => ['10:00', '19:00'],
                'monday' => ['10:00', '19:00'],
                'tuesday' => ['10:00', '19:00'],
                'wednesday' => ['10:00', '19:00'],
                'thursday' => ['10:00', '19:00'],
                'friday' => ['closed'],
                'saturday' => ['11:00', '16:00']
            ],
            'settings' => [
                'auto_reorder' => false,
                'priority_shipping' => false,
                'temperature_controlled' => false
            ]
        ]);

        // Create virtual warehouse for online orders
        $virtualWarehouse = Warehouse::create([
            'code' => 'WH-VIRT-ONLINE',
            'name' => 'المستودع الافتراضي',
            'name_en' => 'Virtual Warehouse',
            'address' => 'متجر إلكتروني',
            'city' => 'رام الله',
            'country' => 'PS',
            'phone' => '+972 2 987 6543',
            'email' => 'online@jenincare.com',
            'manager_name' => 'نظام إدارة',
            'type' => 'virtual',
            'is_active' => true,
            'capacity' => null,
            'operating_hours' => [
                'sunday' => ['00:00', '23:59'],
                'monday' => ['00:00', '23:59'],
                'tuesday' => ['00:00', '23:59'],
                'wednesday' => ['00:00', '23:59'],
                'thursday' => ['00:00', '23:59'],
                'friday' => ['00:00', '23:59'],
                'saturday' => ['00:00', '23:59']
            ],
            'settings' => [
                'auto_reorder' => true,
                'priority_shipping' => true,
                'temperature_controlled' => false
            ]
        ]);

        // Initialize stock for some products in main warehouse
        $products = Product::take(20)->get();
        
        foreach ($products as $product) {
            WarehouseStock::create([
                'warehouse_id' => $mainWarehouse->id,
                'product_id' => $product->id,
                'quantity' => rand(50, 500),
                'reserved_quantity' => 0,
                'low_stock_threshold' => 10,
                'cost_price' => $product->base_price * 0.7,
                'location' => 'A-' . str_pad($product->id, 3, '0', STR_PAD_LEFT),
                'metadata' => [
                    'batch_number' => 'BATCH-' . date('Y-m-d') . '-' . $product->id,
                    'expiry_date' => now()->addYears(2)->toDateString(),
                    'received_date' => now()->toDateString()
                ],
                'last_updated_at' => now()
            ]);

            // Add some stock to Gaza warehouse
            if (rand(0, 1)) {
                WarehouseStock::create([
                    'warehouse_id' => $gazaWarehouse->id,
                    'product_id' => $product->id,
                    'quantity' => rand(20, 200),
                    'reserved_quantity' => 0,
                    'low_stock_threshold' => 5,
                    'cost_price' => $product->base_price * 0.7,
                    'location' => 'B-' . str_pad($product->id, 3, '0', STR_PAD_LEFT),
                    'metadata' => [
                        'batch_number' => 'BATCH-' . date('Y-m-d') . '-' . $product->id,
                        'expiry_date' => now()->addYears(2)->toDateString(),
                        'received_date' => now()->toDateString()
                    ],
                    'last_updated_at' => now()
                ]);
            }
        }

        $this->command->info('Warehouses created successfully!');
        $this->command->info('Main Warehouse: ' . $mainWarehouse->name);
        $this->command->info('Gaza Warehouse: ' . $gazaWarehouse->name);
        $this->command->info('Nablus Pickup: ' . $nablusWarehouse->name);
        $this->command->info('Virtual Warehouse: ' . $virtualWarehouse->name);
        $this->command->info('Stock initialized for ' . $products->count() . ' products');
    }
}
