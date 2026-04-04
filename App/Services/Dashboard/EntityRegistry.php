<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\Proceso;
use App\Models\Secretaria;
use App\Models\Unidad;
use App\Models\Etapa;
use App\Models\Alerta;
use App\Models\PlanAnualAdquisicion;

/**
 * Registro de entidades disponibles para Dashboard Builder
 * Mapea nombres de entidad a modelos Eloquent
 */
class EntityRegistry
{
    /**
     * Entidades disponibles para el Dashboard Builder
     */
    const ENTITIES = [
        'procesos' => [
            'model' => Proceso::class,
            'label' => 'Procesos Contractuales',
            'fields' => [
                'id' => ['type' => 'number', 'label' => 'ID'],
                'numero_proceso' => ['type' => 'string', 'label' => 'Número de Proceso'],
                'objeto' => ['type' => 'string', 'label' => 'Objeto'],
                'estado' => ['type' => 'string', 'label' => 'Estado'],
                'valor_estimado' => ['type' => 'number', 'label' => 'Valor Estimado'],
                'tipo_contratacion' => ['type' => 'string', 'label' => 'Tipo de Contratación'],
                'created_at' => ['type' => 'date', 'label' => 'Fecha de Creación'],
                'secretaria_origen_id' => ['type' => 'number', 'label' => 'ID Secretaría'],
                'unidad_origen_id' => ['type' => 'number', 'label' => 'ID Unidad'],
            ]
        ],
        'usuarios' => [
            'model' => User::class,
            'label' => 'Usuarios del Sistema',
            'fields' => [
                'id' => ['type' => 'number', 'label' => 'ID'],
                'name' => ['type' => 'string', 'label' => 'Nombre'],
                'email' => ['type' => 'string', 'label' => 'Correo'],
                'email_verified_at' => ['type' => 'date', 'label' => 'Verificación Email'],
                'created_at' => ['type' => 'date', 'label' => 'Fecha de Creación'],
                'secretaria_id' => ['type' => 'number', 'label' => 'ID Secretaría'],
                'unidad_id' => ['type' => 'number', 'label' => 'ID Unidad'],
            ]
        ],
        'secretarias' => [
            'model' => Secretaria::class,
            'label' => 'Secretarías',
            'fields' => [
                'id' => ['type' => 'number', 'label' => 'ID'],
                'nombre' => ['type' => 'string', 'label' => 'Nombre'],
                'sigla' => ['type' => 'string', 'label' => 'Sigla'],
                'activa' => ['type' => 'boolean', 'label' => 'Activa'],
                'created_at' => ['type' => 'date', 'label' => 'Fecha de Creación'],
            ]
        ],
        'unidades' => [
            'model' => Unidad::class,
            'label' => 'Unidades',
            'fields' => [
                'id' => ['type' => 'number', 'label' => 'ID'],
                'nombre' => ['type' => 'string', 'label' => 'Nombre'],
                'activa' => ['type' => 'boolean', 'label' => 'Activa'],
                'secretaria_id' => ['type' => 'number', 'label' => 'ID Secretaría'],
                'created_at' => ['type' => 'date', 'label' => 'Fecha de Creación'],
            ]
        ],
        'etapas' => [
            'model' => Etapa::class,
            'label' => 'Etapas de Proceso',
            'fields' => [
                'id' => ['type' => 'number', 'label' => 'ID'],
                'nombre' => ['type' => 'string', 'label' => 'Nombre de Etapa'],
                'estado' => ['type' => 'string', 'label' => 'Estado'],
                'proceso_id' => ['type' => 'number', 'label' => 'ID Proceso'],
                'fecha_inicio' => ['type' => 'date', 'label' => 'Fecha de Inicio'],
                'fecha_fin' => ['type' => 'date', 'label' => 'Fecha de Fin'],
                'dias_estimados' => ['type' => 'number', 'label' => 'Días Estimados'],
                'created_at' => ['type' => 'date', 'label' => 'Fecha de Creación'],
            ]
        ],
        'alertas' => [
            'model' => Alerta::class,
            'label' => 'Alertas del Sistema',
            'fields' => [
                'id' => ['type' => 'number', 'label' => 'ID'],
                'tipo' => ['type' => 'string', 'label' => 'Tipo de Alerta'],
                'mensaje' => ['type' => 'string', 'label' => 'Mensaje'],
                'estado' => ['type' => 'string', 'label' => 'Estado'],
                'proceso_id' => ['type' => 'number', 'label' => 'ID Proceso'],
                'user_id' => ['type' => 'number', 'label' => 'ID Usuario'],
                'created_at' => ['type' => 'date', 'label' => 'Fecha de Creación'],
            ]
        ],
        'plan_anual_adquisiciones' => [
            'model' => PlanAnualAdquisicion::class,
            'label' => 'Plan Anual de Adquisiciones',
            'fields' => [
                'id' => ['type' => 'number', 'label' => 'ID'],
                'objeto_contratar' => ['type' => 'string', 'label' => 'Objeto a Contratar'],
                'modalidad_seleccion' => ['type' => 'string', 'label' => 'Modalidad de Selección'],
                'valor_estimado' => ['type' => 'number', 'label' => 'Valor Estimado'],
                'mes_inicio_proceso' => ['type' => 'number', 'label' => 'Mes de Inicio'],
                'duracion_contrato' => ['type' => 'number', 'label' => 'Duración (meses)'],
                'secretaria_id' => ['type' => 'number', 'label' => 'ID Secretaría'],
                'created_at' => ['type' => 'date', 'label' => 'Fecha de Creación'],
            ]
        ]
    ];

    /**
     * Obtiene la configuración de una entidad
     */
    public static function getEntity(string $entity): ?array
    {
        return self::ENTITIES[$entity] ?? null;
    }

    /**
     * Obtiene el modelo Eloquent de una entidad
     */
    public static function getModel(string $entity): ?string
    {
        return self::ENTITIES[$entity]['model'] ?? null;
    }

    /**
     * Lista todas las entidades disponibles
     */
    public static function all(): array
    {
        return array_map(function($entity, $key) {
            return [
                'key' => $key,
                'label' => $entity['label'],
                'fields' => $entity['fields']
            ];
        }, self::ENTITIES, array_keys(self::ENTITIES));
    }

    /**
     * Obtiene los campos disponibles de una entidad
     */
    public static function getFields(string $entity): array
    {
        return self::ENTITIES[$entity]['fields'] ?? [];
    }

    /**
     * Verifica si una entidad existe
     */
    public static function exists(string $entity): bool
    {
        return array_key_exists($entity, self::ENTITIES);
    }
}