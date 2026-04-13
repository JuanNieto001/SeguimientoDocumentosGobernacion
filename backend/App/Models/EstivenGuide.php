<?php
/**
 * Archivo: backend/App/Models/EstivenGuide.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class EstivenGuide extends Model
{
    protected $fillable = ['role', 'icon', 'icon_image_path', 'title', 'orden', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(EstivenGuideStep::class)->orderBy('step_number');
    }

    public function getIconImageUrlAttribute(): ?string
    {
        if (!$this->icon_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->icon_image_path);
    }

    /**
     * Guías activas para un rol dado (incluye las comunes _common).
     */
    public static function forRole(string $role): \Illuminate\Database\Eloquent\Collection
    {
        return static::with('steps')
            ->where('activo', true)
            ->whereIn('role', [$role, '_common'])
            ->orderBy('role')        // _common primero (alfabético)
            ->orderBy('orden')
            ->get();
    }

    /**
     * Convierte a formato array compatible con Agente Estiven.
     */
    public function toEstivenArray(): array
    {
        return [
            'icon'  => $this->icon,
            'icon_image_url' => $this->icon_image_url,
            'title' => $this->title,
            'steps' => $this->steps->map(function (EstivenGuideStep $step) {
                return [
                    'content' => $step->content,
                    'image_url' => $step->image_url,
                    'image_caption' => $step->image_caption,
                ];
            })->toArray(),
        ];
    }
}

