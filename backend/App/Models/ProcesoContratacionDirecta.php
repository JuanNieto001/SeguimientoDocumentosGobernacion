<?php

namespace App\Models;

use App\Enums\EstadoProcesoCD;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcesoContratacionDirecta extends Model
{
    use SoftDeletes;

    protected $table = 'proceso_contratacion_directa';

    protected $fillable = [
        'codigo',
        'estado',
        'etapa_actual',
        'objeto',
        'valor',
        'plazo_meses',
        'estudio_previo_path',
        // Contratista
        'contratista_nombre',
        'contratista_tipo_documento',
        'contratista_documento',
        'contratista_email',
        'contratista_telefono',
        // Origen
        'secretaria_id',
        'unidad_id',
        // Etapa 2
        'paa_solicitado',
        'certificado_no_planta',
        'paz_salvo_rentas',
        'paz_salvo_contabilidad',
        'compatibilidad_gasto',
        'compatibilidad_aprobada',
        'numero_cdp',
        'valor_cdp',
        'cdp_aprobado',
        // Etapa 3
        'hoja_vida_cargada',
        'documentos_contratista_completos',
        'checklist_validado',
        'resultado_validacion',
        // Etapa 4
        'numero_proceso_juridica',
        'revision_juridica_aprobada',
        'observaciones_juridica',
        // Etapa 5
        'contrato_electronico_path',
        'firma_contratista',
        'firma_ordenador_gasto',
        'observaciones_devolucion',
        // Etapa 6
        'numero_rpc',
        'rpc_firmado_flag',
        'expediente_radicado_flag',
        // Etapa 7
        'numero_contrato',
        'arl_solicitada',
        'acta_inicio_path',
        'acta_inicio_firmada',
        'fecha_inicio_ejecucion',
        // Actores
        'creado_por',
        'supervisor_id',
        'ordenador_gasto_id',
        'jefe_unidad_id',
        'abogado_unidad_id',
        // Meta
        'metadata',
    ];

    protected $casts = [
        'estado'                => EstadoProcesoCD::class,
        'valor'                 => 'decimal:2',
        'valor_cdp'             => 'decimal:2',
        'plazo_meses'           => 'integer',
        'etapa_actual'          => 'integer',
        'paa_solicitado'        => 'boolean',
        'certificado_no_planta' => 'boolean',
        'paz_salvo_rentas'      => 'boolean',
        'paz_salvo_contabilidad'=> 'boolean',
        'compatibilidad_gasto'  => 'boolean',
        'compatibilidad_aprobada'=> 'boolean',
        'cdp_aprobado'          => 'boolean',
        'hoja_vida_cargada'     => 'boolean',
        'documentos_contratista_completos' => 'boolean',
        'checklist_validado'    => 'boolean',
        'revision_juridica_aprobada' => 'boolean',
        'firma_contratista'     => 'boolean',
        'firma_ordenador_gasto' => 'boolean',
        'rpc_firmado_flag'      => 'boolean',
        'expediente_radicado_flag' => 'boolean',
        'arl_solicitada'        => 'boolean',
        'acta_inicio_firmada'   => 'boolean',
        'fecha_inicio_ejecucion'=> 'date',
        'metadata'              => 'array',
    ];

    // ═══════════════════════════════════════════════
    //  BOOT – Código auto-generado
    // ═══════════════════════════════════════════════
    protected static function booted(): void
    {
        static::creating(function (self $proceso) {
            if (!$proceso->codigo) {
                $proceso->codigo = $proceso->generarCodigo();
            }
        });
    }

    public function generarCodigo(): string
    {
        $year = date('Y');
        $ultimo = static::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        if ($ultimo && preg_match('/CD-PS-(\d+)/', $ultimo->codigo, $m)) {
            $numero = (int) $m[1] + 1;
        } else {
            $numero = 1;
        }

        return sprintf('CD-PS-%02d-%s', $numero, $year);
    }

    // ═══════════════════════════════════════════════
    //  RELACIONES
    // ═══════════════════════════════════════════════
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function ordenadorGasto(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordenador_gasto_id');
    }

    public function jefeUnidad(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jefe_unidad_id');
    }

    public function abogadoUnidad(): BelongsTo
    {
        return $this->belongsTo(User::class, 'abogado_unidad_id');
    }

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(ProcesoCDAuditoria::class, 'proceso_cd_id')->latest();
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(ProcesoCDDocumento::class, 'proceso_cd_id');
    }

    // ═══════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════

    /**
     * ¿El usuario tiene un rol válido para operar en el estado actual?
     */
    public function usuarioPuedeOperar(User $user): bool
    {
        $rolesRequeridos = $this->estado->rolesAutorizados();

        foreach ($rolesRequeridos as $rol) {
            if ($user->hasRole($rol)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Documentos faltantes para el estado dado.
     */
    public function documentosFaltantes(?EstadoProcesoCD $estado = null): array
    {
        $estado ??= $this->estado;
        $obligatorios = $estado->documentosObligatorios();

        if (empty($obligatorios)) {
            return [];
        }

        $existentes = $this->documentos()
            ->whereIn('tipo_documento', $obligatorios)
            ->where('estado_aprobacion', '!=', 'rechazado')
            ->pluck('tipo_documento')
            ->toArray();

        return array_diff($obligatorios, $existentes);
    }

    /**
     * Porcentaje de avance.
     */
    public function porcentajeAvance(): int
    {
        $totalEtapas = 7;
        return (int) (($this->etapa_actual / $totalEtapas) * 100);
    }

    /**
     * Verifica si todas las validaciones paralelas de etapa 2 están completas.
     */
    public function validacionesParalelasCompletas(): bool
    {
        return $this->paa_solicitado
            && $this->certificado_no_planta
            && $this->paz_salvo_rentas
            && $this->paz_salvo_contabilidad
            && $this->compatibilidad_gasto;
    }

    /**
     * ¿Puede solicitar CDP?
     */
    public function puedeSolicitarCDP(): bool
    {
        return $this->compatibilidad_aprobada && $this->validacionesParalelasCompletas();
    }

    // ═══════════════════════════════════════════════
    //  SCOPES
    // ═══════════════════════════════════════════════
    public function scopeActivos($query)
    {
        return $query->whereNotIn('estado', [
            EstadoProcesoCD::CANCELADO->value,
            EstadoProcesoCD::SUSPENDIDO->value,
        ]);
    }

    public function scopePorEstado($query, EstadoProcesoCD $estado)
    {
        return $query->where('estado', $estado->value);
    }

    public function scopePorEtapa($query, int $etapa)
    {
        return $query->where('etapa_actual', $etapa);
    }

    public function scopeDelUsuario($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('creado_por', $user->id)
              ->orWhere('supervisor_id', $user->id)
              ->orWhere('ordenador_gasto_id', $user->id)
              ->orWhere('jefe_unidad_id', $user->id)
              ->orWhere('abogado_unidad_id', $user->id);
        });
    }
}
