<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Proceso;
use App\Models\ProcesoAuditoria;
use App\Models\PlanAnualAdquisicion;
use App\Services\AlertaService;

class PlaneacionController extends Controller
{
    /**
     * Obtiene un texto amigable del responsable al que se devuelve el proceso.
     */
    private function resolverResponsableDestino(Proceso $proceso, object $etapaDestino): string
    {
        $areaRole = (string) ($etapaDestino->area_role ?? '');

        if ($areaRole === 'unidad_solicitante') {
            $unidadNombre = DB::table('unidades')
                ->where('id', $proceso->unidad_origen_id)
                ->value('nombre');

            if ($unidadNombre) {
                return "Jefe, {$unidadNombre}";
            }

            $creadorNombre = DB::table('users')
                ->where('id', $proceso->created_by)
                ->value('name');

            if ($creadorNombre) {
                return "Responsable, {$creadorNombre}";
            }

            return 'Unidad solicitante';
        }

        $areaNombre = match ($areaRole) {
            'planeacion' => 'Descentralización',
            'hacienda' => 'Secretaría de Hacienda',
            'juridica' => 'Jurídica',
            'secop' => 'SECOP',
            'compras' => 'Unidad de Compras y Suministros',
            'talento_humano' => 'Jefatura de Talento Humano',
            'rentas' => 'Unidad de Rentas',
            'contabilidad' => 'Unidad de Contabilidad',
            'inversiones_publicas' => 'Unidad de Regalías e Inversiones Públicas',
            'presupuesto' => 'Unidad de Presupuesto',
            'radicacion' => 'Radicación y Correspondencia',
            default => null,
        };

        if ($areaNombre) {
            return $areaNombre;
        }

        if (!empty($etapaDestino->nombre)) {
            return (string) $etapaDestino->nombre;
        }

        return 'área anterior';
    }

    /**
     * Determina si el proceso debe usar solicitudes documentales paralelas en Etapa 1.
     * CD-PN mantiene este comportamiento tanto en workflow legacy como en flujo dinámico.
     */
    private function usaSolicitudesParalelasEtapa1(Proceso $proceso): bool
    {
        // Flujo dinámico actual de CD-PN
        if ((int) ($proceso->flujo_id ?? 0) === 1) {
            return true;
        }

        $workflow = DB::table('workflows')
            ->where('id', $proceso->workflow_id)
            ->select('codigo', 'nombre')
            ->first();

        $codigo = strtoupper((string) ($workflow->codigo ?? ''));
        $nombre = strtoupper((string) ($workflow->nombre ?? ''));

        return in_array($codigo, ['CD_PN', 'FLUJO_1'], true)
            || str_contains($nombre, 'PERSONA NATURAL');
    }

    public function index(Request $request)
    {
        // Bandeja de Descentralización: solo procesos pendientes en su área
        $query = DB::table('procesos')
            ->leftJoin('users as creador', 'creador.id', '=', 'procesos.created_by')
            ->leftJoin('etapas', 'etapas.id', '=', 'procesos.etapa_actual_id')
            ->select(
                'procesos.*',
                'creador.name as creado_por_nombre',
                'etapas.nombre as etapa_nombre',
                'etapas.orden as etapa_orden'
            )
            ->where('procesos.area_actual_role', 'planeacion')
            ->orderByDesc('procesos.id');

        if ($request->filled('buscar')) {
            $q = '%'.$request->buscar.'%';
            $hasContratistaNombre = Schema::hasColumn('procesos', 'contratista_nombre');
            $hasContratistaDocumento = Schema::hasColumn('procesos', 'contratista_documento');

                        $query->where(function ($w) use ($q, $hasContratistaNombre, $hasContratistaDocumento) {
                $w->where('procesos.codigo', 'like', $q)
                  ->orWhere('procesos.objeto', 'like', $q);

                if ($hasContratistaNombre) {
                    $w->orWhere('procesos.contratista_nombre', 'like', $q);
                }

                if ($hasContratistaDocumento) {
                    $w->orWhere('procesos.contratista_documento', 'like', $q);
                }
            });
        }
        if ($request->filled('estado')) {
            $query->where('procesos.estado', $request->estado);
        }
        if ($request->filled('etapa')) {
            $query->where('etapas.orden', $request->etapa);
        }

        $procesos = $query->get();

        $etapas = DB::table('etapas')
            ->where('workflow_id', 1)
            ->orderBy('orden')
            ->get();

        return view('planeacion.index', compact('procesos', 'etapas'));
    }

