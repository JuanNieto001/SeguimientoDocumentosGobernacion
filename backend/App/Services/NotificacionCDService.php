<?php
/**
 * Archivo: backend/App/Services/NotificacionCDService.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Services;

use App\Enums\EstadoProcesoCD;
use App\Models\Alerta;
use App\Models\ProcesoContratacionDirecta;
use App\Models\User;

/**
 * Servicio de notificaciones para el flujo de Contratación Directa – Persona Natural.
 *
 * Envía alertas a todos los usuarios que tienen el rol requerido
 * para actuar en el siguiente estado del proceso.
 */
class NotificacionCDService
{
    /**
     * Mapa de estados → configuración de notificación.
     * Cada entrada define:
     *   - titulo: título de la alerta
     *   - mensaje: plantilla con {codigo} y {objeto}
     *   - area: área responsable
     *   - roles: roles de los destinatarios
     *   - prioridad: alta/media/baja
     */
    protected static function configuraciones(): array
    {
        return [
            // ── ETAPA 2: Planeación recibe proceso ──
            EstadoProcesoCD::EN_VALIDACION_PLANEACION->value => [
                'titulo'   => 'Nuevo proceso para validar',
                'mensaje'  => 'El proceso {codigo} requiere validaciones presupuestales (PAA, No Planta, Paz y Salvos, Compatibilidad). Objeto: {objeto}',
                'area'     => 'planeacion',
                'roles'    => ['planeacion'],
                'prioridad'=> 'alta',
            ],
            EstadoProcesoCD::COMPATIBILIDAD_APROBADA->value => [
                'titulo'   => 'Compatibilidad aprobada',
                'mensaje'  => 'La compatibilidad del gasto del proceso {codigo} fue aprobada. Se puede solicitar CDP.',
                'area'     => 'planeacion',
                'roles'    => ['planeacion'],
                'prioridad'=> 'media',
            ],
            EstadoProcesoCD::CDP_SOLICITADO->value => [
                'titulo'   => 'CDP solicitado – Acción requerida',
                'mensaje'  => 'Se ha solicitado CDP para el proceso {codigo}. Hacienda debe aprobar o rechazar.',
                'area'     => 'hacienda',
                'roles'    => ['hacienda'],
                'prioridad'=> 'alta',
            ],
            EstadoProcesoCD::CDP_APROBADO->value => [
                'titulo'   => 'CDP aprobado – Recopilar documentos',
                'mensaje'  => 'El CDP del proceso {codigo} fue aprobado. La unidad solicitante debe recopilar documentos del contratista.',
                'area'     => 'unidad_solicitante',
                'roles'    => ['unidad_solicitante'],
                'prioridad'=> 'alta',
                'notify_creator' => true,
            ],
            EstadoProcesoCD::CDP_BLOQUEADO->value => [
                'titulo'   => 'CDP bloqueado',
                'mensaje'  => 'El CDP del proceso {codigo} fue bloqueado por falta de compatibilidad. Planeación debe revisar.',
                'area'     => 'planeacion',
                'roles'    => ['planeacion'],
                'prioridad'=> 'alta',
            ],

            // ── ETAPA 3: Documentación contratista ──
            EstadoProcesoCD::DOCUMENTACION_INCOMPLETA->value => [
                'titulo'   => 'Documentación pendiente',
                'mensaje'  => 'El proceso {codigo} requiere documentación del contratista. La unidad solicitante debe completar la Hoja de Vida y documentos.',
                'area'     => 'unidad_solicitante',
                'roles'    => ['unidad_solicitante'],
                'prioridad'=> 'media',
                'notify_creator' => true,
            ],
            EstadoProcesoCD::DOCUMENTACION_VALIDADA->value => [
                'titulo'   => 'Documentación validada',
                'mensaje'  => 'La documentación del proceso {codigo} ha sido validada. Se puede enviar a revisión jurídica.',
                'area'     => 'unidad_solicitante',
                'roles'    => ['unidad_solicitante'],
                'prioridad'=> 'media',
                'notify_creator' => true,
            ],

            // ── ETAPA 4: Revisión Jurídica ──
            EstadoProcesoCD::EN_REVISION_JURIDICA->value => [
                'titulo'   => 'Proceso para revisión jurídica',
                'mensaje'  => 'El proceso {codigo} fue enviado a Secretaría Jurídica para revisión y asignación de número de proceso.',
                'area'     => 'juridica',
                'roles'    => ['juridica'],
                'prioridad'=> 'alta',
            ],
            EstadoProcesoCD::PROCESO_NUMERO_GENERADO->value => [
                'titulo'   => 'Número de proceso asignado',
                'mensaje'  => 'Secretaría Jurídica asignó número al proceso {codigo}. Se procede a generar contrato.',
                'area'     => 'juridica',
                'roles'    => ['juridica'],
                'prioridad'=> 'media',
            ],
            EstadoProcesoCD::GENERACION_CONTRATO->value => [
                'titulo'   => 'Contrato en generación',
                'mensaje'  => 'El contrato del proceso {codigo} está siendo generado por Jurídica.',
                'area'     => 'juridica',
                'roles'    => ['juridica'],
                'prioridad'=> 'media',
            ],

            // ── ETAPA 5: Firma de Contrato ──
            EstadoProcesoCD::CONTRATO_GENERADO->value => [
                'titulo'   => 'Contrato listo para firmas',
                'mensaje'  => 'El contrato del proceso {codigo} fue generado. Requiere firma del contratista y del ordenador del gasto.',
                'area'     => 'unidad_solicitante',
                'roles'    => ['unidad_solicitante', 'juridica'],
                'prioridad'=> 'alta',
                'notify_creator' => true,
            ],
            EstadoProcesoCD::CONTRATO_FIRMADO_PARCIAL->value => [
                'titulo'   => 'Falta una firma en contrato',
                'mensaje'  => 'El contrato del proceso {codigo} tiene una firma registrada. Falta la segunda firma para completar.',
                'area'     => 'unidad_solicitante',
                'roles'    => ['unidad_solicitante', 'juridica'],
                'prioridad'=> 'alta',
                'notify_creator' => true,
            ],
            EstadoProcesoCD::CONTRATO_FIRMADO_TOTAL->value => [
                'titulo'   => 'Contrato firmado completamente',
                'mensaje'  => 'El contrato del proceso {codigo} fue firmado por ambas partes. Se procede a solicitar RPC.',
                'area'     => 'planeacion',
                'roles'    => ['planeacion'],
                'prioridad'=> 'alta',
            ],
            EstadoProcesoCD::CONTRATO_DEVUELTO->value => [
                'titulo'   => 'Contrato devuelto con observaciones',
                'mensaje'  => 'El contrato del proceso {codigo} fue devuelto. Se requieren correcciones por parte de Jurídica.',
                'area'     => 'juridica',
                'roles'    => ['juridica'],
                'prioridad'=> 'alta',
            ],

            // ── ETAPA 6: RPC ──
            EstadoProcesoCD::RPC_SOLICITADO->value => [
                'titulo'   => 'RPC solicitado – Acción requerida',
                'mensaje'  => 'Se ha solicitado RPC para el proceso {codigo}. Hacienda debe firmar el RPC.',
                'area'     => 'hacienda',
                'roles'    => ['hacienda'],
                'prioridad'=> 'alta',
            ],
            EstadoProcesoCD::RPC_FIRMADO->value => [
                'titulo'   => 'RPC firmado – Radicar expediente',
                'mensaje'  => 'El RPC del proceso {codigo} fue firmado. Se debe radicar el expediente final.',
                'area'     => 'planeacion',
                'roles'    => ['planeacion', 'hacienda'],
                'prioridad'=> 'alta',
            ],
            EstadoProcesoCD::EXPEDIENTE_RADICADO->value => [
                'titulo'   => 'Expediente radicado – Iniciar ejecución',
                'mensaje'  => 'El expediente del proceso {codigo} fue radicado. La unidad solicitante debe gestionar ARL y Acta de Inicio.',
                'area'     => 'unidad_solicitante',
                'roles'    => ['unidad_solicitante'],
                'prioridad'=> 'alta',
                'notify_creator' => true,
            ],

            // ── ETAPA 7: Ejecución ──
            EstadoProcesoCD::EN_EJECUCION->value => [
                'titulo'   => 'Contrato en ejecución',
                'mensaje'  => 'El proceso {codigo} inició ejecución. El contrato está activo.',
                'area'     => 'unidad_solicitante',
                'roles'    => ['unidad_solicitante'],
                'prioridad'=> 'media',
                'notify_creator' => true,
                'notify_all_areas' => true,
            ],

            // ── Especiales ──
            EstadoProcesoCD::CANCELADO->value => [
                'titulo'   => 'Proceso cancelado',
                'mensaje'  => 'El proceso {codigo} ha sido cancelado por un administrador.',
                'area'     => 'unidad_solicitante',
                'roles'    => ['unidad_solicitante'],
                'prioridad'=> 'alta',
                'notify_creator' => true,
            ],
        ];
    }

