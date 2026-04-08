<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Scope de secretarias en Motor de Flujos
    |--------------------------------------------------------------------------
    | roles: respeta roles/permisos para decidir quien puede ver todas.
    | all: cualquier usuario autorizado al motor puede ver todas.
    */
    'secretaria_scope' => env('MOTOR_FLUJOS_SECRETARIA_SCOPE', 'roles'),

    /*
    |--------------------------------------------------------------------------
    | Roles con acceso a todas las secretarias
    |--------------------------------------------------------------------------
    */
    'roles_ver_todas_secretarias' => array_values(array_filter(array_map(
        static fn ($value) => trim($value),
        explode(',', env('MOTOR_FLUJOS_ROLES_VER_TODAS_SECRETARIAS', 'admin,admin_general,super_admin'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Permiso opcional para ver todas las secretarias
    |--------------------------------------------------------------------------
    */
    'permission_ver_todas_secretarias' => env('MOTOR_FLUJOS_PERMISSION_VER_TODAS_SECRETARIAS', 'secretarias.ver_todas'),
];
