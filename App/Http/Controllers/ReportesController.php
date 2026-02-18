<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Proceso;
use App\Models\ProcesoAuditoria;
use App\Models\ProcesoEtapaArchivo;
use App\Models\Workflow;
use App\Models\User;
use Carbon\Carbon;

class ReportesController extends Controller
{
    /**
     * Índice de reportes disponibles
     */
    public function index()
    {
        $reportes = [
            [
                'id' => 'estado-general',
                'nombre' => 'Estado General de Procesos',
                'descripcion' => 'Todos los procesos con su estado actual y ubicación',
                'formatos' => ['pdf', 'excel', 'html']
            ],
            [
                'id' => 'por-dependencia',
                'nombre' => 'Procesos por Dependencia',
                'descripcion' => 'Procesos agrupados por dependencia solicitante',
                'formatos' => ['pdf', 'excel', 'html']
            ],
            [
                'id' => 'actividad-actor',
                'nombre' => 'Actividad por Actor',
                'descripcion' => 'Actividades realizadas por cada usuario del sistema',
                'formatos' => ['pdf', 'excel', 'html']
            ],
            [
                'id' => 'auditoria',
                'nombre' => 'Auditoría de Proceso',
                'descripcion' => 'Historial completo de cambios de un proceso específico',
                'formatos' => ['pdf', 'html']
            ],
            [
                'id' => 'certificados-vencer',
                'nombre' => 'Certificados por Vencer',
                'descripcion' => 'Certificados con vigencia menor a 5 días',
                'formatos' => ['pdf', 'excel', 'html']
            ],
            [
                'id' => 'eficiencia',
                'nombre' => 'Eficiencia y Tiempos',
                'descripcion' => 'Tiempos promedio por etapa y modalidad',
                'formatos' => ['pdf', 'excel', 'html']
            ],
        ];

        return view('reportes.index', compact('reportes'));
    }

    /**
     * Reporte: Estado General de Procesos
     */
    public function estadoGeneral(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth());
        $fechaFin = $request->input('fecha_fin', now());
        $modalidad = $request->input('modalidad');
        $estado = $request->input('estado');