    /**
     * Ver detalle de un proceso
     */
    public function show($id)
    {
        $proceso = Proceso::with([
            'workflow',
            'etapaActual',
            'procesoEtapas.etapa',
            'procesoEtapas.checks.etapaItem',
            'archivos',
            'auditorias.usuario'
        ])->findOrFail($id);

        // Verificar que sea del área de planeación o admin
        if ($proceso->area_actual_role !== 'planeacion' && !auth()->user()->hasRole('admin')) {
            return redirect()->route('planeacion.index')
                ->with('success', 'El proceso fue enviado exitosamente a la siguiente área.');
        }

        // Cargar la etapa actual del proceso con sus checks
        $procesoEtapaActual = DB::table('proceso_etapas')
            ->where('proceso_id', $proceso->id)
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        // Cargar checks si existe la etapa
        $checks = collect();
        if ($procesoEtapaActual) {
            $checks = DB::table('proceso_etapa_checks as pc')
                ->join('etapa_items as ei', 'ei.id', '=', 'pc.etapa_item_id')
                ->select('pc.id', 'pc.checked', 'pc.checked_by', 'pc.checked_at', 'ei.label', 'ei.requerido')
                ->where('pc.proceso_etapa_id', $procesoEtapaActual->id)
                ->orderBy('ei.orden')
                ->get();
        }

        return view('planeacion.show', compact('proceso', 'procesoEtapaActual', 'checks'));
    }

