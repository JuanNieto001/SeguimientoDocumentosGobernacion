<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\DocumentType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProcessDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'process_id',
        'step_number',
        'document_type',
        'document_name',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'is_required',
        'requires_approval',
        'approval_status',
        'issued_at',
        'expires_at',
        'is_expired',
        'requires_signature',
        'required_signers',
        'signatures',
        'metadata',
        'observations',
        'uploaded_by',
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'document_type' => DocumentType::class,
        'approval_status' => ApprovalStatus::class,
        'step_number' => 'integer',
        'file_size' => 'integer',
        'is_required' => 'boolean',
        'requires_approval' => 'boolean',
        'is_expired' => 'boolean',
        'requires_signature' => 'boolean',
        'issued_at' => 'date',
        'expires_at' => 'date',
        'required_signers' => 'array',
        'signatures' => 'array',
        'metadata' => 'array',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProcessDocument $document) {
            // Auto-calcular expiración si aplica
            if ($document->document_type->requiresExpiration() && $document->issued_at) {
                $validityDays = $document->document_type->getValidityDays();
                if ($validityDays) {
                    $document->expires_at = Carbon::parse($document->issued_at)->addDays($validityDays);
                }
            }

            // Configurar si requiere firma
            if ($document->document_type->requiresSignature()) {
                $document->requires_signature = true;
            }
        });

        static::updating(function (ProcessDocument $document) {
            // Verificar expiración
            if ($document->expires_at && Carbon::now()->greaterThan($document->expires_at)) {
                $document->is_expired = true;
            }
        });

        static::deleting(function (ProcessDocument $document) {
            // Eliminar archivo físico
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        });
    }

    // Relaciones
    public function process(): BelongsTo
    {
        return $this->belongsTo(ContractProcess::class, 'process_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Métodos auxiliares
    public function isApproved(): bool
    {
        return $this->approval_status === ApprovalStatus::APPROVED;
    }

    public function isPending(): bool
    {
        return $this->approval_status === ApprovalStatus::PENDING;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === ApprovalStatus::REJECTED;
    }

    public function requiresFixes(): bool
    {
        return $this->approval_status === ApprovalStatus::REQUIRES_FIXES;
    }

    public function checkExpiration(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        $isExpired = Carbon::now()->greaterThan($this->expires_at);
        
        if ($isExpired !== $this->is_expired) {
            $this->update(['is_expired' => $isExpired]);
        }

        return $isExpired;
    }

    public function getDaysUntilExpiration(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        $days = Carbon::now()->diffInDays($this->expires_at, false);
        return $days > 0 ? (int) $days : 0;
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        $daysUntil = $this->getDaysUntilExpiration();
        return $daysUntil !== null && $daysUntil <= $days && $daysUntil > 0;
    }

    public function getDownloadUrl(): string
    {
        return route('contract-processes.documents.download', [
            'process' => $this->process_id,
            'document' => $this->id
        ]);
    }

    public function getFileSize(): string
    {
        if (!$this->file_size) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $unit = 0;

        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            $unit++;
        }

        return round($bytes, 2) . ' ' . $units[$unit];
    }

    public function addSignature(User $user, array $data = []): void
    {
        $signatures = $this->signatures ?? [];
        
        $signatures[] = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'signed_at' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
            'data' => $data,
        ];

        $this->update(['signatures' => $signatures]);
    }

    public function hasSignedBy(User $user): bool
    {
        if (!$this->signatures) {
            return false;
        }

        return collect($this->signatures)->contains('user_id', $user->id);
    }

    public function getAllSignaturesPending(): bool
    {
        if (!$this->requires_signature || !$this->required_signers) {
            return false;
        }

        $signedUserIds = collect($this->signatures ?? [])->pluck('user_id')->toArray();
        $requiredUserIds = $this->required_signers;

        return count(array_diff($requiredUserIds, $signedUserIds)) > 0;
    }

    public function approve(User $user, ?string $notes = null): void
    {
        $this->update([
            'approval_status' => ApprovalStatus::APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function reject(User $user, ?string $notes = null): void
    {
        $this->update([
            'approval_status' => ApprovalStatus::REJECTED,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function requestFixes(User $user, string $notes): void
    {
        $this->update([
            'approval_status' => ApprovalStatus::REQUIRES_FIXES,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    // Scopes
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', ApprovalStatus::APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', ApprovalStatus::PENDING);
    }

    public function scopeExpired($query)
    {
        return $query->where('is_expired', true);
    }

    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->whereNotNull('expires_at')
            ->where('is_expired', false)
            ->whereDate('expires_at', '<=', Carbon::now()->addDays($days));
    }

    public function scopeForStep($query, int $stepNumber)
    {
        return $query->where('step_number', $stepNumber);
    }

    public function scopeOfType($query, DocumentType $type)
    {
        return $query->where('document_type', $type);
    }
}
