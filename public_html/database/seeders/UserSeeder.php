<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@jenincare.com',
            'password' => Hash::make('password'),
            'phone' => '0591234567',
            'role' => 'admin',
            'tenant_id' => 1,
        ]);

        User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'phone' => '0591234568',
            'role' => 'customer',
            'tenant_id' => 1,
        ]);
    }
}
