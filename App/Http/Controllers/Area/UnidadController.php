<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Proceso;
use App\Models\ProcesoAuditoria;
use App\Models\Workflow;

class UnidadController extends Controller
{
    public function index()
    {
        $areaRole = 'unidad_solicitante';
        $user = auth()->user();

        // 1) Listado de procesos que están actualmente en Unidad
        // Admin ve todos los que están en esta área.
        // Unidad solicitante ve solo los creados por él/ella.
        $procesosQuery = DB::table('procesos')
            ->where('area_actual_role', $areaRole)
            ->orderByDesc('id');

        if (!$user->hasRole('admin')) {
            // Unidad puede ver todos los procesos en su bandeja, aunque los haya creado otro usuario (ej. admin)
            // Por eso no filtramos por created_by para este rol.
        }

        $procesos = $procesosQuery->get();

        // 2) Selección segura: si viene proceso_id por querystring, validar que exista en la lista
        $requestedId = request('proceso_id');
        $selectedId = null;

        if ($requestedId) {
            $existsInList = $procesos->firstWhere('id', (int) $requestedId);
            $selectedId = $existsInList ? (int) $requestedId : null;
        }

        if (!$selectedId) {
            $selectedId = $procesos->first()->id ?? null;
        }

        // 3) Cargar proceso seleccionado (si hay)
        $proceso = $selectedId
            ? DB::table('procesos')->where('id', $selectedId)->first()
            : null;

        $procesoEtapa = null;
        $checks = collect();
        $enviarHabilitado = false;
        $archivos = collect();
        $puedeEditar = false; // Nueva variable para controlar si puede editar

        if ($proceso) {

            // 4) Traer/crear la instancia de la etapa actual del proceso (proceso_etapas)
            // IMPORTANTE: aquí lo creamos si no existe para evitar null->id
            $procesoEtapa = DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->first();

            if (!$procesoEtapa) {
                $procesoEtapaId = DB::table('proceso_etapas')->insertGetId([
                    'proceso_id' => $proceso->id,
                    'etapa_id'   => $proceso->etapa_actual_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $procesoEtapa = DB::table('proceso_etapas')->where('id', $procesoEtapaId)->first();
            }

            // ✅ NUEVO: Determinar si puede editar (solo si NO ha enviado)
            $puedeEditar = !$procesoEtapa->enviado;

            // 5) Seed checks si faltan (para que la vista siempre tenga checklist)
            $checksCount = DB::table('proceso_etapa_checks')
                ->where('proceso_etapa_id', $procesoEtapa->id)
                ->count();

            if ($checksCount === 0) {
                $items = DB::table('etapa_items')
                    ->where('etapa_id', $proceso->etapa_actual_id)
                    ->orderBy('orden')
                    ->get(['id']);

                foreach ($items as $item) {
                    DB::table('proceso_etapa_checks')->insert([
                        'proceso_etapa_id' => $procesoEtapa->id,
                        'etapa_item_id'    => $item->id,
                        'checked'          => false,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            // 6) Cargar checklist
            $checks = DB::table('proceso_etapa_checks as pc')
                ->join('etapa_items as ei', 'ei.id', '=', 'pc.etapa_item_id')
                ->select('pc.id as check_id', 'pc.checked', 'ei.label', 'ei.requerido')
                ->where('pc.proceso_etapa_id', $procesoEtapa->id)
                ->orderBy('ei.orden')
                ->get();

            // 7) Habilitar envío según etapa
            $etapaActual = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
            $ordenEtapa = $etapaActual ? $etapaActual->orden : 0;
            
            // Verificar archivos según la etapa
            if ($ordenEtapa == 0) {
                // Etapa 0: requiere solo Estudios Previos
                $enviarHabilitado = DB::table('proceso_etapa_archivos')
                    ->where('proceso_id', $proceso->id)
                    ->where('etapa_id', $proceso->etapa_actual_id)
                    ->where('tipo_archivo', 'estudios_previos')
                    ->exists();
            } else {
                // Otras etapas: habilitar si hay al menos 1 archivo
                $enviarHabilitado = DB::table('proceso_etapa_archivos')
                    ->where('proceso_id', $proceso->id)
                    ->where('etapa_id', $proceso->etapa_actual_id)
                    ->exists();
            }
            
            // 8) Cargar archivos de la etapa actual
            $archivos = DB::table('proceso_etapa_archivos as pea')
                ->join('users as u', 'u.id', '=', 'pea.uploaded_by')
                ->select([
                    'pea.*',
                    'u.name as uploaded_by_name'
                ])
                ->where('pea.proceso_id', $proceso->id)
                ->where('pea.etapa_id', $proceso->etapa_actual_id)
                ->orderByDesc('pea.uploaded_at')
                ->get();
        }

        return view('areas.unidad', compact(
            'areaRole',
            'procesos',
            'proceso',
            'procesoEtapa',
            'checks',
            'enviarHabilitado',
            'archivos',
            'puedeEditar' // ✅ NUEVO: Pasar variable a la vista
        ));
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
            'archivos',
            'auditorias.usuario'
        ])->findOrFail($id);

        $user = auth()->user();
        
        // Verificar acceso: admin o creador del proceso
        abort_unless(
            $user->hasRole('admin') || $proceso->created_by === $user->id,
            403
        );

        return view('areas.unidad-detalle', compact('proceso'));
    }

    /**
     * Crear nuevo proceso desde Unidad Solicitante
     */
    public function crear(Request $request)
    {
        if ($request->isMethod('get')) {
            $workflows = Workflow::all();
            return view('areas.unidad-crear', compact('workflows'));
        }

        $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'objeto' => 'required|string|min:10|max:500',
            'valor_estimado' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($request) {
            $workflow = Workflow::findOrFail($request->workflow_id);
            
            // Obtener la primera etapa (Etapa 0 - PAA o Etapa 1)
            $primeraEtapa = $workflow->etapas()
                ->orderBy('orden')
                ->first();

            if (!$primeraEtapa) {
                return back()->withErrors(['workflow_id' => 'El workflow seleccionado no tiene etapas configuradas.']);
            }

            // Crear el proceso
            $proceso = Proceso::create([
                'workflow_id' => $workflow->id,
                'etapa_actual_id' => $primeraEtapa->id,
                'area_actual_role' => 'unidad_solicitante',
                'objeto' => $request->objeto,
                'valor_estimado' => $request->valor_estimado,
                'observaciones' => $request->observaciones,
                'estado' => 'borrador',
                'created_by' => auth()->id(),
            ]);

            // Crear ProcesoEtapa para la primera etapa
            $procesoEtapa = $proceso->procesoEtapas()->create([
                'etapa_id' => $primeraEtapa->id,
                'recibido' => true,
                'fecha_recepcion' => now(),
            ]);

            // Crear checks para los items de la etapa
            $items = $primeraEtapa->items;
            foreach ($items as $item) {
                $procesoEtapa->checks()->create([
                    'etapa_item_id' => $item->id,
                    'checked' => false,
                ]);
            }

            // Registrar auditoría
            ProcesoAuditoria::registrar(
                $proceso->id,
                'proceso_creado',
                'unidad_solicitante',
                $primeraEtapa->nombre,
                null,
                "Proceso creado por Unidad Solicitante: {$workflow->nombre}"
            );

            return redirect()->route('unidad.index')->with('success', 'Proceso creado exitosamente');
        });
    }

    /**
     * Enviar proceso a la siguiente etapa
     */
    public function enviar(Request $request, $id)
    {
        $proceso = Proceso::with(['workflow', 'etapaActual'])->findOrFail($id);
        
        $user = auth()->user();
        
        // Verificar acceso: admin o creador del proceso
        abort_unless(
            $user->hasRole('admin') || $proceso->created_by === $user->id,
            403
        );

        $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        $procesoEtapa = $proceso->procesoEtapas()
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        if (!$procesoEtapa) {
            return back()->withErrors(['error' => 'No se encontró la etapa actual del proceso.']);
        }

        // Verificar archivos requeridos
        $tiposRequeridos = ['borrador_estudios_previos', 'formato_necesidades'];
        foreach ($tiposRequeridos as $tipo) {
            $existe = DB::table('proceso_etapa_archivos')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $proceso->etapa_actual_id)
                ->where('tipo_archivo', $tipo)
                ->exists();
                
            if (!$existe) {
                return back()->withErrors(['error' => 'Debe cargar todos los archivos requeridos antes de enviar.']);
            }
        }

        return DB::transaction(function () use ($proceso, $procesoEtapa, $request) {
            // Marcar como enviado
            $procesoEtapa->update([
                'enviado' => true,
                'fecha_envio' => now(),
            ]);

            // Obtener siguiente etapa
            $siguienteEtapa = $proceso->workflow->etapas()
                ->where('orden', '>', $proceso->etapaActual->orden)
                ->orderBy('orden')
                ->first();

            if ($siguienteEtapa) {
                // Actualizar proceso a siguiente etapa
                $proceso->update([
                    'etapa_actual_id' => $siguienteEtapa->id,
                    'area_actual_role' => $siguienteEtapa->area_role,
                    'estado' => 'en_proceso',
                ]);

                // Crear nuevo ProcesoEtapa
                $nuevoProcesoEtapa = $proceso->procesoEtapas()->create([
                    'etapa_id' => $siguienteEtapa->id,
                    'recibido' => false,
                ]);

                // Crear checks para la nueva etapa
                foreach ($siguienteEtapa->items as $item) {
                    $nuevoProcesoEtapa->checks()->create([
                        'etapa_item_id' => $item->id,
                        'checked' => false,
                    ]);
                }

                ProcesoAuditoria::registrar(
                    $proceso->id,
                    'etapa_enviada',
                    'unidad_solicitante',
                    $siguienteEtapa->nombre,
                    $proceso->etapaActual->nombre ?? null,
                    "Proceso enviado a {$siguienteEtapa->nombre}. Observaciones: " . ($request->observaciones ?? 'Ninguna')
                );
            } else {
                // No hay más etapas, marcar como completado
                $proceso->update([
                    'estado' => 'completado',
                ]);

                ProcesoAuditoria::registrar(
                    $proceso->id,
                    'proceso_completado',
                    'unidad_solicitante',
                    $proceso->etapaActual->nombre ?? 'Última Etapa',
                    null,
                    "Proceso completado exitosamente"
                );
            }

            return redirect()->back()->with('success', 'Proceso enviado exitosamente');
        });
    }
}
