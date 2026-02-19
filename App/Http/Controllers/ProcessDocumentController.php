<?php

namespace App\Http\Controllers;

use App\Enums\ApprovalStatus;
use App\Enums\DocumentType;
use App\Models\ContractProcess;
use App\Models\ProcessDocument;
use App\Models\ProcessAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcessDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Sube un documento al proceso
     */
    public function upload(Request $request, ContractProcess $contractProcess)
    {
        $this->authorize('uploadDocument', $contractProcess);

        $validated = $request->validate([
            'document_type' => 'required|string',
            'step_number' => 'required|integer|min:0|max:9',
            'file' => 'required|file|max:10240', // 10MB max
            'document_name' => 'nullable|string|max:255',
            'issued_at' => 'nullable|date',
            'requires_approval' => 'nullable|boolean',
            'observations' => 'nullable|string|max:500',
        ]);

        // Validar que el tipo de documento existe
        try {
            $docType = DocumentType::from($validated['document_type']);
        } catch (\ValueError $e) {
            return back()->withErrors(['document_type' => 'Tipo de documento no válido']);
        }

        // Guardar archivo
        $file = $request->file('file');
        $path = $file->store('contract-processes/' . $contractProcess->id . '/step-' . $validated['step_number'], 'local');

        // Crear registro del documento
        $document = ProcessDocument::create([
            'process_id' => $contractProcess->id,
            'step_number' => $validated['step_number'],
            'document_type' => $docType,
            'document_name' => $validated['document_name'] ?? $docType->getLabel(),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'is_required' => in_array($docType, DocumentType::getRequiredByStep($validated['step_number'])),
            'requires_approval' => $validated['requires_approval'] ?? false,
            'approval_status' => ApprovalStatus::PENDING,
            'issued_at' => $validated['issued_at'] ?? now(),
            'observations' => $validated['observations'] ?? null,
            'uploaded_by' => auth()->id(),
        ]);

        // Registrar en auditoría
        ProcessAuditLog::logDocumentUpload($document, auth()->user());

        return back()->with('success', 'Documento cargado exitosamente');
    }

    /**
     * Descarga un documento
     */
    public function download(ContractProcess $contractProcess, ProcessDocument $document)
    {
        $this->authorize('view', $contractProcess);

        if (!Storage::exists($document->file_path)) {
            abort(404, 'Archivo no encontrado');
        }

        return Storage::download($document->file_path, $document->file_name);
    }

    /**
     * Elimina un documento
     */
    public function destroy(ContractProcess $contractProcess, ProcessDocument $document)
    {
        $this->authorize('deleteDocument', $contractProcess);

        ProcessAuditLog::logCustomAction(
            $contractProcess,
            'document_deleted',
            "Documento '{$document->document_name}' eliminado",
            [
                'document_id' => $document->id,
                'document_type' => $document->document_type->value,
                'step' => $document->step_number,
            ],
            auth()->user()
        );

        $document->delete();

        return back()->with('success', 'Documento eliminado exitosamente');
    }

    /**
     * Aprueba un documento
     */
    public function approve(Request $request, ContractProcess $contractProcess, ProcessDocument $document)
    {
        $this->authorize('approveDocument', $contractProcess);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $document->approve(auth()->user(), $validated['notes'] ?? null);

        ProcessAuditLog::logCustomAction(
            $contractProcess,
            'document_approved',
            "Documento '{$document->document_name}' aprobado",
            [
                'document_id' => $document->id,
                'notes' => $validated['notes'] ?? null,
            ],
            auth()->user()
        );

        return back()->with('success', 'Documento aprobado exitosamente');
    }

    /**
     * Rechaza un documento
     */
    public function reject(Request $request, ContractProcess $contractProcess, ProcessDocument $document)
    {
        $this->authorize('approveDocument', $contractProcess);

        $validated = $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $document->reject(auth()->user(), $validated['notes']);

        ProcessAuditLog::logCustomAction(
            $contractProcess,
            'document_rejected',
            "Documento '{$document->document_name}' rechazado",
            [
                'document_id' => $document->id,
                'notes' => $validated['notes'],
            ],
            auth()->user()
        );

        // Notificar al usuario que subió el documento
        \App\Models\ProcessNotification::create([
            'process_id' => $contractProcess->id,
            'user_id' => $document->uploaded_by,
            'type' => 'document_rejected',
            'title' => 'Documento Rechazado',
            'message' => "El documento '{$document->document_name}' fue rechazado: {$validated['notes']}",
        ]);

        return back()->with('success', 'Documento rechazado');
    }

    /**
     * Solicita correcciones en un documento
     */
    public function requestFixes(Request $request, ContractProcess $contractProcess, ProcessDocument $document)
    {
        $this->authorize('approveDocument', $contractProcess);

        $validated = $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $document->requestFixes(auth()->user(), $validated['notes']);

        ProcessAuditLog::logCustomAction(
            $contractProcess,
            'document_fixes_requested',
            "Se solicitaron correcciones para '{$document->document_name}'",
            [
                'document_id' => $document->id,
                'notes' => $validated['notes'],
            ],
            auth()->user()
        );

        // Notificar
        \App\Models\ProcessNotification::create([
            'process_id' => $contractProcess->id,
            'user_id' => $document->uploaded_by,
            'type' => 'document_requires_fixes',
            'title' => 'Documento Requiere Ajustes',
            'message' => "El documento '{$document->document_name}' requiere ajustes: {$validated['notes']}",
        ]);

        return back()->with('success', 'Correcciones solicitadas');
    }

    /**
     * Reemplaza un documento (nueva versión)
     */
    public function replace(Request $request, ContractProcess $contractProcess, ProcessDocument $document)
    {
        $this->authorize('uploadDocument', $contractProcess);

        $validated = $request->validate([
            'file' => 'required|file|max:10240',
            'notes' => 'nullable|string|max:500',
        ]);

        // Eliminar archivo anterior
        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        // Guardar nuevo archivo
        $file = $request->file('file');
        $path = $file->store('contract-processes/' . $contractProcess->id . '/step-' . $document->step_number, 'local');

        // Actualizar documento
        $document->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'approval_status' => ApprovalStatus::PENDING,
            'approved_by' => null,
            'approved_at' => null,
            'approval_notes' => $validated['notes'] ?? null,
            'uploaded_by' => auth()->id(),
        ]);

        ProcessAuditLog::logCustomAction(
            $contractProcess,
            'document_replaced',
            "Documento '{$document->document_name}' reemplazado con nueva versión",
            [
                'document_id' => $document->id,
                'notes' => $validated['notes'] ?? null,
            ],
            auth()->user()
        );

        return back()->with('success', 'Documento reemplazado exitosamente');
    }

    /**
     * Agrega firma a un documento
     */
    public function addSignature(Request $request, ContractProcess $contractProcess, ProcessDocument $document)
    {
        $this->authorize('signDocument', $contractProcess);

        if ($document->hasSignedBy(auth()->user())) {
            return back()->with('error', 'Ya ha firmado este documento');
        }

        $validated = $request->validate([
            'signature_data' => 'nullable|array',
        ]);

        $document->addSignature(auth()->user(), $validated['signature_data'] ?? []);

        ProcessAuditLog::logCustomAction(
            $contractProcess,
            'document_signed',
            "Documento '{$document->document_name}' firmado por " . auth()->user()->name,
            [
                'document_id' => $document->id,
            ],
            auth()->user()
        );

        return back()->with('success', 'Documento firmado exitosamente');
    }

    /**
     * Lista documentos próximos a vencer
     */
    public function expiring(Request $request)
    {
        $days = $request->input('days', 7);

        $documents = ProcessDocument::with(['process', 'uploadedBy'])
            ->expiringSoon($days)
            ->get();

        return view('contract-processes.documents.expiring', compact('documents', 'days'));
    }
}
