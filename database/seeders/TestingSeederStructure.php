<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Secretaria;
use App\Models\Unidad;
use App\Models\Proceso;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\DocumentType;

class TestingSeederStructure extends Seeder
{
    /**
     * Seeder especializado para testing automatizado
     * Crea estructura completa y datos predecibles para Cypress E2E
     */
    public function run()
    {
        // Limpiar datos existentes en orden correcto
        $this->cleanDatabase();

        // 1. Crear permisos básicos
        $this->createPermissions();

        // 2. Crear roles del sistema
        $this->createRoles();

        // 3. Crear estructura organizacional
        $this->createOrganizationalStructure();

        // 4. Crear workflows y etapas
        $this->createWorkflows();

        // 5. Crear tipos de documentos
        $this->createDocumentTypes();

        // 6. Crear usuarios de testing
        $this->createTestingUsers();

        // 7. Crear procesos de testing
        $this->createTestingProcesses();

        // 8. Crear configuración de dashboard
        $this->createDashboardConfig();

        $this->command->info('✅ TestingSeederStructure completed successfully');
    }

    /**
     * Limpiar base de datos en orden correcto
     */
    private function cleanDatabase(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpiar en orden inverso a las dependencias
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('dashboard_widgets')->truncate();
        DB::table('dashboard_layouts')->truncate();
        DB::table('proceso_documents')->truncate();
        DB::table('proceso_stage_history')->truncate();
        DB::table('procesos')->truncate();
        DB::table('workflow_stage_documents')->truncate();
        DB::table('workflow_stages')->truncate();
        DB::table('workflows')->truncate();
        DB::table('document_types')->truncate();
        DB::table('users')->truncate();
        DB::table('unidades')->truncate();
        DB::table('secretarias')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Crear permisos del sistema
     */
    private function createPermissions(): void
    {
        $permissions = [
            // Gestión de usuarios
            ['name' => 'users.view', 'description' => 'Ver usuarios'],
            ['name' => 'users.create', 'description' => 'Crear usuarios'],
            ['name' => 'users.edit', 'description' => 'Editar usuarios'],
            ['name' => 'users.delete', 'description' => 'Eliminar usuarios'],

            // Gestión de procesos
            ['name' => 'processes.view_all', 'description' => 'Ver todos los procesos'],
            ['name' => 'processes.view_own', 'description' => 'Ver procesos propios'],
            ['name' => 'processes.view_secretaria', 'description' => 'Ver procesos de secretaría'],
            ['name' => 'processes.create', 'description' => 'Crear procesos'],
            ['name' => 'processes.edit', 'description' => 'Editar procesos'],
            ['name' => 'processes.delete', 'description' => 'Eliminar procesos'],
            ['name' => 'processes.advance_stage', 'description' => 'Avanzar etapas de proceso'],

            // Gestión de documentos
            ['name' => 'documents.view', 'description' => 'Ver documentos'],
            ['name' => 'documents.upload', 'description' => 'Subir documentos'],
            ['name' => 'documents.download', 'description' => 'Descargar documentos'],
            ['name' => 'documents.delete', 'description' => 'Eliminar documentos'],

            // Revisiones especializadas
            ['name' => 'juridical.review', 'description' => 'Revisión jurídica'],
            ['name' => 'budgetary.review', 'description' => 'Revisión presupuestal'],
            ['name' => 'secop.manage', 'description' => 'Gestión SECOP II'],

            // Dashboard y reportes
            ['name' => 'dashboard.view_global', 'description' => 'Ver dashboard global'],
            ['name' => 'dashboard.view_secretaria', 'description' => 'Ver dashboard secretarial'],
            ['name' => 'dashboard.configure', 'description' => 'Configurar dashboard'],
            ['name' => 'reports.generate', 'description' => 'Generar reportes'],
            ['name' => 'reports.export', 'description' => 'Exportar reportes'],

            // Administración del sistema
            ['name' => 'system.configure', 'description' => 'Configurar sistema'],
            ['name' => 'system.logs', 'description' => 'Ver logs del sistema'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }
    }

    /**
     * Crear roles del sistema
     */
    private function createRoles(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'description' => 'Administrador del Sistema',
                'permissions' => ['*'] // Todos los permisos
            ],
            [
                'name' => 'gobernador',
                'description' => 'Gobernador de Caldas',
                'permissions' => [
                    'processes.view_all', 'dashboard.view_global',
                    'reports.generate', 'reports.export'
                ]
            ],
            [
                'name' => 'secretario',
                'description' => 'Secretario de Despacho',
                'permissions' => [
                    'processes.view_secretaria', 'processes.create', 'processes.edit',
                    'dashboard.view_secretaria', 'reports.generate'
                ]
            ],
            [
                'name' => 'coordinador_contratacion',
                'description' => 'Coordinador de Contratación',
                'permissions' => [
                    'processes.view_all', 'processes.create', 'processes.edit',
                    'processes.advance_stage', 'documents.upload', 'documents.view'
                ]
            ],
            [
                'name' => 'profesional_contratacion',
                'description' => 'Profesional de Contratación',
                'permissions' => [
                    'processes.view_own', 'processes.edit', 'documents.upload',
                    'documents.view', 'processes.advance_stage'
                ]
            ],
            [
                'name' => 'revisor_juridico',
                'description' => 'Revisor Jurídico',
                'permissions' => [
                    'juridical.review', 'processes.view_own', 'documents.view'
                ]
            ],
            [
                'name' => 'revisor_presupuestal',
                'description' => 'Revisor Presupuestal',
                'permissions' => [
                    'budgetary.review', 'processes.view_own', 'documents.view'
                ]
            ],
            [
                'name' => 'operador_secop',
                'description' => 'Operador SECOP II',
                'permissions' => [
                    'secop.manage', 'processes.view_own', 'documents.view'
                ]
            ],
            [
                'name' => 'jefe_unidad',
                'description' => 'Jefe de Unidad',
                'permissions' => [
                    'processes.view_secretaria', 'processes.create',
                    'dashboard.view_secretaria'
                ]
            ],
            [
                'name' => 'consulta_ciudadana',
                'description' => 'Consulta Ciudadana',
                'permissions' => []
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                ['description' => $roleData['description']]
            );

            if ($roleData['permissions'][0] === '*') {
                // Super admin obtiene todos los permisos
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($roleData['permissions']);
            }
        }
    }

