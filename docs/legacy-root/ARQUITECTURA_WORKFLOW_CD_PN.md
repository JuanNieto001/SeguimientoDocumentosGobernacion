# 📋 ARQUITECTURA DEL MÓDULO WORKFLOW - CONTRATACIÓN DIRECTA PERSONA NATURAL

## 🎯 OBJETIVO
Sistema de workflow robusto basado en máquina de estados para gestionar el proceso completo de Contratación Directa con Persona Natural, garantizando trazabilidad, validaciones automáticas, control de avance y auditoría completa.

---

## 🏗️ ARQUITECTURA GENERAL

### Capas de la Aplicación

```
┌─────────────────────────────────────────────────────┐
│                  PRESENTACIÓN (Views)                │
│  - Vistas Blade para cada etapa del workflow        │
│  - Componentes reutilizables                        │
│  - UI responsiva con Tailwind CSS                   │
└─────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────┐
│                 CONTROLADORES                        │
│  - ContractProcessController                        │
│  - ProcessDocumentController                        │
│  - Manejo de solicitudes HTTP                       │
└─────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────┐
│              LÓGICA DE NEGOCIO                      │
│  - WorkflowEngine (State Machine)                   │
│  - Validaciones de transiciones                     │
│  - Reglas específicas por etapa                     │
└─────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────┐
│                 MODELOS (Eloquent)                   │
│  - ContractProcess                                  │
│  - ProcessStep, ProcessDocument                     │
│  - ProcessApproval, ProcessAuditLog                 │
└─────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────┐
│              BASE DE DATOS (MySQL)                  │
│  - 6 tablas principales del workflow                │
│  - Relaciones y constraints                         │
└─────────────────────────────────────────────────────┘
```

---

## 📊 MODELO DE DATOS

### Diagrama ER Simplificado

```
┌─────────────────────┐
│  contract_processes │───┐
│  - id               │   │
│  - status           │   │ 1:N
│  - current_step     │   │
│  - process_number   │   ├──► process_steps
│  - contractor_id    │   │
│  - ...              │   │
└─────────────────────┘   │
          │               │
          │ 1:N           │ 1:N
          ├───────────────┴──► process_documents
          │                         │
          │ 1:N                     │ 1:1
          ├──────────────► process_approvals
          │
          │ 1:N
          └──────────────► process_audit_logs
                                   │
                           1:N     │
                     process_notifications
```

### Tablas Principales

#### 1. **contract_processes**
- **Propósito**: Registro principal del proceso contractual
- **Campos clave**:
  - `status` (enum): Estado actual del workflow
  - `current_step` (0-9): Etapa actual
  - `process_number`: CD-PN-XXX-2026
  - `contract_number`: Asignado en etapa 8
  - Datos del contrato (objeto, valor, plazo)
  - Relaciones con personas (contratista, supervisor, etc.)

#### 2. **process_steps**
- **Propósito**: Registro de cada etapa del proceso
- **Campos clave**:
  - `step_number` (0-9)
  - `status` (pending/in_progress/completed)
  - `requirements` (JSON): Requisitos de la etapa
  - Timestamps de inicio y finalización

#### 3. **process_documents**
- **Propósito**: Documentos adjuntos al proceso
- **Campos clave**:
  - `document_type` (enum): Tipo de documento
  - `file_path`: Ruta del archivo
  - `approval_status`: Estado de aprobación
  - `issued_at`, `expires_at`: Control de vigencia
  - `signatures` (JSON): Registro de firmas

#### 4. **process_approvals**
- **Propósito**: Solicitudes de aprobación
- **Campos clave**:
  - `approval_type`: Tipo de aprobación requerida
  - `requested_from`: Usuario al que se solicita
  - `checklist` (JSON): Items a verificar
  - `status`, `comments`

#### 5. **process_audit_logs**
- **Propósito**: Trazabilidad completa de cambios
- **Campos clave**:
  - `action`: Tipo de acción
  - `old_value`, `new_value`
  - `changes` (JSON): Detalles del cambio
  - `user_id`, `ip_address`: Información del usuario

#### 6. **process_notifications**
- **Propósito**: Notificaciones a usuarios
- **Campos clave**:
  - `type`: Tipo de notificación
  - `is_read`, `email_sent`
  - `data` (JSON): Datos adicionales

---

## 🔄 MÁQUINA DE ESTADOS (State Machine)

### Diagrama de Estados

