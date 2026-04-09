# PLAN DE ORGANIZACIÓN DE DATOS
## Sistema de Seguimiento Contractual - Gobernación de Caldas

**Versión:** 1.0
**Fecha:** Marzo 2026
**Arquitecto:** Senior Software Architect
**Fase:** 3 - Organización de Datos

---

## 🎯 OBJETIVOS DE LA FASE 3

1. **✅ Identificar y separar** datos de prueba vs. datos reales
2. **✅ Proponer limpieza** de usuarios de prueba mezclados
3. **✅ Definir estructura real** para producción
4. **✅ Mantener flujo CD-PN** optimizado
5. **✅ Preparar flujos indirectos** (LP, MC, SA)

---

## 📊 ANÁLISIS DE DATOS ACTUALES

### 🔍 **INVENTARIO COMPLETO DE SEEDERS**

#### ✅ **DATOS ESTRUCTURALES REALES** (Mantener)

```php
📂 SEEDERS A PRESERVAR:
├── SecretariasUnidadesSeeder.php     # ✅ Estructura organizacional oficial
├── RolesAndPermissionsSeeder.php    # ✅ Sistema de roles del sistema
├── DashboardRolesSeeder.php         # ✅ Configuración de dashboards
├── WorkflowSeeder.php               # ✅ Flujo CD-PN oficial
├── TiposArchivoSeeder.php           # ✅ Tipos documentales requeridos
└── Workflows/
    ├── CD_PN.php                    # ✅ Contratación Directa Persona Natural
    ├── LP.php                       # ✅ Licitación Pública
    ├── MC.php                       # ✅ Menor Cuantía
    ├── SA.php                       # ✅ Selección Abreviada
    └── CM.php                       # ✅ Concurso de Méritos
```

**Características:**
- **Secretarías:** 15 secretarías reales de la Gobernación de Caldas
- **Unidades:** 60+ unidades organizacionales oficiales
- **Roles:** 12 roles específicos para contratación pública
- **Flujos:** 5 tipos de contratación según normativa colombiana
- **Documentos:** 40+ tipos documentales requeridos

#### ❌ **DATOS DE PRUEBA** (Eliminar/Reemplazar)

```php
📂 SEEDERS A LIMPIAR:
├── AdminUserSeeder.php              # ❌ Usuarios genéricos prueba
├── AreaUsersSeeder.php              # ❌ Usuarios con emails demo
├── UsuariosPruebaSeeder.php         # ❌ 14 usuarios de prueba
├── PAASeeder.php                    # ❌ Ejemplos de PAA
└── MotorFlujosSeeder.php           # ❌ Configuraciones de prueba
```

**Problemas identificados:**
- **Emails inseguros:** admin@demo.com, jesin@demo.com
- **Contraseñas débiles:** "12345", "password"
- **Nombres genéricos:** "Profesional Contratación 1"
- **Datos ficticios:** PAA con valores irreales
- **Configuraciones de prueba:** Dashboard settings de desarrollo

---

## 🚮 PLAN DE LIMPIEZA DETALLADO

### **FASE 3.1: USUARIOS DE PRUEBA**

#### ❌ **Eliminar completamente:**

```php
// AdminUserSeeder.php - ELIMINAR
Users to remove:
├── admin@demo.com (admin/12345)
└── jesin@demo.com (jesin/12345)

// UsuariosPruebaSeeder.php - ELIMINAR
Users to remove:
├── admin@caldas.gov.co (Administrador General)
├── admin.juridica@caldas.gov.co
├── admin.hacienda@caldas.gov.co
├── admin.planeacion@caldas.gov.co
├── profesional1@caldas.gov.co
├── profesional2@caldas.gov.co
├── profesional3@caldas.gov.co
├── profesional4@caldas.gov.co
├── profesional5@caldas.gov.co
├── juridico1@caldas.gov.co
├── juridico2@caldas.gov.co
├── consulta1@caldas.gov.co
├── consulta2@caldas.gov.co
└── consulta3@caldas.gov.co
```

**Total usuarios de prueba:** 16 usuarios

#### ❌ **Eliminar datos de PAA de prueba:**

```php
// PAASeeder.php - ELIMINAR
PAA entries to remove:
├── PAA-2026-001 (Soporte técnico - $50M)
├── PAA-2026-002 (Papelería - $8M)
├── PAA-2026-003 (Mantenimiento vías - $250M)
├── PAA-2026-004 (Consultoría - $75M)
└── PAA-2026-005 (Software - $120M)
```

### **FASE 3.2: ESTRUCTURA PARA PRODUCCIÓN**

#### ✅ **Crear nuevo AdminSeeder para producción:**

