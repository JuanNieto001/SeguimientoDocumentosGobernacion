<?php
/**
 * Archivo: backend/App/Services/AlertaService.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Services;

use App\Models\Alerta;
use App\Models\ContratoAplicacion;
use App\Models\Proceso;
use App\Models\ProcesoEtapaArchivo;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
            'contratos' => 0,
            'total' => 0
        ];

        // 1. Alertas de tiempo
        $alertasCreadas['tiempo'] += self::generarAlertasTiempo();

        // 2. Alertas de documentos
        $alertasCreadas['documentos'] += self::generarAlertasDocumentos();

        // 3. Alertas de responsabilidad
        $alertasCreadas['responsabilidad'] += self::generarAlertasResponsabilidad();

        // 4. Alertas para contratos de aplicaciones
        $alertasCreadas['contratos'] += self::generarAlertasContratosAplicaciones();

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
            
            $existe = Alerta::where('proceso_id', $archivo->proceso->id)
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

        // A2. Certificados vencidos
        $certificadosVencidos = ProcesoEtapaArchivo::where('estado', 'aprobado')
            ->whereNotNull('fecha_vigencia')
            ->whereDate('fecha_vigencia', '<', now()->toDateString())
            ->with(['proceso', 'proceso.workflow'])
            ->get();

        foreach ($certificadosVencidos as $archivo) {
            $diasVencido = (int) now()->diffInDays($archivo->fecha_vigencia, false) * -1;

            $existe = Alerta::where('proceso_id', $archivo->proceso->id)
                ->where('tipo', 'certificado_vencido')
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
                    tipo: 'certificado_vencido',
                    titulo: 'Certificado vencido',
                    mensaje: "El certificado '{$archivo->nombre_original}' está vencido desde hace {$diasVencido} día(s)",
                    prioridad: 'alta',
                    area_responsable: $archivo->proceso->etapaActual->area_responsable ?? 'planeacion',
                    metadata: [
                        'archivo_id' => $archivo->id,
                        'archivo_nombre' => $archivo->nombre_original,
                        'fecha_vigencia' => $archivo->fecha_vigencia,
                        'dias_vencido' => $diasVencido,
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
     * Alertas para contratos de aplicaciones próximos a vencer o vencidos.
     */
    private static function generarAlertasContratosAplicaciones(): int
    {
        $count = 0;
        $today = now()->startOfDay();
        $limit = now()->addDays(365)->toDateString();

        $contratos = ContratoAplicacion::query()
            ->where('activo', true)
            ->whereNotNull('fecha_fin')
            ->whereDate('fecha_fin', '<=', $limit)
            ->orderBy('fecha_fin')
            ->get();

        if ($contratos->isEmpty()) {
            return 0;
        }

        $destinatarios = self::obtenerDestinatariosPorRoles([
            'admin',
            'admin_general',
            'admin_secretaria',
            'gobernador',
            'secretario',
        ]);

        foreach ($contratos as $contrato) {
            if (!$contrato->fecha_fin) {
                continue;
            }

            $diasRestantes = $today->diffInDays($contrato->fecha_fin->copy()->startOfDay(), false);
            $vencido = $diasRestantes < 0;
            $tipo = $vencido ? 'contrato_aplicacion_vencido' : 'contrato_aplicacion_por_vencer';

            $prioridad = match (true) {
                $vencido => 'alta',
                $diasRestantes <= 15 => 'alta',
                $diasRestantes <= 45 => 'media',
                default => 'baja',
            };

            $titulo = $vencido
                ? 'Contrato de aplicativo vencido'
                : 'Contrato de aplicativo próximo a vencer';

            $mensaje = $vencido
                ? "El contrato '{$contrato->aplicacion}' vencio el {$contrato->fecha_fin->format('Y-m-d')} (hace " . abs($diasRestantes) . " dias)."
                : "El contrato '{$contrato->aplicacion}' vence el {$contrato->fecha_fin->format('Y-m-d')} (en {$diasRestantes} dias).";

            $accionUrl = '/contratos-aplicaciones/' . $contrato->id;

            $metadata = [
                'contrato_aplicacion_id' => $contrato->id,
                'aplicacion' => $contrato->aplicacion,
                'secop_proceso_id' => $contrato->secop_proceso_id,
                'fecha_fin' => optional($contrato->fecha_fin)->toDateString(),
                'dias_restantes' => $diasRestantes,
            ];

            if ($destinatarios->isEmpty()) {
                $existe = Alerta::where('tipo', $tipo)
                    ->whereNull('user_id')
                    ->where('created_at', '>', now()->subDay())
                    ->where('leida', false)
                    ->get()
                    ->filter(function ($alerta) use ($contrato) {
                        $meta = $alerta->metadata ?? [];
                        return isset($meta['contrato_aplicacion_id'])
                            && (int) $meta['contrato_aplicacion_id'] === (int) $contrato->id;
                    })
                    ->isNotEmpty();

                if ($existe) {
                    continue;
                }

                Alerta::create([
                    'tipo' => $tipo,
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'prioridad' => $prioridad,
                    'area_responsable' => 'planeacion',
                    'accion_url' => $accionUrl,
                    'leida' => false,
                    'metadata' => $metadata,
                ]);
                $count++;
                continue;
            }

            foreach ($destinatarios as $destinatario) {
                $existe = Alerta::where('tipo', $tipo)
                    ->where('user_id', $destinatario->id)
                    ->where('created_at', '>', now()->subDay())
                    ->where('leida', false)
                    ->get()
                    ->filter(function ($alerta) use ($contrato) {
                        $meta = $alerta->metadata ?? [];
                        return isset($meta['contrato_aplicacion_id'])
                            && (int) $meta['contrato_aplicacion_id'] === (int) $contrato->id;
                    })
                    ->isNotEmpty();

                if ($existe) {
                    continue;
                }

                Alerta::create([
                    'user_id' => $destinatario->id,
                    'tipo' => $tipo,
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'prioridad' => $prioridad,
                    'area_responsable' => 'planeacion',
                    'accion_url' => $accionUrl,
                    'leida' => false,
                    'metadata' => $metadata,
                ]);

                $count++;
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
     * Crear alertas para un area (por usuarios del rol o por area si no hay usuarios)
     */
    public static function crearParaArea(
        Proceso $proceso,
        string $tipo,
        string $titulo,
        string $mensaje,
        string $areaRole,
        string $prioridad = 'alta',
        array $metadata = [],
        ?string $accionUrl = null
    ): int {
        // Usamos el guard configurado para evitar mezclar roles de otros guards.
        $guardName = config('auth.defaults.guard', 'web');

        // Lista base de roles destino.
        $roles = [$areaRole];

        // Alias funcional: planeacion y descentralizacion se tratan como la misma bandeja.
        if ($areaRole === 'planeacion') {
            $roles[] = 'descentralizacion';
        } elseif ($areaRole === 'descentralizacion') {
            $roles[] = 'planeacion';
        }

        // Filtramos solo roles existentes en el guard activo para evitar excepciones.
        $rolesExistentes = Role::query()
            ->where('guard_name', $guardName)
            ->whereIn('name', $roles)
            ->pluck('name')
            ->all();

        // Si no existe ningún rol válido, creamos alerta genérica por área.
        if (empty($rolesExistentes)) {
            Alerta::create([
                'proceso_id' => $proceso->id,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'prioridad' => $prioridad,
                'area_responsable' => $areaRole,
                'accion_url' => $accionUrl,
                'leida' => false,
                'metadata' => $metadata,
            ]);
            return 1;
        }

        // Obtenemos usuarios que realmente tengan roles válidos para notificación individual.
        $destinatarios = User::query()
            ->whereHas('roles', function ($query) use ($rolesExistentes, $guardName) {
                $query->where('guard_name', $guardName)
                    ->whereIn('name', $rolesExistentes);
            })
            ->get();

        // Si no hay usuarios destino, guardamos alerta de área como fallback de bandeja.
        if ($destinatarios->isEmpty()) {
            Alerta::create([
                'proceso_id' => $proceso->id,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'prioridad' => $prioridad,
                'area_responsable' => $areaRole,
                'accion_url' => $accionUrl,
                'leida' => false,
                'metadata' => $metadata,
            ]);
            return 1;
        }

        // Notificación individual por usuario para asegurar visibilidad personal.
        foreach ($destinatarios as $usuario) {
            Alerta::create([
                'proceso_id' => $proceso->id,
                'user_id' => $usuario->id,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'prioridad' => $prioridad,
                'area_responsable' => $areaRole,
                'accion_url' => $accionUrl,
                'leida' => false,
                'metadata' => $metadata,
            ]);
        }

        // Devolvemos cantidad de alertas emitidas para métricas y trazabilidad.
        return $destinatarios->count();
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

    /**
     * Usuarios destinatarios por lista de roles válidos del guard activo.
     */
    private static function obtenerDestinatariosPorRoles(array $roles): Collection
    {
        $guardName = config('auth.defaults.guard', 'web');

        $rolesExistentes = Role::query()
            ->where('guard_name', $guardName)
            ->whereIn('name', $roles)
            ->pluck('name')
            ->all();

        if (empty($rolesExistentes)) {
            return collect();
        }

        return User::query()
            ->whereHas('roles', function ($query) use ($rolesExistentes, $guardName) {
                $query->where('guard_name', $guardName)
                    ->whereIn('name', $rolesExistentes);
            })
            ->get();
    }
}

