<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AreaUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Admin123*');

        $users = [
            [
                'name' => 'Unidad Demo',
                'email' => 'unidad@demo.com',
                'role' => 'unidad_solicitante',
            ],
            [
                'name' => 'Planeación Demo',
                'email' => 'planeacion@demo.com',
                'role' => 'planeacion',
            ],
            [
                'name' => 'Hacienda Demo',
                'email' => 'hacienda@demo.com',
                'role' => 'hacienda',
            ],
            [
                'name' => 'Jurídica Demo',
                'email' => 'juridica@demo.com',
                'role' => 'juridica',
            ],
            [
                'name' => 'SECOP Demo',
                'email' => 'secop@demo.com',
                'role' => 'secop',
            ],
        ];

        foreach ($users as $data) {

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $password,
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