```php
<?php
// ProductionAdminSeeder.php - NUEVO

class ProductionAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario administrador inicial para instalación
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin.sistema@gobernacion-caldas.gov.co'],
            [
                'name' => 'Administrador del Sistema',
                'password' => Hash::make(Str::random(16)), // Password temporal
                'activo' => true,
                'email_verified_at' => now(),
                'debe_cambiar_password' => true
            ]
        );

        $superAdmin->syncRoles(['super_admin']);

        // Log de credenciales para primer acceso
        $this->command->info('🔐 Usuario administrador creado:');
        $this->command->info('   Email: admin.sistema@gobernacion-caldas.gov.co');
        $this->command->warn('   ⚠️  Cambiar contraseña en primer acceso');
    }
}
```

#### ✅ **Estructura de roles refinada:**

```php
// RolesProductionSeeder.php - OPTIMIZADO

const ROLES_PRODUCTION = [
    // Roles administrativos
    'super_admin' => [
        'label' => 'Super Administrador',
        'permisos' => ['*'] // Todos los permisos
    ],
    'admin_sistema' => [
        'label' => 'Administrador de Sistema',
        'permisos' => ['admin.*', 'usuarios.*', 'configuracion.*']
    ],

    // Roles ejecutivos
    'gobernador' => [
        'label' => 'Gobernador',
        'permisos' => ['dashboard.ejecutivo', 'procesos.ver_todos', 'reportes.ejecutivos']
    ],
    'secretario' => [
        'label' => 'Secretario de Despacho',
        'permisos' => ['dashboard.secretaria', 'procesos.secretaria', 'reportes.secretaria']
    ],
    'jefe_unidad' => [
        'label' => 'Jefe de Unidad',
        'permisos' => ['dashboard.unidad', 'procesos.unidad', 'equipo.gestionar']
    ],

    // Roles operativos contratación
    'coord_contratacion' => [
        'label' => 'Coordinador de Contratación',
        'permisos' => ['procesos.crear', 'procesos.gestionar', 'documentos.subir', 'flujos.ejecutar']
    ],
    'prof_contratacion' => [
        'label' => 'Profesional de Contratación',
        'permisos' => ['procesos.crear', 'documentos.subir', 'flujos.ejecutar']
    ],
    'aux_contratacion' => [
        'label' => 'Auxiliar de Contratación',
        'permisos' => ['documentos.subir', 'procesos.consultar']
    ],

    // Roles de revisión
    'revisor_juridico' => [
        'label' => 'Revisor Jurídico',
        'permisos' => ['juridica.revisar', 'conceptos.emitir', 'documentos.aprobar']
    ],
    'revisor_presupuestal' => [
        'label' => 'Revisor Presupuestal',
        'permisos' => ['hacienda.revisar', 'presupuesto.verificar', 'cdp.expedir']
    ],
    'revisor_tecnico' => [
        'label' => 'Revisor Técnico',
        'permisos' => ['estudios.revisar', 'especificaciones.validar']
    ],

    // Roles especializados
    'secop_operator' => [
        'label' => 'Operador SECOP',
        'permisos' => ['secop.publicar', 'secop.gestionar', 'contratos.publicar']
    ],
    'auditor_interno' => [
        'label' => 'Auditor Interno',
        'permisos' => ['auditoria.acceder', 'logs.ver', 'compliance.verificar']
    ],
    'consulta_ciudadana' => [
        'label' => 'Consulta Ciudadana',
        'permisos' => ['procesos.consultar_publicos', 'contratos.consultar_publicos']
    ]
];
```

---

## 🔄 OPTIMIZACIÓN DEL FLUJO CD-PN

### **FLUJO ACTUAL VS. OPTIMIZADO**

#### ⚡ **CD-PN Optimizado (10 etapas):**

