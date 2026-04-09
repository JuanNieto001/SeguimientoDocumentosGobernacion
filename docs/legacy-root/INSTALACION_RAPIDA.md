# 🚀 INSTALACIÓN RÁPIDA - MÓDULO WORKFLOW

## Paso a Paso para Poner en Marcha el Sistema

### 1️⃣ Ejecutar Migraciones

```bash
php artisan migrate
```

Esto creará las 6 tablas necesarias:
- contract_processes
- process_steps  
- process_documents
- process_approvals
- process_audit_logs
- process_notifications

### 2️⃣ Configurar Storage

```bash
php artisan storage:link
```

### 3️⃣ Verificar Roles

Asegúrate de que existen los siguientes roles (o créalos):

```bash
php artisan tinker
```

```php
use Spatie\Permission\Models\Role;

$roles = [
    'Super Admin',
    'Jefe Unidad',
    'Abogado Unidad',
    'Abogado Enlace Jurídica',
    'Apoyo Estructuración',
    'Presupuesto',
    'Supervisor',
    'Contratista'
];

foreach ($roles as $roleName) {
    Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
}
```

### 4️⃣ Ejecutar Tests (Opcional)

```bash
php artisan test --filter WorkflowEngineTest
```

Deberías ver:
```
✓ test_workflow_initialization_creates_all_steps
✓ test_cannot_advance_without_required_documents
✓ test_can_advance_when_all_requirements_met
✓ test_cannot_have_cdp_without_compatibilidad_del_gasto ⭐
✓ test_can_have_cdp_with_approved_compatibilidad
✓ test_cannot_advance_with_expired_documents
✓ test_return_to_step_changes_status_correctly
✓ test_audit_log_created_on_state_change
✓ test_document_expiration_calculated_automatically
✓ test_complete_workflow_flow

Tests:  10 passed
```

### 5️⃣ Crear Usuario de Prueba

```bash
php artisan tinker
```

```php
$user = User::factory()->create([
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
]);

$user->assignRole('Super Admin');
```

### 6️⃣ Acceder al Sistema

1. Inicia el servidor:
```bash
php artisan serve
```

2. Accede a: `http://localhost:8000/login`

3. Credenciales:
   - Email: `admin@test.com`
   - Password: `password`

4. Navega a: `http://localhost:8000/contract-processes`

### 7️⃣ Crear Primer Proceso de Prueba

Opción A - Desde la interfaz:
- Click en "+ Nuevo Proceso"
- Completa el formulario
- El sistema inicializará automáticamente el workflow

Opción B - Desde tinker:
```php
use App\Models\ContractProcess;
use App\Enums\ProcessType;
use App\Enums\ProcessStatus;
use App\Services\WorkflowEngine;

$process = ContractProcess::create([
    'process_type' => ProcessType::CONTRATACION_DIRECTA_PERSONA_NATURAL,
    'status' => ProcessStatus::NEED_DEFINED,
    'current_step' => 0,
    'object' => 'Contratación de servicios profesionales para asesoría jurídica',
    'estimated_value' => 15000000,
    'term_days' => 60,
    'contractor_name' => 'Juan Pérez',
    'contractor_document_type' => 'CC',
    'contractor_document_number' => '1234567890',
    'contractor_email' => 'juan@example.com',
    'created_by' => auth()->id() ?? 1,
]);

$workflowEngine = app(WorkflowEngine::class);
$workflowEngine->initializeWorkflow($process);

echo "Proceso creado: " . $process->process_number;
```

### 8️⃣ Probar el Workflow

1. **Ver proceso**: Navega a `/contract-processes/{id}`
   - Debería redirigir a `/contract-processes/{id}/step/0`
   - Verás barra de progreso con 10 etapas

2. **Subir documento**:
   - Click en "+ Subir Documento"
   - Selecciona tipo "Estudios Previos"
   - Sube un archivo PDF
   - El documento aparecerá en la lista

3. **Intentar avanzar sin completar**:
   - El botón "Avanzar" estará deshabilitado
   - Verás alerta amarilla con requisitos faltantes

4. **Completar requisitos**:
   - Sube "Evidencia de Envío a Unidad"
   - Ahora el botón "Avanzar" se habilita

5. **Avanzar a siguiente etapa**:
   - Click en "✓ Avanzar a Siguiente Etapa"
   - Confirma la acción
   - El proceso avanza a Etapa 1 (INITIAL_DOCS_PENDING)