    /**
     * Crear estructura organizacional
     */
    private function createOrganizationalStructure(): void
    {
        $secretarias = [
            ['id' => 1, 'nombre' => 'Secretaría de Planeación', 'codigo' => 'PLAN'],
            ['id' => 2, 'nombre' => 'Secretaría de Hacienda', 'codigo' => 'HAC'],
            ['id' => 3, 'nombre' => 'Secretaría de Gobierno', 'codigo' => 'GOB'],
            ['id' => 4, 'nombre' => 'Secretaría de Infraestructura', 'codigo' => 'INF'],
            ['id' => 5, 'nombre' => 'Secretaría de Educación', 'codigo' => 'EDU'],
            ['id' => 6, 'nombre' => 'Secretaría de Salud', 'codigo' => 'SAL'],
            ['id' => 7, 'nombre' => 'Secretaría de Agricultura', 'codigo' => 'AGR']
        ];

        foreach ($secretarias as $secretaria) {
            Secretaria::firstOrCreate(['id' => $secretaria['id']], $secretaria);
        }

        $unidades = [
            ['id' => 1, 'nombre' => 'Unidad de Sistemas de Información', 'secretaria_id' => 1],
            ['id' => 2, 'nombre' => 'Unidad de Contratación', 'secretaria_id' => 1],
            ['id' => 3, 'nombre' => 'Unidad de Presupuesto', 'secretaria_id' => 2],
            ['id' => 4, 'nombre' => 'Unidad de Tesorería', 'secretaria_id' => 2],
            ['id' => 5, 'nombre' => 'Unidad Jurídica', 'secretaria_id' => 3],
            ['id' => 6, 'nombre' => 'Unidad de Comunicaciones', 'secretaria_id' => 3],
            ['id' => 7, 'nombre' => 'Unidad de Obras Públicas', 'secretaria_id' => 4],
            ['id' => 8, 'nombre' => 'Unidad de Mantenimiento', 'secretaria_id' => 4]
        ];

        foreach ($unidades as $unidad) {
            Unidad::firstOrCreate(['id' => $unidad['id']], $unidad);
        }
    }

