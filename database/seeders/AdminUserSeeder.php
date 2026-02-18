<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('12345'),
            ]
        );

        $admin->assignRole('admin');

        $admin2 = User::firstOrCreate(
            ['email' => 'jesin@demo.com'],
            [
                'name' => 'jesin',
                'password' => Hash::make('12345'),
            ]
        );

        $admin2->assignRole('admin');
    }
}

