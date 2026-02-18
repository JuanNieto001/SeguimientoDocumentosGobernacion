# 🚀 PLAN DE IMPLEMENTACIÓN PRIORITARIO

## Estado Actual: ⚠️ Sistema Funcional con Gaps Críticos

Este documento detalla las acciones inmediatas que debes ejecutar para tener un backend completo y funcional según la documentación.

---

## ✅ COMPLETADO EN ESTE ANÁLISIS

### 1. Modelos Eloquent Creados (11 modelos)
- ✅ **Workflow.php** - Con relaciones a etapas y procesos
- ✅ **Etapa.php** - Con relaciones completas y métodos útiles
- ✅ **EtapaItem.php** - Para checklist items
- ✅ **ProcesoEtapa.php** - Instancias de etapa con validaciones
- ✅ **ProcesoEtapaCheck.php** - Checks con toggle automático
- ✅ **ProcesoEtapaArchivo.php** - Archivos con métodos útiles
- ✅ **PlanAnualAdquisicion.php** - Gestión del PAA
- ✅ **TipoArchivoPorEtapa.php** - Catálogo de tipos de archivo
- ✅ **ProcesoAuditoria.php** - Sistema de auditoría
- ✅ **Alerta.php** - Sistema de notificaciones
- ✅ **ModificacionContractual.php** - Adiciones, prórrogas, suspensiones
- ✅ **Proceso.php** - Actualizado con todas las relaciones

### 2. Migraciones Creadas
- ✅ **2026_02_17_000020_add_columns_to_workflows_table.php** - Agrega columnas faltantes
- ✅ **2026_02_17_000021_add_paa_id_to_procesos_table.php** - Vincula procesos con PAA

### 3. Documentación Generada
- ✅ **ANALISIS_BACKEND_COMPLETO.md** - Reporte exhaustivo de 65+ páginas
- ✅ **PLAN_IMPLEMENTACION_PRIORITARIO.md** - Este documento

---

## 🔴 ACCIONES CRÍTICAS INMEDIATAS (Hoy mismo)

### ⚠️ ADVERTENCIA: Migraciones Duplicadas
Antes de ejecutar `php artisan migrate`, debes eliminar archivos duplicados:

```bash
# Eliminar estos archivos duplicados:
rm database/migrations/2026_02_17_000006_create_alertas_table.php
rm database/migrations/2026_02_17_000006_create_modificaciones_contractuales_table.php

# Mantener solo las versiones 000007
```

### 1. Ejecutar Nuevas Migraciones

```bash
# 1. Eliminar duplicados primero (ver arriba)

# 2. Ejecutar migraciones nuevas
php artisan migrate

# Esto agregará:
# - Columnas faltantes en workflows
# - Columna paa_id en procesos
```

### 2. Re-ejecutar Seeders

```bash
# Como agregaste columnas a workflows, debes re-seedear
php artisan migrate:fresh --seed

# O si ya tienes datos importantes:
php artisan db:seed --class=WorkflowSeeder
```

---

## 🟠 PRIORIDAD ALTA (Esta semana)

### 1. Crear Etapa 0 en Todos los Workflows

**CRÍTICO**: Según la documentación, TODOS los workflows deben iniciar con:

#### Etapa 0: Verificación y Carga del PAA Vigente
- **Responsable**: Oficina de Contratación (o Planeación)
- **Orden**: Debe ser la primera (orden más bajo)

#### Modificar WorkflowSeeder.php

En cada workflow (CD_PN, MC, SA, LP, CM), agregar ANTES de la actual Etapa 0A:

```php
// ETAPA 0: Verificación PAA (NUEVA)
[
    'orden' => -1, // Menor que el actual 0
    'nombre' => '0: Verificación y Carga del PAA Vigente',
    'area_role' => 'planeacion', // O crear rol 'oficina_contratacion'
    'items' => [
        'PAA vigente del año cargado',
        'Verificación de inclusión en PAA',
        'Certificado de inclusión emitido (si aplica)',
    ],
],
```

