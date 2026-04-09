<?php

namespace App\Http\Controllers;

use App\Models\PlanAnualAdquisicion;
use App\Models\Proceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $anio = (int) $request->get('anio', date('Y'));

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

        $anios = PlanAnualAdquisicion::select('anio')->distinct()->orderByDesc('anio')->pluck('anio');
        if (!$anios->contains($anio)) {
            $anios->prepend($anio);
        }

        $resumen = PlanAnualAdquisicion::where('anio', $anio)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(valor_estimado) as valor_total'),
                DB::raw("SUM(CASE WHEN estado='vigente' THEN 1 ELSE 0 END) as vigentes"),
                DB::raw("SUM(CASE WHEN estado='ejecutado' THEN 1 ELSE 0 END) as ejecutados"),
                DB::raw("SUM(CASE WHEN estado='cancelado' THEN 1 ELSE 0 END) as cancelados"),
                DB::raw("SUM(CASE WHEN estado='modificado' THEN 1 ELSE 0 END) as modificados")
            )->first();

        $modalidades = self::MODALIDADES;
        $trimestres  = self::TRIMESTRES;

        return view('paa.index', compact('paas', 'anios', 'anio', 'resumen', 'modalidades', 'trimestres'));
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
        $anio = (int) $request->get('anio', date('Y'));

        $paas = PlanAnualAdquisicion::where('anio', $anio)
            ->where('activo', true)
            ->orderBy('trimestre_estimado')
            ->orderBy('codigo_necesidad')
            ->get();

        $filename = "PAA_{$anio}_" . now()->format('Ymd_His') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = [
            'Código Necesidad', 'Descripción', 'Dependencia Solicitante',
            'Modalidad', 'Valor Estimado (COP)', 'Trimestre Estimado',
            'Estado', 'Año',
        ];

        $callback = function () use ($paas, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            fputcsv($file, $columns, ';');
            foreach ($paas as $p) {
                fputcsv($file, [
                    $p->codigo_necesidad,
                    $p->descripcion,
                    $p->dependencia_solicitante,
                    self::MODALIDADES[$p->modalidad_contratacion] ?? $p->modalidad_contratacion,
                    number_format($p->valor_estimado, 2, '.', ''),
                    self::TRIMESTRES[$p->trimestre_estimado] ?? $p->trimestre_estimado,
                    ucfirst($p->estado),
                    $p->anio,
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* ------------------------------------------------------------------ */
    /* EXPORTAR PDF (vista imprimible)                                       */
    /* ------------------------------------------------------------------ */

    public function exportarPDF(Request $request)
    {
        $anio = (int) $request->get('anio', date('Y'));

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
