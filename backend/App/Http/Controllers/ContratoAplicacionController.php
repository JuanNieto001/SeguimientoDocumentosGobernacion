<?php
/**
 * Archivo: backend/App/Http/Controllers/ContratoAplicacionController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers;

use App\Models\ContratoAplicacion;
use App\Services\ContratoAplicacionSecopSyncService;
use App\Services\SecopDatosAbiertoService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContratoAplicacionController extends Controller
{
    private const MANAGE_ROLES = [
        'admin',
        'admin_general',
        'admin_secretaria',
        'gobernador',
        'secretario',
        'jefe_unidad',
    ];

    public function index(
        Request $request,
        SecopDatosAbiertoService $secopService,
        ContratoAplicacionSecopSyncService $syncService
    ): View
    {
        $q = trim((string) $request->input('q', ''));
        $secopBuscar = trim((string) $request->input('secop_buscar', ''));
        if ($secopBuscar === '' && preg_match('/^CO1\.[A-Z0-9]+\.[0-9]+$/i', $q)) {
            $secopBuscar = strtoupper($q);
        }
        $today = now()->toDateString();
        $limit90 = now()->addDays(90)->toDateString();

        $contratos = ContratoAplicacion::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('aplicacion', 'like', "%{$q}%")
                        ->orWhere('numero_contrato', 'like', "%{$q}%")
                        ->orWhere('proveedor', 'like', "%{$q}%")
                        ->orWhere('secop_proceso_id', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $resumen = [
            'activos_vigentes' => ContratoAplicacion::query()->activos()->vigentes()->count(),
            'por_vencer_90' => ContratoAplicacion::query()
                ->activos()
                ->whereNotNull('fecha_fin')
                ->whereBetween('fecha_fin', [$today, $limit90])
                ->count(),
            'vencidos' => ContratoAplicacion::query()
                ->where(function ($query) use ($today) {
                    $query->where('estado', 'vencido')
                        ->orWhereDate('fecha_fin', '<', $today);
                })
                ->count(),
            'valor_total_activos' => (float) ContratoAplicacion::query()->activos()->sum('valor_total'),
        ];

        $proximoVencer = ContratoAplicacion::query()
            ->activos()
            ->whereNotNull('fecha_fin')
            ->whereDate('fecha_fin', '>=', $today)
            ->orderBy('fecha_fin')
            ->first(['aplicacion', 'fecha_fin', 'secop_proceso_id']);

        $estadoRows = ContratoAplicacion::query()
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->orderBy('estado')
            ->get();

        $estadoLabels = [
            'vigente' => 'Vigentes',
            'por_vencer' => 'Por vencer',
            'vencido' => 'Vencidos',
            'suspendido' => 'Suspendidos',
        ];

        $chartEstado = [
            'labels' => $estadoRows->map(fn ($row) => $estadoLabels[$row->estado] ?? ucfirst((string) $row->estado))->toArray(),
            'data' => $estadoRows->pluck('total')->map(fn ($value) => (int) $value)->toArray(),
            'colors' => ['#16a34a', '#f59e0b', '#dc2626', '#64748b', '#2563eb'],
        ];

        $vencimientosRows = ContratoAplicacion::query()
            ->activos()
            ->whereNotNull('fecha_fin')
            ->orderBy('fecha_fin')
            ->limit(8)
            ->get(['aplicacion', 'fecha_fin']);

        $chartVencimientos = [
            'labels' => $vencimientosRows->pluck('aplicacion')->toArray(),
            'data' => $vencimientosRows->map(function (ContratoAplicacion $contrato) {
                if (!$contrato->fecha_fin) {
                    return 0;
                }

                return max(0, now()->startOfDay()->diffInDays($contrato->fecha_fin->copy()->startOfDay(), false));
            })->toArray(),
        ];

        $secopResultados = [];

        if ($secopBuscar !== '' && auth()->user()?->hasAnyRole(self::MANAGE_ROLES)) {
            $rawResults = [];

            if (preg_match('/^CO1\.[A-Z0-9]+\.[0-9]+$/i', $secopBuscar)) {
                $secopId = strtoupper($secopBuscar);
                $contrato = $secopService->obtenerContrato($secopId);
                if (!$contrato) {
                    $match = $secopService->buscarPorReferencia($secopId);
                    $contrato = $match[0] ?? null;
                }

                if (is_array($contrato)) {
                    $rawResults = [$contrato];
                }
            } else {
                $rawResults = $secopService->buscarPorReferencia($secopBuscar);
            }

            $rawResults = array_slice($rawResults, 0, 10);

            $secopIds = collect($rawResults)
                ->map(fn (array $row) => strtoupper(trim((string) ($row['id_contrato'] ?? ''))))
                ->filter()
                ->values()
                ->all();

            $yaEnLista = [];
            if ($secopIds !== []) {
                $yaEnLista = ContratoAplicacion::query()
                    ->whereIn('secop_proceso_id', $secopIds)
                    ->pluck('secop_proceso_id')
                    ->map(fn ($id) => strtoupper((string) $id))
                    ->all();
            }

            $mapYaEnLista = array_flip($yaEnLista);

            $secopResultados = collect($rawResults)
                ->map(function (array $row) use ($mapYaEnLista, $syncService) {
                    $secopId = strtoupper(trim((string) ($row['id_contrato'] ?? '')));
                    $aplicacion = $syncService->sugerirNombreAplicacion($row, $secopId);
                    $referencia = trim((string) ($row['referencia_del_contrato'] ?? ''));

                    $fechaFin = $this->normalizeDate($row['fecha_de_fin_del_contrato'] ?? null)
                        ?? $this->normalizeDate($row['fecha_de_terminacion_del_contrato'] ?? null);

                    return [
                        'secop_id' => $secopId,
                        'aplicacion' => $aplicacion,
                        'referencia' => $referencia,
                        'proveedor' => trim((string) ($row['proveedor_adjudicado'] ?? '')),
                        'fecha_fin' => $fechaFin,
                        'estado' => trim((string) ($row['estado_contrato'] ?? '')),
                        'ya_agregado' => $secopId !== '' && isset($mapYaEnLista[$secopId]),
                    ];
                })
                ->filter(fn (array $item) => $item['secop_id'] !== '')
                ->values()
                ->all();
        }

        return view('contratos-aplicaciones.index', compact(
            'contratos',
            'q',
            'secopBuscar',
            'secopResultados',
            'resumen',
            'proximoVencer',
            'chartEstado',
            'chartVencimientos',
            'vencimientosRows'
        ));
    }

    public function adicionarDesdeSecop(Request $request, ContratoAplicacionSecopSyncService $syncService): RedirectResponse
    {
        $this->authorizeManage();

        $data = $request->validate([
            'secop_id' => ['nullable', 'string', 'max:160', 'regex:/^CO1\.[A-Z0-9]+\.[0-9]+$/i'],
            'aplicacion_secop' => ['nullable', 'string', 'max:160'],
            'activo_secop' => ['nullable', 'boolean'],
        ]);

        $items = [];

        $secopIdInput = strtoupper(trim((string) ($data['secop_id'] ?? '')));
        if ($secopIdInput !== '') {
            $activoSecop = array_key_exists('activo_secop', $data)
                ? (bool) $data['activo_secop']
                : true;

            $items[$secopIdInput] = [
                'secop_id' => $secopIdInput,
                'aplicacion' => trim((string) ($data['aplicacion_secop'] ?? '')),
                'activo' => $activoSecop,
            ];
        }

        if ($items === []) {
            return back()
                ->withErrors(['secop_id' => 'Ingresa un ID de SECOP válido.'])
                ->withInput();
        }

        $summary = $syncService->upsertFromSecopReferences(array_values($items));
        $sync = $summary['sync'];

        $message = sprintf(
            'Referencias procesadas: %d. Nuevos: %d. Existentes: %d. Sincronizados: %d. No encontrados en SECOP: %d.',
            count($items),
            (int) $summary['created'],
            (int) $summary['existing'],
            (int) $sync['updated'],
            (int) $sync['not_found']
        );

        return redirect()->route('contratos-aplicaciones.index')->with('success', $message);
    }

    public function sincronizarSecop(ContratoAplicacionSecopSyncService $syncService): RedirectResponse
    {
        $this->authorizeManage();

        $contratos = ContratoAplicacion::query()
            ->where('activo', true)
            ->whereNotNull('secop_proceso_id')
            ->where('secop_proceso_id', '!=', '')
            ->orderBy('id')
            ->get();

        if ($contratos->isEmpty()) {
            return redirect()->route('contratos-aplicaciones.index')
                ->with('success', 'No hay contratos activos con ID de SECOP para sincronizar.');
        }

        $summary = $syncService->syncCollection($contratos);

        $message = sprintf(
            'Sincronizacion completada. Actualizados: %d. No encontrados: %d. Omitidos: %d. Errores: %d.',
            (int) $summary['updated'],
            (int) $summary['not_found'],
            (int) $summary['skipped'],
            (int) $summary['errors']
        );

        return redirect()->route('contratos-aplicaciones.index')->with('success', $message);
    }

    public function sincronizarContrato(
        ContratoAplicacion $contratosAplicacione,
        ContratoAplicacionSecopSyncService $syncService
    ): RedirectResponse {
        $this->authorizeManage();

        $result = $syncService->syncContrato($contratosAplicacione);

        $message = match ($result['status'] ?? 'skipped') {
            'updated' => 'Contrato sincronizado desde SECOP correctamente.',
            'not_found' => 'No se encontro el ID en SECOP. Verifica el valor registrado.',
            default => 'No fue posible sincronizar porque el contrato no tiene ID de SECOP.',
        };

        return redirect()->route('contratos-aplicaciones.index')->with('success', $message);
    }

    public function actualizarActivo(Request $request, ContratoAplicacion $contratosAplicacione): RedirectResponse
    {
        $this->authorizeManage();

        $data = $request->validate([
            'activo' => ['required', 'boolean'],
        ]);

        $contratosAplicacione->update([
            'activo' => (bool) $data['activo'],
        ]);

        return redirect()->route('contratos-aplicaciones.index')
            ->with('success', 'Estado de actividad actualizado.');
    }

    public function create(): View
    {
        $this->authorizeManage();

        return view('contratos-aplicaciones.create', [
            'contrato' => new ContratoAplicacion(),
        ]);
    }

    public function store(Request $request, ContratoAplicacionSecopSyncService $syncService): RedirectResponse
    {
        $this->authorizeManage();

        $data = $this->validateData($request);
        $contrato = ContratoAplicacion::create($data);

        $syncResult = null;
        if (!empty($contrato->secop_proceso_id)) {
            $syncResult = $syncService->syncContrato($contrato);
        }

        $message = 'Contrato de aplicación creado correctamente.';
        $syncMessage = $this->syncMessage($syncResult);
        if ($syncMessage !== null) {
            $message .= ' ' . $syncMessage;
        }

        return redirect()->route('contratos-aplicaciones.index')
            ->with('success', $message);
    }

    public function show(ContratoAplicacion $contratosAplicacione): View
    {
        return view('contratos-aplicaciones.show', [
            'contrato' => $contratosAplicacione,
        ]);
    }

    public function edit(ContratoAplicacion $contratosAplicacione): View
    {
        $this->authorizeManage();

        return view('contratos-aplicaciones.edit', [
            'contrato' => $contratosAplicacione,
        ]);
    }

    public function update(
        Request $request,
        ContratoAplicacion $contratosAplicacione,
        ContratoAplicacionSecopSyncService $syncService
    ): RedirectResponse {
        $this->authorizeManage();

        $data = $this->validateData($request);
        $contratosAplicacione->update($data);

        $syncResult = null;
        if (!empty($contratosAplicacione->secop_proceso_id)) {
            $syncResult = $syncService->syncContrato($contratosAplicacione);
        }

        $message = 'Contrato de aplicación actualizado correctamente.';
        $syncMessage = $this->syncMessage($syncResult);
        if ($syncMessage !== null) {
            $message .= ' ' . $syncMessage;
        }

        return redirect()->route('contratos-aplicaciones.show', $contratosAplicacione)
            ->with('success', $message);
    }

    public function destroy(ContratoAplicacion $contratosAplicacione): RedirectResponse
    {
        $this->authorizeManage();
        $contratosAplicacione->delete();

        return redirect()->route('contratos-aplicaciones.index')
            ->with('success', 'Contrato de aplicación eliminado.');
    }

    private function authorizeManage(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasAnyRole(self::MANAGE_ROLES), 403);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'aplicacion' => ['nullable', 'string', 'max:160', 'required_without:secop_proceso_id'],
            'numero_contrato' => ['nullable', 'string', 'max:120'],
            'proveedor' => ['nullable', 'string', 'max:180'],
            'objeto' => ['nullable', 'string'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'valor_total' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', 'string', 'max:40'],
            'secop_proceso_id' => ['nullable', 'string', 'max:160', 'regex:/^CO1\.[A-Z0-9]+\.[0-9]+$/i'],
            'secop_url' => ['nullable', 'url', 'max:500'],
            'responsable' => ['nullable', 'string', 'max:150'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $data['secop_proceso_id'] = strtoupper(trim((string) ($data['secop_proceso_id'] ?? '')));
        if ($data['secop_proceso_id'] === '') {
            $data['secop_proceso_id'] = null;
        }

        $data['aplicacion'] = trim((string) ($data['aplicacion'] ?? ''));
        if ($data['aplicacion'] === '') {
            $data['aplicacion'] = (string) ($data['secop_proceso_id'] ?? 'Aplicativo sin nombre');
        }

        $data['activo'] = $request->boolean('activo');

        return $data;
    }

    private function syncMessage(?array $syncResult): ?string
    {
        if (!$syncResult) {
            return null;
        }

        return match ($syncResult['status'] ?? 'skipped') {
            'updated' => 'Datos sincronizados automáticamente desde SECOP.',
            'not_found' => 'No se encontró el ID en SECOP; verifica el identificador.',
            default => null,
        };
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}