### 9️⃣ Verificar Auditoría

```bash
php artisan tinker
```

```php
$process = ContractProcess::first();
$process->auditLogs()->latest()->get();
```

Deberías ver:
- workflow_initialized
- document_uploaded (x2)
- state_changed

### 🔟 Verificar Regla Crítica: CDP sin Compatibilidad

1. Avanza a Etapa 1
2. Intenta subir documento tipo "CDP"
3. El sistema permitirá subirlo
4. Intenta avanzar a Etapa 2
5. **Verás error**: "No se puede tener CDP sin Compatibilidad del Gasto aprobada"

✅ La regla crítica está funcionando!

Para permitir el avance:
1. Sube "Compatibilidad del Gasto"
2. Apruébalo (si tienes permisos)
3. Ahora sí podrás avanzar con el CDP

---

## 📁 Estructura de Archivos Creados

```
App/
├── Enums/
│   ├── ProcessStatus.php ⭐
│   ├── ProcessType.php
│   ├── DocumentType.php ⭐
│   └── ApprovalStatus.php
├── Models/
│   ├── ContractProcess.php ⭐
│   ├── ProcessStep.php
│   ├── ProcessDocument.php ⭐
│   ├── ProcessApproval.php
│   ├── ProcessAuditLog.php
│   └── ProcessNotification.php
├── Services/
│   └── WorkflowEngine.php ⭐ (600+ líneas)
├── Http/
│   ├── Controllers/
│   │   ├── ContractProcessController.php ⭐
│   │   └── ProcessDocumentController.php
│   └── Policies/
│       └── ContractProcessPolicy.php ⭐
└── Providers/
    └── AppServiceProvider.php (actualizado)

database/
├── migrations/
│   ├── 2026_02_19_000001_create_contract_processes_table.php
│   ├── 2026_02_19_000002_create_process_steps_table.php
│   ├── 2026_02_19_000003_create_process_documents_table.php
│   ├── 2026_02_19_000004_create_process_approvals_table.php
│   ├── 2026_02_19_000005_create_process_audit_logs_table.php
│   └── 2026_02_19_000006_create_process_notifications_table.php
└── factories/
    └── ContractProcessFactory.php

resources/
└── views/
    └── contract-processes/
        ├── index.blade.php
        └── steps/
            └── step-0.blade.php

routes/
└── web.php (actualizado con 20+ rutas)

tests/
└── Feature/
    └── WorkflowEngineTest.php ⭐ (10 tests)

Documentación/
├── ARQUITECTURA_WORKFLOW_CD_PN.md (1000+ líneas)
├── RESUMEN_IMPLEMENTACION_WORKFLOW.md
└── INSTALACION_RAPIDA.md (este archivo)
```

**Total**: 30+ archivos creados/modificados

---

## 🆘 Troubleshooting

### Error: "Policy not found"

Solución:
```bash
php artisan optimize:clear
```

### Error: "Table doesn't exist"

Solución:
```bash
php artisan migrate:fresh
```

### Error: "Role does not exist"

Solución: Ejecuta el paso 3️⃣ nuevamente para crear los roles.

### Error al subir archivos

Verifica permisos:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

---

## ✅ Verificación Final

Checklist para confirmar que todo funciona:

- [ ] Migraciones ejecutadas (6 tablas creadas)
- [ ] Storage vinculado
- [ ] Roles creados (8 roles)
- [ ] Tests pasan (10/10)
- [ ] Usuario admin creado
- [ ] Puedes acceder a `/contract-processes`
- [ ] Puedes crear un proceso nuevo
- [ ] Se inicializan las 10 etapas
- [ ] Puedes subir documentos
- [ ] Barra de progreso se muestra
- [ ] Validaciones bloquean avance
- [ ] Regla crítica CDP funciona
- [ ] Auditoría registra cambios

---

## 📞 Contacto

Si necesitas ayuda con:
- Personalización de vistas
- Integración con SharePoint/SECOP II
- Notificaciones por email
- Dashboard de métricas
- Nuevas validaciones

Consulta la documentación completa en:
- `ARQUITECTURA_WORKFLOW_CD_PN.md`
- `RESUMEN_IMPLEMENTACION_WORKFLOW.md`

---

**¡El sistema está listo para usar!** 🎉

El módulo implementa un workflow completo, robusto y auditable para Contratación Directa con Persona Natural, con validaciones automáticas, control de estados, gestión de documentos, permisos granulares y trazabilidad completa.