**Luego renumerar** las etapas existentes:
- Actual orden 0 → orden 0 (0A)
- Actual orden 1 → orden 1 (0B)
- Etc.

O usar orden -1 para la Etapa 0 y dejar el resto como está.

### 2. Crear Controlador de PAA

Crear `App/Http/Controllers/PAAController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\PlanAnualAdquisicion;
use Illuminate\Http\Request;

class PAAController extends Controller
{
    // Listar PAA vigente
    public function index()
    {
        $paa = PlanAnualAdquisicion::anioVigente()
            ->activos()
            ->vigentes()
            ->orderBy('codigo_necesidad')
            ->get();

        return view('paa.index', compact('paa'));
    }

    // Crear nueva necesidad en PAA
    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo_necesidad' => 'required|unique:plan_anual_adquisiciones',
            'descripcion' => 'required',
            'valor_estimado' => 'required|numeric',
            'modalidad_contratacion' => 'required|in:CD_PN,MC,SA,LP,CM',
            'trimestre_estimado' => 'required|integer|between:1,4',
            'dependencia_solicitante' => 'required',
        ]);

        $data['anio'] = date('Y');
        $data['estado'] = 'vigente';
        $data['activo'] = true;

        PlanAnualAdquisicion::create($data);

        return redirect()->route('paa.index')
            ->with('success', 'Necesidad agregada al PAA correctamente.');
    }

    // Modificar PAA (adición/sustracción/modificación)
    public function update(Request $request, PlanAnualAdquisicion $paa)
    {
        // Implementar lógica de modificación
    }

    // Generar certificado de inclusión
    public function certificadoInclu sion(PlanAnualAdquisicion $paa)
    {
        // Generar PDF del certificado
    }
}
```

### 3. Crear Controladores de Áreas Faltantes

Basándote en `UnidadController.php`, crear:

#### PlaneacionController.php
```php
<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PlaneacionController extends Controller
{
    public function index()
    {
        $areaRole = 'planeacion';
        $user = auth()->user();

        // Lógica similar a UnidadController
        // pero con validaciones específicas de Planeación
    }
}
```

#### HaciendaController.php
```php
<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;

class HaciendaController extends Controller
{
    public function index()
    {
        $areaRole = 'hacienda';
        // Validar CDP, emitir viabilidad económica, etc.
    }
}
```

#### JuridicaController.php
```php
<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;

class JuridicaController extends Controller
{
    public function index()
    {
        $areaRole = 'juridica';
        // Ajustado a derecho, verificación contratista, pólizas, etc.
    }
}
```

#### SecopController.php
```php
<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;

class SecopController extends Controller
{
    public function index()
    {
        $areaRole = 'secop';
        // Publicación, estructuración, firma, acta de inicio, etc.
    }
}
```

### 4. Implementar Auditoría en WorkflowController

Modificar `WorkflowController.php` para registrar auditoría:

```php
public function recibir(Request $request, int $proceso)
{
    $proceso = $this->loadProcesoOrFail($proceso);
    $this->authorizeAreaOrAdmin($proceso);

    return DB::transaction(function () use ($proceso) {
        $procesoEtapa = $this->getProcesoEtapaActual($proceso);
        $this->seedChecksSiFaltan($procesoEtapa->id, $proceso->etapa_actual_id);

        if (!$procesoEtapa->recibido) {
            DB::table('proceso_etapas')->where('id', $procesoEtapa->id)->update([
                'recibido'     => true,
                'recibido_por' => auth()->id(),
                'recibido_at'  => now(),
                'updated_at'   => now(),
            ]);

            // ✅ NUEVO: Registrar auditoría
            ProcesoAuditoria::registrar(
                $proceso->id,
                'recibir_etapa',
                'Etapa ' . $procesoEtapa->etapa_id . ' marcada como recibida',
                $procesoEtapa->etapa_id
            );
        }

        return back()->with('success', 'Documento recibido.');
    });
}
```

Hacer lo mismo en `toggleCheck()` y `enviar()`.

### 5. Implementar Validación de Archivos por Catálogo

Modificar `WorkflowFilesController::store()`:

