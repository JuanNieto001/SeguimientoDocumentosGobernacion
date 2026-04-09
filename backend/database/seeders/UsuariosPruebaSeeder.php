<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Secretaria;
use App\Models\Unidad;

class UsuariosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Caldas2025*');

        /*
        |--------------------------------------------------------------------------
        | Cargamos secretarías y unidades para asignar usuarios
        |--------------------------------------------------------------------------
        */
        $secJuridica     = Secretaria::where('nombre', 'Secretaría Jurídica')->first();
        $secHacienda     = Secretaria::where('nombre', 'Secretaría de Hacienda')->first();
        $secPlaneacion   = Secretaria::where('nombre', 'Secretaría de Planeación')->first();
        $secGeneral      = Secretaria::where('nombre', 'Secretaría General')->first();
        $secInfra        = Secretaria::where('nombre', 'Secretaría de Infraestructura')->first();
        $secAgricultura  = Secretaria::where('nombre', 'Secretaría de Agricultura y Desarrollo Rural')->first();
        $secGobierno     = Secretaria::where('nombre', 'Secretaría de Gobierno')->first();
        $secCultura      = Secretaria::where('nombre', 'Secretaría de Cultura')->first();

        $uContratacion   = Unidad::where('nombre', 'Unidad de Contratación')->first();
        $uPresupuesto    = Unidad::where('nombre', 'Unidad de Presupuesto')->first();
        $uSistemas       = Unidad::where('nombre', 'Unidad de Sistemas')->first();
        $uCompras        = Unidad::where('nombre', 'Unidad de Compras y Suministros')->first();
        $uIngenieria     = Unidad::where('nombre', 'Unidad de Ingeniería')->first();
        $uDesarrolloR    = Unidad::where('nombre', 'Unidad de Desarrollo Rural')->first();
        $uDerechosH      = Unidad::where('nombre', 'Unidad de Derechos Humanos')->first();
        $uFomento        = Unidad::where('nombre', 'Unidad de Fomento y Promoción Cultural')->first();

        /*
        |--------------------------------------------------------------------------
        | 1. ADMINISTRADOR GENERAL (1)
        |--------------------------------------------------------------------------
        */
        $adminGeneral = User::firstOrCreate(
            ['email' => 'admin@caldas.gov.co'],
            [
                'name'     => 'Administrador General',
                'password' => $password,
                'activo'   => true,
            ]
        );
        $adminGeneral->syncRoles(['admin_general', 'admin']);

        /*
        |--------------------------------------------------------------------------
        | 2. ADMINISTRADORES DE SECRETARÍA (3)
        |--------------------------------------------------------------------------
        */
        $adminsSecretaria = [
            [
                'name'          => 'Admin Secretaría Jurídica',
                'email'         => 'admin.juridica@caldas.gov.co',
                'secretaria_id' => $secJuridica?->id,
                'unidad_id'     => null,
            ],
            [
                'name'          => 'Admin Secretaría Hacienda',
                'email'         => 'admin.hacienda@caldas.gov.co',
                'secretaria_id' => $secHacienda?->id,
                'unidad_id'     => null,
            ],
            [
                'name'          => 'Admin Secretaría Planeación',
                'email'         => 'admin.planeacion@caldas.gov.co',
                'secretaria_id' => $secPlaneacion?->id,
                'unidad_id'     => null,
            ],
        ];

        foreach ($adminsSecretaria as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'          => $data['name'],
                    'password'      => $password,
                    'secretaria_id' => $data['secretaria_id'],
                    'unidad_id'     => $data['unidad_id'],
                    'activo'        => true,
                ]
            );
            $user->syncRoles(['admin_secretaria']);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. PROFESIONALES DE CONTRATACIÓN (5)
        |--------------------------------------------------------------------------
        */
        $profesionales = [
            [
                'name'          => 'Profesional Contratación 1',
                'email'         => 'profesional1@caldas.gov.co',
                'secretaria_id' => $secJuridica?->id,
                'unidad_id'     => $uContratacion?->id,
            ],
            [
                'name'          => 'Profesional Contratación 2',
                'email'         => 'profesional2@caldas.gov.co',
                'secretaria_id' => $secHacienda?->id,
                'unidad_id'     => $uPresupuesto?->id,
            ],
            [
                'name'          => 'Profesional Contratación 3',
                'email'         => 'profesional3@caldas.gov.co',
                'secretaria_id' => $secGeneral?->id,
                'unidad_id'     => $uCompras?->id,
            ],
            [
                'name'          => 'Profesional Contratación 4',
                'email'         => 'profesional4@caldas.gov.co',
                'secretaria_id' => $secInfra?->id,
                'unidad_id'     => $uIngenieria?->id,
            ],
            [
                'name'          => 'Profesional Contratación 5',
                'email'         => 'profesional5@caldas.gov.co',
                'secretaria_id' => $secAgricultura?->id,
                'unidad_id'     => $uDesarrolloR?->id,
            ],
        ];

        foreach ($profesionales as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'          => $data['name'],
                    'password'      => $password,
                    'secretaria_id' => $data['secretaria_id'],
                    'unidad_id'     => $data['unidad_id'],
                    'activo'        => true,
                ]
            );
            $user->syncRoles(['profesional_contratacion']);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. REVISORES JURÍDICOS (2)
        |--------------------------------------------------------------------------
        */
        $revisores = [
            [
                'name'          => 'Revisor Jurídico 1',
                'email'         => 'juridico1@caldas.gov.co',
                'secretaria_id' => $secJuridica?->id,
                'unidad_id'     => $uContratacion?->id,
            ],
            [
                'name'          => 'Revisor Jurídico 2',
                'email'         => 'juridico2@caldas.gov.co',
                'secretaria_id' => $secJuridica?->id,
                'unidad_id'     => $uContratacion?->id,
            ],
        ];

        foreach ($revisores as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'          => $data['name'],
                    'password'      => $password,
                    'secretaria_id' => $data['secretaria_id'],
                    'unidad_id'     => $data['unidad_id'],
                    'activo'        => true,
                ]
            );
            $user->syncRoles(['revisor_juridico']);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. USUARIOS DE CONSULTA (3)
        |--------------------------------------------------------------------------
        */
        $consultas = [
            [
                'name'          => 'Consulta Gobierno',
                'email'         => 'consulta1@caldas.gov.co',
                'secretaria_id' => $secGobierno?->id,
                'unidad_id'     => $uDerechosH?->id,
            ],
            [
                'name'          => 'Consulta Cultura',
                'email'         => 'consulta2@caldas.gov.co',
                'secretaria_id' => $secCultura?->id,
                'unidad_id'     => $uFomento?->id,
            ],
            [
                'name'          => 'Consulta Sistemas',
                'email'         => 'consulta3@caldas.gov.co',
                'secretaria_id' => $secPlaneacion?->id,
                'unidad_id'     => $uSistemas?->id,
            ],
        ];

        foreach ($consultas as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'          => $data['name'],
                    'password'      => $password,
                    'secretaria_id' => $data['secretaria_id'],
                    'unidad_id'     => $data['unidad_id'],
                    'activo'        => true,
                ]
            );
            $user->syncRoles(['consulta']);
        }

        $this->command->info('✅ 14 usuarios de prueba creados correctamente.');
        $this->command->info('   Contraseña para todos: Caldas2025*');
    }
}