    /**
     * Crear workflows y etapas
     */
    private function createWorkflows(): void
    {
        // Workflow CD-PN Optimizado (10 etapas)
        $workflow = Workflow::firstOrCreate([
            'id' => 1,
            'name' => 'CD_PN_OPTIMIZED',
            'description' => 'Contratación Directa Persona Natural - 10 Etapas',
            'is_active' => true,
            'created_by' => 1
        ]);

        $stages = [
            [
                'stage_number' => 0,
                'name' => 'Identificación de la Necesidad',
                'description' => 'Identificar y documentar la necesidad contractual',
                'estimated_days' => 2,
                'responsible_role' => 'profesional_contratacion'
            ],
            [
                'stage_number' => 1,
                'name' => 'Elaboración de Estudios Previos',
                'description' => 'Elaborar estudios previos y especificaciones técnicas',
                'estimated_days' => 5,
                'responsible_role' => 'profesional_contratacion'
            ],
            [
                'stage_number' => 2,
                'name' => 'Validación del Contratista',
                'description' => 'Validar documentos y capacidad del contratista',
                'estimated_days' => 3,
                'responsible_role' => 'profesional_contratacion'
            ],
            [
                'stage_number' => 3,
                'name' => 'Revisión Presupuestal',
                'description' => 'Verificar disponibilidad presupuestal y generar CDP',
                'estimated_days' => 2,
                'responsible_role' => 'revisor_presupuestal'
            ],
            [
                'stage_number' => 4,
                'name' => 'Consolidación Expediente Precontractual',
                'description' => 'Consolidar toda la documentación precontractual',
                'estimated_days' => 3,
                'responsible_role' => 'coordinador_contratacion'
            ],
            [
                'stage_number' => 5,
                'name' => 'Revisión Jurídica',
                'description' => 'Revisión y concepto jurídico del proceso',
                'estimated_days' => 4,
                'responsible_role' => 'revisor_juridico'
            ],
            [
                'stage_number' => 6,
                'name' => 'Publicación SECOP II',
                'description' => 'Publicar proceso en la plataforma SECOP II',
                'estimated_days' => 1,
                'responsible_role' => 'operador_secop'
            ],
            [
                'stage_number' => 7,
                'name' => 'Solicitud de RPC',
                'description' => 'Solicitar y obtener Registro Presupuestal de Compromiso',
                'estimated_days' => 2,
                'responsible_role' => 'revisor_presupuestal'
            ],
            [
                'stage_number' => 8,
                'name' => 'Suscripción del Contrato',
                'description' => 'Firma del contrato y constitución de garantías',
                'estimated_days' => 3,
                'responsible_role' => 'coordinador_contratacion'
            ],
            [
                'stage_number' => 9,
                'name' => 'Inicio de Ejecución',
                'description' => 'Inicio formal de la ejecución contractual',
                'estimated_days' => 2,
                'responsible_role' => 'coordinador_contratacion'
            ]
        ];

        foreach ($stages as $stageData) {
            WorkflowStage::firstOrCreate([
                'workflow_id' => $workflow->id,
                'stage_number' => $stageData['stage_number']
            ], [
                'workflow_id' => $workflow->id,
                'name' => $stageData['name'],
                'description' => $stageData['description'],
                'estimated_days' => $stageData['estimated_days'],
                'responsible_role' => $stageData['responsible_role'],
                'is_required' => true,
                'allows_parallel' => false
            ]);
        }
    }

