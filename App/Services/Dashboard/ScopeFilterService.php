<?php

namespace App\Services\Dashboard;

use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ScopeFilterService
{
    /**
     * Niveles de scope definidos por propiedades del rol, no por nombre.
     * Esto permite que el sistema se adapte automáticamente a nuevos roles.
     */
    const SCOPE_LEVELS = [
        'global' => 1,       // Ve todo (admin, gobernador)
        'secretaria' => 2,   // Ve su secretaría completa
        'unidad' => 3,       // Ve solo su unidad
        'usuario' => 4,      // Ve solo lo que creó
    ];

    /**
     * Aplica filtros de scope automáticamente según el rol del usuario.
     * ESTOS FILTROS NO PUEDEN SER MODIFICADOS DESDE FRONTEND.
     */
    public function applyUserScope(Builder $query, User $user, array $entityConfig): Builder
    {
        $scopeLevel = $this->resolveUserScopeLevel($user);

        // Admin global no tiene restricciones
        if ($scopeLevel === 'global') {
            return $query;
        }

        $scopeField = $entityConfig['scope_field'] ?? null;
        $unitField = $entityConfig['unit_field'] ?? null;
        $userField = $entityConfig['user_field'] ?? null;

        switch ($scopeLevel) {
            case 'secretaria':
                if ($scopeField && $user->secretaria_id) {
                    $query->where($scopeField, $user->secretaria_id);
                }
                break;

            case 'unidad':
                if ($unitField && $user->unidad_id) {
                    $query->where($unitField, $user->unidad_id);
                } elseif ($scopeField && $user->secretaria_id) {
                    // Fallback a secretaría si no hay campo de unidad
                    $query->where($scopeField, $user->secretaria_id);
                }
                break;

            case 'usuario':
                if ($userField) {
                    $query->where($userField, $user->id);
                } elseif ($unitField && $user->unidad_id) {
                    // Fallback a unidad
                    $query->where($unitField, $user->unidad_id);
                } elseif ($scopeField && $user->secretaria_id) {
                    // Fallback a secretaría
                    $query->where($scopeField, $user->secretaria_id);
                }
                break;
        }

        return $query;
    }

    /**
     * Resuelve el nivel de scope del usuario basándose en propiedades del rol,
     * NO en el nombre del rol directamente.
     */
    public function resolveUserScopeLevel(User $user): string
    {
        // Cache por 5 minutos para evitar consultas repetidas
        $cacheKey = "user_scope_level_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            // Obtener la configuración de scope del rol
            $roleScopeConfig = $this->getRoleScopeConfig();

            // Buscar el scope más permisivo entre todos los roles del usuario
            $userRoles = $user->getRoleNames()->toArray();
            $highestScope = 'usuario'; // Default más restrictivo
            $highestPriority = self::SCOPE_LEVELS['usuario'];

            foreach ($userRoles as $roleName) {
                $roleConfig = $roleScopeConfig[$roleName] ?? null;

                if ($roleConfig) {
                    $scopeLevel = $roleConfig['scope_level'];
                    $priority = self::SCOPE_LEVELS[$scopeLevel] ?? 4;

                    // Menor número = mayor permiso
                    if ($priority < $highestPriority) {
                        $highestPriority = $priority;
                        $highestScope = $scopeLevel;
                    }
                }
            }

            return $highestScope;
        });
    }

    /**
     * Configuración de scope por rol.
     * Esta configuración se puede mover a base de datos para hacerla dinámica.
     */
    protected function getRoleScopeConfig(): array
    {
        // Intentar cargar desde base de datos primero
        $dbConfig = $this->loadScopeConfigFromDatabase();
        if (!empty($dbConfig)) {
            return $dbConfig;
        }

        // Configuración por defecto basada en propiedades, no en nombres
        return [
            // Roles con acceso global (scope_level: global)
            'super_admin' => ['scope_level' => 'global', 'can_override' => true],
            'admin' => ['scope_level' => 'global', 'can_override' => true],
            'admin_general' => ['scope_level' => 'global', 'can_override' => true],
            'gobernador' => ['scope_level' => 'global', 'can_override' => false],
            'auditor_interno' => ['scope_level' => 'global', 'can_override' => false],

            // Roles con acceso a secretaría (scope_level: secretaria)
            'secretario' => ['scope_level' => 'secretaria', 'can_override' => false],
            'admin_secretaria' => ['scope_level' => 'secretaria', 'can_override' => true],
            'coord_contratacion' => ['scope_level' => 'secretaria', 'can_override' => false],

            // Roles con acceso a unidad (scope_level: unidad)
            'jefe_unidad' => ['scope_level' => 'unidad', 'can_override' => false],
            'admin_unidad' => ['scope_level' => 'unidad', 'can_override' => true],
            'profesional_contratacion' => ['scope_level' => 'unidad', 'can_override' => false],
            'abogado_unidad' => ['scope_level' => 'unidad', 'can_override' => false],

            // Roles operativos con acceso por secretaría
            'planeacion' => ['scope_level' => 'secretaria', 'can_override' => false],
            'hacienda' => ['scope_level' => 'secretaria', 'can_override' => false],
            'juridica' => ['scope_level' => 'secretaria', 'can_override' => false],
            'secop' => ['scope_level' => 'global', 'can_override' => false],
            'talento_humano' => ['scope_level' => 'secretaria', 'can_override' => false],
            'presupuesto' => ['scope_level' => 'secretaria', 'can_override' => false],
            'contabilidad' => ['scope_level' => 'secretaria', 'can_override' => false],
            'rentas' => ['scope_level' => 'secretaria', 'can_override' => false],

            // Roles con acceso limitado a usuario
            'unidad_solicitante' => ['scope_level' => 'unidad', 'can_override' => false],
            'revisor_juridico' => ['scope_level' => 'secretaria', 'can_override' => false],
            'consulta' => ['scope_level' => 'secretaria', 'can_override' => false],
            'consulta_ciudadana' => ['scope_level' => 'usuario', 'can_override' => false],
        ];
    }

    /**
     * Cargar configuración de scope desde base de datos.
     * Esto permite agregar nuevos roles sin modificar código.
     */
    protected function loadScopeConfigFromDatabase(): array
    {
        try {
            // Usar la tabla role_scope_configs si existe
            if (\Schema::hasTable('role_scope_configs')) {
                $configs = \DB::table('role_scope_configs')
                    ->where('activo', true)
                    ->get();

                $result = [];
                foreach ($configs as $config) {
                    $result[$config->role_name] = [
                        'scope_level' => $config->scope_level,
                        'can_override' => (bool)$config->can_override,
                    ];
                }
                return $result;
            }
        } catch (\Exception $e) {
            // Silenciar errores y usar config por defecto
        }

        return [];
    }

    /**
     * Obtener información del scope actual del usuario (para debug/UI).
     */
    public function getUserScopeInfo(User $user): array
    {
        $scopeLevel = $this->resolveUserScopeLevel($user);
        $roleScopeConfig = $this->getRoleScopeConfig();

        return [
            'scope_level' => $scopeLevel,
            'scope_priority' => self::SCOPE_LEVELS[$scopeLevel] ?? 4,
            'secretaria_id' => $user->secretaria_id,
            'secretaria_nombre' => $user->secretaria?->nombre,
            'unidad_id' => $user->unidad_id,
            'unidad_nombre' => $user->unidad?->nombre,
            'roles' => $user->getRoleNames()->toArray(),
            'role_configs' => array_filter(
                $roleScopeConfig,
                fn($k) => in_array($k, $user->getRoleNames()->toArray()),
                ARRAY_FILTER_USE_KEY
            ),
            'description' => $this->getScopeDescription($scopeLevel, $user),
        ];
    }

    /**
     * Descripción legible del scope.
     */
    protected function getScopeDescription(string $scopeLevel, User $user): string
    {
        return match ($scopeLevel) {
            'global' => 'Acceso completo a todos los datos del sistema',
            'secretaria' => $user->secretaria
                ? "Acceso a datos de la Secretaría: {$user->secretaria->nombre}"
                : 'Acceso a datos de su secretaría',
            'unidad' => $user->unidad
                ? "Acceso a datos de la Unidad: {$user->unidad->nombre}"
                : 'Acceso a datos de su unidad',
            'usuario' => 'Acceso solo a datos creados por usted',
            default => 'Acceso restringido',
        };
    }

    /**
     * Limpiar cache de scope del usuario (llamar cuando cambian roles).
     */
    public function clearUserScopeCache(User $user): void
    {
        Cache::forget("user_scope_level_{$user->id}");
    }

    /**
     * Verificar si el usuario puede ver datos de una secretaría específica.
     */
    public function canAccessSecretaria(User $user, int $secretariaId): bool
    {
        $scopeLevel = $this->resolveUserScopeLevel($user);

        if ($scopeLevel === 'global') {
            return true;
        }

        if ($scopeLevel === 'secretaria' || $scopeLevel === 'unidad' || $scopeLevel === 'usuario') {
            return $user->secretaria_id === $secretariaId;
        }

        return false;
    }

    /**
     * Verificar si el usuario puede ver datos de una unidad específica.
     */
    public function canAccessUnidad(User $user, int $unidadId): bool
    {
        $scopeLevel = $this->resolveUserScopeLevel($user);

        if ($scopeLevel === 'global') {
            return true;
        }

        if ($scopeLevel === 'secretaria') {
            // Puede ver cualquier unidad de su secretaría
            $unidad = \App\Models\Unidad::find($unidadId);
            return $unidad && $unidad->secretaria_id === $user->secretaria_id;
        }

        if ($scopeLevel === 'unidad' || $scopeLevel === 'usuario') {
            return $user->unidad_id === $unidadId;
        }

        return false;
    }
}
