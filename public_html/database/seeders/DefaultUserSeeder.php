<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'default@jenincare.com'],
            [
                'name' => 'Default User',
                'password' => Hash::make('password123'),
                'phone' => '0500000000',
            ]
        );

        $user->tokens()->delete();
        $token = $user->createToken('default-token', ['*']);

        $this->command->info('Token: ' . $token->plainTextToken);
    }
}