```php
use App\Models\TipoArchivoPorEtapa;

public function store(Request $request, int $proceso)
{
    $proceso = $this->loadProcesoOrFail($proceso);
    $this->authorizeAreaOrAdmin($proceso);

    // ✅ NUEVO: Obtener tipos permitidos para esta etapa
    $tiposPermitidos = TipoArchivoPorEtapa::where('etapa_id', $proceso->etapa_actual_id)
        ->pluck('tipo')
        ->toArray();

    if (empty($tiposPermitidos)) {
        $tiposPermitidos = ['otro']; // Fallback si no hay tipos definidos
    }

    $request->validate([
        'archivo' => ['required', 'file', 'max:10240'],
        'tipo_archivo' => ['required', 'string', 'in:' . implode(',', $tiposPermitidos)],
    ]);

    // Resto del código...
}
```

---

## 🟡 PRIORIDAD MEDIA (Próximas 2 semanas)

### 1. Sistema de Alertas Automáticas

Crear `App/Services/AlertaService.php`:

```php
<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Proceso;

class AlertaService
{
    /**
     * Generar alertas para procesos estancados
     */
    public static function alertarProcesosEstancados(): void
    {
        $procesosEstancados = Proceso::enCurso()
            ->whereHas('procesoEtapas', function ($q) {
                $q->where('recibido', true)
                  ->where('enviado', false)
                  ->where('created_at', '<', now()->subDays(7));
            })
            ->get();

        foreach ($procesosEstancados as $proceso) {
            // Alertar al responsable del área actual
            $usuarios = User::role($proceso->area_actual_role)->get();
            
            foreach ($usuarios as $usuario) {
                Alerta::crear(
                    $usuario->id,
                    'proceso_estancado',
                    'Proceso sin avance por 7 días',
                    "El proceso {$proceso->codigo} lleva más de 7 días sin avance en tu área.",
                    $proceso->id,
                    'alta',
                    route('area.' . $proceso->area_actual_role, ['proceso_id' => $proceso->id])
                );
            }
        }
    }

    /**
     * Generar alertas para certificados próximos a vencer
     */
    public static function alertarCertificadosVencen(): void
    {
        // Implementar lógica de vencimiento de CDP, PAA, etc.
    }
}
```

Crear comando Artisan para ejecutar diariamente:

```bash
php artisan make:command GenerarAlertasAutomaticas
```

```php
<?php

namespace App\Console\Commands;

use App\Services\AlertaService;
use Illuminate\Console\Command;

class GenerarAlertasAutomaticas extends Command
{
    protected $signature = 'alertas:generar';
    protected $description = 'Genera alertas automáticas para procesos y certificados';

    public function handle()
    {
        AlertaService::alertarProcesosEstancados();
        AlertaService::alertarCertificadosVencen();
        
        $this->info('Alertas generadas correctamente.');
    }
}
```

Programar en `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('alertas:generar')->daily();
}
```

### 2. Dashboard Básico

Crear `DashboardController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\Alerta;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Indicadores generales
        $stats = [
            'total_procesos_activos' => Proceso::enCurso()->count(),
            'procesos_en_mi_area' => $this->getProcesosMiArea($user),
            'alertas_no_leidas' => Alerta::paraUsuario($user->id)->noLeidas()->count(),
            'procesos_estancados' => $this->getProcesosEstancados(),
        ];

        // Procesos por etapa
        $procesosPorEtapa = Proceso::enCurso()
            ->select('area_actual_role', DB::raw('count(*) as total'))
            ->groupBy('area_actual_role')
            ->get();

        // Procesos recientes
        $procesosRecientes = Proceso::orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard', compact('stats', 'procesosPorEtapa', 'procesosRecientes'));
    }

    private function getProcesosMiArea($user)
    {
        $roles = ['planeacion', 'hacienda', 'juridica', 'secop', 'unidad_solicitante'];
        $miRol = collect($roles)->first(fn($r) => $user->hasRole($r));

        if ($miRol === 'unidad_solicitante') {
            return Proceso::creadosPor($user->id)->count();
        }

        return $miRol ? Proceso::area($miRol)->count() : 0;
    }

    private function getProcesosEstancados()
    {
        return Proceso::enCurso()
            ->whereHas('procesoEtapas', function ($q) {
                $q->where('recibido', true)
                  ->where('enviado', false)
                  ->where('created_at', '<', now()->subDays(7));
            })
            ->count();
    }
}
```