```
NEED_DEFINED (Etapa 0)
       ↓
INITIAL_DOCS_PENDING (Etapa 1)
       ↓
CONTRACTOR_VALIDATION (Etapa 2)
       ↓
CONTRACT_DOCS_DRAFTED (Etapa 3)
       ↓
PRECONTRACT_FILE_READY (Etapa 4)
       ↓
LEGAL_REVIEW_PENDING (Etapa 5) ──┐
       ↓                          │
RETURNED_FOR_FIXES ──────────────┘
       ↓
ADJUSTED_OK
       ↓
SIGNED
       ↓
SECOP_PUBLISHED_AND_SIGNED (Etapa 6)
       ↓
RPC_REQUESTED (Etapa 7)
       ↓
RPC_ISSUED
       ↓
CONTRACT_NUMBER_ASSIGNED (Etapa 8)
       ↓
STARTED (Etapa 9) [FINAL]
```

### Enum ProcessStatus

**Ubicación**: `App/Enums/ProcessStatus.php`

**Métodos principales**:
- `getStepNumber()`: Retorna número de etapa (0-9)
- `getLabel()`: Retorna nombre legible
- `allowedTransitions()`: Array de estados permitidos siguientes
- `canTransitionTo(ProcessStatus $target)`: Valida transición
- `isFinalState()`: Verifica si es estado final

---

## ⚙️ WORKFLOW ENGINE

### Servicio WorkflowEngine

**Ubicación**: `App/Services/WorkflowEngine.php`

#### Métodos Principales

##### `initializeWorkflow(ContractProcess $process)`
- Crea las 10 etapas del proceso
- Marca etapa 0 como "in_progress"
- Registra auditoría

##### `canAdvance(ContractProcess $process): array`
- Valida documentos requeridos
- Verifica documentos expirados
- Valida aprobaciones pendientes
- Ejecuta reglas específicas por etapa
- **Retorna**: Array de errores (vacío si puede avanzar)

##### `advance(ContractProcess $process, User $user)`
- Valida que se puede avanzar
- Marca etapa actual como completada
- Transiciona al siguiente estado
- Registra auditoría
- Envía notificaciones

##### `returnToStep(ContractProcess $process, int $targetStep, string $reason, User $user)`
- Devuelve proceso a etapa anterior
- Registra motivo
- Notifica responsables

#### Validaciones Específicas por Etapa

**Etapa 1** (CRÍTICO):
```php
// CDP solo si existe Compatibilidad del Gasto aprobada
$hasCompatibilidad = $process->documents()
    ->where('document_type', DocumentType::COMPATIBILIDAD_GASTO)
    ->where('approval_status', ApprovalStatus::APPROVED)
    ->exists();

if ($hasCDP && !$hasCompatibilidad) {
    return error;
}
```

**Etapa 2**:
- Requiere checklist de Secretaría Jurídica aprobado
- Requiere checklist de Abogado de Unidad aprobado

**Etapa 5**:
- Requiere concepto "Ajustado a Derecho"
- Valida fecha de radicación en Jurídica

**Etapa 6**:
- Requiere ID de proceso en SECOP II
- Requiere contrato electrónico descargado

**Etapa 7**:
- Requiere número de RPC

**Etapa 8**:
- Requiere número de contrato asignado

---

## 🎨 CONTROLADORES

### ContractProcessController

**Rutas principales**:
- `GET /contract-processes` - Lista procesos
- `POST /contract-processes` - Crea proceso
- `GET /contract-processes/{id}/step/{step}` - Vista de etapa específica
- `POST /contract-processes/{id}/advance` - Avanza a siguiente etapa
- `POST /contract-processes/{id}/return` - Devuelve a etapa anterior

**Métodos clave**:
- `show()`: Redirige automáticamente a la etapa actual
- `showStep()`: Muestra interfaz de la etapa específica
- `advance()`: Valida y avanza (usa WorkflowEngine)
- `returnToStep()`: Permite devolución (solo ciertos roles)

### ProcessDocumentController

**Funcionalidades**:
- `upload()`: Subir documentos con validación
- `download()`: Descargar documentos
- `approve()`, `reject()`, `requestFixes()`: Gestión de aprobaciones
- `addSignature()`: Firmar documentos
- `replace()`: Reemplazar versión de documento

---

## 🔐 SEGURIDAD Y PERMISOS

### Roles del Sistema