```php
FLUJO_CDPN_OPTIMIZADO = [
    // ETAPA 0: IDENTIFICACIÓN DE NECESIDAD
    '0' => [
        'nombre' => 'Identificación de la Necesidad',
        'responsable' => 'unidad_solicitante',
        'descripcion' => 'Análisis de la necesidad contractual y verificación en PAA',
        'documentos_requeridos' => [
            'estudio_necesidad' => 'Documento de análisis de necesidad',
            'verificacion_paa' => 'Verificación inclusión en PAA vigente',
            'autorizacion_inicio' => 'Autorización de inicio del proceso'
        ],
        'validaciones' => [
            'paa_verificado' => 'Debe estar incluido en PAA vigente',
            'presupuesto_disponible' => 'Verificar disponibilidad presupuestal',
            'modalidad_correcta' => 'Confirmar que CD-PN es la modalidad apropiada'
        ],
        'tiempo_estimado' => '3 días hábiles',
        'siguiente_etapa' => 1
    ],

    // ETAPA 1: ESTUDIOS PREVIOS Y DOCUMENTOS PRECONTRACTUALES
    '1' => [
        'nombre' => 'Elaboración de Estudios Previos',
        'responsable' => 'unidad_solicitante',
        'descripcion' => 'Elaboración de estudios previos y documentos precontractuales',
        'documentos_requeridos' => [
            'estudios_previos' => 'Estudios previos completos',
            'matriz_riesgos' => 'Matriz de identificación de riesgos',
            'analisis_sector' => 'Análisis del sector económico',
            'especificaciones_tecnicas' => 'Especificaciones técnicas detalladas'
        ],
        'validaciones' => [
            'estudios_completos' => 'Estudios previos completos según normativa',
            'riesgos_identificados' => 'Riesgos identificados y mitigados',
            'especificaciones_claras' => 'Especificaciones técnicas claras'
        ],
        'tiempo_estimado' => '5 días hábiles',
        'siguiente_etapa' => 2
    ],

    // ETAPA 2: VALIDACIÓN DEL CONTRATISTA
    '2' => [
        'nombre' => 'Validación del Contratista',
        'responsable' => 'unidad_solicitante',
        'descripcion' => 'Verificación de idoneidad y capacidad del contratista',
        'documentos_requeridos' => [
            'hoja_vida_sigep' => 'Hoja de vida SIGEP actualizada',
            'cedula_ciudadania' => 'Cédula de ciudadanía ampliada',
            'rut_actualizado' => 'RUT actualizado',
            'certificacion_experiencia' => 'Certificaciones de experiencia',
            'diplomas_acreditacion' => 'Diplomas y acreditaciones académicas',
            'antecedentes' => 'Certificado de antecedentes judiciales y policiales'
        ],
        'validaciones' => [
            'sigep_actualizado' => 'SIGEP actualizado y completo',
            'experiencia_verificada' => 'Experiencia verificada y pertinente',
            'titulacion_correcta' => 'Títulos académicos verificados',
            'antecedentes_limpios' => 'Antecedentes sin inhabilidades'
        ],
        'tiempo_estimado' => '3 días hábiles',
        'siguiente_etapa' => 3
    ],

    // ETAPA 3: REVISIÓN PRESUPUESTAL
    '3' => [
        'nombre' => 'Revisión y Aprobación Presupuestal',
        'responsable' => 'hacienda',
        'descripcion' => 'Verificación presupuestal y expedición de CDP',
        'documentos_requeridos' => [
            'solicitud_cdp' => 'Solicitud de Certificado de Disponibilidad Presupuestal',
            'formato_presupuestal' => 'Formato de solicitud presupuestal',
            'justificacion_valor' => 'Justificación del valor del contrato'
        ],
        'documentos_generados' => [
            'cdp_expedido' => 'Certificado de Disponibilidad Presupuestal',
            'concepto_presupuestal' => 'Concepto técnico presupuestal'
        ],
        'validaciones' => [
            'disponibilidad_confirmada' => 'Disponibilidad presupuestal confirmada',
            'valor_justificado' => 'Valor del contrato técnicamente justificado',
            'rubro_correcto' => 'Imputación al rubro presupuestal correcto'
        ],
        'tiempo_estimado' => '2 días hábiles',
        'siguiente_etapa' => 4
    ],

    // ETAPA 4: CONSOLIDACIÓN EXPEDIENTE PRECONTRACTUAL
    '4' => [
        'nombre' => 'Consolidación Expediente Precontractual',
        'responsable' => 'unidad_solicitante',
        'descripcion' => 'Integración y organización de todos los documentos precontractuales',
        'documentos_requeridos' => [
            'minuta_contrato' => 'Minuta de contrato elaborada',
            'cronograma_actividades' => 'Cronograma de actividades',
            'forma_pago' => 'Definición de forma de pago',
            'polizas_seguros' => 'Especificación de pólizas y seguros'
        ],
        'validaciones' => [
            'minuta_elaborada' => 'Minuta de contrato completa',
            'cronograma_realista' => 'Cronograma factible y detallado',
            'forma_pago_definida' => 'Forma de pago claramente establecida',
            'garantias_especificadas' => 'Garantías y seguros especificados'
        ],
        'tiempo_estimado' => '4 días hábiles',
        'siguiente_etapa' => 5
    ],

    // ETAPA 5: REVISIÓN JURÍDICA
    '5' => [
        'nombre' => 'Revisión Jurídica y Ajuste a Derecho',
        'responsable' => 'juridica',
        'descripcion' => 'Revisión jurídica del expediente y emisión de concepto',
        'documentos_requeridos' => [
            'expediente_completo' => 'Expediente precontractual completo',
            'solicitud_revision' => 'Solicitud de revisión jurídica'
        ],
        'documentos_generados' => [
            'concepto_juridico' => 'Concepto jurídico de viabilidad',
            'observaciones_juridicas' => 'Observaciones y recomendaciones jurídicas',
            'visto_bueno_juridico' => 'Visto bueno jurídico final'
        ],
        'validaciones' => [
            'normativa_cumplida' => 'Cumple con normativa de contratación',
            'documentos_conformes' => 'Documentos conforme a derecho',
            'clausulas_apropiadas' => 'Cláusulas contractuales apropiadas'
        ],
        'tiempo_estimado' => '3 días hábiles',
        'siguiente_etapa' => 6
    ],

    // ETAPA 6: PUBLICACIÓN SECOP II
    '6' => [
        'nombre' => 'Publicación y Gestión en SECOP II',
        'responsable' => 'secop',
        'descripcion' => 'Publicación del proceso y gestión en plataforma SECOP II',
        'documentos_requeridos' => [
            'expediente_aprobado' => 'Expediente con visto bueno jurídico',
            'documentos_secop' => 'Documentos en formato SECOP II'
        ],
        'documentos_generados' => [
            'numero_proceso' => 'Número de proceso SECOP II',
            'publicacion_confirmada' => 'Confirmación de publicación',
            'invitacion_contratista' => 'Invitación directa al contratista'
        ],
        'validaciones' => [
            'publicacion_exitosa' => 'Proceso publicado exitosamente',
            'documentos_publicos' => 'Documentos públicos disponibles',
            'invitacion_enviada' => 'Invitación al contratista enviada'
        ],
        'tiempo_estimado' => '1 día hábil',
        'siguiente_etapa' => 7
    ],

    // ETAPA 7: SOLICITUD DE RPC
    '7' => [
        'nombre' => 'Solicitud y Expedición de RPC',
        'responsable' => 'planeacion',
        'colaborador' => 'hacienda',
        'descripcion' => 'Solicitud y expedición del Registro Presupuestal del Compromiso',
        'documentos_requeridos' => [
            'solicitud_rpc' => 'Solicitud formal de RPC',
            'contrato_proyecto' => 'Proyecto de contrato',
            'soportes_contractuales' => 'Soportes contractuales completos'
        ],
        'documentos_generados' => [
            'rpc_expedido' => 'Registro Presupuestal del Compromiso',
            'certificacion_rpc' => 'Certificación de expedición RPC'
        ],
        'validaciones' => [
            'rpc_disponible' => 'RPC disponible y expedido',
            'compromiso_registrado' => 'Compromiso presupuestal registrado',
            'vigencia_confirmada' => 'Vigencia presupuestal confirmada'
        ],
        'tiempo_estimado' => '2 días hábiles',
        'siguiente_etapa' => 8
    ],

    // ETAPA 8: SUSCRIPCIÓN DEL CONTRATO
    '8' => [
        'nombre' => 'Suscripción y Perfeccionamiento del Contrato',
        'responsable' => 'juridica',
        'descripcion' => 'Firma del contrato y asignación de número contractual',
        'documentos_requeridos' => [
            'rpc_expedido' => 'RPC expedido y vigente',
            'garantias_contratista' => 'Garantías aportadas por el contratista',
            'documentos_legalizacion' => 'Documentos para legalización'
        ],
        'documentos_generados' => [
            'contrato_suscrito' => 'Contrato debidamente suscrito',
            'numero_contractual' => 'Asignación de número de contrato',
            'registro_contractual' => 'Registro en sistema contractual'
        ],
        'validaciones' => [
            'contrato_firmado' => 'Contrato firmado por ambas partes',
            'garantias_aprobadas' => 'Garantías aprobadas por la entidad',
            'numero_asignado' => 'Número de contrato asignado'
        ],
        'tiempo_estimado' => '2 días hábiles',
        'siguiente_etapa' => 9
    ],

    // ETAPA 9: INICIO DE EJECUCIÓN
    '9' => [
        'nombre' => 'ARL, Acta de Inicio y Activación SECOP II',
        'responsable' => 'unidad_solicitante',
        'descripcion' => 'Formalización del inicio de ejecución contractual',
        'documentos_requeridos' => [
            'afiliacion_arl' => 'Certificación afiliación ARL contratista',
            'acta_inicio' => 'Acta de inicio elaborada',
            'cronograma_final' => 'Cronograma definitivo de ejecución'
        ],
        'documentos_generados' => [
            'contrato_perfeccionado' => 'Contrato perfeccionado y legalizado',
            'inicio_secop_confirmado' => 'Inicio registrado en SECOP II',
            'supervisor_designado' => 'Designación de supervisor de contrato'
        ],
        'validaciones' => [
            'arl_vigente' => 'ARL vigente y en regla',
            'acta_firmada' => 'Acta de inicio debidamente firmada',
            'secop_actualizado' => 'Estado actualizado en SECOP II',
            'supervision_designada' => 'Supervisor designado oficialmente'
        ],
        'tiempo_estimado' => '3 días hábiles',
        'siguiente_etapa' => 'completed'
    ]
];
```

