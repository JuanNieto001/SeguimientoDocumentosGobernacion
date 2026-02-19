<?php

namespace App\Support;

/**
 * RoleLabels
 *
 * Centraliza los nombres en español de los roles del sistema.
 * Los nombres INTERNOS (claves) son los identificadores que usa
 * el código (hasRole, middleware, area_actual_role en BD, etc.)
 * y NO deben cambiarse. Los valores son solo para mostrar en la UI.
 */
class RoleLabels
{
    /**
     * Mapa de nombre interno → etiqueta en español para la interfaz.
     *
     * Roles del sistema:
     *  - admin              : Acceso total. Puede gestionar usuarios, roles, permisos,
     *                         ver todos los procesos y el dashboard administrativo.
     *  - unidad_solicitante : Área que inicia el proceso. Crea solicitudes, carga
     *                         estudios previos y cotizaciones, y envía a Planeación.
     *  - planeacion         : Secretaría de Planeación. Verifica el PAA, gestiona
     *                         CDPs y revisa/aprueba documentos del proceso.
     *  - hacienda           : Secretaría de Hacienda. Emite CDP, RP y viabilidad
     *                         económica. Aprueba la disponibilidad presupuestal.
     *  - juridica           : Área Jurídica. Verifica contratistas, emite el
     *                         Ajustado a Derecho, aprueba pólizas y firma contratos.
     *  - secop              : Gestión SECOP II. Publica el proceso, registra el
     *                         contrato electrónico y genera el acta de inicio.
     */
    public const LABELS = [
        'admin'                     => 'Administrador',
        'admin_general'             => 'Administrador General',
        'admin_secretaria'          => 'Administrador de Secretaría',
        'profesional_contratacion'  => 'Profesional de Contratación',
        'revisor_juridico'          => 'Revisor Jurídico',
        'consulta'                  => 'Consulta',
        'unidad_solicitante'        => 'Unidad Solicitante',
        'planeacion'                => 'Planeación',
        'hacienda'                  => 'Hacienda',
        'juridica'                  => 'Jurídica',
        'secop'                     => 'SECOP',
    ];

    /**
     * Retorna la etiqueta en español de un rol.
     * Si no tiene traducción definida, devuelve el nombre interno sin cambios.
     */
    public static function label(string $internalName): string
    {
        return self::LABELS[$internalName] ?? $internalName;
    }

    /**
     * Retorna todos los roles con su etiqueta en español,
     * útil para construir selects en las vistas.
     *
     * @return array<string, string>  ['nombre_interno' => 'Etiqueta en español']
     */
    public static function all(): array
    {
        return self::LABELS;
    }
}
