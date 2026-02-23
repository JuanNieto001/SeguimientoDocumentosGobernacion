<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_id',
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
        'email_sent',
        'email_sent_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    // Relaciones
    public function process(): BelongsTo
    {
        return $this->belongsTo(ContractProcess::class, 'process_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Métodos auxiliares
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function markEmailAsSent(): void
    {
        if (!$this->email_sent) {
            $this->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);
        }
    }

    // Métodos estáticos para crear notificaciones
    public static function notifyMissingDocument(ContractProcess $process, User $user, string $documentName): void
    {
        static::create([
            'process_id' => $process->id,
            'user_id' => $user->id,
            'type' => 'missing_document',
            'title' => 'Documento Faltante',
            'message' => "El documento '{$documentName}' es requerido para avanzar en el proceso {$process->process_number}",
            'data' => [
                'document_name' => $documentName,
                'process_number' => $process->process_number,
                'current_step' => $process->current_step,
            ],
        ]);
    }

    public static function notifyLegalReturn(ContractProcess $process, User $user, string $observations): void
    {
        static::create([
            'process_id' => $process->id,
            'user_id' => $user->id,
            'type' => 'legal_return',
            'title' => 'Proceso Devuelto con Observaciones',
            'message' => "El proceso {$process->process_number} ha sido devuelto por Jurídica con observaciones",
            'data' => [
                'observations' => $observations,
                'process_number' => $process->process_number,
            ],
        ]);
    }

    public static function notifyApprovalRequired(ContractProcess $process, User $user, string $approvalType): void
    {
        static::create([
            'process_id' => $process->id,
            'user_id' => $user->id,
            'type' => 'approval_required',
            'title' => 'Aprobación Requerida',
            'message' => "Se requiere su aprobación para '{$approvalType}' en el proceso {$process->process_number}",
            'data' => [
                'approval_type' => $approvalType,
                'process_number' => $process->process_number,
            ],
        ]);
    }

    public static function notifySecopSignatureReady(ContractProcess $process, User $user): void
    {
        static::create([
            'process_id' => $process->id,
            'user_id' => $user->id,
            'type' => 'secop_signature_ready',
            'title' => 'Firma en SECOP II Disponible',
            'message' => "El proceso {$process->process_number} está listo para firma en SECOP II",
            'data' => [
                'process_number' => $process->process_number,
                'secop_id' => $process->secop_id,
            ],
        ]);
    }

    public static function notifyRpcIssued(ContractProcess $process, User $user): void
    {
        static::create([
            'process_id' => $process->id,
            'user_id' => $user->id,
            'type' => 'rpc_issued',
            'title' => 'RPC Expedido',
            'message' => "El RPC {$process->rpc_number} ha sido expedido para el proceso {$process->process_number}",
            'data' => [
                'rpc_number' => $process->rpc_number,
                'process_number' => $process->process_number,
            ],
        ]);
    }

    public static function notifyReadyForActaInicio(ContractProcess $process, User $user): void
    {
        static::create([
            'process_id' => $process->id,
            'user_id' => $user->id,
            'type' => 'ready_for_acta_inicio',
            'title' => 'Listo para Acta de Inicio',
            'message' => "El proceso {$process->process_number} está listo para elaborar el Acta de Inicio",
            'data' => [
                'process_number' => $process->process_number,
                'contract_number' => $process->contract_number,
            ],
        ]);
    }

    public static function notifyDocumentExpiring(ContractProcess $process, User $user, ProcessDocument $document): void
    {
        $daysUntil = $document->getDaysUntilExpiration();
        
        static::create([
            'process_id' => $process->id,
            'user_id' => $user->id,
            'type' => 'document_expiring',
            'title' => 'Documento Próximo a Vencer',
            'message' => "El documento '{$document->document_name}' vencerá en {$daysUntil} días",
            'data' => [
                'document_id' => $document->id,
                'document_name' => $document->document_name,
                'expires_at' => $document->expires_at->toDateString(),
                'days_until_expiration' => $daysUntil,
            ],
        ]);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
