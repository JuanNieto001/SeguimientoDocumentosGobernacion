<?php
/**
 * Archivo: backend/App/Http/Controllers/PAAController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers;

use App\Models\PlanAnualAdquisicion;
use App\Models\Proceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PAAController extends Controller
{
    private const MODALIDADES = [
        'CD_PN' => 'Contratación Directa - Persona Natural',
        'MC'    => 'Mínima Cuantía',
        'SA'    => 'Selección Abreviada',
        'LP'    => 'Licitación Pública',
        'CM'    => 'Concurso de Méritos',
    ];

    private const TRIMESTRES = [
        1 => 'I Trimestre (Ene–Mar)',
        2 => 'II Trimestre (Abr–Jun)',
        3 => 'III Trimestre (Jul–Sep)',
        4 => 'IV Trimestre (Oct–Dic)',
    ];

    /* ------------------------------------------------------------------ */
    /* INDEX                                                                 */
    /* ------------------------------------------------------------------ */

    public function index(Request $request)
    {
        $anio = $this->normalizarAnioPermitido((int) $request->get('anio', date('Y')));

        $query = PlanAnualAdquisicion::query()->where('anio', $anio);

        if ($request->filled('modalidad')) {
            $query->where('modalidad_contratacion', $request->modalidad);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('q')) {
            $busq = $request->q;
            $query->where(function ($sub) use ($busq) {
                $sub->where('codigo_necesidad', 'like', "%{$busq}%")
                    ->orWhere('descripcion', 'like', "%{$busq}%")
                    ->orWhere('dependencia_solicitante', 'like', "%{$busq}%");
            });
        }

        $paas = $query->orderBy('trimestre_estimado')->orderBy('codigo_necesidad')->paginate(25)->withQueryString();

        $anios = collect($this->aniosPermitidos());

        $resumen = PlanAnualAdquisicion::where('anio', $anio)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(valor_estimado) as valor_total'),
                DB::raw("SUM(CASE WHEN estado='vigente' THEN 1 ELSE 0 END) as vigentes"),
                DB::raw("SUM(CASE WHEN estado='ejecutado' THEN 1 ELSE 0 END) as ejecutados"),
                DB::raw("SUM(CASE WHEN estado='cancelado' THEN 1 ELSE 0 END) as cancelados"),
                DB::raw("SUM(CASE WHEN estado='modificado' THEN 1 ELSE 0 END) as modificados")
            )->first();

        $descargaOficialDisponible = $this->archivoOficialDisponible($anio);

        $modalidades = self::MODALIDADES;
        $trimestres  = self::TRIMESTRES;

        return view('paa.index', compact('paas', 'anios', 'anio', 'resumen', 'modalidades', 'trimestres', 'descargaOficialDisponible'));
    }

    /* ------------------------------------------------------------------ */
    /* CREATE                                                                */
    /* ------------------------------------------------------------------ */

    public function create()
    {
        $modalidades = self::MODALIDADES;
        $trimestres  = self::TRIMESTRES;
        $anioActual  = (int) date('Y');

        return view('paa.create', compact('modalidades', 'trimestres', 'anioActual'));
    }

    /* ------------------------------------------------------------------ */
    /* STORE                                                                 */
    /* ------------------------------------------------------------------ */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anio'                   => 'required|integer|min:2020|max:2099',
            'codigo_necesidad'       => 'required|string|max:50|unique:plan_anual_adquisiciones,codigo_necesidad',
            'descripcion'            => 'required|string|max:1000',
            'valor_estimado'         => 'required|numeric|min:0',
            'modalidad_contratacion' => 'required|in:CD_PN,MC,SA,LP,CM',
            'trimestre_estimado'     => 'required|integer|between:1,4',
            'dependencia_solicitante'=> 'required|string|max:300',
            'observaciones'          => 'nullable|string|max:2000',
        ]);

        $paa = PlanAnualAdquisicion::create(array_merge($validated, [
            'estado' => 'vigente',
            'activo' => true,
        ]));

        return redirect()
            ->route('paa.show', $paa->id)
            ->with('success', "Necesidad {$paa->codigo_necesidad} incluida en el PAA {$paa->anio}.");
    }

    /* ------------------------------------------------------------------ */
    /* SHOW                                                                  */
    /* ------------------------------------------------------------------ */

    public function show($id)
    {
        $paa = PlanAnualAdquisicion::findOrFail($id);

        $procesos = Proceso::where('paa_id', $paa->id)
            ->with(['workflow'])
            ->orderByDesc('id')
            ->get();

        $modalidades = self::MODALIDADES;
        $trimestres  = self::TRIMESTRES;

        return view('paa.show', compact('paa', 'procesos', 'modalidades', 'trimestres'));
    }

    /* ------------------------------------------------------------------ */
    /* EDIT                                                                  */
    /* ------------------------------------------------------------------ */

    public function edit($id)
    {
        $paa        = PlanAnualAdquisicion::findOrFail($id);
        $modalidades = self::MODALIDADES;
        $trimestres  = self::TRIMESTRES;

        return view('paa.edit', compact('paa', 'modalidades', 'trimestres'));
    }

    /* ------------------------------------------------------------------ */
    /* UPDATE                                                                */
    /* ------------------------------------------------------------------ */

    public function update(Request $request, $id)
    {
        $paa = PlanAnualAdquisicion::findOrFail($id);

        $validated = $request->validate([
            'anio'                   => 'required|integer|min:2020|max:2099',
            'codigo_necesidad'       => "required|string|max:50|unique:plan_anual_adquisiciones,codigo_necesidad,{$paa->id}",
            'descripcion'            => 'required|string|max:1000',
            'valor_estimado'         => 'required|numeric|min:0',
            'modalidad_contratacion' => 'required|in:CD_PN,MC,SA,LP,CM',
            'trimestre_estimado'     => 'required|integer|between:1,4',
            'dependencia_solicitante'=> 'required|string|max:300',
            'estado'                 => 'required|in:vigente,modificado,ejecutado,cancelado',
            'observaciones'          => 'nullable|string|max:2000',
        ]);

        $paa->update($validated);

        return redirect()
            ->route('paa.show', $paa->id)
            ->with('success', 'PAA actualizado correctamente.');
    }

    /* ------------------------------------------------------------------ */
    /* CERTIFICADO DE INCLUSIÓN                                              */
    /* ------------------------------------------------------------------ */

    public function certificadoInclusion($id)
    {
        $paa = PlanAnualAdquisicion::findOrFail($id);
        $modalidades = self::MODALIDADES;

        return view('paa.certificado', compact('paa', 'modalidades'));
    }

    /* ------------------------------------------------------------------ */
    /* EXPORTAR CSV                                                           */
    /* ------------------------------------------------------------------ */

    public function exportarCSV(Request $request)
    {
        $anio = $this->normalizarAnioPermitido((int) $request->get('anio', date('Y')));

        try {
            return $this->descargarPAAOficial($anio);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('paa.index', ['anio' => $anio])
                ->with('error', 'No fue posible descargar el archivo oficial del PAA en este momento.');
        }
    }

    /* ------------------------------------------------------------------ */
    /* EXPORTAR PDF (vista imprimible)                                       */
    /* ------------------------------------------------------------------ */

    public function exportarPDF(Request $request)
    {
        $anio = $this->normalizarAnioPermitido((int) $request->get('anio', date('Y')));

        $paas = PlanAnualAdquisicion::where('anio', $anio)
            ->where('activo', true)
            ->orderBy('trimestre_estimado')
            ->orderBy('codigo_necesidad')
            ->get();

        $modalidades = self::MODALIDADES;
        $trimestres  = self::TRIMESTRES;
        $resumen = ['total' => $paas->count(), 'valor_total' => $paas->sum('valor_estimado')];

        return view('paa.pdf', compact('paas', 'anio', 'modalidades', 'trimestres', 'resumen'));
    }

    private function aniosPermitidos(): array
    {
        $anioActual = (int) date('Y');

        return [$anioActual, $anioActual + 1];
    }

    private function normalizarAnioPermitido(int $anio): int
    {
        $permitidos = $this->aniosPermitidos();

        return in_array($anio, $permitidos, true) ? $anio : $permitidos[0];
    }

    private function descargarPAAOficial(int $anio)
    {
        $urlDescarga = $this->resolverUrlDescargaOficial($anio);

        if (!$urlDescarga) {
            throw new \RuntimeException("No se encontró archivo oficial para PAA {$anio}.");
        }

        $archivo = Http::timeout(90)
            ->withHeaders([
                'User-Agent' => 'SeguimientoDocumentosGobernacion/1.0',
            ])
            ->get($urlDescarga);

        if (!$archivo->successful()) {
            throw new \RuntimeException("La descarga oficial respondió con estado {$archivo->status()}.");
        }

        $nombreArchivo = $this->extraerNombreArchivo($archivo->header('Content-Disposition'));
        if (empty($nombreArchivo)) {
            $nombreArchivo = "PAA_{$anio}_oficial.xlsx";
        }

        $contentType = (string) $archivo->header(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        return response($archivo->body(), 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
        ]);
    }

    private function archivoOficialDisponible(int $anio): bool
    {
        $cacheKey = "paa_publico_disponible_{$anio}";

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($anio): bool {
            try {
                return $this->resolverUrlDescargaOficial($anio) !== null;
            } catch (\Throwable $e) {
                report($e);
                return false;
            }
        });
    }

    private function resolverUrlDescargaOficial(int $anio): ?string
    {
        $indexUrl = (string) config('services.paa_publico.index_url', 'https://caldas.gov.co/secop-y-contrataciones');
        $baseUrl = rtrim((string) config('services.paa_publico.base_url', 'https://caldas.gov.co'), '/');

        $indexHtml = $this->obtenerHtml($indexUrl);
        $paginaAnio = $this->resolverPaginaAnio($indexHtml, $baseUrl, $anio) ?? $indexUrl;
        $htmlAnio = $this->obtenerHtml($paginaAnio);

        return $this->resolverDescargaReciente($htmlAnio, $baseUrl, $anio);
    }

    private function obtenerHtml(string $url): string
    {
        $response = Http::timeout(45)
            ->withHeaders([
                'User-Agent' => 'SeguimientoDocumentosGobernacion/1.0',
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException("No se pudo consultar {$url}. Estado {$response->status()}.");
        }

        return (string) $response->body();
    }

    private function resolverPaginaAnio(string $html, string $baseUrl, int $anio): ?string
    {
        $links = $this->extraerHrefs($html);

        foreach ($links as $href) {
            if (!preg_match('#/secop-y-contrataciones/\d+-[^\s"\']*' . $anio . '[^\s"\']*#i', $href)) {
                continue;
            }

            return $this->absolutizarUrl($href, $baseUrl);
        }

        return null;
    }

    private function resolverDescargaReciente(string $html, string $baseUrl, int $anio): ?string
    {
        $links = $this->extraerHrefs($html);
        $candidatos = [];

        foreach ($links as $idx => $href) {
            $url = $this->absolutizarUrl($href, $baseUrl);
            if (!$url) {
                continue;
            }

            $host = (string) parse_url($url, PHP_URL_HOST);
            if ($host !== '' && !str_ends_with(strtolower($host), 'caldas.gov.co')) {
                continue;
            }

            $path = strtolower((string) parse_url($url, PHP_URL_PATH));
            $esDownload = preg_match('#/secop-y-contrataciones/.+/download$#i', $path) === 1;
            $esArchivo = preg_match('#\.(xlsx|xls|csv)$#i', $path) === 1;
            if (!$esDownload && !$esArchivo) {
                continue;
            }

            $version = 0;
            if (preg_match('/(?:^|[^\d])v(\d{1,3})(?:[^\d]|$)/i', $url, $m)) {
                $version = (int) $m[1];
            }

            $extScore = 0;
            if (preg_match('/\.(xlsx|xls|csv)$/i', $path, $mExt)) {
                $ext = strtolower($mExt[1]);
                $extScore = match ($ext) {
                    'xlsx' => 3,
                    'xls'  => 2,
                    'csv'  => 1,
                    default => 0,
                };
            }

            $candidatos[] = [
                'url' => $url,
                'idx' => $idx,
                'version' => $version,
                'ext' => $extScore,
                'anio' => str_contains(strtolower($url), (string) $anio),
            ];
        }

        if (empty($candidatos)) {
            return null;
        }

        $conAnio = array_values(array_filter($candidatos, fn ($c) => $c['anio']));
        $pool = !empty($conAnio) ? $conAnio : $candidatos;

        usort($pool, function (array $a, array $b): int {
            return [$b['version'], $b['ext'], $b['idx']] <=> [$a['version'], $a['ext'], $a['idx']];
        });

        return $pool[0]['url'] ?? null;
    }

    private function extraerHrefs(string $html): array
    {
        preg_match_all('/href\s*=\s*(["\'])(.*?)\1/i', $html, $matches);

        if (empty($matches[2])) {
            return [];
        }

        return array_values(array_unique(array_map(static function (string $href): string {
            return html_entity_decode(trim($href));
        }, $matches[2])));
    }

    private function absolutizarUrl(string $url, string $baseUrl): ?string
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '#') || str_starts_with(strtolower($url), 'javascript:')) {
            return null;
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        if (str_starts_with($url, '/')) {
            return $baseUrl . $url;
        }

        return $baseUrl . '/' . ltrim($url, '/');
    }

    private function extraerNombreArchivo(?string $contentDisposition): ?string
    {
        if (empty($contentDisposition)) {
            return null;
        }

        if (preg_match('/filename\*=UTF-8\'\'([^;]+)/i', $contentDisposition, $m)) {
            $name = rawurldecode($m[1]);
            return $this->normalizarNombreArchivo($name);
        }

        if (preg_match('/filename="?([^";]+)"?/i', $contentDisposition, $m)) {
            return $this->normalizarNombreArchivo($m[1]);
        }

        return null;
    }

    private function normalizarNombreArchivo(string $filename): ?string
    {
        $filename = basename(trim($filename));
        $filename = str_replace(["\r", "\n"], '', $filename);

        return $filename !== '' ? $filename : null;
    }

    /* ------------------------------------------------------------------ */
    /* VERIFICAR INCLUSIÓN (API JSON)                                        */
    /* ------------------------------------------------------------------ */

    public function verificarInclusion(Request $request)
    {
        $validated = $request->validate([
            'descripcion' => 'required|string',
            'anio'        => 'required|integer',
        ]);

        $paa = PlanAnualAdquisicion::where('descripcion', 'like', '%' . $validated['descripcion'] . '%')
            ->where('anio', $validated['anio'])
            ->where('estado', 'vigente')
            ->first();

        if ($paa) {
            return response()->json(['incluido' => true, 'paa' => $paa, 'mensaje' => 'La necesidad está incluida en el PAA vigente.']);
        }

        return response()->json(['incluido' => false, 'mensaje' => 'La necesidad NO está incluida en el PAA vigente.'], 404);
    }
}

