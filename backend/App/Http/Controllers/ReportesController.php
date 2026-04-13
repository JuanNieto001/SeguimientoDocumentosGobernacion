<?php
/**
 * Archivo: backend/App/Http/Controllers/ReportesController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\ProcesoAuditoria;
use App\Models\ProcesoEtapaArchivo;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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
            return $this->generarPDF(
                nombreBase: 'estado-general',
                titulo: 'Estado General de Procesos',
                columnas: ['Código', 'Objeto', 'Modalidad', 'Estado', 'Etapa Actual', 'Fecha Creación'],
                filas: $procesos->map(fn($item) => [
                    $item->codigo ?? '-',
                    $item->objeto ?? '-',
                    $item->workflow->nombre ?? '-',
                    $item->estado ?? '-',
                    $item->etapaActual->nombre ?? '-',
                    optional($item->created_at)->format('d/m/Y H:i') ?? '-',
                ])->all(),
                contexto: [
                    'Desde' => (string) $fechaInicio,
                    'Hasta' => (string) $fechaFin,
                    'Total procesos' => (string) $estadisticas['total'],
                ]
            );
        }

        if ($formato === 'excel') {
            return $this->generarExcel(
                'estado-general',
                ['Código', 'Objeto', 'Modalidad', 'Estado', 'Etapa Actual', 'Fecha Creación'],
                $procesos->map(fn($item) => [
                    $item->codigo ?? '-',
                    $item->objeto ?? '-',
                    $item->workflow->nombre ?? '-',
                    $item->estado ?? '-',
                    $item->etapaActual->nombre ?? '-',
                    optional($item->created_at)->format('d/m/Y H:i') ?? '-',
                ])->all()
            );
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
            return $this->generarPDF(
                nombreBase: 'por-dependencia',
                titulo: 'Procesos por Dependencia',
                columnas: ['Dependencia', 'Código', 'Objeto', 'Modalidad', 'Estado', 'Fecha Creación'],
                filas: $procesos->map(fn($item) => [
                    $item->creador->name ?? 'Sin Asignar',
                    $item->codigo ?? '-',
                    $item->objeto ?? '-',
                    $item->workflow->nombre ?? '-',
                    $item->estado ?? '-',
                    optional($item->created_at)->format('d/m/Y H:i') ?? '-',
                ])->all(),
                contexto: [
                    'Desde' => (string) $fechaInicio,
                    'Hasta' => (string) $fechaFin,
                    'Dependencias con actividad' => (string) count($estadisticas),
                ]
            );
        }

        if ($formato === 'excel') {
            return $this->generarExcel(
                'por-dependencia',
                ['Dependencia', 'Código', 'Objeto', 'Modalidad', 'Estado', 'Fecha Creación'],
                $procesos->map(fn($item) => [
                    $item->creador->name ?? 'Sin Asignar',
                    $item->codigo ?? '-',
                    $item->objeto ?? '-',
                    $item->workflow->nombre ?? '-',
                    $item->estado ?? '-',
                    optional($item->created_at)->format('d/m/Y H:i') ?? '-',
                ])->all()
            );
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
            return $this->generarPDF(
                nombreBase: 'actividad-actor',
                titulo: 'Actividad por Actor',
                columnas: ['Fecha', 'Usuario', 'Email', 'Acción', 'Proceso', 'Descripción'],
                filas: $auditorias->map(fn($item) => [
                    optional($item->created_at)->format('d/m/Y H:i:s') ?? '-',
                    $item->usuario?->name ?? 'Sistema',
                    $item->usuario?->email ?? '-',
                    $item->accion ?? '-',
                    $item->proceso?->codigo ?? '-',
                    $item->descripcion ?? '-',
                ])->all(),
                contexto: [
                    'Desde' => (string) $fechaInicio,
                    'Hasta' => (string) $fechaFin,
                    'Eventos' => (string) $auditorias->count(),
                ]
            );
        }

        if ($formato === 'excel') {
            return $this->generarExcel(
                'actividad-actor',
                ['Fecha', 'Usuario', 'Email', 'Acción', 'Proceso', 'Descripción'],
                $auditorias->map(fn($item) => [
                    optional($item->created_at)->format('d/m/Y H:i:s') ?? '-',
                    $item->usuario?->name ?? 'Sistema',
                    $item->usuario?->email ?? '-',
                    $item->accion ?? '-',
                    $item->proceso?->codigo ?? '-',
                    $item->descripcion ?? '-',
                ])->all()
            );
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
            return $this->generarPDF(
                nombreBase: 'auditoria-' . $proceso->codigo,
                titulo: 'Auditoría de Proceso ' . $proceso->codigo,
                columnas: ['Fecha', 'Usuario', 'Acción', 'Descripción'],
                filas: $auditorias->map(fn($item) => [
                    optional($item->created_at)->format('d/m/Y H:i:s') ?? '-',
                    $item->usuario?->name ?? 'Sistema',
                    $item->accion ?? '-',
                    $item->descripcion ?? '-',
                ])->all(),
                contexto: [
                    'Proceso' => $proceso->codigo,
                    'Total eventos' => (string) $estadisticas['total_eventos'],
                    'Usuarios involucrados' => (string) $estadisticas['usuarios_involucrados'],
                ]
            );
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
            return $this->generarPDF(
                nombreBase: 'certificados-vencer',
                titulo: 'Certificados Próximos a Vencer',
                columnas: ['Proceso', 'Documento', 'Etapa', 'Estado', 'Fecha vigencia', 'Días restantes'],
                filas: $certificados->map(function ($item) {
                    $diasRestantes = now()->diffInDays($item->fecha_vigencia, false);
                    return [
                        $item->proceso?->codigo ?? '-',
                        $item->nombre_original ?? '-',
                        $item->etapa?->nombre ?? '-',
                        $item->estado ?? '-',
                        optional($item->fecha_vigencia)->format('d/m/Y') ?? '-',
                        (string) $diasRestantes,
                    ];
                })->all(),
                contexto: [
                    'Ventana (días)' => (string) $dias,
                    'Total certificados' => (string) $estadisticas['total'],
                ]
            );
        }

        if ($formato === 'excel') {
            return $this->generarExcel(
                'certificados-vencer',
                ['Proceso', 'Documento', 'Etapa', 'Estado', 'Fecha vigencia', 'Días restantes'],
                $certificados->map(function ($item) {
                    $diasRestantes = now()->diffInDays($item->fecha_vigencia, false);
                    return [
                        $item->proceso?->codigo ?? '-',
                        $item->nombre_original ?? '-',
                        $item->etapa?->nombre ?? '-',
                        $item->estado ?? '-',
                        optional($item->fecha_vigencia)->format('d/m/Y') ?? '-',
                        (string) $diasRestantes,
                    ];
                })->all()
            );
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
        $porModalidad = $procesosFinalizados->groupBy(function ($proceso) {
            return $proceso->workflow->nombre ?? 'Sin modalidad';
        })->map(function($items) {
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
            return $this->generarPDF(
                nombreBase: 'eficiencia',
                titulo: 'Eficiencia por Modalidad',
                columnas: ['Modalidad', 'Cantidad', 'Promedio días', 'Mínimo días', 'Máximo días'],
                filas: collect($porModalidad)->map(fn($item, $modalidad) => [
                    (string) $modalidad,
                    (string) $item['cantidad'],
                    (string) $item['promedio_dias'],
                    (string) $item['min_dias'],
                    (string) $item['max_dias'],
                ])->values()->all(),
                contexto: [
                    'Desde' => (string) $fechaInicio,
                    'Hasta' => (string) $fechaFin,
                    'Finalizados' => (string) $estadisticas['total_finalizados'],
                    'Promedio general (días)' => (string) $estadisticas['promedio_general'],
                ]
            );
        }

        if ($formato === 'excel') {
            return $this->generarExcel(
                'eficiencia',
                ['Modalidad', 'Cantidad', 'Promedio días', 'Mínimo días', 'Máximo días'],
                collect($porModalidad)->map(fn($item, $modalidad) => [
                    (string) $modalidad,
                    (string) $item['cantidad'],
                    (string) $item['promedio_dias'],
                    (string) $item['min_dias'],
                    (string) $item['max_dias'],
                ])->values()->all()
            );
        }

        return view('reportes.eficiencia', compact('procesosFinalizados', 'estadisticas', 'fechaInicio', 'fechaFin'));
    }

    private function generarPDF(string $nombreBase, string $titulo, array $columnas, array $filas, array $contexto = [])
    {
        $html = view('reportes.pdf.tabla', [
            'titulo' => $titulo,
            'columnas' => $columnas,
            'filas' => $filas,
            'contexto' => $contexto,
            'generadoEn' => now(),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->nombreArchivo($nombreBase, 'pdf') . '"',
        ]);
    }

    private function generarExcel(string $nombreBase, array $columnas, array $filas)
    {
        $tmpRoot = storage_path('app/tmp');
        File::ensureDirectoryExists($tmpRoot);

        $workDir = $tmpRoot . DIRECTORY_SEPARATOR . 'xlsx_' . Str::uuid()->toString();
        File::makeDirectory($workDir . DIRECTORY_SEPARATOR . '_rels', 0755, true);
        File::makeDirectory($workDir . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . '_rels', 0755, true);
        File::makeDirectory($workDir . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'worksheets', 0755, true);

        $allRows = array_merge([$columnas], $filas);
        $rowCount = count($allRows);
        $colCount = max(count($columnas), 1);
        $lastCol = $this->excelColumnName($colCount);
        $dimension = 'A1:' . $lastCol . $rowCount;

        $sheetRowsXml = '';
        foreach ($allRows as $rowIndex => $row) {
            $r = $rowIndex + 1;
            $cells = '';

            for ($i = 0; $i < $colCount; $i++) {
                $cellRef = $this->excelColumnName($i + 1) . $r;
                $value = $this->escapeXml((string) ($row[$i] ?? ''));
                $cells .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $value . '</t></is></c>';
            }

            $sheetRowsXml .= '<row r="' . $r . '">' . $cells . '</row>';
        }

        File::put($workDir . DIRECTORY_SEPARATOR . '[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>'
        );

        File::put($workDir . DIRECTORY_SEPARATOR . '_rels' . DIRECTORY_SEPARATOR . '.rels',
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>'
        );

        File::put($workDir . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'workbook.xml',
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Reporte" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>'
        );

        File::put($workDir . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . '_rels' . DIRECTORY_SEPARATOR . 'workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>'
        );

        File::put($workDir . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'worksheets' . DIRECTORY_SEPARATOR . 'sheet1.xml',
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="' . $dimension . '"/>'
            . '<sheetData>' . $sheetRowsXml . '</sheetData>'
            . '</worksheet>'
        );

        $xlsxTempPath = $tmpRoot . DIRECTORY_SEPARATOR . 'reporte_' . Str::uuid()->toString() . '.xlsx';
        $zip = new \ZipArchive();
        if ($zip->open($xlsxTempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            File::deleteDirectory($workDir);
            abort(500, 'No fue posible generar el archivo XLSX.');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($workDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }

            $absolutePath = $file->getRealPath();
            $relativePath = str_replace($workDir . DIRECTORY_SEPARATOR, '', $absolutePath);
            $relativePath = str_replace('\\', '/', $relativePath);
            $zip->addFile($absolutePath, $relativePath);
        }

        $zip->close();
        File::deleteDirectory($workDir);

        return response()->download(
            $xlsxTempPath,
            $this->nombreArchivo($nombreBase, 'xlsx'),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function nombreArchivo(string $base, string $extension): string
    {
        return $base . '_' . now()->format('Ymd_His') . '.' . $extension;
    }

    private function excelColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }
        return $name;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}