    /**
     * Verificar inclusión en PAA
     */
    public function verificarPAA($id)
    {
        $proceso = Proceso::findOrFail($id);
        
        // Buscar en PAA
        $paa = PlanAnualAdquisicion::where('vigencia', now()->year)
            ->where('workflow_id', $proceso->workflow_id)
            ->where('descripcion_necesidad', 'like', '%' . $proceso->objeto . '%')
            ->orWhere('codigo_bpin', $proceso->codigo_bpin ?? '')
            ->first();

        if ($paa) {
            // Actualizar proceso con datos del PAA
            $proceso->update([
                'paa_verificado' => true,
                'paa_id' => $paa->id,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'paa_verificado',
                'planeacion',
                $proceso->etapaActual->nombre ?? 'Verificación PAA',
                null,
                "Proceso verificado en PAA. Registro PAA #{$paa->id}"
            );

            return redirect()->back()->with('success', 'Proceso verificado exitosamente en el PAA');
        }

        return redirect()->back()->with('error', 'El proceso NO está incluido en el PAA vigente');
    }

    /**
     * Aprobar proceso en Planeación
     */
    public function aprobar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        
        // Validar que el proceso esté en el área de planeación
        if ($proceso->area_actual_role !== 'planeacion') {
            return redirect()->route('planeacion.index')
                ->with('error', 'Este proceso ya no está en tu bandeja.');
        }

        $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        // ── Validar que el proceso haya sido marcado como recibido ──
        $procesoEtapaActual = DB::table('proceso_etapas')
            ->where('proceso_id', $proceso->id)
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();
        if (!$procesoEtapaActual || !$procesoEtapaActual->recibido) {
            return redirect()->back()->with('error',
                'Debes marcar el documento como recibido antes de aprobar.');
        }

        // ── Validar que todos los documentos paralelos hayan sido recibidos ──
        $solicitudes = DB::table('proceso_documentos_solicitados')
            ->where('proceso_id', $proceso->id)
            ->get();

        if ($solicitudes->count() > 0) {
            $pendientes = $solicitudes->where('estado', '!=', 'subido');
            if ($pendientes->count() > 0) {
                $faltantes = $pendientes->pluck('nombre_documento')->implode(', ');
                return redirect()->back()->with('error', 
                    "No se puede aprobar: faltan documentos por recibir de las áreas → {$faltantes}");
            }
        } else {
            // CD-PN usa solicitudes paralelas en Etapa 1 incluso con flujo dinámico.
            if ($this->usaSolicitudesParalelasEtapa1($proceso)) {
                $wfController = app(\App\Http\Controllers\WorkflowController::class);
                $wfController->solicitarDocumentosEtapa1($proceso);

                return redirect()->back()->with('info',
                    'Se han enviado las solicitudes de documentos a las áreas correspondientes. ' .
                    'Espera a que todas las áreas suban sus documentos antes de aprobar.');
            }
        }
        // ── FIN Validación documentos paralelos ──

        $result = DB::transaction(function () use ($proceso, $request) {
            // Marcar etapa actual como enviada
            $procesoEtapaActual = DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();
            
            if ($procesoEtapaActual) {
                DB::table('proceso_etapas')
                    ->where('id', $procesoEtapaActual->id)
                    ->update([
                        'enviado' => true,
                        'enviado_por' => auth()->id(),
                        'enviado_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            
            // Buscar siguiente etapa
            $siguienteEtapa = DB::table('etapas')
                ->where('workflow_id', $proceso->workflow_id)
                ->where('orden', '>', $proceso->etapaActual->orden)
                ->where('activa', 1)
                ->orderBy('orden')
                ->first();
            
            if ($siguienteEtapa) {
                // Actualizar proceso a siguiente etapa
                $proceso->update([
                    'etapa_actual_id' => $siguienteEtapa->id,
                    'area_actual_role' => $siguienteEtapa->area_role,
                    'aprobado_planeacion' => true,
                    'observaciones_planeacion' => $request->observaciones,
                ]);
                
                // Crear instancia de siguiente etapa
                $procesoEtapaSiguiente = DB::table('proceso_etapas')->insertGetId([
                    'proceso_id' => $proceso->id,
                    'etapa_id' => $siguienteEtapa->id,
                    'recibido' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Crear checks de siguiente etapa
                $items = DB::table('etapa_items')
                    ->where('etapa_id', $siguienteEtapa->id)
                    ->orderBy('orden')
                    ->get(['id']);
                
                foreach ($items as $item) {
                    DB::table('proceso_etapa_checks')->insert([
                        'proceso_etapa_id' => $procesoEtapaSiguiente,
                        'etapa_item_id' => $item->id,
                        'checked' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                // No hay más etapas - proceso completado
                $proceso->update([
                    'estado' => 'completado',
                    'aprobado_planeacion' => true,
                    'observaciones_planeacion' => $request->observaciones,
                ]);
            }

            ProcesoAuditoria::registrar(
                $proceso->id,
                'aprobado_planeacion',
                'planeacion',
                $proceso->etapaActual->nombre ?? 'Aprobación Planeación',
                null,
                "Proceso aprobado por Planeación. Observaciones: " . ($request->observaciones ?? 'Ninguna')
            );
            
            return true;
        });

        if ($result) {
            return redirect()->route('planeacion.index')->with('success', 'Proceso aprobado y enviado a la siguiente etapa');
        }
        
        return redirect()->route('planeacion.index')->with('error', 'Error al aprobar el proceso');
    }

    /**
     * Rechazar proceso en Planeación
     */
    public function rechazar(Request $request, $id)
    {
        $proceso = Proceso::with('etapaActual')->findOrFail($id);
        
        // Validar que el proceso esté en el área de planeación
        if ($proceso->area_actual_role !== 'planeacion') {
            return redirect()->route('planeacion.index')
                ->with('error', 'Este proceso ya no está en tu bandeja.');
        }

        $validated = $request->validate([
            'motivo_rechazo' => 'required|string|min:10|max:500',
        ]);

        $etapaActual = $proceso->etapaActual;

        // Buscar etapa anterior por el enlace del flujo (fallback por orden para flujos legacy)
        $etapaAnterior = DB::table('etapas')
            ->where('workflow_id', $proceso->workflow_id)
            ->where('next_etapa_id', $etapaActual->id)
            ->first();

        if (!$etapaAnterior && isset($etapaActual->orden)) {
            $etapaAnterior = DB::table('etapas')
                ->where('workflow_id', $proceso->workflow_id)
                ->where('orden', '<', $etapaActual->orden)
                ->where('activa', 1)
                ->orderByDesc('orden')
                ->first();
        }

        if (!$etapaAnterior) {
            return redirect()->back()->with('error', 'No se pudo devolver el proceso a una etapa anterior.');
        }

        $responsableDestino = $this->resolverResponsableDestino($proceso, $etapaAnterior);

        DB::transaction(function () use ($proceso, $validated, $etapaActual, $etapaAnterior, $responsableDestino) {
            // Devolver proceso a la etapa anterior para reingreso de información/documentos
            DB::table('procesos')->where('id', $proceso->id)->update([
                'etapa_actual_id' => $etapaAnterior->id,
                'area_actual_role' => $etapaAnterior->area_role,
                'estado' => 'EN_CURSO',
                'rechazado_por_area' => 'planeacion',
                'observaciones_rechazo' => $validated['motivo_rechazo'],
                'updated_at' => now(),
            ]);

            // Reabrir etapa anterior (si ya existe) o crear la instancia si faltaba
            $procesoEtapaAnterior = DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $etapaAnterior->id)
                ->first();

            if ($procesoEtapaAnterior) {
                DB::table('proceso_etapas')
                    ->where('id', $procesoEtapaAnterior->id)
                    ->update([
                        'enviado' => false,
                        'enviado_por' => null,
                        'enviado_at' => null,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('proceso_etapas')->insert([
                    'proceso_id' => $proceso->id,
                    'etapa_id' => $etapaAnterior->id,
                    'recibido' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Marcar etapa actual como no recibida para dejar trazabilidad consistente
            DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $etapaActual->id)
                ->update([
                    'recibido' => false,
                    'recibido_por' => null,
                    'recibido_at' => null,
                    'updated_at' => now(),
                ]);

            $procesoActualizado = Proceso::find($proceso->id);
            if ($procesoActualizado) {
                AlertaService::crearParaArea(
                    proceso: $procesoActualizado,
                    tipo: 'proceso_devuelto',
                    titulo: 'Proceso devuelto por rechazo',
                    mensaje: "El proceso {$procesoActualizado->codigo} fue devuelto a {$responsableDestino}. Motivo: {$validated['motivo_rechazo']}",
                    areaRole: $etapaAnterior->area_role,
                    prioridad: 'alta',
                    metadata: [
                        'motivo_rechazo' => $validated['motivo_rechazo'],
                        'etapa_origen' => $etapaActual->id,
                        'etapa_destino' => $etapaAnterior->id,
                        'responsable_destino' => $responsableDestino,
                    ],
                    accionUrl: route('procesos.show', $proceso->id)
                );
            }

            ProcesoAuditoria::registrar(
                $proceso->id,
                'rechazado_planeacion',
                "Proceso devuelto por Planeación a {$responsableDestino}",
                $etapaActual->id,
                ['area_origen' => 'planeacion'],
                [
                    'motivo' => $validated['motivo_rechazo'],
                    'etapa_destino' => $etapaAnterior->nombre,
                    'responsable_destino' => $responsableDestino,
                ]
            );
        });

        return redirect()->route('planeacion.index')
            ->with('success', "Proceso rechazado con éxito y devuelto a {$responsableDestino}.");
    }

    /**
     * Reportes de Planeación
     */
    public function reportes(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth());

        $estadisticas = [
            'total_procesos' => Proceso::whereBetween('created_at', [$fechaInicio, $fechaFin])->count(),
            'en_planeacion' => Proceso::where('area_actual_role', 'planeacion')->count(),
            'aprobados' => Proceso::where('aprobado_planeacion', true)->count(),
            'rechazados' => Proceso::where('rechazado_por_area', 'planeacion')->count(),
            'por_modalidad' => DB::table('procesos')
                ->join('workflows', 'procesos.workflow_id', '=', 'workflows.id')
                ->select('workflows.nombre', DB::raw('count(*) as total'))
                ->whereBetween('procesos.created_at', [$fechaInicio, $fechaFin])
                ->groupBy('workflows.nombre')
                ->get(),
        ];

        return view('areas.planeacion-reportes', compact('estadisticas', 'fechaInicio', 'fechaFin'));
    }
}