1. **Super Admin**: Acceso total
2. **Jefe Unidad**: Gestiona procesos de su unidad
3. **Abogado Unidad**: Valida documentos del contratista
4. **Abogado Enlace Jurídica**: Revisión legal, firma contratos
5. **Apoyo Estructuración**: Soporte en estructuración
6. **Presupuesto**: Gestión CDP, RPC
7. **Supervisor**: Seguimiento ejecución
8. **Contratista**: Solo visualización

### Policy: ContractProcessPolicy

**Métodos principales**:
- `view()`: Puede ver si está relacionado al proceso
- `advance()`: Permisos específicos por etapa
- `uploadDocument()`: Usuarios relacionados al proceso
- `approveDocument()`: Solo abogados y jefes
- `signDocument()`: Solo personas autorizadas

**Ejemplo de lógica por etapa**:
```php
match($step) {
    0 => $user->hasRole(['Jefe Unidad', 'Apoyo Estructuración']),
    1 => $user->hasRole(['Presupuesto']),
    2 => $user->hasRole(['Abogado Unidad']),
    5 => $user->hasRole('Abogado Enlace Jurídica'),
    // ...
}
```

---

## 📝 TIPOS DE DOCUMENTOS (DocumentType Enum)

### Documentos por Etapa

**Etapa 0**:
- Estudios Previos ✓
- Evidencia Envío Unidad ✓

**Etapa 1**:
- PAA ✓
- No Planta ✓
- Paz y Salvo Rentas ✓
- Paz y Salvo Contabilidad ✓
- Compatibilidad del Gasto ✓ (REQUERIDO ANTES DE CDP)
- CDP ✓

**Etapa 2** (Contratista):
- Hoja de Vida SIGEP
- Certificados estudio/experiencia
- RUT, Cédula
- Antecedentes (disciplinarios, fiscales, judiciales, etc.)
- Seguridad Social (salud, pensión) [Validez: 30 días]
- Certificado Cuenta Bancaria [Validez: 30 días]
- Certificado Médico [Validez: 90 días]
- Tarjeta Profesional (si aplica)
- Checklists de validación

**Etapa 3**:
- Invitación a Presentar Oferta
- Solicitud de Contratación
- Designación de Supervisor
- Certificado de Idoneidad
- Análisis del Sector
- Aceptación Oferta (Contratista)

**Etapa 5**:
- Radicado Jurídica
- Ajustado a Derecho
- Contrato Firmado

**Etapa 6**:
- Proceso SECOP II
- Contrato Electrónico

**Etapa 7**:
- Solicitud RPC
- RPC

**Etapa 9**:
- Solicitud ARL
- Acta de Inicio
- Registro Inicio SECOP II

### Documentos con Vigencia Automática

```php
ANTECEDENTES_* → 30 días
CERTIFICADO_CUENTA_BANCARIA → 30 días
SEGURIDAD_SOCIAL_* → 30 días
CERTIFICADO_MEDICO → 90 días
```

El modelo `ProcessDocument` calcula automáticamente `expires_at` basado en `issued_at` + validez.

---

## 📬 SISTEMA DE NOTIFICACIONES

### Tipos de Notificaciones

1. **missing_document**: Falta documento requerido
2. **legal_return**: Jurídica devuelve con observaciones
3. **approval_required**: Se requiere aprobación
4. **secop_signature_ready**: Listo para firma SECOP
5. **rpc_issued**: RPC expedido
6. **ready_for_acta_inicio**: Listo para acta de inicio
7. **document_expiring**: Documento próximo a vencer

### Ejemplo de Uso

```php
ProcessNotification::notifyLegalReturn(
    $process,
    $user,
    'Falta firma del ordenador del gasto'
);
```

Las notificaciones se crean en BD y pueden enviarse por email (campo `email_sent`).

---

## 🧪 TESTING

### Test Suite: WorkflowEngineTest

**Ubicación**: `tests/Feature/WorkflowEngineTest.php`

#### Tests Principales

1. ✅ `test_workflow_initialization_creates_all_steps`
   - Verifica que se crean las 10 etapas

2. ✅ `test_cannot_advance_without_required_documents`
   - Bloquea avance si faltan documentos

3. ✅ `test_can_advance_when_all_requirements_met`
   - Permite avance cuando cumple requisitos

4. ✅ `test_cannot_have_cdp_without_compatibilidad_del_gasto`
   - **REGLA CRÍTICA**: CDP requiere Compatibilidad

5. ✅ `test_can_have_cdp_with_approved_compatibilidad`
   - CDP permitido después de Compatibilidad aprobada