    /**
     * Crear tipos de documentos
     */
    private function createDocumentTypes(): void
    {
        $documentTypes = [
            // Etapa 0
            ['name' => 'Solicitud de Proceso', 'stage' => 0, 'required' => true],
            ['name' => 'Verificación PAA', 'stage' => 0, 'required' => true],

            // Etapa 1
            ['name' => 'Estudios Previos', 'stage' => 1, 'required' => true],
            ['name' => 'Matriz de Riesgos', 'stage' => 1, 'required' => true],
            ['name' => 'Especificaciones Técnicas', 'stage' => 1, 'required' => true],

            // Etapa 2
            ['name' => 'Hoja de Vida SIGEP', 'stage' => 2, 'required' => true],
            ['name' => 'Cédula del Contratista', 'stage' => 2, 'required' => true],
            ['name' => 'RUT del Contratista', 'stage' => 2, 'required' => true],
            ['name' => 'Certificación de Experiencia', 'stage' => 2, 'required' => true],
            ['name' => 'Diplomas y Acreditación', 'stage' => 2, 'required' => false],
            ['name' => 'Antecedentes', 'stage' => 2, 'required' => true],

            // Etapa 3
            ['name' => 'Solicitud CDP', 'stage' => 3, 'required' => true],
            ['name' => 'Formato Presupuestal', 'stage' => 3, 'required' => true],
            ['name' => 'Justificación del Valor', 'stage' => 3, 'required' => true],

            // Etapa 4
            ['name' => 'Minuta del Contrato', 'stage' => 4, 'required' => true],
            ['name' => 'Cronograma de Actividades', 'stage' => 4, 'required' => true],

            // Etapa 8
            ['name' => 'Pólizas y Seguros', 'stage' => 8, 'required' => true],

            // Etapa 9
            ['name' => 'Afiliación ARL', 'stage' => 9, 'required' => true],
            ['name' => 'Acta de Inicio', 'stage' => 9, 'required' => true],
        ];

        foreach ($documentTypes as $docType) {
            DocumentType::firstOrCreate([
                'name' => $docType['name']
            ], [
                'name' => $docType['name'],
                'stage_number' => $docType['stage'],
                'is_required' => $docType['required'],
                'max_file_size' => 5120, // 5MB
                'allowed_extensions' => ['pdf', 'doc', 'docx']
            ]);
        }
    }