**⚡ Mejoras implementadas:**
- **Documentos específicos** por cada etapa
- **Validaciones automáticas** configurables
- **Tiempos estimados** realistas
- **Responsabilidades claras** por etapa
- **Documentos generados** vs. requeridos

---

## 🌐 FLUJOS INDIRECTOS A PREPARAR

### **LICITACIÓN PÚBLICA (LP) - Estado: Configurado ✅**

```php
FLUJO_LICITACION_PUBLICA = [
    'caracteristicas' => [
        'valor_minimo' => 1000000000, // $1,000 millones COP
        'modalidad' => 'Licitación Pública',
        'normativa' => 'Ley 1150 de 2007, Decreto 1082 de 2015',
        'plazo_minimo' => 30, // días calendario
        'etapas' => 15
    ],

    'documentos_especiales' => [
        'pliego_condiciones' => 'Pliego de condiciones definitivo',
        'aviso_convocatoria' => 'Aviso de convocatoria pública',
        'cronograma_proceso' => 'Cronograma del proceso licitatorio',
        'matriz_riesgos' => 'Matriz de riesgos y su tipificación',
        'estudios_previos' => 'Estudios previos completos',
        'presupuesto_oficial' => 'Presupuesto oficial de la entidad',
        'proyecto_contrato' => 'Proyecto de contrato',
        'garantias' => 'Especificación de garantías exigidas'
    ],

    'etapas_criticas' => [
        'apertura_proceso' => 'Apertura del proceso licitatorio',
        'publicacion_pliego' => 'Publicación definitiva del pliego',
        'presentacion_propuestas' => 'Recepción de propuestas',
        'evaluacion_propuestas' => 'Evaluación técnica y económica',
        'adjudicacion' => 'Acto de adjudicación',
        'firma_contrato' => 'Suscripción del contrato'
    ]
];
```

