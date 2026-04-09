<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoEtapaCheck extends Model
{
    protected $table = 'proceso_etapa_checks';

    protected $fillable = [
        'proceso_etapa_id',
        'etapa_item_id',
        'checked',
        'checked_by',
        'checked_at',
    ];

    protected $casts = [
        'checked' => 'boolean',
        'checked_at' => 'datetime',
    ];

    /**
     * Relación: Un check pertenece a una instancia de etapa
     */
    public function procesoEtapa(): BelongsTo
    {
        return $this->belongsTo(ProcesoEtapa::class);
    }

    /**
     * Relación: Un check pertenece a un item de etapa
     */
    public function etapaItem(): BelongsTo
    {
        return $this->belongsTo(EtapaItem::class);
    }

    /**
     * Alias para compatibilidad con vistas que usan 'item'
     */
    public function item(): BelongsTo
    {
        return $this->etapaItem();
    }

    /**
     * Relación: Usuario que marcó el check
     */
    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /**
     * Marcar/desmarcar el check
     */
    public function toggle(User $user): void
    {
        $this->update([
            'checked' => !$this->checked,
            'checked_by' => $user->id,
            'checked_at' => now(),
        ]);
    }

    /**
     * Scope: Solo checks marcados
     */
    public function scopeChecked($query)
    {
        return $query->where('checked', true);
    }

    /**
     * Scope: Solo checks requeridos
     */
    public function scopeRequeridos($query)
    {
        return $query->whereHas('etapaItem', function ($q) {
            $q->where('requerido', true);
        });
    }
}
