<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => 'password',
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Organizer',
                'email' => 'organizer@example.com',
                'password' => 'password',
                'role' => 'organizer',
                'is_active' => true,
            ],
            [
                'name' => 'User',
                'email' => 'user@example.com',
                'password' => 'password',
                'role' => 'user',
                'is_active' => true,
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}

