<?php

namespace App\Services\Dashboard;

use App\Models\User;

/**
 * Resuelve filtros de roles para queries del Dashboard Builder
 * CRÍTICO: Los filtros de rol NO pueden bypassearse desde frontend
 */
class RoleFilterResolver
{
    /**
     * Aplica filtros obligatorios según el nivel de scope del rol del usuario
     */
    public static function applyRoleFilters($query, string $entity, User $user): void
    {
        $role = $user->roles()->first();
        if (!$role) return;

        $scopeLevel = $role->scope_level ?? 'unidad';

        switch ($scopeLevel) {
            case 'global':
                // admin, admin_general, gobernador - sin filtros
                break;

            case 'secretaria':
                // admin_secretaria, secretario, planeacion, secop
                self::filterBySecretaria($query, $entity, $user->secretaria_id);
                break;

            case 'unidad':
            default:
                // resto de roles - filtrar por unidad
                self::filterByUnidad($query, $entity, $user->unidad_id);
                break;
        }
    }

    /**
     * Filtra query por secretaria
     */
    private static function filterBySecretaria($query, string $entity, ?int $secretariaId): void
    {
        if (!$secretariaId) return;

        switch ($entity) {
            case 'procesos':
                $query->where('secretaria_origen_id', $secretariaId);
                break;
            case 'usuarios':
                $query->where('secretaria_id', $secretariaId);
                break;
            case 'unidades':
                $query->where('secretaria_id', $secretariaId);
                break;
            case 'secretarias':
                $query->where('id', $secretariaId);
                break;
            case 'etapas':
            case 'alertas':
                $query->whereHas('proceso', function($q) use ($secretariaId) {
                    $q->where('secretaria_origen_id', $secretariaId);
                });
                break;
            case 'plan_anual_adquisiciones':
                $query->where('secretaria_id', $secretariaId);
                break;
        }
    }

    /**
     * Filtra query por unidad
     */
    private static function filterByUnidad($query, string $entity, ?int $unidadId): void
    {
        if (!$unidadId) return;

        switch ($entity) {
            case 'procesos':
                $query->where('unidad_origen_id', $unidadId);
                break;
            case 'usuarios':
                $query->where('unidad_id', $unidadId);
                break;
            case 'unidades':
                $query->where('id', $unidadId);
                break;
            case 'secretarias':
                $query->whereHas('unidades', function($q) use ($unidadId) {
                    $q->where('id', $unidadId);
                });
                break;
            case 'etapas':
            case 'alertas':
                $query->whereHas('proceso', function($q) use ($unidadId) {
                    $q->where('unidad_origen_id', $unidadId);
                });
                break;
            case 'plan_anual_adquisiciones':
                $query->whereHas('unidad', function($q) use ($unidadId) {
                    $q->where('id', $unidadId);
                });
                break;
        }
    }
}