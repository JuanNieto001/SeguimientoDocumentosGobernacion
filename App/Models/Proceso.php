<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proceso extends Model
{
    protected $table = 'procesos';

    protected $fillable = [
        'workflow_id',
        'codigo',
        'objeto',
        'descripcion',
        'estado',
        'etapa_actual_id',
        'area_actual_role',
        'created_by',
        'paa_id',
        'valor_estimado',
        // Planeación
        'paa_verificado',
        'aprobado_planeacion',
        'observaciones_planeacion',
        // Hacienda
        'numero_cdp',
        'valor_cdp',
        'rubro_presupuestal',
        'cdp_emitido',
        'numero_rp',
        'valor_rp',
        'rp_emitido',
        'aprobado_hacienda',
        'observaciones_hacienda',
        // Jurídica
        'ajustado_emitido',
        'numero_ajustado',
        'contratista_verificado',
        'polizas_aprobadas',
        'aprobado_juridica',
        'observaciones_juridica',
        // SECOP
        'secop_publicado',
        'secop_codigo',
        'contrato_registrado',
        'numero_contrato',
        'acta_inicio_registrada',
        'fecha_acta_inicio',
        'publicado_secop',
        'fecha_publicacion_secop',
        'url_secop',
        'numero_proceso_secop',
        'contrato_registrado_secop',
        'fecha_contrato',
        'numero_acta_inicio',
        'acta_inicio_registrada',
        'cerrado_secop',
        'fecha_cierre_secop',
        'observaciones_cierre_secop',
        'aprobado_secop',
        'observaciones_secop',
        // Rechazo
        'rechazado_por_area',
        'observaciones_rechazo',
        // Validaciones legales (Fase 3)
        'requiere_secop',
        'requiere_rup',
        'plazo_minimo_dias',
        'cuantia_smmlv',
        'valor_modificaciones',
        'porcentaje_modificaciones',
        'garantias_presentadas',
        'garantias_detalle',
        'requisitos_habilitantes',
        'requisitos_verificados',
        'validaciones_modalidad',
        'modalidad_validada',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'fecha_publicacion_secop' => 'datetime',
        'fecha_contrato' => 'date',
        'fecha_acta_inicio' => 'date',
        'fecha_cierre_secop' => 'datetime',
        'garantias_detalle' => 'array',
        'requisitos_habilitantes' => 'array',
        'validaciones_modalidad' => 'array',
    ];

    /**
     * Relación: Un proceso pertenece a un workflow
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Relación: Un proceso está en una etapa actual
     */
    public function etapaActual(): BelongsTo
    {
        return $this->belongsTo(Etapa::class, 'etapa_actual_id');
    }

    /**
     * Relación: Un proceso fue creado por un usuario
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación: Un proceso tiene muchas instancias de etapas
     */
    public function procesoEtapas(): HasMany
    {
        return $this->hasMany(ProcesoEtapa::class)->orderBy('created_at');
    }

    /**
     * Relación: Un proceso tiene muchos archivos
     */
    public function archivos(): HasMany
    {
        return $this->hasMany(ProcesoEtapaArchivo::class);
    }

    /**
     * Relación: Un proceso tiene muchas auditorías
     */
    public function auditorias(): HasMany
    {
        return $this->hasMany(ProcesoAuditoria::class)->orderByDesc('created_at');
    }

    /**
     * Relación: Un proceso tiene muchas alertas
     */
    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }

    /**
     * Relación: Un proceso tiene muchas modificaciones contractuales
     */
    public function modificaciones(): HasMany
    {
        return $this->hasMany(ModificacionContractual::class)->orderBy('fecha_modificacion');
    }

    /**
     * Relación: Un proceso puede estar asociado a una necesidad del PAA
     */
    public function paa(): BelongsTo
    {
        return $this->belongsTo(PlanAnualAdquisicion::class, 'paa_id');
    }

    /**
     * Obtener la instancia de etapa actual
     */
    public function getProcesoEtapaActual()
    {
        return $this->procesoEtapas()
            ->where('etapa_id', $this->etapa_actual_id)
            ->first();
    }

    /**
     * Verificar si el proceso está en curso
     */
    public function esEnCurso(): bool
    {
        return $this->estado === 'EN_CURSO';
    }

    /**
     * Verificar si el proceso está finalizado
     */
    public function estaFinalizado(): bool
    {
        return $this->estado === 'FINALIZADO';
    }

    /**
     * Verificar si el proceso está suspendido
     */
    public function estaSuspendido(): bool
    {
        return $this->estado === 'SUSPENDIDO';
    }

    /**
     * Avanzar a la siguiente etapa
     */
    public function avanzarEtapa(): bool
    {
        $siguienteEtapa = $this->etapaActual->siguienteEtapa;

        if (!$siguienteEtapa) {
            // No hay siguiente etapa, finalizar proceso
            $this->update([
                'estado' => 'FINALIZADO',
            ]);
            return false;
        }

        $this->update([
            'etapa_actual_id' => $siguienteEtapa->id,
            'area_actual_role' => $siguienteEtapa->area_role,
        ]);

        // Crear instancia de la nueva etapa
        ProcesoEtapa::firstOrCreate([
            'proceso_id' => $this->id,
            'etapa_id' => $siguienteEtapa->id,
        ]);

        return true;
    }

    /**
     * Registrar auditoría
     */
    public function registrarAuditoria(string $accion, string $descripcion, ?int $etapaId = null): void
    {
        ProcesoAuditoria::registrar(
            $this->id,
            $accion,
            $descripcion,
            $etapaId
        );
    }

    /**
     * Crear alerta para usuario específico
     */
    public function crearAlerta(
        int $userId,
        string $tipo,
        string $titulo,
        string $mensaje,
        string $prioridad = 'media',
        ?string $accionUrl = null
    ): void {
        Alerta::crear($userId, $tipo, $titulo, $mensaje, $this->id, $prioridad, $accionUrl);
    }

    /**
     * Scope: Procesos en curso
     */
    public function scopeEnCurso($query)
    {
        return $query->where('estado', 'EN_CURSO');
    }

    /**
     * Scope: Procesos finalizados
     */
    public function scopeFinalizados($query)
    {
        return $query->where('estado', 'FINALIZADO');
    }

    /**
     * Scope: Procesos de un área específica
     */
    public function scopeArea($query, string $areaRole)
    {
        return $query->where('area_actual_role', $areaRole);
    }

    /**
     * Scope: Procesos creados por un usuario
     */
    public function scopeCreadosPor($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope: Procesos de un workflow específico
     */
    public function scopeWorkflow($query, string $codigoWorkflow)
    {
        return $query->whereHas('workflow', function ($q) use ($codigoWorkflow) {
            $q->where('codigo', $codigoWorkflow);
        });
    }

    /**
     * Calcular porcentaje de modificaciones usado
     */
    public function calcularPorcentajeModificaciones(): float
    {
        if (!$this->valor_estimado || $this->valor_estimado == 0) {
            return 0;
        }

        $totalModificaciones = $this->modificaciones()
            ->where('tipo', 'adicion')
            ->where('estado', 'aprobado')
            ->sum('valor_modificacion');

        return ($totalModificaciones / $this->valor_estimado) * 100;
    }

    /**
     * Verificar si puede recibir más modificaciones
     */
    public function puedeRecibirModificaciones(): bool
    {
        return $this->calcularPorcentajeModificaciones() < 50;
    }

    /**
     * Obtener valor disponible para modificaciones
     */
    public function valorDisponibleModificaciones(): float
    {
        $limite = $this->valor_estimado * 0.5;
        $usado = $this->modificaciones()
            ->where('tipo', 'adicion')
            ->where('estado', 'aprobado')
            ->sum('valor_modificacion');

        return max(0, $limite - $usado);
    }
}
