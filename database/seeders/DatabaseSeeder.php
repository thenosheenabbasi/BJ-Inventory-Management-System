<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'BJ Admin',
            'email' => 'bilaljamsheed6@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('pass123'),
            'role' => User::ROLE_ADMIN,
        ]);

        User::factory()->create([
            'name' => 'BJ Manager',
            'email' => 'manager@bjlaptophub.com',
            'email_verified_at' => now(),
            'password' => Hash::make('pass123'),
            'role' => User::ROLE_MANAGER,
        ]);

        User::factory()->create([
            'name' => 'BJ Customer',
            'email' => 'customer@bjlaptophub.com',
            'email_verified_at' => now(),
            'password' => Hash::make('pass123'),
            'role' => User::ROLE_CUSTOMER,
        ]);
    }
}




