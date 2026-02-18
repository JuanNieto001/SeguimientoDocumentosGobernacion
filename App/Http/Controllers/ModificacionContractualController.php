<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Proceso;
use App\Models\ModificacionContractual;
use App\Models\ProcesoAuditoria;

class ModificacionContractualController extends Controller
{
    /**
     * Listar modificaciones de un proceso
     */
    public function index($procesoId)
    {
        $proceso = Proceso::with(['modificaciones' => function($q) {
            $q->orderByDesc('fecha_solicitud');
        }])->findOrFail($procesoId);

        // Verificar acceso
        $user = auth()->user();
        abort_unless(
            $user->hasRole('admin') || 
            $proceso->area_actual_role === $user->getRoleNames()->first(),
            403
        );

        $estadisticas = [
            'total_modificaciones' => $proceso->modificaciones->count(),
            'valor_acumulado' => $proceso->modificaciones->where('estado', 'aprobado')->sum('valor_modificacion'),
            'porcentaje_usado' => $this->calcularPorcentajeUsado($proceso),
            'porcentaje_disponible' => 50 - $this->calcularPorcentajeUsado($proceso),
        ];

        return view('modificaciones.index', compact('proceso', 'estadisticas'));
    }

    /**
     * Crear nueva modificación contractual
     */
    public function store(Request $request, $procesoId)
    {
        $proceso = Proceso::with('modificaciones')->findOrFail($procesoId);

        $request->validate([
            'tipo' => 'required|in:adicion,prorroga,suspension,cesion,terminacion,otro',
            'descripcion' => 'required|string|min:10|max:1000',
            'valor_modificacion' => 'required_if:tipo,adicion|nullable|numeric|min:0',
            'plazo_adicional_dias' => 'required_if:tipo,prorroga|nullable|integer|min:1',
            'justificacion' => 'required|string|min:20|max:2000',
            'archivo_soporte' => 'required|file|mimes:pdf|max:10240',
        ]);

        // Validar límite del 50% para adiciones
        if ($request->tipo === 'adicion') {
            $porcentajeActual = $this->calcularPorcentajeUsado($proceso);
            $porcentajeNuevo = ($request->valor_modificacion / $proceso->valor_estimado) * 100;
            
            if (($porcentajeActual + $porcentajeNuevo) > 50) {
                return back()->withErrors([
                    'valor_modificacion' => sprintf(
                        'La modificación excede el límite del 50%%. Ya se ha usado %.2f%% y esta modificación representa %.2f%%. Disponible: %.2f%%',
                        $porcentajeActual,
                        $porcentajeNuevo,
                        50 - $porcentajeActual
                    )
                ]);
            }
        }

        return DB::transaction(function () use ($proceso, $request) {
            // Guardar archivo
            $archivo = $request->file('archivo_soporte');
            $nombreGuardado = 'MODIFICACION_' . strtoupper($request->tipo) . '_' . time() . '.pdf';
            $ruta = "procesos/{$proceso->id}/modificaciones/{$nombreGuardado}";
            
            $archivo->storeAs('public/' . dirname($ruta), basename($ruta));

            // Crear modificación
            $modificacion = ModificacionContractual::create([
                'proceso_id' => $proceso->id,
                'tipo' => $request->tipo,
                'descripcion' => $request->descripcion,
                'valor_modificacion' => $request->valor_modificacion,
                'plazo_adicional_dias' => $request->plazo_adicional_dias,
                'justificacion' => $request->justificacion,
                'estado' => 'pendiente',
                'fecha_solicitud' => now(),
                'solicitado_por' => auth()->id(),
                'archivo_soporte' => $ruta,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'modificacion_solicitada',
                auth()->user()->getRoleNames()->first() ?? 'usuario',
                'Modificación Contractual',
                null,
                "Modificación tipo {$request->tipo} solicitada. Valor: " . ($request->valor_modificacion ?? 'N/A')
            );

            return redirect()->route('modificaciones.index', $proceso->id)
                ->with('success', 'Modificación contractual solicitada exitosamente');
        });
    }

    /**
     * Aprobar modificación contractual
     */
    public function aprobar(Request $request, $procesoId, $modificacionId)
    {
        $proceso = Proceso::findOrFail($procesoId);
        $modificacion = ModificacionContractual::findOrFail($modificacionId);

        abort_unless(auth()->user()->hasRole('admin|juridica'), 403);
        abort_unless($modificacion->proceso_id === $proceso->id, 404);

        $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($modificacion, $proceso, $request) {
            $modificacion->update([
                'estado' => 'aprobado',
                'fecha_aprobacion' => now(),
                'aprobado_por' => auth()->id(),
                'observaciones_aprobacion' => $request->observaciones,
            ]);

            // Actualizar valores del proceso según tipo de modificación
            if ($modificacion->tipo === 'adicion') {
                $proceso->update([
                    'valor_estimado' => $proceso->valor_estimado + $modificacion->valor_modificacion,
                ]);
            }

            if ($modificacion->tipo === 'prorroga' && $modificacion->plazo_adicional_dias) {
                // Aquí podrías actualizar la fecha de finalización si la tienes
            }

            ProcesoAuditoria::registrar(
                $proceso->id,
                'modificacion_aprobada',
                'juridica',
                'Modificación Contractual',
                null,
                "Modificación #{$modificacion->id} tipo {$modificacion->tipo} aprobada"
            );
        });

        return back()->with('success', 'Modificación contractual aprobada');
    }

    /**
     * Rechazar modificación contractual
     */
    public function rechazar(Request $request, $procesoId, $modificacionId)
    {
        $proceso = Proceso::findOrFail($procesoId);
        $modificacion = ModificacionContractual::findOrFail($modificacionId);

        abort_unless(auth()->user()->hasRole('admin|juridica'), 403);
        abort_unless($modificacion->proceso_id === $proceso->id, 404);

        $request->validate([
            'observaciones' => 'required|string|min:10|max:500',
        ]);

        DB::transaction(function () use ($modificacion, $proceso, $request) {
            $modificacion->update([
                'estado' => 'rechazado',
                'fecha_rechazo' => now(),
                'rechazado_por' => auth()->id(),
                'observaciones_rechazo' => $request->observaciones,
            ]);

            ProcesoAuditoria::registrar(
                $proceso->id,
                'modificacion_rechazada',
                'juridica',
                'Modificación Contractual',
                null,
                "Modificación #{$modificacion->id} rechazada. Motivo: {$request->observaciones}"
            );
        });

        return back()->with('warning', 'Modificación contractual rechazada');
    }

    /**
     * Calcular porcentaje usado del 50%
     */
    private function calcularPorcentajeUsado(Proceso $proceso): float
    {
        if (!$proceso->valor_estimado || $proceso->valor_estimado == 0) {
            return 0;
        }

        $totalAdiciones = $proceso->modificaciones()
            ->where('tipo', 'adicion')
            ->where('estado', 'aprobado')
            ->sum('valor_modificacion');

        return ($totalAdiciones / $proceso->valor_estimado) * 100;
    }

    /**
     * Descargar archivo de soporte
     */
    public function descargar($procesoId, $modificacionId)
    {
        $modificacion = ModificacionContractual::findOrFail($modificacionId);
        abort_unless($modificacion->proceso_id == $procesoId, 404);

        $rutaCompleta = storage_path('app/public/' . $modificacion->archivo_soporte);

        if (!file_exists($rutaCompleta)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->download($rutaCompleta);
    }
}
