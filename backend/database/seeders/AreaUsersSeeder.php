<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Secretaria;
use App\Models\Unidad;

/**
 * AreaUsersSeeder – Usuarios específicos por dependencia para el flujo CD-PN
 * =============================================================================
 * 
 * Este seeder crea usuarios para cada actor del flujo de Contratación Directa.
 * Cada usuario tiene permisos específicos según su rol en el proceso.
 */
class AreaUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('12345');

        // ═════════════════════════════════════════════════════════════
        // OBTENER SECRETARÍAS
        // ═════════════════════════════════════════════════════════════
        $secPlaneacion = Secretaria::where('nombre', 'Secretaría de Planeación')->first();
        $secHacienda   = Secretaria::where('nombre', 'Secretaría de Hacienda')->first();
        $secJuridica   = Secretaria::where('nombre', 'Secretaría Jurídica')->first();
        $secGeneral    = Secretaria::where('nombre', 'Secretaría General')->first();

        // ═════════════════════════════════════════════════════════════
        // OBTENER UNIDADES ESPECÍFICAS
        // ═════════════════════════════════════════════════════════════
        $uSistemas           = Unidad::where('nombre', 'Unidad de Sistemas')->first();
        $uDescentralizacion  = Unidad::where('nombre', 'Unidad de Descentralización')->first();
        $uRegalias           = Unidad::where('nombre', 'Unidad de Regalías e Inversiones Públicas')->first();
        $uPresupuesto        = Unidad::where('nombre', 'Unidad de Presupuesto')->first();
        $uContabilidad       = Unidad::where('nombre', 'Unidad de Contabilidad')->first();
        $uRentas             = Unidad::where('nombre', 'Unidad de Rentas')->first();
        $uContratacion       = Unidad::where('nombre', 'Unidad de Contratación')->first();
        $uCompras            = Unidad::where('nombre', 'Unidad de Compras y Suministros')->first();
        $uTalentoHumano      = Unidad::where('nombre', 'Jefatura de Gestión del Talento Humano')->first();

        // ═════════════════════════════════════════════════════════════
        // USUARIOS POR DEPENDENCIA – FLUJO CD-PN
        // ═════════════════════════════════════════════════════════════
        $users = [
            // ─────────────────────────────────────────────────────────
            // UNIDAD SOLICITANTE (Unidad de Sistemas)
            // Responsable: Etapas 0, 2, 3, 4, 9
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Jefe Unidad Sistemas',
                'email'         => 'jefe.sistemas@demo.com',
                'roles'         => ['unidad_solicitante', 'jefe_unidad'],
                'secretaria_id' => $secPlaneacion?->id,
                'unidad_id'     => $uSistemas?->id,
            ],
            [
                'name'          => 'Abogado Unidad Sistemas',
                'email'         => 'abogado.sistemas@demo.com',
                'role'          => 'unidad_solicitante',
                'secretaria_id' => $secPlaneacion?->id,
                'unidad_id'     => $uSistemas?->id,
            ],

            // ─────────────────────────────────────────────────────────
            // UNIDAD DE DESCENTRALIZACIÓN – SECRETARÍA DE PLANEACIÓN
            // Responsable: Coordinar Etapa 1 (solicitud documentos)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Coordinador Descentralización',
                'email'         => 'descentralizacion@demo.com',
                'role'          => 'planeacion',
                'secretaria_id' => $secPlaneacion?->id,
                'unidad_id'     => $uDescentralizacion?->id,
            ],

            // ─────────────────────────────────────────────────────────
            // UNIDAD DE REGALÍAS E INVERSIONES PÚBLICAS
            // Responsable: Compatibilidad del Gasto (Etapa 1)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Analista Regalías e Inversiones',
                'email'         => 'regalias@demo.com',
                'roles'         => ['planeacion', 'inversiones_publicas'],
                'secretaria_id' => $secPlaneacion?->id,
                'unidad_id'     => $uRegalias?->id,
            ],

            // ─────────────────────────────────────────────────────────
            // SECRETARÍA DE PLANEACIÓN (Secretario)
            // Responsable: Firmar solicitud RPC (Etapa 7)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Secretario de Planeación',
                'email'         => 'secretario.planeacion@demo.com',
                'role'          => 'planeacion',
                'secretaria_id' => $secPlaneacion?->id,
                'unidad_id'     => null,
            ],

            // ─────────────────────────────────────────────────────────
            // SECRETARÍA DE HACIENDA – UNIDAD DE PRESUPUESTO
            // Responsable: Expedición CDP (Etapa 1) y RPC (Etapa 7)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Analista Presupuesto',
                'email'         => 'presupuesto@demo.com',
                'roles'         => ['hacienda', 'presupuesto'],
                'secretaria_id' => $secHacienda?->id,
                'unidad_id'     => $uPresupuesto?->id,
            ],

            // ─────────────────────────────────────────────────────────
            // SECRETARÍA DE HACIENDA – UNIDAD DE CONTABILIDAD
            // Responsable: Paz y Salvo Contabilidad (Etapa 1)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Analista Contabilidad',
                'email'         => 'contabilidad@demo.com',
                'roles'         => ['hacienda', 'contabilidad'],
                'secretaria_id' => $secHacienda?->id,
                'unidad_id'     => $uContabilidad?->id,
            ],

            // ─────────────────────────────────────────────────────────
            // SECRETARÍA DE HACIENDA – UNIDAD DE RENTAS
            // Responsable: Paz y Salvo Rentas (Etapa 1)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Analista Rentas',
                'email'         => 'rentas@demo.com',
                'roles'         => ['hacienda', 'rentas'],
                'secretaria_id' => $secHacienda?->id,
                'unidad_id'     => $uRentas?->id,
            ],

            // ─────────────────────────────────────────────────────────
            // SECRETARÍA JURÍDICA – UNIDAD DE CONTRATACIÓN
            // Responsable: Etapas 5 y 8 (Ajustado a Derecho, Número contrato)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Abogado Enlace Jurídica',
                'email'         => 'juridica@demo.com',
                'role'          => 'juridica',
                'secretaria_id' => $secJuridica?->id,
                'unidad_id'     => $uContratacion?->id,
            ],

            // ─────────────────────────────────────────────────────────
            // SECRETARÍA GENERAL – UNIDAD DE COMPRAS Y SUMINISTROS
            // Responsable: PAA (Etapa 1)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Coordinador Compras y Suministros',
                'email'         => 'compras@demo.com',
                'role'          => 'compras',
                'secretaria_id' => $secGeneral?->id,
                'unidad_id'     => $uCompras?->id,
            ],

            // ─────────────────────────────────────────────────────────
            // SECRETARÍA GENERAL – SECOP II
            // Responsable: Publicación SECOP II (Etapa 6)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Operador SECOP II',
                'email'         => 'secop@demo.com',
                'role'          => 'secop',
                'secretaria_id' => $secGeneral?->id,
                'unidad_id'     => $uCompras?->id,
            ],

            // ─────────────────────────────────────────────────────────
            // SECRETARÍA GENERAL – TALENTO HUMANO
            // Responsable: Certificado No Planta (Etapa 1)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Analista Talento Humano',
                'email'         => 'talentohumano@demo.com',
                'role'          => 'talento_humano',
                'secretaria_id' => $secGeneral?->id,
                'unidad_id'     => $uTalentoHumano?->id,
            ],

            // ─────────────────────────────────────────────────────────
            // SECRETARÍA GENERAL – RADICACIÓN Y CORRESPONDENCIA
            // Responsable: Documentos de Radicación (Etapa 1)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Analista Radicación',
                'email'         => 'radicacion@demo.com',
                'role'          => 'radicacion',
                'secretaria_id' => $secGeneral?->id,
                'unidad_id'     => null,
            ],

            // ─────────────────────────────────────────────────────────
            // USUARIOS DE COMPATIBILIDAD LEGACY (mantener)
            // ─────────────────────────────────────────────────────────
            [
                'name'          => 'Sistemas Planeación',
                'email'         => 'sistemas@demo.com',
                'role'          => 'unidad_solicitante',
                'secretaria_id' => $secPlaneacion?->id,
                'unidad_id'     => $uSistemas?->id,
            ],
            [
                'name'          => 'Hacienda Demo',
                'email'         => 'hacienda@demo.com',
                'role'          => 'hacienda',
                'secretaria_id' => $secHacienda?->id,
                'unidad_id'     => $uPresupuesto?->id,
            ],
        ];

        // ═════════════════════════════════════════════════════════════
        // CREAR O ACTUALIZAR USUARIOS
        // ═════════════════════════════════════════════════════════════
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
            // Soporta tanto 'role' (string) como 'roles' (array)
            $roles = $data['roles'] ?? [$data['role']];
            $user->syncRoles($roles);
        }

        $this->command->info('✅ Usuarios específicos por dependencia creados correctamente.');
    }
}