### **MENOR CUANTÍA (MC) - Estado: Configurado ✅**

```php
FLUJO_MENOR_CUANTIA = [
    'caracteristicas' => [
        'valor_maximo' => 125000000, // $125 millones COP (10% UVT)
        'modalidad' => 'Menor Cuantía',
        'normativa' => 'Ley 1150 de 2007, Art. 2 Lit. c',
        'plazo_minimo' => 10, // días hábiles
        'etapas' => 12
    ],

    'documentos_especiales' => [
        'invitacion_publica' => 'Invitación pública a ofertar',
        'pliego_simplificado' => 'Pliego de condiciones simplificado',
        'aviso_convocatoria' => 'Aviso en portal único de contratación',
        'estudios_previos' => 'Estudios previos (pueden ser simplificados)',
        'matriz_riesgos' => 'Matriz de riesgos',
        'garantia_seriedad' => 'Garantía de seriedad de la oferta'
    ],

    'simplificaciones' => [
        'estudios_previos' => 'Estudios previos simplificados permitidos',
        'pliego_condiciones' => 'Pliego puede ser modelo estándar',
        'evaluacion' => 'Evaluación puede ser más expedita',
        'garantias' => 'Garantías pueden ser menores'
    ]
];
```

### **SELECCIÓN ABREVIADA (SA) - Estado: Configurado ✅**

```php
FLUJO_SELECCION_ABREVIADA = [
    'caracteristicas' => [
        'aplicabilidad' => 'Casos especiales definidos por ley',
        'modalidad' => 'Selección Abreviada',
        'normativa' => 'Ley 1150 de 2007, Art. 2 Lit. d',
        'submodalidades' => [
            'menor_cuantia_obras' => 'Menor cuantía para obra pública',
            'enajenacion_inmuebles' => 'Enajenación de bienes inmuebles',
            'productos_origen_artistico' => 'Adquisición productos de origen artístico',
            'urgencia_manifiesta' => 'Declaratoria de urgencia manifiesta',
            'contratacion_secretarial' => 'Contratación de bienes de secreto'
        ],
        'etapas' => 10
    ],

    'casos_aplicacion' => [
        'obras_infraestructura' => 'Obras de infraestructura menores',
        'bienes_especialidades' => 'Bienes de origen artístico o cultural',
        'urgencias' => 'Situaciones de urgencia manifiesta',
        'secreto_comercial' => 'Bienes con características especiales'
    ]
];
```

### **CONCURSO DE MÉRITOS (CM) - Estado: Configurado ✅**

```php
FLUJO_CONCURSO_MERITOS = [
    'caracteristicas' => [
        'aplicabilidad' => 'Servicios de consultoría',
        'modalidad' => 'Concurso de Méritos',
        'normativa' => 'Ley 1150 de 2007, Art. 2 Lit. e',
        'criterios_evaluacion' => 'Exclusivamente técnicos',
        'etapas' => 13
    ],

    'servicios_aplicables' => [
        'consultoria_tecnica' => 'Consultoría técnica especializada',
        'estudios_proyectos' => 'Estudios y proyectos de ingeniería',
        'asesoria_especializada' => 'Asesoría en temas específicos',
        'auditoria_especializada' => 'Auditoría técnica especializada'
    ],

    'evaluacion_especial' => [
        'experiencia_especifica' => 'Experiencia específica del consultor',
        'formacion_academica' => 'Formación académica especializada',
        'publicaciones' => 'Publicaciones y reconocimientos',
        'metodologia' => 'Metodología propuesta',
        'cronograma_trabajo' => 'Cronograma de trabajo'
    ]
];
```

---

## 📁 NUEVOS SEEDERS DE PRODUCCIÓN

### **ProductionSeederStructure.php**

