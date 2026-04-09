<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_id',
        'action',
        'entity_type',
        'entity_id',
        'old_value',
        'new_value',
        'changes',
        'description',
        'notes',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'entity_id' => 'integer',
        'changes' => 'array',
    ];

    // Relaciones
    public function process(): BelongsTo
    {
        return $this->belongsTo(ContractProcess::class, 'process_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Métodos estáticos para crear logs
    public static function logStateChange(ContractProcess $process, string $oldState, string $newState, ?User $user = null): void
    {
        static::create([
            'process_id' => $process->id,
            'action' => 'state_changed',
            'entity_type' => ContractProcess::class,
            'entity_id' => $process->id,
            'old_value' => $oldState,
            'new_value' => $newState,
            'description' => "Estado cambiado de '{$oldState}' a '{$newState}'",
            'user_id' => $user?->id ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logDocumentUpload(ProcessDocument $document, ?User $user = null): void
    {
        static::create([
            'process_id' => $document->process_id,
            'action' => 'document_uploaded',
            'entity_type' => ProcessDocument::class,
            'entity_id' => $document->id,
            'description' => "Documento '{$document->document_name}' cargado para etapa {$document->step_number}",
            'user_id' => $user?->id ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logApproval(ProcessApproval $approval, ?User $user = null): void
    {
        static::create([
            'process_id' => $approval->process_id,
            'action' => 'approval_granted',
            'entity_type' => ProcessApproval::class,
            'entity_id' => $approval->id,
            'description' => "Aprobación '{$approval->approval_type}' otorgada",
            'user_id' => $user?->id ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logCustomAction(ContractProcess $process, string $action, string $description, array $changes = [], ?User $user = null): void
    {
        static::create([
            'process_id' => $process->id,
            'action' => $action,
            'changes' => $changes,
            'description' => $description,
            'user_id' => $user?->id ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // Scopes
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
