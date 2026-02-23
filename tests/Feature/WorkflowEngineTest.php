<?php

namespace Tests\Feature;

use App\Enums\ApprovalStatus;
use App\Enums\DocumentType;
use App\Enums\ProcessStatus;
use App\Models\ContractProcess;
use App\Models\ProcessDocument;
use App\Models\User;
use App\Services\WorkflowEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowEngineTest extends TestCase
{
    use RefreshDatabase;

    protected WorkflowEngine $workflowEngine;
    protected User $admin;
    protected ContractProcess $process;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->workflowEngine = app(WorkflowEngine::class);
        
        // Crear usuario admin
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
        
        // Crear proceso de prueba
        $this->process = ContractProcess::factory()->create([
            'process_type' => 'cd_pn',
            'status' => ProcessStatus::NEED_DEFINED,
            'current_step' => 0,
            'object' => 'Contratación de servicios profesionales',
            'estimated_value' => 10000000,
            'term_days' => 30,
        ]);

        $this->workflowEngine->initializeWorkflow($this->process);
    }

    /** @test */
    public function test_workflow_initialization_creates_all_steps()
    {
        $this->assertEquals(10, $this->process->steps()->count());
        
        $firstStep = $this->process->steps()->where('step_number', 0)->first();
        $this->assertEquals('in_progress', $firstStep->status);
        
        $secondStep = $this->process->steps()->where('step_number', 1)->first();
        $this->assertEquals('pending', $secondStep->status);
    }

    /** @test */
    public function test_cannot_advance_without_required_documents()
    {
        $errors = $this->workflowEngine->canAdvance($this->process);
        
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Faltan documentos requeridos', $errors[0]);
    }

    /** @test */
    public function test_can_advance_when_all_requirements_met()
    {
        // Agregar documentos requeridos para etapa 0
        $this->createDocument(DocumentType::ESTUDIOS_PREVIOS);
        $this->createDocument(DocumentType::EVIDENCIA_ENVIO_UNIDAD);

        $errors = $this->workflowEngine->canAdvance($this->process);
        
        $this->assertEmpty($errors);
    }

    /** @test */
    public function test_advance_transitions_to_next_state()
    {
        // Preparar documentos requeridos
        $this->createDocument(DocumentType::ESTUDIOS_PREVIOS);
        $this->createDocument(DocumentType::EVIDENCIA_ENVIO_UNIDAD);

        $this->actingAs($this->admin);
        $this->workflowEngine->advance($this->process, $this->admin);

        $this->process->refresh();
        
        $this->assertEquals(ProcessStatus::INITIAL_DOCS_PENDING, $this->process->status);
        $this->assertEquals(1, $this->process->current_step);
    }

    /** @test */
    public function test_cannot_have_cdp_without_compatibilidad_del_gasto()
    {
        // Avanzar a etapa 1
        $this->createDocument(DocumentType::ESTUDIOS_PREVIOS);
        $this->createDocument(DocumentType::EVIDENCIA_ENVIO_UNIDAD);
        $this->actingAs($this->admin);
        $this->workflowEngine->advance($this->process, $this->admin);
        
        $this->process->refresh();

        // Intentar agregar CDP sin compatibilidad
        $this->createDocument(DocumentType::CDP);

        $errors = $this->workflowEngine->canAdvance($this->process);
        
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('CDP sin Compatibilidad del Gasto', $errors[0]);
    }

    /** @test */
    public function test_can_have_cdp_with_approved_compatibilidad()
    {
        // Avanzar a etapa 1
        $this->createDocument(DocumentType::ESTUDIOS_PREVIOS);
        $this->createDocument(DocumentType::EVIDENCIA_ENVIO_UNIDAD);
        $this->actingAs($this->admin);
        $this->workflowEngine->advance($this->process, $this->admin);
        
        $this->process->refresh();

        // Agregar y aprobar compatibilidad
        $compatibilidad = $this->createDocument(DocumentType::COMPATIBILIDAD_GASTO);
        $compatibilidad->update(['approval_status' => ApprovalStatus::APPROVED]);

        // Ahora sí se puede agregar CDP
        $this->createDocument(DocumentType::CDP);

        // Agregar resto de documentos requeridos
        $this->createDocument(DocumentType::PAA);
        $this->createDocument(DocumentType::NO_PLANTA);
        $this->createDocument(DocumentType::PAZ_SALVO_RENTAS);
        $this->createDocument(DocumentType::PAZ_SALVO_CONTABILIDAD);

        $errors = $this->workflowEngine->canAdvance($this->process);
        
        // No debe haber error de CDP
        $this->assertEmpty(array_filter($errors, fn($error) => str_contains($error, 'CDP')));
    }

    /** @test */
    public function test_cannot_advance_with_expired_documents()
    {
        $this->createDocument(DocumentType::ESTUDIOS_PREVIOS);
        
        // Crear documento expirado
        $expiredDoc = $this->createDocument(DocumentType::EVIDENCIA_ENVIO_UNIDAD);
        $expiredDoc->update([
            'expires_at' => now()->subDays(1),
            'is_expired' => true,
        ]);

        $errors = $this->workflowEngine->canAdvance($this->process);
        
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('expirados', $errors[0]);
    }

    /** @test */
    public function test_return_to_step_changes_status_correctly()
    {
        // Avanzar a etapa 1
        $this->createDocument(DocumentType::ESTUDIOS_PREVIOS);
        $this->createDocument(DocumentType::EVIDENCIA_ENVIO_UNIDAD);
        $this->actingAs($this->admin);
        $this->workflowEngine->advance($this->process, $this->admin);
        
        $this->process->refresh();
        $this->assertEquals(1, $this->process->current_step);

        // Devolver a etapa 0
        $this->workflowEngine->returnToStep(
            $this->process, 
            0, 
            'Falta información en estudios previos',
            $this->admin
        );

        $this->process->refresh();
        
        $this->assertEquals(0, $this->process->current_step);
        $this->assertEquals(ProcessStatus::NEED_DEFINED, $this->process->status);
    }

    /** @test */
    public function test_audit_log_created_on_state_change()
    {
        $this->createDocument(DocumentType::ESTUDIOS_PREVIOS);
        $this->createDocument(DocumentType::EVIDENCIA_ENVIO_UNIDAD);

        $this->actingAs($this->admin);
        $this->workflowEngine->advance($this->process, $this->admin);

        $this->assertDatabaseHas('process_audit_logs', [
            'process_id' => $this->process->id,
            'action' => 'state_changed',
            'old_value' => ProcessStatus::NEED_DEFINED->value,
            'new_value' => ProcessStatus::INITIAL_DOCS_PENDING->value,
        ]);
    }

    /** @test */
    public function test_document_expiration_calculated_automatically()
    {
        $document = $this->createDocument(
            DocumentType::ANTECEDENTES_DISCIPLINARIOS,
            ['issued_at' => now()]
        );

        $this->assertNotNull($document->expires_at);
        $this->assertEquals(30, $document->getDaysUntilExpiration());
    }

    /** @test */
    public function test_complete_workflow_flow()
    {
        Storage::fake('local');

        // Etapa 0
        $this->createDocument(DocumentType::ESTUDIOS_PREVIOS);
        $this->createDocument(DocumentType::EVIDENCIA_ENVIO_UNIDAD);
        $this->actingAs($this->admin);
        $this->workflowEngine->advance($this->process, $this->admin);
        $this->process->refresh();
        $this->assertEquals(1, $this->process->current_step);

        // Etapa 1 - Agregar todos los documentos iniciales
        $compatibilidad = $this->createDocument(DocumentType::COMPATIBILIDAD_GASTO);
        $compatibilidad->approve($this->admin);
        
        $this->createDocument(DocumentType::CDP);
        $this->createDocument(DocumentType::PAA);
        $this->createDocument(DocumentType::NO_PLANTA);
        $this->createDocument(DocumentType::PAZ_SALVO_RENTAS);
        $this->createDocument(DocumentType::PAZ_SALVO_CONTABILIDAD);
        
        $this->workflowEngine->advance($this->process, $this->admin);
        $this->process->refresh();
        $this->assertEquals(2, $this->process->current_step);
        $this->assertEquals(ProcessStatus::CONTRACTOR_VALIDATION, $this->process->status);
    }

    // Helper methods
    protected function createDocument(DocumentType $type, array $attributes = []): ProcessDocument
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        return ProcessDocument::create(array_merge([
            'process_id' => $this->process->id,
            'step_number' => $this->process->current_step,
            'document_type' => $type,
            'document_name' => $type->getLabel(),
            'file_path' => $file->store('test'),
            'file_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'is_required' => true,
            'approval_status' => ApprovalStatus::APPROVED,
            'uploaded_by' => $this->admin->id,
        ], $attributes));
    }
}
