<?php

namespace App\Http\Controllers;

use App\Enums\EstadoProcesoCD;
use App\Http\Requests\CrearProcesoContratacionRequest;
use App\Http\Requests\TransicionProcesoRequest;
use App\Models\ProcesoCDAuditoria;
use App\Models\ProcesoCDDocumento;
use App\Models\ProcesoContratacionDirecta;
use App\Models\Secretaria;
use App\Models\Unidad;
use App\Services\ContratoDirectoPNStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcesoContratacionDirectaController extends Controller
{
    public function __construct(
        protected ContratoDirectoPNStateMachine $stateMachine
    ) {}

    // ═══════════════════════════════════════════════
    //  LISTADO
    // ═══════════════════════════════════════════════
    public function index(Request $request)
    {
        $query = ProcesoContratacionDirecta::with([
            'creadoPor', 'secretaria', 'unidad',
        ])->activos();

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('etapa')) {
            $query->where('etapa_actual', $request->etapa);
        }
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                  ->orWhere('objeto', 'like', "%{$buscar}%")
                  ->orWhere('contratista_nombre', 'like', "%{$buscar}%");
            });
        }

        // Filtrar por rol del usuario
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('unidad_solicitante')) {
                $query->where('creado_por', $user->id);
            } elseif ($user->hasRole('planeacion')) {
                $query->whereIn('estado', [
                    EstadoProcesoCD::EN_VALIDACION_PLANEACION->value,
                    EstadoProcesoCD::COMPATIBILIDAD_APROBADA->value,
                    EstadoProcesoCD::CDP_SOLICITADO->value,
                    EstadoProcesoCD::CDP_BLOQUEADO->value,
                    EstadoProcesoCD::RPC_SOLICITADO->value,
                    EstadoProcesoCD::EXPEDIENTE_RADICADO->value,
                ]);
            } elseif ($user->hasRole('hacienda')) {
                $query->whereIn('estado', [
                    EstadoProcesoCD::CDP_SOLICITADO->value,
                    EstadoProcesoCD::CDP_APROBADO->value,
                    EstadoProcesoCD::RPC_FIRMADO->value,
                ]);
            } elseif ($user->hasRole('juridica')) {
                $query->whereIn('estado', [
                    EstadoProcesoCD::EN_REVISION_JURIDICA->value,
                    EstadoProcesoCD::PROCESO_NUMERO_GENERADO->value,
                    EstadoProcesoCD::GENERACION_CONTRATO->value,
                    EstadoProcesoCD::CONTRATO_GENERADO->value,
                    EstadoProcesoCD::CONTRATO_DEVUELTO->value,
                ]);
            } else {
                $query->delUsuario($user);
            }
        }

        $procesos = $query->latest()->paginate(20);
        $estados  = EstadoProcesoCD::cases();

        return view('proceso-cd.index', compact('procesos', 'estados'));
    }

    // ═══════════════════════════════════════════════
    //  CREAR
    // ═══════════════════════════════════════════════
    public function create()
    {
        $secretarias = Secretaria::orderBy('nombre')->get();
        $unidades    = Unidad::orderBy('nombre')->get();

        return view('proceso-cd.create', compact('secretarias', 'unidades'));
    }

    public function store(CrearProcesoContratacionRequest $request)
    {
        $datos = $request->validated();

        // Guardar archivo de estudios previos
        if ($request->hasFile('estudio_previo')) {
            $datos['estudio_previo_path'] = $request->file('estudio_previo')
                ->store('procesos-cd/estudios-previos', 'public');
        }

        // Eliminar el campo 'estudio_previo' (es file, no se guarda directo)
        unset($datos['estudio_previo']);

        $proceso = $this->stateMachine->crearSolicitud($datos, auth()->user());

        return redirect()
            ->route('proceso-cd.show', $proceso)
            ->with('success', "Solicitud creada exitosamente. Código: {$proceso->codigo}. Enviada automáticamente a Planeación.");
    }

    // ═══════════════════════════════════════════════
    //  VER DETALLE (con vista por etapa)
    // ═══════════════════════════════════════════════
    public function show(ProcesoContratacionDirecta $procesoCD)
    {
        $procesoCD->load([
            'creadoPor', 'supervisor', 'ordenadorGasto',
            'jefeUnidad', 'abogadoUnidad',
            'secretaria', 'unidad',
            'documentos.subidoPor',
        ]);

        $user = auth()->user();
        $puedeAvanzar     = $this->stateMachine->puedeAvanzar($procesoCD, $user);
        $erroresAvance     = $this->stateMachine->erroresParaAvanzar($procesoCD);
        $transiciones      = $procesoCD->estado->transicionesPermitidas();
        $documentosFaltantes = $procesoCD->documentosFaltantes();
        $auditoria         = $procesoCD->auditorias()->with('usuario')->take(20)->get();

        return view('proceso-cd.show', compact(
            'procesoCD',
            'puedeAvanzar',
            'erroresAvance',
            'transiciones',
            'documentosFaltantes',
            'auditoria'
        ));
    }

    // ═══════════════════════════════════════════════
    //  TRANSICIONAR ESTADO
    // ═══════════════════════════════════════════════
    public function transicionar(TransicionProcesoRequest $request, ProcesoContratacionDirecta $procesoCD)
    {
        $destino = EstadoProcesoCD::from($request->estado_destino);

        try {
            $this->stateMachine->transicionar(
                $procesoCD,
                $destino,
                auth()->user(),
                $request->comentario,
                $request->only([
                    'numero_cdp', 'valor_cdp', 'numero_proceso',
                    'numero_rpc', 'numero_contrato', 'observaciones',
                    'observaciones_juridica', 'resultado', 'fecha_inicio',
                ])
            );

            return redirect()
                ->route('proceso-cd.show', $procesoCD)
                ->with('success', "Proceso avanzado a: {$destino->label()}");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'No se puede realizar la transición.');
        }
    }

    // ═══════════════════════════════════════════════
    //  VALIDACIONES PARALELAS (Etapa 2)
    // ═══════════════════════════════════════════════
    public function registrarValidacion(Request $request, ProcesoContratacionDirecta $procesoCD)
    {
        $request->validate(['campo' => 'required|string']);

        try {
            $this->stateMachine->registrarValidacionParalela(
                $procesoCD,
                $request->campo,
                auth()->user()
            );

            return back()->with('success', "Validación «{$request->campo}» registrada correctamente.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    // ═══════════════════════════════════════════════
    //  FIRMAS (Etapa 5)
    // ═══════════════════════════════════════════════
    public function registrarFirma(Request $request, ProcesoContratacionDirecta $procesoCD)
    {
        $request->validate(['tipo_firma' => 'required|in:contratista,ordenador_gasto']);

        try {
            $this->stateMachine->registrarFirma(
                $procesoCD,
                $request->tipo_firma,
                auth()->user()
            );

            return back()->with('success', 'Firma registrada correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    // ═══════════════════════════════════════════════
    //  DEVOLVER (Etapa 4 – Jurídica / Etapa 5 – Contrato)
    // ═══════════════════════════════════════════════
    public function devolver(Request $request, ProcesoContratacionDirecta $procesoCD)
    {
        $request->validate([
            'tipo_devolucion' => 'required|in:juridica,contrato',
            'observaciones'   => 'required|string|max:2000',
        ]);

        try {
            if ($request->tipo_devolucion === 'juridica') {
                $this->stateMachine->devolverDesdeJuridica(
                    $procesoCD,
                    $request->observaciones,
                    auth()->user()
                );
            } else {
                $this->stateMachine->devolverContrato(
                    $procesoCD,
                    $request->observaciones,
                    auth()->user()
                );
            }

            return redirect()
                ->route('proceso-cd.show', $procesoCD)
                ->with('success', 'Proceso devuelto con observaciones.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    // ═══════════════════════════════════════════════
    //  SUBIR DOCUMENTO
    // ═══════════════════════════════════════════════
    public function subirDocumento(Request $request, ProcesoContratacionDirecta $procesoCD)
    {
        $request->validate([
            'tipo_documento' => 'required|string|max:80',
            'archivo'        => 'required|file|max:20480',
            'observaciones'  => 'nullable|string|max:1000',
        ]);

        $archivo = $request->file('archivo');
        $ruta = $archivo->store("procesos-cd/{$procesoCD->id}/documentos", 'public');

        $documento = ProcesoCDDocumento::create([
            'proceso_cd_id'    => $procesoCD->id,
            'tipo_documento'   => $request->tipo_documento,
            'nombre_archivo'   => $archivo->getClientOriginalName(),
            'ruta_archivo'     => $ruta,
            'mime_type'        => $archivo->getMimeType(),
            'tamano_bytes'     => $archivo->getSize(),
            'etapa'            => $procesoCD->etapa_actual,
            'estado_aprobacion'=> 'pendiente',
            'observaciones'    => $request->observaciones,
            'subido_por'       => auth()->id(),
            'es_obligatorio'   => in_array($request->tipo_documento, $procesoCD->estado->documentosObligatorios()),
        ]);

        ProcesoCDAuditoria::registrarAccion(
            $procesoCD,
            'documento_cargado',
            "Documento cargado: {$request->tipo_documento} ({$archivo->getClientOriginalName()})",
            ['tipo' => $request->tipo_documento, 'documento_id' => $documento->id],
            auth()->user()
        );

        // Si es hoja de vida SIGEP, marcar
        if ($request->tipo_documento === 'hoja_vida_sigep') {
            $procesoCD->update(['hoja_vida_cargada' => true]);
        }

        return back()->with('success', 'Documento cargado correctamente.');
    }

    // ═══════════════════════════════════════════════
    //  DESCARGAR DOCUMENTO
    // ═══════════════════════════════════════════════
    public function descargarDocumento(ProcesoContratacionDirecta $procesoCD, ProcesoCDDocumento $documento)
    {
        if ($documento->proceso_cd_id !== $procesoCD->id) {
            abort(403);
        }

        $fullPath = storage_path('app/public/' . $documento->ruta_archivo);
        return response()->download($fullPath, $documento->nombre_archivo);
    }

    // ═══════════════════════════════════════════════
    //  APROBAR / RECHAZAR DOCUMENTO
    // ═══════════════════════════════════════════════
    public function aprobarDocumento(Request $request, ProcesoContratacionDirecta $procesoCD, ProcesoCDDocumento $documento)
    {
        $request->validate(['accion' => 'required|in:aprobar,rechazar', 'observaciones' => 'nullable|string|max:1000']);

        $documento->update([
            'estado_aprobacion' => $request->accion === 'aprobar' ? 'aprobado' : 'rechazado',
            'aprobado_por'      => auth()->id(),
            'fecha_aprobacion'  => now(),
            'observaciones'     => $request->observaciones ?? $documento->observaciones,
        ]);

        ProcesoCDAuditoria::registrarAccion(
            $procesoCD,
            "documento_{$request->accion}",
            "Documento {$request->accion}: {$documento->tipo_documento}",
            ['documento_id' => $documento->id, 'accion' => $request->accion],
            auth()->user()
        );

        return back()->with('success', "Documento " . ($request->accion === 'aprobar' ? 'aprobado' : 'rechazado') . " correctamente.");
    }

    // ═══════════════════════════════════════════════
    //  CANCELAR PROCESO
    // ═══════════════════════════════════════════════
    public function cancelar(Request $request, ProcesoContratacionDirecta $procesoCD)
    {
        $request->validate(['motivo' => 'required|string|max:2000']);

        try {
            $this->stateMachine->cancelar($procesoCD, $request->motivo, auth()->user());
            return redirect()
                ->route('proceso-cd.index')
                ->with('success', 'Proceso cancelado.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    // ═══════════════════════════════════════════════
    //  HISTORIAL DE AUDITORÍA
    // ═══════════════════════════════════════════════
    public function auditoria(ProcesoContratacionDirecta $procesoCD)
    {
        $registros = $procesoCD->auditorias()
            ->with('usuario')
            ->paginate(50);

        return view('proceso-cd.auditoria', compact('procesoCD', 'registros'));
    }
}