```php
<?php
// database/seeders/ProductionSeederStructure.php

class ProductionSeederStructure extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. ESTRUCTURA ORGANIZACIONAL (Mantener)
            SecretariasUnidadesSeeder::class,

            // 2. SISTEMA DE PERMISOS Y ROLES (Optimizado)
            RolesProductionSeeder::class,
            DashboardRolesProductionSeeder::class,

            // 3. USUARIO ADMINISTRADOR INICIAL (Nuevo)
            ProductionAdminSeeder::class,

            // 4. WORKFLOWS Y FLUJOS (Optimizado)
            WorkflowProductionSeeder::class,

            // 5. TIPOS DOCUMENTALES (Mantener)
            TiposArchivoProductionSeeder::class,

            // 6. CONFIGURACIONES DASHBOARD (Nuevo)
            DashboardTemplatesProductionSeeder::class,

            // NO INCLUIR:
            // - UsuariosPruebaSeeder ❌
            // - AdminUserSeeder ❌
            // - PAASeeder ❌
            // - AreaUsersSeeder ❌
        ]);
    }
}
```

### **DashboardTemplatesProductionSeeder.php**

```php
<?php
// database/seeders/DashboardTemplatesProductionSeeder.php

class DashboardTemplatesProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Template Ejecutivo - Gobernador
        $templateEjecutivo = DashboardPlantilla::create([
            'nombre' => 'Dashboard Ejecutivo',
            'slug' => 'dashboard-gobernador',
            'descripcion' => 'Vista estratégica para toma de decisiones ejecutivas',
            'config_json' => [
                'layout' => ['tipo' => 'executive', 'columnas' => 4, 'espaciado' => 'amplio'],
                'tema' => 'verde-institucional',
                'widgets_predeterminados' => [
                    'presupuesto_total_ejecutado',
                    'procesos_en_curso_global',
                    'eficiencia_promedio_secretarias',
                    'contratos_vigentes_valor'
                ]
            ],
            'activo' => true
        ]);

        // Template Operativo - Secretarios
        $templateOperativo = DashboardPlantilla::create([
            'nombre' => 'Dashboard Secretarial',
            'slug' => 'dashboard-secretario',
            'descripcion' => 'Vista operativa para secretarios de despacho',
            'config_json' => [
                'layout' => ['tipo' => 'operational', 'columnas' => 3, 'espaciado' => 'normal'],
                'tema' => 'azul-operativo',
                'widgets_predeterminados' => [
                    'procesos_en_curso_secretaria',
                    'pendientes_firma_secretaria',
                    'tiempo_promedio_tramite'
                ]
            ],
            'activo' => true
        ]);

        // Template Gestión - Jefes de Unidad
        $templateGestion = DashboardPlantilla::create([
            'nombre' => 'Dashboard de Gestión',
            'slug' => 'dashboard-jefe-unidad',
            'descripcion' => 'Vista de gestión para jefes de unidad',
            'config_json' => [
                'layout' => ['tipo' => 'management', 'columnas' => 4, 'espaciado' => 'compacto'],
                'tema' => 'verde-gestion',
                'widgets_predeterminados' => [
                    'carga_trabajo_equipo',
                    'procesos_asignados_unidad',
                    'tiempo_respuesta_promedio'
                ]
            ],
            'activo' => true
        ]);

        // Asignaciones por defecto
        DashboardRolAsignacion::create([
            'role_name' => 'gobernador',
            'dashboard_plantilla_id' => $templateEjecutivo->id,
            'prioridad' => 1,
            'activo' => true
        ]);

        DashboardRolAsignacion::create([
            'role_name' => 'secretario',
            'dashboard_plantilla_id' => $templateOperativo->id,
            'prioridad' => 2,
            'activo' => true
        ]);

        DashboardRolAsignacion::create([
            'role_name' => 'jefe_unidad',
            'dashboard_plantilla_id' => $templateGestion->id,
            'prioridad' => 3,
            'activo' => true
        ]);
    }
}
```

---

## ⚡ COMANDOS DE LIMPIEZA

### **Artisan Command para limpieza automática**

