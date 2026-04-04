<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleScopeLevelSeeder extends Seeder
{
    /**
     * Poblar el campo scope_level en los roles existentes.
     * 
     * Basado en el análisis del sistema:
     * - global: acceso a todos los procesos sin filtro
     * - secretaria: solo procesos de su secretaría
     * - unidad: solo procesos de su unidad
     */
    public function run(): void
    {
        $scopeMap = [
            // Administradores - scope global
            'admin'             => 'global',
            'admin_general'     => 'global',
            
            // Administrador de secretaría - scope secretaría
            'admin_secretaria'  => 'secretaria',
            
            // Roles ejecutivos - scope secretaría (ven toda su dependencia)
            'gobernador'        => 'global',      // Despacho ve todo
            'secretario'        => 'secretaria',  // Ve su secretaría
            
            // Roles operativos principales - scope secretaría
            'planeacion'        => 'secretaria',  // Coordina flujos de toda planeación
            
            // Roles específicos por unidad - scope unidad
            'unidad_solicitante'     => 'unidad',
            'hacienda'               => 'unidad',
            'juridica'               => 'unidad',
            'secop'                  => 'secretaria', // SECOP ve procesos de planeación
            'talento_humano'         => 'unidad',
            'compras'                => 'unidad',
            'contabilidad'           => 'unidad',
            'rentas'                 => 'unidad',
            'inversiones_publicas'   => 'unidad',
            'presupuesto'            => 'unidad',
            'radicacion'             => 'unidad',
            
            // Roles de gestión - scope según jerarquía
            'profesional_contratacion' => 'unidad',
            'revisor_juridico'         => 'unidad',
            'jefe_unidad'              => 'unidad',
            
            // Consulta - scope unidad (solo ve su área)
            'consulta'          => 'unidad',
        ];

        foreach ($scopeMap as $roleName => $scopeLevel) {
            Role::where('name', $roleName)
                ->update(['scope_level' => $scopeLevel]);
        }

        // Todos los roles no mapeados quedan con el default 'unidad' 
        // definido en la migración

        $this->command->info('✅ scope_level poblado en ' . count($scopeMap) . ' roles.');
    }
}