---

## 🟢 PRIORIDAD BAJA (Cuando esté estable)

### 1. Reportes Exportables

- Instalar Laravel Excel: `composer require maatwebsite/excel`
- Crear exportadores para:
  - Estado general de procesos
  - Procesos por dependencia
  - Actividad por actor
  - Auditoría completa

### 2. Indicadores Avanzados

- Tiempo promedio por etapa
- Tasa de cumplimiento
- Cuellos de botella
- Eficiencia por área
- Comparativa entre workflows

### 3. Liquidación de Contratos

- Crear modelo `Liquidacion`
- Migración para tabla `liquidaciones`
- Controlador y vistas

---

## 📋 CHECKLIST DE IMPLEMENTACIÓN

### Fase 1: Correcciones Inmediatas ✅

- [x] Crear modelos Eloquent (11 modelos)
- [x] Crear migraciones adicionales (2 migraciones)
- [x] Generar análisis completo
- [ ] Eliminar migraciones duplicadas
- [ ] Ejecutar `php artisan migrate`
- [ ] Re-ejecutar seeders

### Fase 2: Etapa 0 y PAA 🔴

- [ ] Agregar Etapa 0 a todos los workflows
- [ ] Crear PAAController
- [ ] Crear rutas para PAA
- [ ] Crear vistas para PAA
- [ ] Vincular procesos con PAA en creación

### Fase 3: Controladores de Áreas 🟠

- [ ] PlaneacionController completo
- [ ] HaciendaController completo
- [ ] JuridicaController completo
- [ ] SecopController completo
- [ ] Vistas para cada área
- [ ] Rutas para cada área

### Fase 4: Auditoría y Validaciones 🟠

- [ ] Implementar auditoría en WorkflowController
- [ ] Implementar auditoría en WorkflowFilesController
- [ ] Validación de archivos por catálogo
- [ ] Validación de archivos requeridos por tipo

### Fase 5: Alertas y Dashboard 🟡

- [ ] AlertaService completo
- [ ] Comando GenerarAlertasAutomaticas
- [ ] Programar comando diario
- [ ] DashboardController básico
- [ ] Vista del dashboard
- [ ] Indicadores principales

### Fase 6: Features Avanzados 🟢

- [ ] Reportes exportables
- [ ] Indicadores avanzados
- [ ] Liquidación de contratos
- [ ] Modificaciones contractuales (UI completa)
- [ ] Sistema de notificaciones en tiempo real

---

## 🎯 OBJETIVO FINAL

Tener un sistema completamente funcional que:

1. ✅ Cumpla con TODOS los flujos documentados (5 modalidades)
2. ✅ Tenga Etapa 0 (Verificación PAA) en todos los workflows
3. ✅ Gestione el PAA correctamente
4. ✅ Tenga bandejas funcionales para TODAS las áreas
5. ✅ Registre auditoría completa
6. ✅ Genere alertas automáticas
7. ✅ Tenga dashboard con indicadores
8. ✅ Valide archivos correctamente
9. ✅ Permita modificaciones contractuales
10. ✅ Genere reportes exportables

---

## 📞 SOPORTE Y DUDAS

Si tienes dudas durante la implementación:

1. Revisa **ANALISIS_BACKEND_COMPLETO.md** para detalles técnicos
2. Consulta los modelos Eloquent creados (tienen comentarios)
3. Los controladores existentes sirven como ejemplo (UnidadController, WorkflowController)
4. La documentación oficial de Laravel 11.x

---

**Última actualización**: 17 de Febrero de 2026  
**Versión**: 1.0  
**Autor**: GitHub Copilot (Claude Sonnet 4.5)