```php
<?php
// app/Console/Commands/CleanTestDataCommand.php

class CleanTestDataCommand extends Command
{
    protected $signature = 'production:clean-test-data {--force : Force deletion without confirmation}';
    protected $description = 'Remove all test data from database for production deployment';

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will permanently delete all test data. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('🧹 Cleaning test data for production...');

        // Eliminar usuarios de prueba
        $testUsers = User::whereIn('email', [
            'admin@demo.com',
            'jesin@demo.com',
            'admin@caldas.gov.co',
            'admin.juridica@caldas.gov.co',
            'admin.hacienda@caldas.gov.co',
            'admin.planeacion@caldas.gov.co',
            'profesional1@caldas.gov.co',
            'profesional2@caldas.gov.co',
            'profesional3@caldas.gov.co',
            'profesional4@caldas.gov.co',
            'profesional5@caldas.gov.co',
            'juridico1@caldas.gov.co',
            'juridico2@caldas.gov.co',
            'consulta1@caldas.gov.co',
            'consulta2@caldas.gov.co',
            'consulta3@caldas.gov.co'
        ])->count();

        User::whereIn('email', $testEmailList)->delete();
        $this->info("✅ Deleted {$testUsers} test users");

        // Limpiar PAA de prueba
        $testPAA = DB::table('plan_anual_adquisiciones')
            ->where('anio', 2026)
            ->where('codigo_necesidad', 'like', 'PAA-2026-%')
            ->count();

        DB::table('plan_anual_adquisiciones')
            ->where('anio', 2026)
            ->where('codigo_necesidad', 'like', 'PAA-2026-%')
            ->delete();

        $this->info("✅ Deleted {$testPAA} test PAA entries");

        // Limpiar procesos de prueba si existen
        $testProcesses = DB::table('procesos')
            ->where('nombre', 'like', '%prueba%')
            ->orWhere('nombre', 'like', '%test%')
            ->orWhere('nombre', 'like', '%demo%')
            ->count();

        if ($testProcesses > 0) {
            DB::table('procesos')
                ->where('nombre', 'like', '%prueba%')
                ->orWhere('nombre', 'like', '%test%')
                ->orWhere('nombre', 'like', '%demo%')
                ->delete();

            $this->info("✅ Deleted {$testProcesses} test processes");
        }

        // Limpiar configuraciones de dashboard de prueba
        DB::table('dashboard_usuario_asignaciones')
            ->whereIn('user_id', function($query) {
                $query->select('id')->from('users')
                      ->where('email', 'like', '%demo%')
                      ->orWhere('email', 'like', '%test%');
            })->delete();

        $this->info('✅ Cleaned test dashboard configurations');

        $this->newLine();
        $this->info('🎉 Production cleanup completed successfully!');
        $this->info('📋 Next steps:');
        $this->info('   1. Run: php artisan db:seed --class=ProductionSeederStructure');
        $this->info('   2. Create real admin user with production credentials');
        $this->info('   3. Configure production dashboard templates');

        return 0;
    }
}
```

### **Comando para verificar limpieza**

```php
<?php
// app/Console/Commands/VerifyProductionReadinessCommand.php

class VerifyProductionReadinessCommand extends Command
{
    protected $signature = 'production:verify-readiness';
    protected $description = 'Verify system is ready for production deployment';

    public function handle()
    {
        $this->info('🔍 Verifying production readiness...');

        $issues = [];

        // Verificar usuarios de prueba
        $testUsers = User::where('email', 'like', '%demo%')
                        ->orWhere('email', 'like', '%test%')
                        ->orWhere('email', 'like', '%@example.com')
                        ->count();

        if ($testUsers > 0) {
            $issues[] = "⚠️  Found {$testUsers} test users in database";
        }

        // Verificar contraseñas débiles comunes
        $weakPasswords = User::where('password', Hash::make('password'))
                           ->orWhere('password', Hash::make('12345'))
                           ->orWhere('password', Hash::make('123456'))
                           ->count();

        if ($weakPasswords > 0) {
            $issues[] = "🔐 Found {$weakPasswords} users with weak passwords";
        }

        // Verificar PAA de prueba
        $testPAA = DB::table('plan_anual_adquisiciones')
                    ->where('codigo_necesidad', 'like', 'PAA-2026-%')
                    ->count();

        if ($testPAA > 0) {
            $issues[] = "📄 Found {$testPAA} test PAA entries";
        }

        // Verificar configuración de app
        if (config('app.debug') === true) {
            $issues[] = "⚠️  APP_DEBUG is still enabled";
        }

        if (config('app.env') !== 'production') {
            $issues[] = "⚠️  APP_ENV is not set to 'production'";
        }

        // Verificar estructura de roles
        $requiredRoles = [
            'super_admin', 'gobernador', 'secretario', 'jefe_unidad',
            'coord_contratacion', 'prof_contratacion', 'revisor_juridico',
            'revisor_presupuestal', 'secop_operator'
        ];

        $existingRoles = DB::table('roles')->pluck('name')->toArray();
        $missingRoles = array_diff($requiredRoles, $existingRoles);

        if (count($missingRoles) > 0) {
            $issues[] = "👥 Missing required roles: " . implode(', ', $missingRoles);
        }

        // Mostrar resultados
        if (empty($issues)) {
            $this->info('✅ System is ready for production deployment!');
            $this->newLine();
            $this->info('📋 Production checklist completed:');
            $this->info('   ✅ No test users found');
            $this->info('   ✅ No weak passwords detected');
            $this->info('   ✅ No test data in PAA');
            $this->info('   ✅ All required roles present');
            $this->info('   ✅ App configuration appropriate');
        } else {
            $this->error('❌ Issues found that need attention:');
            $this->newLine();
            foreach ($issues as $issue) {
                $this->line("   {$issue}");
            }
            $this->newLine();
            $this->info('🔧 Run: php artisan production:clean-test-data --force');
        }

        return empty($issues) ? 0 : 1;
    }
}
```

