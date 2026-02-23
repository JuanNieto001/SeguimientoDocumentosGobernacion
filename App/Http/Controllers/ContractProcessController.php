<?php

namespace App\Http\Controllers;

use App\Enums\ProcessStatus;
use App\Enums\ProcessType;
use App\Models\ContractProcess;
use App\Models\Secretaria;
use App\Models\Unidad;
use App\Models\User;
use App\Services\WorkflowEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractProcessController extends Controller
{
    public function __construct(protected WorkflowEngine $workflowEngine)
    {
        $this->middleware('auth');
    }

    /**
     * Lista todos los procesos
     */
    public function index(Request $request)
    {
        $query = ContractProcess::with([
            'contractor',
            'supervisor',
            'secretaria',
            'unidad',
            'createdBy'
        ]);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('step')) {
            $query->where('current_step', $request->step);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('process_number', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%")
                  ->orWhere('contractor_name', 'like', "%{$search}%")
                  ->orWhere('object', 'like', "%{$search}%");
            });
        }

        // Filtrar por usuario si no es admin
        if (!auth()->user()->hasRole('Super Admin')) {
            $query->forUser(auth()->user());
        }

        $processes = $query->latest()->paginate(20);

        return view('contract-processes.index', compact('processes'));
    }

    /**
     * Muestra formulario para crear nuevo proceso
     */
    public function create()
    {
        $this->authorize('create', ContractProcess::class);

        $secretarias = Secretaria::orderBy('nombre')->get();
        $unidades = Unidad::orderBy('nombre')->get();
        $processTypes = ProcessType::cases();

        return view('contract-processes.create', compact('secretarias', 'unidades', 'processTypes'));
    }

    /**
     * Guarda nuevo proceso
     */
    public function store(Request $request)
    {
        $this->authorize('create', ContractProcess::class);

        $validated = $request->validate([
            'process_type' => 'required|in:cd_pn,cd_pj,lp,sa,cm,mc',
            'object' => 'required|string|max:1000',
            'estimated_value' => 'required|numeric|min:0',
            'term_days' => 'required|integer|min:1',
            'expected_start_date' => 'nullable|date',
            'contractor_name' => 'nullable|string|max:255',
            'contractor_document_type' => 'nullable|in:CC,NIT,CE',
            'contractor_document_number' => 'nullable|string|max:50',
            'contractor_email' => 'nullable|email',
            'contractor_phone' => 'nullable|string|max:20',
            'supervisor_id' => 'nullable|exists:users,id',
            'secretaria_id' => 'required|exists:secretarias,id',
            'unidad_id' => 'required|exists:unidades,id',
        ]);

        $validated['status'] = ProcessStatus::NEED_DEFINED;
        $validated['current_step'] = 0;
        $validated['created_by'] = auth()->id();

        $process = DB::transaction(function () use ($validated) {
            $process = ContractProcess::create($validated);
            
            // Inicializar workflow
            $this->workflowEngine->initializeWorkflow($process);
            
            return $process;
        });

        return redirect()
            ->route('contract-processes.show', $process)
            ->with('success', 'Proceso creado exitosamente. Número: ' . $process->process_number);
    }

    /**
     * Muestra el proceso y redirige a la etapa actual
     */
    public function show(ContractProcess $contractProcess)
    {
        $this->authorize('view', $contractProcess);

        // Redirigir automáticamente a la etapa actual
        return redirect()->route('contract-processes.step', [
            'process' => $contractProcess,
            'step' => $contractProcess->current_step
        ]);
    }

    /**
     * Muestra una etapa específica del proceso
     */
    public function showStep(ContractProcess $contractProcess, int $step)
    {
        $this->authorize('view', $contractProcess);

        // Validar que la etapa existe
        if ($step < 0 || $step > 9) {
            abort(404, 'Etapa no válida');
        }

        // Cargar relaciones necesarias
        $contractProcess->load([
            'steps',
            'documents' => fn($q) => $q->where('step_number', $step),
            'approvals' => fn($q) => $q->where('step_number', $step),
            'contractor',
            'supervisor',
            'unitHead',
            'unitLawyer',
            'linkLawyer',
        ]);

        // Obtener información de la etapa
        $stepInfo = $contractProcess->steps()->where('step_number', $step)->first();
        $canAdvance = $step === $contractProcess->current_step && count($this->workflowEngine->canAdvance($contractProcess)) === 0;
        $validationErrors = $this->workflowEngine->canAdvance($contractProcess);

        // Obtener documentos requeridos
        $requiredDocuments = \App\Enums\DocumentType::getRequiredByStep($step);

        return view('contract-processes.steps.step-' . $step, compact(
            'contractProcess',
            'step',
            'stepInfo',
            'canAdvance',
            'validationErrors',
            'requiredDocuments'
        ));
    }

    /**
     * Avanza el proceso a la siguiente etapa
     */
    public function advance(ContractProcess $contractProcess)
    {
        $this->authorize('advance', $contractProcess);

        try {
            $this->workflowEngine->advance($contractProcess, auth()->user());

            return redirect()
                ->route('contract-processes.show', $contractProcess)
                ->with('success', 'Proceso avanzado exitosamente a: ' . $contractProcess->status->getLabel());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->with('error', 'No se puede avanzar. Revise los errores.');
        }
    }

    /**
     * Devuelve el proceso a una etapa anterior
     */
    public function returnToStep(Request $request, ContractProcess $contractProcess)
    {
        $this->authorize('return', $contractProcess);

        $validated = $request->validate([
            'target_step' => 'required|integer|min:0|max:9',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->workflowEngine->returnToStep(
                $contractProcess,
                $validated['target_step'],
                $validated['reason'],
                auth()->user()
            );

            return redirect()
                ->route('contract-processes.show', $contractProcess)
                ->with('success', 'Proceso devuelto a etapa ' . $validated['target_step']);
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al devolver proceso: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza información básica del proceso
     */
    public function update(Request $request, ContractProcess $contractProcess)
    {
        $this->authorize('update', $contractProcess);

        $validated = $request->validate([
            'object' => 'nullable|string|max:1000',
            'estimated_value' => 'nullable|numeric|min:0',
            'term_days' => 'nullable|integer|min:1',
            'expected_start_date' => 'nullable|date',
            'contractor_name' => 'nullable|string|max:255',
            'contractor_document_type' => 'nullable|in:CC,NIT,CE',
            'contractor_document_number' => 'nullable|string|max:50',
            'contractor_email' => 'nullable|email',
            'contractor_phone' => 'nullable|string|max:20',
            'supervisor_id' => 'nullable|exists:users,id',
            'ordering_officer_id' => 'nullable|exists:users,id',
            'unit_head_id' => 'nullable|exists:users,id',
            'unit_lawyer_id' => 'nullable|exists:users,id',
            'link_lawyer_id' => 'nullable|exists:users,id',
            'secop_id' => 'nullable|string|max:100',
            'rpc_number' => 'nullable|string|max:100',
            'contract_number' => 'nullable|string|max:100',
            'observations' => 'nullable|string|max:2000',
        ]);

        $validated['updated_by'] = auth()->id();

        $contractProcess->update($validated);

        return back()->with('success', 'Proceso actualizado exitosamente');
    }

    /**
     * Muestra el historial de auditoría del proceso
     */
    public function auditLog(ContractProcess $contractProcess)
    {
        $this->authorize('view', $contractProcess);

        $contractProcess->load(['auditLogs.user']);

        return view('contract-processes.audit-log', compact('contractProcess'));
    }

    /**
     * Exporta el proceso a PDF
     */
    public function export(ContractProcess $contractProcess)
    {
        $this->authorize('view', $contractProcess);

        // TODO: Implementar exportación a PDF
        return back()->with('info', 'Exportación a PDF en desarrollo');
    }

    /**
     * Cancela el proceso
     */
    public function cancel(Request $request, ContractProcess $contractProcess)
    {
        $this->authorize('cancel', $contractProcess);

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $contractProcess->update([
            'status' => ProcessStatus::CANCELLED,
            'cancelled_at' => now(),
            'observations' => $validated['reason'],
            'updated_by' => auth()->id(),
        ]);

        \App\Models\ProcessAuditLog::logCustomAction(
            $contractProcess,
            'process_cancelled',
            'Proceso cancelado: ' . $validated['reason'],
            ['reason' => $validated['reason']],
            auth()->user()
        );

        return redirect()
            ->route('contract-processes.index')
            ->with('success', 'Proceso cancelado exitosamente');
    }
}