6. ✅ `test_cannot_advance_with_expired_documents`
   - Bloquea avance con documentos vencidos

7. ✅ `test_return_to_step_changes_status_correctly`
   - Devolución a etapa anterior funciona

8. ✅ `test_audit_log_created_on_state_change`
   - Auditoría registra cambios de estado

9. ✅ `test_document_expiration_calculated_automatically`
   - Expiración se calcula automáticamente

10. ✅ `test_complete_workflow_flow`
    - Flujo completo de múltiples etapas

#### Ejecutar Tests

```bash
php artisan test --filter WorkflowEngineTest
```

---

## 🚀 INSTALACIÓN Y CONFIGURACIÓN

### 1. Ejecutar Migraciones

```bash
php artisan migrate
```

Esto creará las 6 tablas:
- `contract_processes`
- `process_steps`
- `process_documents`
- `process_approvals`
- `process_audit_logs`
- `process_notifications`

### 2. Configurar Storage

```bash
php artisan storage:link
```

Los documentos se guardan en `storage/app/contract-processes/{process_id}/step-{step}/`.

### 3. Configurar Roles

Ejecutar seeder de roles o crear manualmente:

```bash
php artisan db:seed --class=RoleSeeder
```

Roles requeridos:
- Super Admin
- Jefe Unidad
- Abogado Unidad
- Abogado Enlace Jurídica
- Apoyo Estructuración
- Presupuesto
- Supervisor
- Contratista

### 4. Asignar WorkflowEngine al Service Container

Ya está configurado en `AppServiceProvider`, Laravel lo resolverá automáticamente.

---

## 📱 INTERFAZ DE USUARIO

### Vistas Principales

1. **index.blade.php**: Lista de procesos con filtros
2. **create.blade.php**: Formulario de creación
3. **steps/step-{0-9}.blade.php**: Vistas por etapa
4. **audit-log.blade.php**: Historial de auditoría

### Componentes UI

- **Barra de progreso**: Muestra visualmente etapa actual (0-9)
- **Panel de validación**: Lista requisitos faltantes
- **Botón "Avanzar"**: Solo habilitado si cumple reglas
- **Lista de documentos**: Con estados de aprobación y vigencia
- **Modal de carga**: Upload de documentos

### Redirección Automática

Al acceder a `/contract-processes/{id}`, **automáticamente redirige** a la etapa actual:

```php
public function show(ContractProcess $contractProcess) {
    return redirect()->route('contract-processes.step', [
        'process' => $contractProcess,
        'step' => $contractProcess->current_step
    ]);
}
```

El usuario siempre ve **"qué sigue"**.

---

## 🔍 AUDITORÍA Y TRAZABILIDAD

### ¿Qué se Registra?

- ✅ Cambios de estado
- ✅ Carga/eliminación de documentos
- ✅ Aprobaciones/rechazos
- ✅ Devoluciones a etapas anteriores
- ✅ Firmas de documentos
- ✅ Modificaciones de datos

### Información Capturada

- Usuario que ejecuta la acción
- IP y User-Agent
- Valores anteriores y nuevos
- Timestamp exacto
- Descripción legible

### Consultar Auditoría

```php
$process->auditLogs()->latest()->get();
```

O desde la interfaz:
```
/contract-processes/{id}/audit-log
```

---

## ⚠️ REGLAS CRÍTICAS DE NEGOCIO

### 1. CDP Requiere Compatibilidad del Gasto (Etapa 1)

```php
if ($hasCDP && !$hasCompatibilidadAprobada) {
    throw ValidationException;
}
```

### 2. Paz y Salvos Requieren Datos del Contratista (Etapa 1)

Nombre completo + documento del contratista obligatorios.

### 3. Validación del Contratista Requiere Dos Checklists (Etapa 2)

- Checklist Secretaría Jurídica
- Checklist Abogado de Unidad

Ambos deben estar aprobados.

### 4. Documentos Vigentes (Múltiples Etapas)

Antecedentes, cuenta bancaria, seguridad social → **30 días**.
Certificado médico → **90 días**.

Sistema bloquea avance si hay documentos expirados.

### 5. No Saltar Etapas

El sistema valida transiciones permitidas:

```php
if (!$currentStatus->canTransitionTo($targetStatus)) {
    throw ValidationException;
}
```

### 6. Firmas Requeridas (Etapa 3, 5, 6)

Ciertos documentos requieren firmas específicas antes de avanzar.

---

