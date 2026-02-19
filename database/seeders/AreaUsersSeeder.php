<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Secretaria;
use App\Models\Unidad;

class AreaUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Admin123*');

        // Secretarías
        $secPlaneacion = Secretaria::where('nombre', 'Secretaría de Planeación')->first();
        $secHacienda   = Secretaria::where('nombre', 'Secretaría de Hacienda')->first();
        $secJuridica   = Secretaria::where('nombre', 'Secretaría Jurídica')->first();
        $secGeneral    = Secretaria::where('nombre', 'Secretaría General')->first();

        // Unidades
        $uSistemas     = Unidad::where('nombre', 'Unidad de Sistemas')->first();
        $uPresupuesto  = Unidad::where('nombre', 'Unidad de Presupuesto')->first();
        $uContratacion = Unidad::where('nombre', 'Unidad de Contratación')->first();
        $uCompras      = Unidad::where('nombre', 'Unidad de Compras y Suministros')->first();

        $users = [
            [
                'name'          => 'Unidad Demo',
                'email'         => 'unidad@demo.com',
                'role'          => 'unidad_solicitante',
                'secretaria_id' => $secPlaneacion?->id,
                'unidad_id'     => $uSistemas?->id,
            ],
            [
                'name'          => 'Sistemas Planeación',
                'email'         => 'sistemas@demo.com',
                'role'          => 'unidad_solicitante',
                'secretaria_id' => $secPlaneacion?->id,
                'unidad_id'     => $uSistemas?->id,
            ],
            [
                'name'          => 'Planeación Demo',
                'email'         => 'planeacion@demo.com',
                'role'          => 'planeacion',
                'secretaria_id' => $secPlaneacion?->id,
                'unidad_id'     => null,
            ],
            [
                'name'          => 'Hacienda Demo',
                'email'         => 'hacienda@demo.com',
                'role'          => 'hacienda',
                'secretaria_id' => $secHacienda?->id,
                'unidad_id'     => $uPresupuesto?->id,
            ],
            [
                'name'          => 'Jurídica Demo',
                'email'         => 'juridica@demo.com',
                'role'          => 'juridica',
                'secretaria_id' => $secJuridica?->id,
                'unidad_id'     => $uContratacion?->id,
            ],
            [
                'name'          => 'SECOP Demo',
                'email'         => 'secop@demo.com',
                'role'          => 'secop',
                'secretaria_id' => $secGeneral?->id,
                'unidad_id'     => $uCompras?->id,
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'          => $data['name'],
                    'password'      => $password,
                    'secretaria_id' => $data['secretaria_id'],
                    'unidad_id'     => $data['unidad_id'],
                    'activo'        => true,
                ]
            );
            $user->syncRoles([$data['role']]);
        }
    }
}
