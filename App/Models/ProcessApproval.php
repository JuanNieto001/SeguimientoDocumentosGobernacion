<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_id',
        'document_id',
        'approval_type',
        'step_number',
        'status',
        'comments',
        'checklist',
        'requested_from',
        'approved_by',
        'requested_at',
        'responded_at',
        'due_date',
    ];

    protected $casts = [
        'status' => ApprovalStatus::class,
        'step_number' => 'integer',
        'checklist' => 'array',
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    // Relaciones
    public function process(): BelongsTo
    {
        return $this->belongsTo(ContractProcess::class, 'process_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ProcessDocument::class, 'document_id');
    }

    public function requestedFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_from');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Métodos auxiliares
    public function isPending(): bool
    {
        return $this->status === ApprovalStatus::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === ApprovalStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === ApprovalStatus::REJECTED;
    }

    public function requiresFixes(): bool
    {
        return $this->status === ApprovalStatus::REQUIRES_FIXES;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && now()->greaterThan($this->due_date) && $this->isPending();
    }

    public function approve(User $user, ?string $comments = null): void
    {
        $this->update([
            'status' => ApprovalStatus::APPROVED,
            'approved_by' => $user->id,
            'responded_at' => now(),
            'comments' => $comments ?? $this->comments,
        ]);
    }

    public function reject(User $user, string $comments): void
    {
        $this->update([
            'status' => ApprovalStatus::REJECTED,
            'approved_by' => $user->id,
            'responded_at' => now(),
            'comments' => $comments,
        ]);
    }

    public function requestFixes(User $user, string $comments): void
    {
        $this->update([
            'status' => ApprovalStatus::REQUIRES_FIXES,
            'approved_by' => $user->id,
            'responded_at' => now(),
            'comments' => $comments,
        ]);
    }

    public function updateChecklist(array $checklist): void
    {
        $this->update(['checklist' => $checklist]);
    }

    public function isChecklistComplete(): bool
    {
        if (!$this->checklist) {
            return false;
        }

        return collect($this->checklist)->every(fn($item) => $item['checked'] ?? false);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', ApprovalStatus::PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', ApprovalStatus::APPROVED);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', ApprovalStatus::PENDING)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('requested_from', $user->id);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('approval_type', $type);
    }
}
