<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesProductionSeeder extends Seeder
{
    /**
     * Seeder de roles y permisos optimizado para ambiente de producción
     *
     * Define la estructura completa de roles específicos para contratación pública
     * con permisos granulares y roles diferenciados por responsabilidades.
     */
    public function run(): void
    {
        $this->command->info('👥 Creando estructura de roles y permisos para producción...');

        // Definición completa de roles para producción
        $rolesProduction = [
            // ROLES ADMINISTRATIVOS
            'super_admin' => [
                'label' => 'Super Administrador',
                'descripcion' => 'Acceso total al sistema, configuración y gestión completa',
                'permisos' => [
                    'admin.*', 'usuarios.*', 'configuracion.*', 'sistema.*',
                    'auditoria.*', 'roles.*', 'permisos.*', 'database.*'
                ]
            ],
            'admin_sistema' => [
                'label' => 'Administrador de Sistema',
                'descripcion' => 'Gestión de usuarios, configuración básica del sistema',
                'permisos' => [
                    'admin.gestionar', 'usuarios.crear', 'usuarios.editar', 'usuarios.ver',
                    'configuracion.dashboard', 'configuracion.flujos', 'reportes.sistema'
                ]
            ],

            // ROLES EJECUTIVOS
            'gobernador' => [
                'label' => 'Gobernador',
                'descripcion' => 'Vista ejecutiva estratégica, reportes consolidados',
                'permisos' => [
                    'dashboard.ejecutivo', 'procesos.ver_todos', 'reportes.ejecutivos',
                    'presupuesto.ver_global', 'contratos.ver_todos', 'secretarias.ver_todas',
                    'indicadores.estrategicos', 'alertas.criticas'
                ]
            ],
            'secretario' => [
                'label' => 'Secretario de Despacho',
                'descripcion' => 'Gestión operativa de secretaría, aprobaciones',
                'permisos' => [
                    'dashboard.secretaria', 'procesos.secretaria', 'reportes.secretaria',
                    'procesos.aprobar', 'documentos.revisar', 'contratos.secretaria',
                    'presupuesto.secretaria', 'equipo.ver', 'alertas.secretaria'
                ]
            ],
            'jefe_unidad' => [
                'label' => 'Jefe de Unidad',
                'descripcion' => 'Gestión de unidad, supervisión de equipo',
                'permisos' => [
                    'dashboard.unidad', 'procesos.unidad', 'equipo.gestionar',
                    'asignaciones.crear', 'asignaciones.modificar', 'reportes.unidad',
                    'carga_trabajo.ver', 'rendimiento.equipo', 'alertas.unidad'
                ]
            ],

            // ROLES OPERATIVOS CONTRATACIÓN
            'coord_contratacion' => [
                'label' => 'Coordinador de Contratación',
                'descripcion' => 'Coordinación general de procesos contractuales',
                'permisos' => [
                    'procesos.crear', 'procesos.gestionar', 'procesos.coordinar',
                    'documentos.subir', 'documentos.organizar', 'flujos.ejecutar',
                    'equipos.asignar', 'plazos.gestionar', 'secop.coordinar',
                    'reportes.coordinacion', 'dashboard.coordinacion'
                ]
            ],
            'prof_contratacion' => [
                'label' => 'Profesional de Contratación',
                'descripcion' => 'Ejecución de procesos contractuales específicos',
                'permisos' => [
                    'procesos.crear', 'procesos.ejecutar', 'documentos.subir',
                    'documentos.elaborar', 'flujos.ejecutar', 'estudios.elaborar',
                    'especificaciones.crear', 'minutas.elaborar', 'secop.gestionar',
                    'dashboard.profesional'
                ]
            ],
            'aux_contratacion' => [
                'label' => 'Auxiliar de Contratación',
                'descripcion' => 'Apoyo en documentación y gestión administrativa',
                'permisos' => [
                    'documentos.subir', 'documentos.organizar', 'procesos.consultar',
                    'archivos.gestionar', 'comunicaciones.gestionar', 'secop.apoyo',
                    'dashboard.auxiliar'
                ]
            ],

            // ROLES DE REVISIÓN
            'revisor_juridico' => [
                'label' => 'Revisor Jurídico',
                'descripcion' => 'Revisión jurídica y emisión de conceptos legales',
                'permisos' => [
                    'juridica.revisar', 'conceptos.emitir', 'documentos.aprobar',
                    'normativa.consultar', 'precedentes.consultar', 'minutas.revisar',
                    'clausulas.validar', 'riesgos.evaluar', 'dashboard.juridica'
                ]
            ],
            'revisor_presupuestal' => [
                'label' => 'Revisor Presupuestal',
                'descripcion' => 'Verificación presupuestal y expedición de CDP/RPC',
                'permisos' => [
                    'hacienda.revisar', 'presupuesto.verificar', 'cdp.expedir',
                    'rpc.expedir', 'disponibilidad.verificar', 'rubros.validar',
                    'vigencias.controlar', 'registros.presupuesto', 'dashboard.hacienda'
                ]
            ],
            'revisor_tecnico' => [
                'label' => 'Revisor Técnico',
                'descripcion' => 'Validación técnica de estudios y especificaciones',
                'permisos' => [
                    'estudios.revisar', 'especificaciones.validar', 'tecnica.evaluar',
                    'requisitos.verificar', 'planos.revisar', 'alcances.validar',
                    'metodologias.evaluar', 'dashboard.tecnica'
                ]
            ],

            // ROLES ESPECIALIZADOS
            'secop_operator' => [
                'label' => 'Operador SECOP',
                'descripcion' => 'Gestión especializada de SECOP II',
                'permisos' => [
                    'secop.publicar', 'secop.gestionar', 'contratos.publicar',
                    'secop.configurar', 'documentos.secop', 'procesos.secop',
                    'notificaciones.secop', 'reportes.secop', 'dashboard.secop'
                ]
            ],
            'auditor_interno' => [
                'label' => 'Auditor Interno',
                'descripcion' => 'Auditoría y control interno de procesos',
                'permisos' => [
                    'auditoria.acceder', 'logs.ver', 'compliance.verificar',
                    'trazabilidad.revisar', 'indicadores.auditoria', 'riesgos.evaluar',
                    'controles.verificar', 'reportes.auditoria', 'dashboard.auditoria'
                ]
            ],
            'consulta_ciudadana' => [
                'label' => 'Consulta Ciudadana',
                'descripcion' => 'Acceso público a información de contratación',
                'permisos' => [
                    'procesos.consultar_publicos', 'contratos.consultar_publicos',
                    'documentos.consultar_publicos', 'estadisticas.publicas'
                ]
            ]
        ];

        // Crear roles y asignar permisos
        foreach ($rolesProduction as $roleName => $roleData) {
            $this->command->info("   Creando rol: {$roleData['label']}");

            $role = Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'guard_name' => 'web'
                ]
            );

            // Crear y asignar permisos
            foreach ($roleData['permisos'] as $permisoPattern) {
                if ($permisoPattern === 'admin.*' || $permisoPattern === '*') {
                    // Super admin tiene todos los permisos
                    $role->syncPermissions(Permission::all());
                } else {
                    // Crear permiso específico si no existe
                    $permission = Permission::firstOrCreate([
                        'name' => $permisoPattern,
                        'guard_name' => 'web'
                    ]);

                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }

            $permissionsCount = $role->permissions()->count();
            $this->command->info("     ✅ Permisos asignados: {$permissionsCount}");
        }

        $this->command->info('');
        $this->command->info('📊 Estructura de roles creada exitosamente:');
        $this->command->info('   👑 Roles Ejecutivos: 3');
        $this->command->info('   ⚙️  Roles Operativos: 3');
        $this->command->info('   🔍 Roles de Revisión: 3');
        $this->command->info('   🎯 Roles Especializados: 3');
        $this->command->info('   🛡️  Roles Administrativos: 2');
        $this->command->info('   📊 Total de Roles: ' . count($rolesProduction));

        // Log del sistema
        \Log::info('Estructura de roles de producción creada', [
            'total_roles' => count($rolesProduction),
            'total_permissions' => Permission::count(),
            'created_at' => now()
        ]);
    }
}