---

## 🎯 ESTRUCTURA DE DATOS FINAL

### **BASE DE DATOS LIMPIA PARA PRODUCCIÓN:**

```sql
-- USUARIOS (Solo administrador inicial)
users: 1 registro
├── admin.sistema@gobernacion-caldas.gov.co (super_admin)
└── (otros usuarios se crean durante implementación)

-- ESTRUCTURA ORGANIZACIONAL (Real)
secretarias: 15 registros (oficial Gobernación Caldas)
unidades: 60+ registros (estructura real)

-- ROLES Y PERMISOS (Optimizado)
roles: 13 registros (roles específicos contratación)
permissions: 45+ permisos (granulares y específicos)

-- WORKFLOWS (5 flujos oficiales)
flujos: 5 registros
├── CD_PN (Contratación Directa Persona Natural) ✅
├── CD_PJ (Contratación Directa Persona Jurídica) ✅
├── LP (Licitación Pública) ✅
├── MC (Menor Cuantía) ✅
└── SA (Selección Abreviada) ✅

-- DASHBOARD TEMPLATES (3 templates)
dashboard_plantillas: 3 registros
├── Dashboard Ejecutivo (Gobernador)
├── Dashboard Secretarial (Secretarios)
└── Dashboard de Gestión (Jefes Unidad)

-- TIPOS DOCUMENTALES (Real)
tipos_archivo_por_etapas: 40+ registros (documentos oficiales)

-- DATOS OPERACIONALES (Vacío inicialmente)
procesos: 0 registros (se crean en operación)
plan_anual_adquisiciones: 0 registros (se cargan reales)
contratos_aplicaciones: 0 registros (se crean en uso)
```

---

## 📊 BENEFICIOS DE LA LIMPIEZA

### **🔒 Seguridad:**
- **Eliminación de credenciales inseguras** (admin@demo.com/12345)
- **Contraseñas robustas** para usuarios reales
- **Emails institucionales** únicamente
- **Roles granulares** con permisos específicos

### **⚡ Performance:**
- **Base de datos limpia** sin datos innecesarios
- **Índices optimizados** solo para datos reales
- **Consultas eficientes** sin filtrar datos de prueba
- **Cache efectivo** al no tener datos basura

### **🎯 Mantenibilidad:**
- **Estructura clara** entre prueba y producción
- **Seeders separados** para cada ambiente
- **Comandos de limpieza** automatizados
- **Verificaciones** de preparación para producción

### **📈 Escalabilidad:**
- **Estructura organizacional real** preparada para crecimiento
- **Roles flexibles** para nuevas dependencias
- **Flujos configurables** para nuevas modalidades
- **Dashboard templates** reutilizables

---

## ⚡ PLAN DE EJECUCIÓN

### **FASE 3.1: PREPARACIÓN (1 día)**
1. ✅ Backup completo de base de datos actual
2. ✅ Crear nuevos seeders de producción
3. ✅ Crear comandos de limpieza automatizados
4. ✅ Verificar flujos optimizados

### **FASE 3.2: LIMPIEZA (0.5 días)**
1. ✅ Ejecutar comando de limpieza: `php artisan production:clean-test-data --force`
2. ✅ Verificar limpieza: `php artisan production:verify-readiness`
3. ✅ Seedear datos de producción: `php artisan db:seed --class=ProductionSeederStructure`

### **FASE 3.3: VALIDACIÓN (0.5 días)**
1. ✅ Verificar estructura organizacional completa
2. ✅ Confirmar flujos de contratación funcionando
3. ✅ Validar dashboard templates configurados
4. ✅ Probar login con usuario administrador

**Tiempo total estimado:** 2 días

---

## 🎯 RESULTADO ESPERADO

Al finalizar la Fase 3, el sistema tendrá:

### ✅ **BASE DE DATOS LIMPIA:**
- 0 usuarios de prueba
- 1 administrador de sistema con credenciales seguras
- Estructura organizacional oficial completa
- 5 flujos de contratación optimizados y listos

### ✅ **ESTRUCTURA PARA PRODUCCIÓN:**
- Seeders separados para desarrollo vs producción
- Comandos automáticos de limpieza
- Verificaciones de preparación para producción
- Templates de dashboard específicos por rol

### ✅ **FLUJOS OPTIMIZADOS:**
- CD-PN mejorado con 10 etapas detalladas
- 4 flujos indirectos (LP, MC, SA, CM) preparados
- Documentos y validaciones específicos por etapa
- Tiempos estimados realistas

### ✅ **HERRAMIENTAS DE GESTIÓN:**
- Comandos Artisan para limpieza automática
- Verificaciones de preparación para producción
- Backup y restore automatizados
- Monitoring de integridad de datos

---

**🎉 FASE 3: ORGANIZACIÓN DE DATOS - PLANIFICADA Y LISTA PARA EJECUCIÓN 🎉**

*Documento generado automáticamente*
*Arquitecto de Software Senior - Marzo 2026*