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
        $user = auth()->user();

        $procesos = DB::table('procesos as p')
            ->leftJoin('etapas as e', 'e.id', '=', 'p.etapa_actual_id')
            ->where('p.area_actual_role', 'unidad_solicitante')
            ->orderByDesc('p.id')
            ->select(
                'p.id', 'p.codigo', 'p.objeto', 'p.estado',
                'p.valor_estimado', 'p.created_at',
                'e.nombre as etapa_nombre', 'e.orden as etapa_orden'
            )
            ->get();

        return view('areas.unidad', compact('procesos'));
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

        // Verificar acceso: admin o unidad_solicitante
        abort_unless(
            $user->hasRole('admin') || $user->hasRole('unidad_solicitante'),
            403
        );

        // Obtener etapa actual
        $etapaActual = $proceso->etapaActual;
        $ordenEtapa = $etapaActual ? $etapaActual->orden : 0;

        // Cargar proceso_etapa actual
        $procesoEtapaActual = DB::table('proceso_etapas')
            ->where('proceso_id', $proceso->id)
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        // Si estamos en etapa 2, 3 o 4 (del abogado: Validación Contratista, Docs Contractuales, Consolidación)
        if ($ordenEtapa >= 2 && $ordenEtapa <= 4 && $proceso->area_actual_role === 'unidad_solicitante') {

            // Verificar si $procesoEtapaActual tiene recibido
            $recibido = $procesoEtapaActual && $procesoEtapaActual->recibido;

            // Cargar checks con campos de recibido_fisico y archivos
            $documentos = collect();
            if ($procesoEtapaActual) {
                // Asegurar que existan los checks (seedear si faltan)
                $checksCount = DB::table('proceso_etapa_checks')
                    ->where('proceso_etapa_id', $procesoEtapaActual->id)
                    ->count();

                if ($checksCount === 0) {
                    $items = DB::table('etapa_items')
                        ->where('etapa_id', $proceso->etapa_actual_id)
                        ->orderBy('orden')
                        ->get();

                    foreach ($items as $item) {
                        DB::table('proceso_etapa_checks')->insert([
                            'proceso_etapa_id' => $procesoEtapaActual->id,
                            'etapa_item_id'    => $item->id,
                            'checked'          => false,
                            'recibido_fisico'  => false,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                }

                $documentos = DB::table('proceso_etapa_checks as pc')
                    ->join('etapa_items as ei', 'ei.id', '=', 'pc.etapa_item_id')
                    ->select(
                        'pc.id as check_id',
                        'pc.checked',
                        'pc.recibido_fisico',
                        'pc.recibido_fisico_at',
                        'pc.archivo_path',
                        'pc.archivo_nombre',
                        'pc.archivo_subido_at',
                        'ei.label',
                        'ei.requerido',
                        'ei.tipo_documento',
                        'ei.responsable_unidad',
                        'ei.notas'
                    )
                    ->where('pc.proceso_etapa_id', $procesoEtapaActual->id)
                    ->orderBy('ei.orden')
                    ->get();
            }

            // Calcular progreso (total y sólo requeridos para habilitar el botón)
            $totalDocs        = $documentos->count();
            $docsRequeridos   = $documentos->where('requerido', true);
            $totalRequeridos  = $docsRequeridos->count();
            $recibidosFisico  = $documentos->where('recibido_fisico', true)->count();
            $archivosSubidos  = $documentos->whereNotNull('archivo_path')->count();
            $reqFisico        = $docsRequeridos->where('recibido_fisico', true)->count();
            $reqArchivo       = $docsRequeridos->filter(fn($d) => !is_null($d->archivo_path))->count();
            $todosCompletos   = $totalRequeridos > 0 && $reqFisico === $totalRequeridos && $reqArchivo === $totalRequeridos;

            // ── Documentos recibidos de Descentralización (etapa 1) ───────────
            $docsDescentralizacion = DB::table('proceso_documentos_solicitados as pds')
                ->leftJoin('proceso_etapa_archivos as pea', 'pea.id', '=', 'pds.archivo_id')
                ->leftJoin('users as u', 'u.id', '=', 'pds.subido_por')
                ->leftJoin('etapas as e', 'e.id', '=', 'pds.etapa_id')
                ->where('pds.proceso_id', $proceso->id)
                ->select(
                    'pds.id',
                    'pds.nombre_documento',
                    'pds.area_responsable_nombre',
                    'pds.area_responsable_rol',
                    'pds.estado',
                    'pds.subido_at',
                    'pds.archivo_id',
                    'pea.nombre_original as archivo_nombre',
                    'pea.ruta as archivo_ruta',
                    'u.name as subido_por_nombre',
                    'e.nombre as etapa_nombre'
                )
                ->orderBy('pds.id')
                ->get();

            return view('areas.unidad-abogado', compact(
                'proceso',
                'procesoEtapaActual',
                'recibido',
                'documentos',
                'totalDocs',
                'recibidosFisico',
                'archivosSubidos',
                'todosCompletos',
                'ordenEtapa',
                'docsDescentralizacion'
            ));
        }

        // Para etapa 0 u otras, usar la vista por defecto
        return view('areas.unidad-detalle', compact('proceso'));
    }

    /**
     * Marcar como recibido (para etapa 2+)
     */
    public function recibir(Request $request, $id)
    {
        $proceso = Proceso::findOrFail($id);
        $user = auth()->user();

        abort_unless($user->hasRole('admin') || $user->hasRole('unidad_solicitante'), 403);

        $procesoEtapa = DB::table('proceso_etapas')
            ->where('proceso_id', $proceso->id)
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        if ($procesoEtapa && !$procesoEtapa->recibido) {
            DB::table('proceso_etapas')
                ->where('id', $procesoEtapa->id)
                ->update([
                    'recibido'     => true,
                    'recibido_por' => $user->id,
                    'recibido_at'  => now(),
                    'updated_at'   => now(),
                ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'documento_recibido',
                'unidad_solicitante',
                $proceso->etapaActual->nombre ?? 'Etapa actual',
                null,
                'Documento recibido por el abogado'
            );
        }

        return redirect()->back()->with('success', 'Documento marcado como recibido.');
    }

    /**
     * Marcar un documento del contratista como recibido físicamente
     */
    public function marcarRecibidoFisico(Request $request, $procesoId, $checkId)
    {
        $proceso = Proceso::findOrFail($procesoId);
        $user = auth()->user();

        abort_unless($user->hasRole('admin') || $user->hasRole('unidad_solicitante'), 403);

        $check = DB::table('proceso_etapa_checks')->where('id', $checkId)->first();
        abort_unless($check, 404);

        // Toggle recibido_fisico
        $nuevoEstado = !$check->recibido_fisico;

        DB::table('proceso_etapa_checks')
            ->where('id', $checkId)
            ->update([
                'recibido_fisico'     => $nuevoEstado,
                'recibido_fisico_at'  => $nuevoEstado ? now() : null,
                'recibido_fisico_por' => $nuevoEstado ? $user->id : null,
                'checked'             => $nuevoEstado, // También marcar el check general
                'checked_by'          => $nuevoEstado ? $user->id : null,
                'checked_at'          => $nuevoEstado ? now() : null,
                'updated_at'          => now(),
            ]);

        // Obtener etapa_item label
        $item = DB::table('etapa_items')->where('id', $check->etapa_item_id)->first();
        $labelDoc = $item ? $item->label : 'Documento #'.$checkId;

        ProcesoAuditoria::registrar(
            $proceso->id,
            $nuevoEstado ? 'recibido_fisico' : 'recibido_fisico_revertido',
            'unidad_solicitante',
            $proceso->etapaActual->nombre ?? 'Validación Contratista',
            null,
            ($nuevoEstado ? 'Recibido físicamente: ' : 'Recepción revertida: ') . $labelDoc
        );

        return redirect()->back()->with('success',
            $nuevoEstado
                ? "Documento marcado como recibido físicamente: {$labelDoc}"
                : "Recepción revertida: {$labelDoc}"
        );
    }

    /**
     * Subir archivo digital para un documento del contratista
     */
    public function subirArchivoDigital(Request $request, $procesoId, $checkId)
    {
        $proceso = Proceso::findOrFail($procesoId);
        $user = auth()->user();

        abort_unless($user->hasRole('admin') || $user->hasRole('unidad_solicitante'), 403);

        $request->validate([
            'archivo' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.max' => 'El archivo no debe superar 10MB.',
            'archivo.mimes' => 'Formato no permitido. Usa: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG.',
        ]);

        $check = DB::table('proceso_etapa_checks')->where('id', $checkId)->first();
        abort_unless($check, 404);

        // Guardar archivo
        $file = $request->file('archivo');
        $nombreOriginal = $file->getClientOriginalName();
        $path = $file->store("procesos/{$procesoId}/contratista", 'public');

        DB::table('proceso_etapa_checks')
            ->where('id', $checkId)
            ->update([
                'archivo_path'      => $path,
                'archivo_nombre'    => $nombreOriginal,
                'archivo_subido_at' => now(),
                'updated_at'        => now(),
            ]);

        $item = DB::table('etapa_items')->where('id', $check->etapa_item_id)->first();
        $labelDoc = $item ? $item->label : 'Documento #'.$checkId;

        ProcesoAuditoria::registrar(
            $proceso->id,
            'archivo_digital_subido',
            'unidad_solicitante',
            $proceso->etapaActual->nombre ?? 'Validación Contratista',
            null,
            "Archivo digital subido: {$labelDoc} ({$nombreOriginal})"
        );

        return redirect()->back()->with('success', "Archivo subido exitosamente: {$nombreOriginal}");
    }

    /**
     * Aprobar etapa 2 (Validación Contratista) y enviar a siguiente etapa
     */
    public function aprobarEtapa2(Request $request, $id)
    {
        $proceso = Proceso::with(['workflow', 'etapaActual'])->findOrFail($id);
        $user = auth()->user();

        abort_unless($user->hasRole('admin') || $user->hasRole('unidad_solicitante'), 403);

        // Verificar que esté en etapa 2 con role unidad_solicitante
        if ($proceso->area_actual_role !== 'unidad_solicitante') {
            return redirect()->back()->with('error', 'Este proceso no está en tu área.');
        }

        // Verificar recibido
        $procesoEtapa = DB::table('proceso_etapas')
            ->where('proceso_id', $proceso->id)
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        if (!$procesoEtapa || !$procesoEtapa->recibido) {
            return redirect()->back()->with('error', 'Debes marcar el proceso como recibido primero.');
        }

        // Verificar que todos los documentos requeridos tengan recibido_fisico y archivo
        $docsRequeridos = DB::table('proceso_etapa_checks as pc')
            ->join('etapa_items as ei', 'ei.id', '=', 'pc.etapa_item_id')
            ->where('pc.proceso_etapa_id', $procesoEtapa->id)
            ->where('ei.requerido', true)
            ->get();

        foreach ($docsRequeridos as $doc) {
            if (!$doc->recibido_fisico) {
                return redirect()->back()->with('error', 'Todos los documentos requeridos deben estar marcados como recibidos físicamente.');
            }
            if (!$doc->archivo_path) {
                return redirect()->back()->with('error', 'Todos los documentos requeridos deben tener su archivo digital subido.');
            }
        }

        return DB::transaction(function () use ($proceso, $procesoEtapa, $request, $user) {
            // Marcar etapa actual como enviada
            DB::table('proceso_etapas')
                ->where('id', $procesoEtapa->id)
                ->update([
                    'enviado'     => true,
                    'enviado_por' => $user->id,
                    'enviado_at'  => now(),
                    'updated_at'  => now(),
                ]);

            // Buscar siguiente etapa usando next_etapa_id
            $etapaActual = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
            $siguienteEtapa = $etapaActual && $etapaActual->next_etapa_id
                ? DB::table('etapas')->where('id', $etapaActual->next_etapa_id)->first()
                : null;

            if ($siguienteEtapa) {
                $proceso->update([
                    'etapa_actual_id'  => $siguienteEtapa->id,
                    'area_actual_role' => $siguienteEtapa->area_role,
                    'estado'           => 'EN_CURSO',
                ]);

                // Crear proceso_etapa para la siguiente si no existe
                $yaExiste = DB::table('proceso_etapas')
                    ->where('proceso_id', $proceso->id)
                    ->where('etapa_id', $siguienteEtapa->id)
                    ->exists();

                if (!$yaExiste) {
                    DB::table('proceso_etapas')->insert([
                        'proceso_id' => $proceso->id,
                        'etapa_id'   => $siguienteEtapa->id,
                        'recibido'   => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                ProcesoAuditoria::registrar(
                    $proceso->id,
                    'etapa_aprobada',
                    'unidad_solicitante',
                    $siguienteEtapa->nombre,
                    $etapaActual->nombre ?? null,
                    'Documentos del contratista verificados. Proceso enviado a: ' . $siguienteEtapa->nombre
                );
            }

            return redirect()->route('unidad.show', $proceso->id)
                ->with('success', 'Documentos verificados y proceso enviado a la siguiente etapa: ' . ($siguienteEtapa->nombre ?? '—'));
        });
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
