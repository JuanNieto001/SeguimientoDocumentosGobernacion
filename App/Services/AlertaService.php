<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Proceso;
use App\Models\ProcesoEtapaArchivo;
use Carbon\Carbon;

class AlertaService
{
    /**
     * Genera todas las alertas automáticas del sistema
     */
    public static function generarAlertasAutomaticas(): array
    {
        $alertasCreadas = [
            'tiempo' => 0,
            'documentos' => 0,
            'responsabilidad' => 0,
            'total' => 0
        ];

        // 1. Alertas de tiempo
        $alertasCreadas['tiempo'] += self::generarAlertasTiempo();

        // 2. Alertas de documentos
        $alertasCreadas['documentos'] += self::generarAlertasDocumentos();

        // 3. Alertas de responsabilidad
        $alertasCreadas['responsabilidad'] += self::generarAlertasResponsabilidad();

        $alertasCreadas['total'] = array_sum(array_filter($alertasCreadas, 'is_int'));

        return $alertasCreadas;
    }

    /**
     * Alertas de tiempo (vencimientos, retrasos, inactividad)
     */
    private static function generarAlertasTiempo(): int
    {
        $count = 0;

        // A. Certificados próximos a vencer (5 días antes)
        $certificadosProximos = ProcesoEtapaArchivo::where('estado', 'aprobado')
            ->whereNotNull('fecha_vigencia')
            ->whereBetween('fecha_vigencia', [now(), now()->addDays(5)])
            ->with(['proceso', 'proceso.workflow'])
            ->get();

        foreach ($certificadosProximos as $archivo) {
            $diasRestantes = now()->diffInDays($archivo->fecha_vigencia, false);
            
            $existe = Alerta::where('proceso_id', $proceso->id)
                ->where('tipo', 'certificado_por_vencer')
                ->where('leida', false)
                ->get()
                ->filter(function($alerta) use ($archivo) {
                    $metadata = $alerta->metadata ?? [];
                    return isset($metadata['archivo_id']) && $metadata['archivo_id'] == $archivo->id;
                })
                ->isNotEmpty();

            if (!$existe) {
                self::crear(
                    proceso: $archivo->proceso,
                    tipo: 'certificado_por_vencer',
                    titulo: 'Certificado próximo a vencer',
                    mensaje: "El certificado '{$archivo->nombre_original}' vence en {$diasRestantes} día(s)",
                    prioridad: $diasRestantes <= 2 ? 'alta' : 'media',
                    area_responsable: $archivo->proceso->etapaActual->area_responsable ?? 'planeacion',
                    metadata: [
                        'archivo_id' => $archivo->id,
                        'archivo_nombre' => $archivo->nombre_original,
                        'fecha_vigencia' => $archivo->fecha_vigencia,
                        'dias_restantes' => $diasRestantes
                    ]
                );
                $count++;
            }
        }

        // B. Procesos con más tiempo del estimado en etapa
        $procesosEnEtapa = Proceso::whereIn('estado', ['en_tramite', 'en_revision'])
            ->with(['etapaActual', 'procesoEtapas' => function($q) {
                $q->whereNotNull('recibido_at')->whereNull('enviado_at');
            }])
            ->get();

        foreach ($procesosEnEtapa as $proceso) {
            $etapaActiva = $proceso->procesoEtapas->first();
            if ($etapaActiva && $etapaActiva->etapa && $etapaActiva->etapa->dias_estimados) {
                $diasEnEtapa = $etapaActiva->recibido_at->diffInDays(now());
                $diasEstimados = $etapaActiva->etapa->dias_estimados;

                if ($diasEnEtapa > $diasEstimados) {
                    $existe = Alerta::where('proceso_id', $proceso->id)
                        ->where('tipo', 'tiempo_excedido')
                        ->where('leida', false)
                        ->get()
                        ->filter(function($alerta) use ($etapaActiva) {
                            $metadata = $alerta->metadata ?? [];
                            return isset($metadata['etapa_id']) && $metadata['etapa_id'] == $etapaActiva->etapa_id;
                        })
                        ->isNotEmpty();

                    if (!$existe) {
                        self::crear(
                            proceso: $proceso,
                            tipo: 'tiempo_excedido',
                            titulo: 'Proceso con retraso',
                            mensaje: "El proceso '{$proceso->objeto}' lleva {$diasEnEtapa} días en {$etapaActiva->etapa->nombre} (estimado: {$diasEstimados} días)",
                            prioridad: 'alta',
                            area_responsable: $etapaActiva->etapa->area_responsable,
                            metadata: [
                                'etapa_id' => $etapaActiva->etapa_id,
                                'etapa_nombre' => $etapaActiva->etapa->nombre,
                                'dias_en_etapa' => $diasEnEtapa,
                                'dias_estimados' => $diasEstimados,
                                'dias_excedidos' => $diasEnEtapa - $diasEstimados
                            ]
                        );
                        $count++;
                    }
                }
            }
        }

        // C. Procesos sin actividad en 7 días
        $procesosSinActividad = Proceso::whereIn('estado', ['en_tramite', 'en_revision'])
            ->where('updated_at', '<', now()->subDays(7))
            ->with('etapaActual')
            ->get();

        foreach ($procesosSinActividad as $proceso) {
            $diasInactivo = $proceso->updated_at->diffInDays(now());
            
            $existe = Alerta::where('proceso_id', $proceso->id)
                ->where('tipo', 'sin_actividad')
                ->where('leida', false)
                ->where('created_at', '>', now()->subDays(1))
                ->exists();

            if (!$existe) {
                self::crear(
                    proceso: $proceso,
                    tipo: 'sin_actividad',
                    titulo: 'Proceso sin actividad',
                    mensaje: "El proceso '{$proceso->objeto}' no tiene actividad desde hace {$diasInactivo} días",
                    prioridad: $diasInactivo > 15 ? 'alta' : 'media',
                    area_responsable: $proceso->etapaActual->area_responsable ?? 'planeacion',
                    metadata: [
                        'dias_inactivo' => $diasInactivo,
                        'ultima_actividad' => $proceso->updated_at
                    ]
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * Alertas de documentos (rechazados, pendientes de aprobación, faltantes)
     */
    private static function generarAlertasDocumentos(): int
    {
        $count = 0;

        // A. Documentos rechazados
        $documentosRechazados = ProcesoEtapaArchivo::where('estado', 'rechazado')
            ->with(['proceso', 'usuario'])
            ->get();

        foreach ($documentosRechazados as $archivo) {
            // Verificar si ya existe alerta para este archivo
            $existeAlerta = Alerta::where('proceso_id', $archivo->proceso_id)
                ->where('tipo', 'documento_rechazado')
                ->where('leida', false)
                ->get()
                ->filter(function($alerta) use ($archivo) {
                    $metadata = $alerta->metadata ?? [];
                    return isset($metadata['archivo_id']) && $metadata['archivo_id'] == $archivo->id;
                })
                ->isNotEmpty();

            if (!$existeAlerta) {
                self::crear(
                    proceso: $archivo->proceso,
                    tipo: 'documento_rechazado',
                    titulo: 'Documento rechazado',
                    mensaje: "El documento '{$archivo->nombre_original}' fue rechazado",
                    prioridad: 'alta',
                    area_responsable: $archivo->proceso->etapaActual->area_responsable ?? 'unidad_solicitante',
                    metadata: [
                        'archivo_id' => $archivo->id,
                        'archivo_nombre' => $archivo->nombre_original,
                        'observaciones' => $archivo->observaciones,
                        'rechazado_por' => $archivo->aprobado_por
                    ]
                );
                $count++;
            }
        }

        // B. Documentos pendientes de aprobación (más de 3 días)
        $documentosPendientes = ProcesoEtapaArchivo::where('estado', 'pendiente')
            ->where('created_at', '<', now()->subDays(3))
            ->with(['proceso', 'etapa'])
            ->get();

        foreach ($documentosPendientes as $archivo) {
            $existe = Alerta::where('proceso_id', $archivo->proceso_id)
                ->where('tipo', 'documento_pendiente')
                ->where('leida', false)
                ->get()
                ->filter(function($alerta) use ($archivo) {
                    $metadata = $alerta->metadata ?? [];
                    return isset($metadata['archivo_id']) && $metadata['archivo_id'] == $archivo->id;
                })
                ->isNotEmpty();

            if (!$existe) {
                $diasPendiente = $archivo->created_at->diffInDays(now());
                
                self::crear(
                    proceso: $archivo->proceso,
                    tipo: 'documento_pendiente',
                    titulo: 'Documento pendiente de aprobación',
                    mensaje: "El documento '{$archivo->nombre_original}' está pendiente de aprobación desde hace {$diasPendiente} días",
                    prioridad: $diasPendiente > 5 ? 'alta' : 'media',
                    area_responsable: $archivo->etapa->area_responsable ?? 'planeacion',
                    metadata: [
                        'archivo_id' => $archivo->id,
                        'archivo_nombre' => $archivo->nombre_original,
                        'dias_pendiente' => $diasPendiente
                    ]
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * Alertas de responsabilidad (tareas asignadas, acciones requeridas)
     */
    private static function generarAlertasResponsabilidad(): int
    {
        $count = 0;

        // A. Procesos recién recibidos en área (últimas 24 horas)
        $procesosRecientes = Proceso::whereHas('procesoEtapas', function($q) {
                $q->whereNotNull('recibido_at')
                    ->whereNull('enviado_at')
                    ->where('recibido_at', '>', now()->subDay());
            })
            ->with(['etapaActual', 'procesoEtapas' => function($q) {
                $q->whereNotNull('recibido_at')
                    ->whereNull('enviado_at')
                    ->where('recibido_at', '>', now()->subDay());
            }])
            ->get();

        foreach ($procesosRecientes as $proceso) {
            $etapaActiva = $proceso->procesoEtapas->first();
            
            if ($etapaActiva) {
                $existe = Alerta::where('proceso_id', $proceso->id)
                    ->where('tipo', 'nueva_tarea')
                    ->where('created_at', '>', now()->subDay())
                    ->get()
                    ->filter(function($alerta) use ($etapaActiva) {
                        $metadata = $alerta->metadata ?? [];
                        return isset($metadata['etapa_id']) && $metadata['etapa_id'] == $etapaActiva->etapa_id;
                    })
                    ->isNotEmpty();

                if (!$existe) {
                    self::crear(
                        proceso: $proceso,
                        tipo: 'nueva_tarea',
                        titulo: 'Nueva tarea asignada',
                        mensaje: "Se ha recibido el proceso '{$proceso->objeto}' en {$etapaActiva->etapa->nombre}",
                        prioridad: 'media',
                        area_responsable: $etapaActiva->etapa->area_responsable,
                        metadata: [
                            'etapa_id' => $etapaActiva->etapa_id,
                            'etapa_nombre' => $etapaActiva->etapa->nombre,
                            'recibido_at' => $etapaActiva->recibido_at
                        ]
                    );
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Crear una alerta
     */
    public static function crear(
        Proceso $proceso,
        string $tipo,
        string $titulo,
        string $mensaje,
        string $prioridad = 'media',
        ?string $area_responsable = null,
        ?int $user_id = null,
        array $metadata = []
    ): Alerta {
        return Alerta::create([
            'proceso_id' => $proceso->id,
            'user_id' => $user_id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'prioridad' => $prioridad,
            'area_responsable' => $area_responsable ?? $proceso->etapaActual->area_responsable ?? 'planeacion',
            'leida' => false,
            'metadata' => $metadata
        ]);
    }

    /**
     * Marcar alerta como leída
     */
    public static function marcarLeida(int $alertaId): bool
    {
        return Alerta::where('id', $alertaId)
            ->update([
                'leida' => true,
                'leida_at' => now()
            ]);
    }

    /**
     * Marcar todas las alertas de un usuario como leídas
     */
    public static function marcarTodasLeidas(int $userId): int
    {
        return Alerta::where('user_id', $userId)
            ->orWhereNull('user_id')
            ->update([
                'leida' => true,
                'leida_at' => now()
            ]);
    }

    /**
     * Obtener alertas de un área
     */
    public static function obtenerPorArea(string $area, bool $soloNoLeidas = true): \Illuminate\Database\Eloquent\Collection
    {
        $query = Alerta::where('area_responsable', $area)
            ->with(['proceso', 'proceso.workflow']);

        if ($soloNoLeidas) {
            $query->where('leida', false);
        }

        return $query->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener alertas de un usuario
     */
    public static function obtenerPorUsuario(int $userId, bool $soloNoLeidas = true): \Illuminate\Database\Eloquent\Collection
    {
        $query = Alerta::where('user_id', $userId)
            ->with(['proceso', 'proceso.workflow']);

        if ($soloNoLeidas) {
            $query->where('leida', false);
        }

        return $query->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