    /**
     * Crear usuarios de testing
     */
    private function createTestingUsers(): void
    {
        $testUsers = [
            [
                'name' => 'Administrador Sistema',
                'email' => 'admin.sistema@gobernacion-caldas.gov.co',
                'password' => 'TestingPassword123!',
                'role' => 'super_admin',
                'secretaria_id' => null,
                'unidad_id' => null
            ],
            [
                'name' => 'Carlos Eduardo Osorio Buriticá',
                'email' => 'gobernador@gobernacion-caldas.gov.co',
                'password' => 'TestingPassword123!',
                'role' => 'gobernador',
                'secretaria_id' => null,
                'unidad_id' => null
            ],
            [
                'name' => 'María Elena Rodríguez',
                'email' => 'secretario.planeacion@gobernacion-caldas.gov.co',
                'password' => 'TestingPassword123!',
                'role' => 'secretario',
                'secretaria_id' => 1,
                'unidad_id' => null
            ],
            [
                'name' => 'Jorge Luis Martínez',
                'email' => 'secretario.hacienda@gobernacion-caldas.gov.co',
                'password' => 'TestingPassword123!',
                'role' => 'secretario',
                'secretaria_id' => 2,
                'unidad_id' => null
            ],
            [
                'name' => 'Ana Patricia Gómez',
                'email' => 'coord.contratacion@gobernacion-caldas.gov.co',
                'password' => 'TestingPassword123!',
                'role' => 'coordinador_contratacion',
                'secretaria_id' => 1,
                'unidad_id' => 2
            ],
            [
                'name' => 'Luis Fernando Torres',
                'email' => 'profesional.contratacion@gobernacion-caldas.gov.co',
                'password' => 'TestingPassword123!',
                'role' => 'profesional_contratacion',
                'secretaria_id' => 1,
                'unidad_id' => 2
            ],
            [
                'name' => 'Carmen Lucía Vásquez',
                'email' => 'revisor.juridico@gobernacion-caldas.gov.co',
                'password' => 'TestingPassword123!',
                'role' => 'revisor_juridico',
                'secretaria_id' => 3,
                'unidad_id' => 5
            ],
            [
                'name' => 'Roberto Carlos Henao',
                'email' => 'revisor.presupuestal@gobernacion-caldas.gov.co',
                'password' => 'TestingPassword123!',
                'role' => 'revisor_presupuestal',
                'secretaria_id' => 2,
                'unidad_id' => 3
            ],
            [
                'name' => 'Diana Marcela López',
                'email' => 'operador.secop@gobernacion-caldas.gov.co',
                'password' => 'TestingPassword123!',
                'role' => 'operador_secop',
                'secretaria_id' => 1,
                'unidad_id' => 1
            ],
            [
                'name' => 'Fernando Andrés Mejía',
                'email' => 'jefe.sistemas@gobernacion-caldas.gov.co',
                'password' => 'TestingPassword123!',
                'role' => 'jefe_unidad',
                'secretaria_id' => 1,
                'unidad_id' => 1
            ],
            [
                'name' => 'Usuario Consulta',
                'email' => 'consulta.ciudadana@gobernacion-caldas.gov.co',
                'password' => 'TestingPassword123!',
                'role' => 'consulta_ciudadana',
                'secretaria_id' => null,
                'unidad_id' => null
            ]
        ];

        foreach ($testUsers as $userData) {
            $user = User::firstOrCreate([
                'email' => $userData['email']
            ], [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'email_verified_at' => now(),
                'secretaria_id' => $userData['secretaria_id'],
                'unidad_id' => $userData['unidad_id'],
                'is_active' => true
            ]);

            if ($userData['role'] === 'super_admin') {
                $roles = collect(['super_admin', 'admin_general', 'admin'])
                    ->filter(fn ($roleName) => \Spatie\Permission\Models\Role::where('name', $roleName)->exists())
                    ->values()
                    ->all();

                if (!empty($roles)) {
                    $user->syncRoles($roles);
                }
            } else {
                $user->assignRole($userData['role']);
            }
        }
    }

    /**
     * Crear procesos de testing
     */
    private function createTestingProcesses(): void
    {
        $testProcesses = [
            [
                'codigo' => 'TEST-CD-PN-001-2026',
                'objeto' => 'Prestación de servicios profesionales para desarrollo de software',
                'descripcion' => 'Desarrollo e implementación de módulo de gestión documental para el sistema de contratación',
                'valor_estimado' => 45000000,
                'plazo_ejecucion' => '60 días',
                'contratista_nombre' => 'Juan Carlos Pérez Rodríguez',
                'contratista_documento' => '1234567890',
                'workflow_id' => 1,
                'current_stage' => 0,
                'secretaria_id' => 1,
                'unidad_id' => 1,
                'created_by' => 5, // coord_contratacion
                'assigned_to' => 6  // profesional_contratacion
            ],
            [
                'codigo' => 'TEST-CD-PN-002-2026',
                'objeto' => 'Consultoría técnica especializada en sistemas de información',
                'descripcion' => 'Análisis y optimización de la arquitectura de sistemas existente',
                'valor_estimado' => 35000000,
                'plazo_ejecucion' => '45 días',
                'contratista_nombre' => 'María Fernanda Gómez López',
                'contratista_documento' => '9876543210',
                'workflow_id' => 1,
                'current_stage' => 1,
                'secretaria_id' => 1,
                'unidad_id' => 1,
                'created_by' => 5,
                'assigned_to' => 6
            ],
            [
                'codigo' => 'TEST-CD-PN-003-2026',
                'objeto' => 'Mantenimiento preventivo y correctivo de equipos de cómputo',
                'descripcion' => 'Servicio de mantenimiento para 150 equipos de cómputo de la gobernación',
                'valor_estimado' => 28000000,
                'plazo_ejecucion' => '90 días',
                'contratista_nombre' => 'Tecnología y Soporte SAS',
                'contratista_documento' => '900123456-7',
                'workflow_id' => 1,
                'current_stage' => 3,
                'secretaria_id' => 1,
                'unidad_id' => 1,
                'created_by' => 5,
                'assigned_to' => 8 // revisor_presupuestal
            ]
        ];

        foreach ($testProcesses as $processData) {
            Proceso::firstOrCreate([
                'codigo' => $processData['codigo']
            ], $processData);
        }
    }