## 📊 MÉTRICAS Y REPORTES

### Información Disponible

- Progreso del proceso (porcentaje 0-100%)
- Tiempo en cada etapa (días)
- Documentos pendientes/aprobados/expirados
- Aprobaciones pendientes
- Historial completo de cambios

### Métodos del Modelo

```php
$process->getProgressPercentage(); // 0-100
$process->getMissingRequiredDocuments(); // Array
$process->hasExpiredDocuments(); // bool
$process->getPendingApprovals(); // Collection
$process->canAdvanceToNextStep(); // bool
```

---

## 🔄 FLUJO COMPLETO - RESUMEN EJECUTIVO

```
1. Usuario crea proceso (Etapa 0)
   ↓ Agrega Estudios Previos
   ↓ Envía a Planeación

2. Sistema avanza a Etapa 1
   ↓ Solicita documentos iniciales
   ↓ VALIDA: CDP solo con Compatibilidad aprobada

3. Sistema avanza a Etapa 2
   ↓ Carga documentos del contratista
   ↓ Valida vigencias (30 días)
   ↓ Checklists de validación

4. Sistema avanza a Etapa 3
   ↓ Proyecta documentos contractuales
   ↓ Recopila firmas

5. Sistema avanza a Etapa 4
   ↓ Consolida expediente precontractual

6. Sistema avanza a Etapa 5
   ↓ Radica en Jurídica (SharePoint)
   ↓ Abogado enlace revisa
   ↓ Puede devolver con observaciones
   ↓ Emite "Ajustado a Derecho"
   ↓ Firma de contrato

7. Sistema avanza a Etapa 6
   ↓ Publica en SECOP II
   ↓ Firmas electrónicas
   ↓ Descarga contrato electrónico

8. Sistema avanza a Etapa 7
   ↓ Solicita RPC a Hacienda
   ↓ Espera expedición

9. Sistema avanza a Etapa 8
   ↓ Radica expediente final
   ↓ Obtiene número de contrato

10. Sistema avanza a Etapa 9 (FINAL)
    ↓ Solicita ARL
    ↓ Elabora Acta de Inicio
    ↓ Registra en SECOP II
    ✅ PROCESO COMPLETO
```

---

## 📞 SOPORTE Y MANTENIMIENTO

### Agregar Nueva Etapa

1. Actualizar `ProcessStatus` enum
2. Agregar definición en `WorkflowEngine::getStepDefinitions()`
3. Crear vista `step-{N}.blade.php`
4. Actualizar tests

### Agregar Nuevo Tipo de Documento

1. Agregar caso en `DocumentType` enum
2. Actualizar `getRequiredByStep()`
3. Si tiene vigencia, configurar en `getValidityDays()`

### Agregar Nueva Regla de Validación

Editar `WorkflowEngine::validateStepSpecificRules()`:

```php
case N: // Nueva etapa
    if (!$condicion) {
        $errors[] = 'Mensaje de error';
    }
    break;
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

- [x] Enums de estados y tipos de documentos
- [x] Migraciones de base de datos (6 tablas)
- [x] Modelos Eloquent con relaciones
- [x] WorkflowEngine con State Machine
- [x] Controladores (Process y Document)
- [x] Policies de permisos por rol
- [x] Rutas configuradas
- [x] Vistas Blade (ejemplos)
- [x] Tests funcionales completos
- [x] Documentación de arquitectura
- [ ] Notificaciones por email (pendiente implementar Mail)
- [ ] Integración con SharePoint (Etapa 5)
- [ ] Integración con SECOP II API (Etapa 6)
- [ ] Exportación a PDF
- [ ] Dashboard de métricas

---

## 🎓 CONCLUSIÓN

Este módulo proporciona un **sistema robusto, auditable y guiado** para la gestión del proceso de Contratación Directa con Persona Natural. 

**Características principales**:
- ✅ Máquina de estados que previene saltos
- ✅ Validaciones automáticas por etapa
- ✅ Control de vigencias de documentos
- ✅ Trazabilidad completa (auditoría)
- ✅ Notificaciones automáticas
- ✅ Permisos granulares por rol
- ✅ Tests que garantizan reglas críticas
- ✅ UI que guía al usuario paso a paso

**El usuario siempre sabe "qué sigue"** y el sistema **no permite avanzar sin cumplir requisitos**.

---

**Documentación creada**: 2026-02-19  
**Versión**: 1.0  
**Autor**: Arquitecto de Software Senior
