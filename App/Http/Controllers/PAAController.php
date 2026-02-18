<?php

namespace App\Http\Controllers;

use App\Models\PlanAnualAdquisicion;
use App\Models\Workflow;
use App\Models\ProcesoAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PAAController extends Controller
{
    /**
     * Muestra la lista de planes anuales de adquisiciones
     */
    public function index(Request $request)
    {
        $vigencia = $request->get('vigencia', date('Y'));
        
        $paas = PlanAnualAdquisicion::with(['workflow', 'creador'])
            ->where('vigencia', $vigencia)
            ->orderBy('codigo_bpin', 'asc')
            ->paginate(50);
        
        $vigencias = PlanAnualAdquisicion::select('vigencia')
            ->distinct()
            ->orderBy('vigencia', 'desc')
            ->pluck('vigencia');
        
        return view('paa.index', compact('paas', 'vigencias', 'vigencia'));
    }

    /**
     * Muestra el formulario para crear un nuevo PAA
     */
    public function create()
    {
        $workflows = Workflow::where('activo', true)->get();
        return view('paa.create', compact('workflows'));
    }

    /**
     * Almacena un nuevo PAA en la base de datos
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vigencia' => 'required|integer|min:2020|max:2099',
            'codigo_bpin' => 'nullable|string|max:50',
            'nombre_proyecto' => 'required|string|max:500',
            'objeto_contrato' => 'required|string',
            'workflow_id' => 'required|exists:workflows,id',
            'valor_estimado' => 'required|numeric|min:0',
            'modalidad_seleccion' => 'required|in:CD_PN,MC,SA,LP,CM',
            'fuente_financiacion' => 'required|string|max:200',
            'dependencia' => 'required|string|max:200',
            'fecha_estimada_inicio' => 'required|date',
            'duracion_estimada' => 'required|integer|min:1',
            'unidad_duracion' => 'required|in:dias,meses,años',
        ]);

        try {
            DB::beginTransaction();

            $paa = PlanAnualAdquisicion::create([
                'vigencia' => $validated['vigencia'],
                'codigo_bpin' => $validated['codigo_bpin'],
                'nombre_proyecto' => $validated['nombre_proyecto'],
                'objeto_contrato' => $validated['objeto_contrato'],
                'workflow_id' => $validated['workflow_id'],
                'valor_estimado' => $validated['valor_estimado'],
                'modalidad_seleccion' => $validated['modalidad_seleccion'],
                'fuente_financiacion' => $validated['fuente_financiacion'],
                'dependencia' => $validated['dependencia'],
                'fecha_estimada_inicio' => $validated['fecha_estimada_inicio'],
                'duracion_estimada' => $validated['duracion_estimada'],
                'unidad_duracion' => $validated['unidad_duracion'],
                'estado' => 'vigente',
                'created_by' => auth()->id(),
            ]);

            // Registrar auditoría
            ProcesoAuditoria::registrar(
                null,
                'paa_creado',
                'PAA',
                null,
                null,
                "PAA creado: {$paa->nombre_proyecto} - Vigencia {$paa->vigencia}"
            );

            DB::commit();

            return redirect()
                ->route('paa.index')
                ->with('success', 'PAA creado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al crear el PAA: ' . $e->getMessage());
        }
    }

    /**
     * Muestra un PAA específico
     */
    public function show($id)
    {
        $paa = PlanAnualAdquisicion::with(['workflow', 'creador', 'procesos.workflow', 'procesos.etapaActual'])
            ->findOrFail($id);
        
        return view('paa.show', compact('paa'));
    }

    /**
     * Muestra el formulario de edición
     */
    public function edit($id)
    {
        $paa = PlanAnualAdquisicion::findOrFail($id);
        $workflows = Workflow::where('activo', true)->get();
        
        return view('paa.edit', compact('paa', 'workflows'));
    }

    /**
     * Actualiza un PAA existente
     */
    public function update(Request $request, $id)
    {
        $paa = PlanAnualAdquisicion::findOrFail($id);

        $validated = $request->validate([
            'vigencia' => 'required|integer|min:2020|max:2099',
            'codigo_bpin' => 'nullable|string|max:50',
            'nombre_proyecto' => 'required|string|max:500',
            'objeto_contrato' => 'required|string',
            'workflow_id' => 'required|exists:workflows,id',
            'valor_estimado' => 'required|numeric|min:0',
            'modalidad_seleccion' => 'required|in:CD_PN,MC,SA,LP,CM',
            'fuente_financiacion' => 'required|string|max:200',
            'dependencia' => 'required|string|max:200',
            'fecha_estimada_inicio' => 'required|date',
            'duracion_estimada' => 'required|integer|min:1',
            'unidad_duracion' => 'required|in:dias,meses,años',
            'estado' => 'required|in:vigente,modificado,suspendido,cancelado',
        ]);

        try {
            DB::beginTransaction();

            $cambios = [];
            foreach ($validated as $campo => $valor) {
                if ($paa->$campo != $valor) {
                    $cambios[] = "$campo: {$paa->$campo} → $valor";
                }
            }

            $paa->update($validated);

            // Registrar auditoría
            if (!empty($cambios)) {
                ProcesoAuditoria::registrar(
                    null,
                    'paa_modificado',
                    'PAA',
                    null,
                    null,
                    "PAA modificado: " . implode(', ', $cambios)
                );
            }

            DB::commit();

            return redirect()
                ->route('paa.show', $paa->id)
                ->with('success', 'PAA actualizado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el PAA: ' . $e->getMessage());
        }
    }

    /**
     * Genera el certificado de inclusión en el PAA
     */
    public function certificadoInclusion($id)
    {
        $paa = PlanAnualAdquisicion::with(['workflow', 'creador'])
            ->findOrFail($id);

        // Registrar auditoría
        ProcesoAuditoria::registrar(
            null,
            'certificado_paa_generado',
            'PAA',
            null,
            null,
            "Certificado de inclusión generado para PAA: {$paa->nombre_proyecto}"
        );

        return view('paa.certificado', compact('paa'));
    }

    /**
     * Verifica si un proceso está incluido en el PAA vigente
     */
    public function verificarInclusion(Request $request)
    {
        $validated = $request->validate([
            'nombre_proyecto' => 'required|string',
            'vigencia' => 'required|integer',
        ]);

        $paa = PlanAnualAdquisicion::where('nombre_proyecto', 'like', '%' . $validated['nombre_proyecto'] . '%')
            ->where('vigencia', $validated['vigencia'])
            ->where('estado', 'vigente')
            ->first();

        if ($paa) {
            return response()->json([
                'incluido' => true,
                'paa' => $paa,
                'mensaje' => 'El proceso está incluido en el PAA vigente',
            ]);
        }

        return response()->json([
            'incluido' => false,
            'mensaje' => 'El proceso NO está incluido en el PAA vigente',
        ], 404);
    }

    /**
     * Exporta el PAA a PDF
     */
    public function exportarPDF($vigencia)
    {
        $paas = PlanAnualAdquisicion::with(['workflow', 'creador'])
            ->where('vigencia', $vigencia)
            ->where('estado', 'vigente')
            ->orderBy('codigo_bpin', 'asc')
            ->get();

        // Aquí puedes usar una librería como DomPDF o TCPDF
        // Por ahora retornamos una vista que puede imprimirse
        return view('paa.pdf', compact('paas', 'vigencia'));
    }
}