    /**
     * Envía notificaciones a los usuarios correspondientes cuando un proceso cambia de estado.
     */
    public static function notificarTransicion(
        ProcesoContratacionDirecta $proceso,
        EstadoProcesoCD $estadoNuevo,
        User $ejecutor
    ): int {
        $config = static::configuraciones()[$estadoNuevo->value] ?? null;

        if (!$config) {
            return 0;
        }

        $titulo  = $config['titulo'];
        $mensaje = str_replace(
            ['{codigo}', '{objeto}'],
            [$proceso->codigo, \Illuminate\Support\Str::limit($proceso->objeto, 80)],
            $config['mensaje']
        );
        $url = route('proceso-cd.show', $proceso->id);
        $count = 0;

        // Buscar usuarios destinatarios por rol
        $destinatarios = static::obtenerDestinatarios($proceso, $config, $ejecutor);

        foreach ($destinatarios as $usuario) {
            // No notificar al mismo usuario que ejecutó la acción
            if ($usuario->id === $ejecutor->id) {
                continue;
            }

            Alerta::create([
                'user_id'           => $usuario->id,
                'proceso_cd_id'     => $proceso->id,
                'tipo'              => 'transicion_cd_pn',
                'titulo'            => $titulo,
                'mensaje'           => $mensaje,
                'prioridad'         => $config['prioridad'],
                'area_responsable'  => $config['area'],
                'accion_url'        => $url,
                'leida'             => false,
                'metadata'          => [
                    'estado_nuevo'  => $estadoNuevo->value,
                    'etapa'         => $estadoNuevo->etapa(),
                    'proceso_codigo'=> $proceso->codigo,
                    'ejecutor_id'   => $ejecutor->id,
                    'ejecutor_name' => $ejecutor->name,
                ],
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Notificación cuando se sube un documento.
     */
    public static function notificarDocumentoCargado(
        ProcesoContratacionDirecta $proceso,
        string $tipoDocumento,
        User $ejecutor
    ): int {
        $rolesDestino = $proceso->estado->rolesAutorizados();
        $destinatarios = User::role($rolesDestino)->where('id', '!=', $ejecutor->id)->get();

        $count = 0;
        foreach ($destinatarios as $usuario) {
            Alerta::create([
                'user_id'           => $usuario->id,
                'proceso_cd_id'     => $proceso->id,
                'tipo'              => 'documento_cd_pn',
                'titulo'            => 'Documento cargado',
                'mensaje'           => "Se cargó el documento «{$tipoDocumento}» en el proceso {$proceso->codigo}.",
                'prioridad'         => 'baja',
                'area_responsable'  => static::areaFromRoles($rolesDestino),
                'accion_url'        => route('proceso-cd.show', $proceso->id),
                'leida'             => false,
                'metadata'          => [
                    'tipo_documento' => $tipoDocumento,
                    'proceso_codigo' => $proceso->codigo,
                ],
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Notificación cuando se devuelve un proceso.
     */
    public static function notificarDevolucion(
        ProcesoContratacionDirecta $proceso,
        string $tipoDevolucion,
        string $observaciones,
        User $ejecutor
    ): int {
        $titulo = $tipoDevolucion === 'juridica'
            ? 'Proceso devuelto por Jurídica'
            : 'Contrato devuelto con observaciones';

        $roles = $tipoDevolucion === 'juridica'
            ? ['unidad_solicitante']
            : ['juridica'];

        // Siempre notificar al creador del proceso
        $destinatarioIds = User::role($roles)->pluck('id')->toArray();
        if ($proceso->creado_por && !in_array($proceso->creado_por, $destinatarioIds)) {
            $destinatarioIds[] = $proceso->creado_por;
        }

        $destinatarios = User::whereIn('id', $destinatarioIds)
            ->where('id', '!=', $ejecutor->id)
            ->get();

        $count = 0;
        foreach ($destinatarios as $usuario) {
            Alerta::create([
                'user_id'           => $usuario->id,
                'proceso_cd_id'     => $proceso->id,
                'tipo'              => 'devolucion_cd_pn',
                'titulo'            => $titulo,
                'mensaje'           => "Proceso {$proceso->codigo} devuelto: " . \Illuminate\Support\Str::limit($observaciones, 120),
                'prioridad'         => 'alta',
                'area_responsable'  => $roles[0],
                'accion_url'        => route('proceso-cd.show', $proceso->id),
                'leida'             => false,
                'metadata'          => [
                    'tipo_devolucion' => $tipoDevolucion,
                    'observaciones'   => $observaciones,
                    'proceso_codigo'  => $proceso->codigo,
                ],
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Obtiene los usuarios destinatarios según la configuración.
     */
    protected static function obtenerDestinatarios(
        ProcesoContratacionDirecta $proceso,
        array $config,
        User $ejecutor
    ): \Illuminate\Support\Collection {
        $ids = [];

        // Usuarios con los roles indicados
        $usuariosPorRol = User::role($config['roles'])->pluck('id')->toArray();
        $ids = array_merge($ids, $usuariosPorRol);

        // Notificar al creador del proceso si corresponde
        if (!empty($config['notify_creator']) && $proceso->creado_por) {
            $ids[] = $proceso->creado_por;
        }

        // Notificar supervisor y actores clave del proceso
        foreach (['supervisor_id', 'ordenador_gasto_id', 'jefe_unidad_id', 'abogado_unidad_id'] as $campo) {
            if ($proceso->$campo) {
                $ids[] = $proceso->$campo;
            }
        }

        // Si notify_all_areas, notificar a planeación, hacienda y jurídica también
        if (!empty($config['notify_all_areas'])) {
            $extras = User::role(['planeacion', 'hacienda', 'juridica'])->pluck('id')->toArray();
            $ids = array_merge($ids, $extras);
        }

        $ids = array_unique($ids);

        return User::whereIn('id', $ids)->get();
    }

    /**
     * Obtiene el nombre del área principal a partir de los roles.
     */
    protected static function areaFromRoles(array $roles): string
    {
        foreach (['planeacion', 'hacienda', 'juridica', 'unidad_solicitante', 'secop'] as $area) {
            if (in_array($area, $roles)) {
                return $area;
            }
        }
        return $roles[0] ?? 'sistema';
    }
}