    /**
     * Crear configuración de dashboard
     */
    private function createDashboardConfig(): void
    {
        // Configuraciones de widgets por rol
        $dashboardConfigs = [
            [
                'user_id' => null,
                'role' => 'super_admin',
                'widget_type' => 'procesos-globales',
                'position' => 0,
                'size' => 'large',
                'is_visible' => true,
                'config' => json_encode(['chart_type' => 'bar', 'show_trend' => true])
            ],
            [
                'user_id' => null,
                'role' => 'super_admin',
                'widget_type' => 'presupuesto-total',
                'position' => 1,
                'size' => 'medium',
                'is_visible' => true,
                'config' => json_encode(['show_percentage' => true, 'show_breakdown' => true])
            ],
            [
                'user_id' => null,
                'role' => 'coordinador_contratacion',
                'widget_type' => 'procesos-asignados',
                'position' => 0,
                'size' => 'large',
                'is_visible' => true,
                'config' => json_encode(['show_priority' => true, 'show_deadlines' => true])
            ],
            [
                'user_id' => null,
                'role' => 'coordinador_contratacion',
                'widget_type' => 'carga-trabajo',
                'position' => 1,
                'size' => 'medium',
                'is_visible' => true,
                'config' => json_encode(['show_capacity' => true, 'alert_threshold' => 80])
            ]
        ];

        foreach ($dashboardConfigs as $config) {
            DB::table('dashboard_widgets')->insert([
                'user_id' => $config['user_id'],
                'role' => $config['role'],
                'widget_type' => $config['widget_type'],
                'position' => $config['position'],
                'size' => $config['size'],
                'is_visible' => $config['is_visible'],
                'config' => $config['config'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Layouts de dashboard por defecto
        $defaultLayouts = [
            [
                'user_id' => null,
                'role' => 'super_admin',
                'layout_data' => json_encode([
                    'columns' => 12,
                    'widgets' => [
                        ['id' => 'procesos-globales', 'x' => 0, 'y' => 0, 'w' => 8, 'h' => 4],
                        ['id' => 'presupuesto-total', 'x' => 8, 'y' => 0, 'w' => 4, 'h' => 2],
                        ['id' => 'usuarios-activos', 'x' => 8, 'y' => 2, 'w' => 4, 'h' => 2],
                        ['id' => 'alertas-sistema', 'x' => 0, 'y' => 4, 'w' => 12, 'h' => 3]
                    ]
                ])
            ],
            [
                'user_id' => null,
                'role' => 'coordinador_contratacion',
                'layout_data' => json_encode([
                    'columns' => 12,
                    'widgets' => [
                        ['id' => 'procesos-asignados', 'x' => 0, 'y' => 0, 'w' => 8, 'h' => 4],
                        ['id' => 'carga-trabajo', 'x' => 8, 'y' => 0, 'w' => 4, 'h' => 2],
                        ['id' => 'documentos-pendientes', 'x' => 8, 'y' => 2, 'w' => 4, 'h' => 2]
                    ]
                ])
            ]
        ];

        foreach ($defaultLayouts as $layout) {
            DB::table('dashboard_layouts')->insert([
                'user_id' => $layout['user_id'],
                'role' => $layout['role'],
                'layout_data' => $layout['layout_data'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}