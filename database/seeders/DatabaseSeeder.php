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
        $users = [
            [
                'name' => 'BJ Admin',
                'email' => 'bilaljamsheed6@gmail.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'BJ Manager',
                'email' => 'manager@bjlaptophub.com',
                'role' => User::ROLE_MANAGER,
            ],
            [
                'name' => 'BJ Customer',
                'email' => 'customer@bjlaptophub.com',
                'role' => User::ROLE_CUSTOMER,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('pass123'),
                    'role' => $user['role'],
                ]
            );
        }
    }
}