        $query = Proceso::with(['workflow', 'etapaActual', 'creador']);

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }

        if ($modalidad) {
            $query->where('workflow_id', $modalidad);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        $procesos = $query->orderBy('created_at', 'desc')->get();

        $estadisticas = [
            'total' => $procesos->count(),
            'en_tramite' => $procesos->where('estado', 'en_tramite')->count(),
            'finalizados' => $procesos->where('estado', 'FINALIZADO')->count(),
            'rechazados' => $procesos->where('estado', 'rechazado')->count(),
        ];

        $formato = $request->input('formato', 'html');

        if ($formato === 'pdf') {
            return $this->generarPDF('reportes.pdf.estado-general', compact('procesos', 'estadisticas', 'fechaInicio', 'fechaFin'));
        }

        if ($formato === 'excel') {
            return $this->generarExcel('estado-general', $procesos, ['Código', 'Objeto', 'Modalidad', 'Estado', 'Etapa Actual', 'Fecha Creación']);
        }

        return view('reportes.estado-general', compact('procesos', 'estadisticas', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Reporte: Procesos por Dependencia
     */
    public function porDependencia(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth());
        $fechaFin = $request->input('fecha_fin', now());

        $procesos = Proceso::with(['workflow', 'etapaActual', 'creador'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->get();

        // Agrupar por dependencia solicitante (basado en el usuario creador)
        $porDependencia = $procesos->groupBy(function($proceso) {
            return $proceso->creador->name ?? 'Sin Asignar';
        });

        $estadisticas = [];
        foreach ($porDependencia as $dependencia => $items) {
            $estadisticas[$dependencia] = [
                'total' => $items->count(),
                'finalizados' => $items->where('estado', 'FINALIZADO')->count(),
                'en_tramite' => $items->where('estado', 'en_tramite')->count(),
            ];
        }

        $formato = $request->input('formato', 'html');

        if ($formato === 'pdf') {
            return $this->generarPDF('reportes.pdf.por-dependencia', compact('porDependencia', 'estadisticas', 'fechaInicio', 'fechaFin'));
        }

        return view('reportes.por-dependencia', compact('porDependencia', 'estadisticas', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Reporte: Actividad por Actor
     */
    public function actividadPorActor(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth());
        $fechaFin = $request->input('fecha_fin', now());
        $userId = $request->input('user_id');

        $query = ProcesoAuditoria::with(['proceso', 'usuario'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $auditorias = $query->orderBy('created_at', 'desc')->get();

        // Agrupar por usuario
        $porUsuario = $auditorias->groupBy('user_id');

        $estadisticas = [];
        foreach ($porUsuario as $uid => $items) {
            $usuario = $items->first()->usuario;
            $estadisticas[$uid] = [
                'nombre' => $usuario->name ?? 'Sistema',
                'email' => $usuario->email ?? '-',
                'total_acciones' => $items->count(),
                'acciones_por_tipo' => $items->groupBy('accion')->map->count(),
            ];
        }

        $formato = $request->input('formato', 'html');

        if ($formato === 'pdf') {
            return $this->generarPDF('reportes.pdf.actividad-actor', compact('auditorias', 'estadisticas', 'fechaInicio', 'fechaFin'));
        }

        return view('reportes.actividad-actor', compact('auditorias', 'estadisticas', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Reporte: Auditoría de Proceso
     */
    public function auditoria(Request $request, $procesoId)
    {
        $proceso = Proceso::with(['workflow', 'creador'])->findOrFail($procesoId);
        
        $auditorias = ProcesoAuditoria::with('usuario')
            ->where('proceso_id', $procesoId)
            ->orderBy('created_at', 'desc')
            ->get();

        $estadisticas = [
            'total_eventos' => $auditorias->count(),
            'usuarios_involucrados' => $auditorias->pluck('user_id')->unique()->count(),
            'duracion_dias' => $proceso->created_at->diffInDays($proceso->updated_at),
            'por_accion' => $auditorias->groupBy('accion')->map->count(),
        ];

        $formato = $request->input('formato', 'html');

        if ($formato === 'pdf') {
            return $this->generarPDF('reportes.pdf.auditoria', compact('proceso', 'auditorias', 'estadisticas'));
        }

        return view('reportes.auditoria', compact('proceso', 'auditorias', 'estadisticas'));
    }

    /**
     * Reporte: Certificados por Vencer
     */
    public function certificadosVencer(Request $request)
    {
        $dias = $request->input('dias', 5);

        $certificados = ProcesoEtapaArchivo::with(['proceso', 'proceso.workflow', 'etapa'])
            ->whereNotNull('fecha_vigencia')
            ->where('estado', 'aprobado')
            ->whereBetween('fecha_vigencia', [now(), now()->addDays($dias)])
            ->orderBy('fecha_vigencia', 'asc')
            ->get();

        $estadisticas = [
            'total' => $certificados->count(),
            'vencen_hoy' => $certificados->filter(fn($c) => $c->fecha_vigencia->isToday())->count(),
            'vencen_manana' => $certificados->filter(fn($c) => $c->fecha_vigencia->isTomorrow())->count(),
            'proximos_3_dias' => $certificados->filter(fn($c) => $c->fecha_vigencia->diffInDays(now()) <= 3)->count(),
        ];

        $formato = $request->input('formato', 'html');

        if ($formato === 'pdf') {
            return $this->generarPDF('reportes.pdf.certificados-vencer', compact('certificados', 'estadisticas', 'dias'));
        }

        return view('reportes.certificados-vencer', compact('certificados', 'estadisticas', 'dias'));
    }

    /**
     * Reporte: Eficiencia y Tiempos
     */
    public function eficiencia(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonths(3));
        $fechaFin = $request->input('fecha_fin', now());

        $procesosFinalizados = Proceso::with(['workflow', 'procesoEtapas.etapa'])
            ->where('estado', 'FINALIZADO')
            ->whereBetween('updated_at', [$fechaInicio, $fechaFin])
            ->get();

        // Tiempo promedio total
        $tiempoPromedioTotal = $procesosFinalizados->avg(function($proceso) {
            return $proceso->created_at->diffInDays($proceso->updated_at);
        });

        // Tiempo promedio por modalidad
        $porModalidad = $procesosFinalizados->groupBy('workflow.nombre')->map(function($items) {
            return [
                'cantidad' => $items->count(),
                'promedio_dias' => round($items->avg(function($proceso) {
                    return $proceso->created_at->diffInDays($proceso->updated_at);
                }), 2),
                'min_dias' => $items->min(function($proceso) {
                    return $proceso->created_at->diffInDays($proceso->updated_at);
                }),
                'max_dias' => $items->max(function($proceso) {
                    return $proceso->created_at->diffInDays($proceso->updated_at);
                }),
            ];
        });

        $estadisticas = [
            'total_finalizados' => $procesosFinalizados->count(),
            'promedio_general' => round($tiempoPromedioTotal, 2),
            'por_modalidad' => $porModalidad,
        ];

        $formato = $request->input('formato', 'html');

        if ($formato === 'pdf') {
            return $this->generarPDF('reportes.pdf.eficiencia', compact('procesosFinalizados', 'estadisticas', 'fechaInicio', 'fechaFin'));
        }

        return view('reportes.eficiencia', compact('procesosFinalizados', 'estadisticas', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Generar PDF (placeholder - requiere barryvdh/laravel-dompdf)
     */
    private function generarPDF($view, $data)
    {
        // TODO: Implementar cuando se instale barryvdh/laravel-dompdf
        // $pdf = PDF::loadView($view, $data);
        // return $pdf->download('reporte.pdf');
        
        // Por ahora, retornar vista HTML
        return view($view, $data)->with('modo_pdf', true);
    }

    /**
     * Generar Excel (placeholder - requiere maatwebsite/excel)
     */
    private function generarExcel($nombre, $datos, $columnas)
    {
        // TODO: Implementar cuando se instale maatwebsite/excel
        // return Excel::download(new ReporteExport($datos, $columnas), "{$nombre}.xlsx");
        
        // Por ahora, retornar CSV simple
        return response()->streamDownload(function() use ($datos, $columnas) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columnas);
            
            foreach ($datos as $item) {
                $row = [
                    $item->codigo ?? '-',
                    $item->objeto ?? '-',
                    $item->workflow->nombre ?? '-',
                    $item->estado ?? '-',
                    $item->etapaActual->nombre ?? '-',
                    $item->created_at->format('d/m/Y') ?? '-',
                ];
                fputcsv($handle, $row);
            }
            
            fclose($handle);
        }, "{$nombre}.csv");
    }
}
