<?php
/**
 * Archivo: backend/App/Models/EstivenGuideStep.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EstivenGuideStep extends Model
{
    protected $fillable = [
        'estiven_guide_id',
        'step_number',
        'content',
        'image_path',
        'image_caption',
    ];

    protected $casts = [
        'step_number' => 'integer',
    ];

    public function guide(): BelongsTo
    {
        return $this->belongsTo(EstivenGuide::class, 'estiven_guide_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }
}

