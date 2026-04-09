<?php

namespace App\Models;

use App\Enums\ProcessStatus;
use App\Enums\ProcessType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractProcess extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'process_type',
        'status',
        'current_step',
        'process_number',
        'contract_number',
        'secop_id',
        'rpc_number',
        'object',
        'estimated_value',
        'term_days',
        'expected_start_date',
        'actual_start_date',
        'contractor_id',
        'contractor_name',
        'contractor_document_type',
        'contractor_document_number',
        'contractor_email',
        'contractor_phone',
        'supervisor_id',
        'ordering_officer_id',
        'unit_head_id',
        'unit_lawyer_id',
        'link_lawyer_id',
        'secretaria_id',
        'unidad_id',
        'observations',
        'metadata',
        'submitted_to_legal_at',
        'signed_at',
        'published_secop_at',
        'rpc_issued_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => ProcessStatus::class,
        'process_type' => ProcessType::class,
        'estimated_value' => 'decimal:2',
        'term_days' => 'integer',
        'current_step' => 'integer',
        'expected_start_date' => 'date',
        'actual_start_date' => 'date',
        'metadata' => 'array',
        'submitted_to_legal_at' => 'datetime',
        'signed_at' => 'datetime',
        'published_secop_at' => 'datetime',
        'rpc_issued_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ContractProcess $process) {
            if (!$process->process_number) {
                $process->process_number = $process->generateProcessNumber();
            }
        });
    }

    // Relaciones
    public function contractor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contractor_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function orderingOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordering_officer_id');
    }

    public function unitHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unit_head_id');
    }

    public function unitLawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unit_lawyer_id');
    }

    public function linkLawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'link_lawyer_id');
    }

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProcessStep::class, 'process_id')->orderBy('step_number');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProcessDocument::class, 'process_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ProcessApproval::class, 'process_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ProcessAuditLog::class, 'process_id')->latest();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ProcessNotification::class, 'process_id');
    }

    // Métodos auxiliares
    public function getCurrentStep(): ?ProcessStep
    {
        return $this->steps()->where('step_number', $this->current_step)->first();
    }

    public function getDocumentsForStep(int $stepNumber): \Illuminate\Database\Eloquent\Collection
    {
        return $this->documents()->where('step_number', $stepNumber)->get();
    }

    public function getRequiredDocumentsForStep(int $stepNumber): \Illuminate\Database\Eloquent\Collection
    {
        return $this->documents()
            ->where('step_number', $stepNumber)
            ->where('is_required', true)
            ->get();
    }

    public function getMissingRequiredDocuments(): array
    {
        $required = \App\Enums\DocumentType::getRequiredByStep($this->current_step);
        $existing = $this->documents()
            ->where('step_number', $this->current_step)
            ->pluck('document_type')
            ->map(fn($type) => $type instanceof \App\Enums\DocumentType ? $type->value : $type)
            ->toArray();

        return array_filter($required, function($docType) use ($existing) {
            $value = $docType instanceof \App\Enums\DocumentType ? $docType->value : $docType;
            return !in_array($value, $existing);
        });
    }

    public function hasExpiredDocuments(): bool
    {
        return $this->documents()
            ->where('is_expired', true)
            ->exists();
    }

    public function getPendingApprovals(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->approvals()
            ->where('status', 'pending')
            ->with('requestedFrom')
            ->get();
    }

    public function canAdvanceToNextStep(): bool
    {
        // Verificar que no falten documentos requeridos
        if (count($this->getMissingRequiredDocuments()) > 0) {
            return false;
        }

        // Verificar que no haya documentos expirados
        if ($this->hasExpiredDocuments()) {
            return false;
        }

        // Verificar que no haya aprobaciones pendientes
        if ($this->getPendingApprovals()->count() > 0) {
            return false;
        }

        // Regla específica: CDP requiere Compatibilidad del Gasto
        if ($this->current_step === 1) {
            $hasCompatibilidad = $this->documents()
                ->where('document_type', 'compatibilidad_gasto')
                ->where('approval_status', 'approved')
                ->exists();

            if (!$hasCompatibilidad) {
                return false;
            }
        }

        return true;
    }

    public function getProgressPercentage(): int
    {
        $totalSteps = 10; // Etapas 0-9
        return (int) (($this->current_step / $totalSteps) * 100);
    }

    protected function generateProcessNumber(): string
    {
        $year = date('Y');
        $prefix = $this->process_type->getPrefix();
        
        // Obtener el último número de proceso del año
        $lastProcess = static::where('process_type', $this->process_type)
            ->whereYear('created_at', $year)
            ->whereNotNull('process_number')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastProcess && preg_match('/-(\d+)-/', $lastProcess->process_number, $matches)) {
            $number = (int) $matches[1] + 1;
        } else {
            $number = 1;
        }

        // Formato: CD-PN-001-2026
        return sprintf('%s-%03d-%s', $prefix, $number, $year);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            ProcessStatus::CANCELLED->value,
            ProcessStatus::STARTED->value
        ]);
    }

    public function scopeByStatus($query, ProcessStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByStep($query, int $step)
    {
        return $query->where('current_step', $step);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where(function($q) use ($user) {
            $q->where('contractor_id', $user->id)
              ->orWhere('supervisor_id', $user->id)
              ->orWhere('unit_head_id', $user->id)
              ->orWhere('unit_lawyer_id', $user->id)
              ->orWhere('link_lawyer_id', $user->id)
              ->orWhere('created_by', $user->id);
        });
    }
}
