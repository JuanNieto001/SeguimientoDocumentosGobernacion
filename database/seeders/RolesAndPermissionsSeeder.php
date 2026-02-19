<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia caché de permisos para evitar conflictos al re-ejecutar el seeder
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | PERMISOS DEL SISTEMA
        |--------------------------------------------------------------------------
        | Convención de nombres: recurso.accion  (todo en minúsculas con puntos)
        | Los permisos se agrupan visualmente en la UI por el prefijo (antes del punto).
        |--------------------------------------------------------------------------
        */
        $permissions = [

            // ─── SECRETARÍAS Y UNIDADES ──────────────────────────────────────────────
            'secretarias.ver',
            'secretarias.crear',
            'secretarias.editar',
            'secretarias.eliminar',
            'unidades.ver',
            'unidades.crear',
            'unidades.editar',
            'unidades.eliminar',

            // ─── USUARIOS ────────────────────────────────────────────────────────────
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',

            // ─── ROLES ───────────────────────────────────────────────────────────────
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',

            // ─── PERMISOS ────────────────────────────────────────────────────────────
            'permisos.ver',
            'permisos.crear',
            'permisos.editar',
            'permisos.eliminar',

            // ─── PROCESOS ────────────────────────────────────────────────────────────
            'procesos.ver',
            'procesos.crear',
            'procesos.editar',
            'procesos.recibir',
            'procesos.enviar',
            'procesos.rechazar',
            'procesos.aprobar',

            // ─── ARCHIVOS Y DOCUMENTOS ───────────────────────────────────────────────
            'archivos.subir',
            'archivos.descargar',
            'archivos.eliminar',
            'archivos.aprobar',
            'archivos.rechazar',
            'archivos.reemplazar',

            // ─── PAA (PLAN ANUAL DE ADQUISICIONES) ───────────────────────────────────
            'paa.ver',
            'paa.crear',
            'paa.editar',
            'paa.verificar',
            'paa.certificado',
            'paa.exportar',

            // ─── ALERTAS ─────────────────────────────────────────────────────────────
            'alertas.ver',
            'alertas.leer',
            'alertas.leer.todas',
            'alertas.eliminar',

            // ─── REPORTES Y ESTADÍSTICAS ─────────────────────────────────────────────
            'reportes.ver',
            'reportes.estado_general',
            'reportes.por_dependencia',
            'reportes.actividad_actor',
            'reportes.auditoria',
            'reportes.certificados_vencer',
            'reportes.eficiencia',

            // ─── MODIFICACIONES CONTRACTUALES ────────────────────────────────────────
            'modificaciones.ver',
            'modificaciones.crear',
            'modificaciones.aprobar',
            'modificaciones.rechazar',
            'modificaciones.descargar',

            // ─── DASHBOARD ───────────────────────────────────────────────────────────
            'dashboard.ver',
            'dashboard.admin',
            'dashboard.buscar',

            // ─── ASIGNAR ROLES ───────────────────────────────────────────────────────
            'asignar_roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        /*
        |--------------------------------------------------------------------------
        | ROLES DEL SISTEMA
        |--------------------------------------------------------------------------
        | Roles originales del workflow + nuevos roles de la estructura jerárquica
        |--------------------------------------------------------------------------
        */

        // --- Roles originales del workflow (se mantienen) ---
        $workflowRoles = [
            'admin',
            'unidad_solicitante',
            'planeacion',
            'hacienda',
            'juridica',
            'secop',
            'talento_humano',  // Nuevo rol para Talento Humano (Certificado No Planta)
        ];

        foreach ($workflowRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // --- Nuevos roles de la estructura jerárquica ---
        $nuevosRoles = [
            'admin_general'             => 'Administrador General – Acceso total al sistema',
            'admin_secretaria'          => 'Administrador de Secretaría – Gestiona su secretaría',
            'profesional_contratacion'  => 'Profesional de Contratación – Crea y edita procesos',
            'revisor_juridico'          => 'Revisor Jurídico – Aprueba/rechaza procesos',
            'consulta'                  => 'Consulta – Solo lectura',
        ];

        foreach ($nuevosRoles as $roleName => $description) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        /*
        |--------------------------------------------------------------------------
        | ASIGNACIÓN DE PERMISOS POR ROL
        |--------------------------------------------------------------------------
        */

        // ── admin (original) → TODOS los permisos ───────────────────────────────
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions($permissions);
        }

        // ── admin_general → TODOS los permisos ──────────────────────────────────
        $adminGeneral = Role::where('name', 'admin_general')->first();
        if ($adminGeneral) {
            $adminGeneral->syncPermissions($permissions);
        }

        // ── admin_secretaria → Gestión dentro de su secretaría ───────────────────
        $adminSecretaria = Role::where('name', 'admin_secretaria')->first();
        if ($adminSecretaria) {
            $adminSecretaria->syncPermissions([
                'secretarias.ver',
                'unidades.ver',
                'usuarios.ver',
                'usuarios.crear',
                'usuarios.editar',
                'roles.ver',
                'procesos.ver',
                'procesos.crear',
                'procesos.editar',
                'archivos.subir',
                'archivos.descargar',
                'alertas.ver',
                'alertas.leer',
                'alertas.leer.todas',
                'reportes.ver',
                'reportes.estado_general',
                'reportes.por_dependencia',
                'dashboard.ver',
                'dashboard.buscar',
                'asignar_roles',
            ]);
        }

        // ── profesional_contratacion → Crea procesos, edita documentos ───────────
        $profesional = Role::where('name', 'profesional_contratacion')->first();
        if ($profesional) {
            $profesional->syncPermissions([
                'procesos.ver',
                'procesos.crear',
                'procesos.editar',
                'procesos.enviar',
                'archivos.subir',
                'archivos.descargar',
                'archivos.reemplazar',
                'paa.ver',
                'paa.verificar',
                'alertas.ver',
                'alertas.leer',
                'modificaciones.ver',
                'modificaciones.crear',
                'dashboard.ver',
                'dashboard.buscar',
                'unidades.ver',
            ]);
        }

        // ── revisor_juridico → Solo procesos asignados, aprobar/rechazar ─────────
        $revisor = Role::where('name', 'revisor_juridico')->first();
        if ($revisor) {
            $revisor->syncPermissions([
                'procesos.ver',
                'procesos.aprobar',
                'procesos.rechazar',
                'procesos.recibir',
                'procesos.enviar',
                'archivos.descargar',
                'archivos.aprobar',
                'archivos.rechazar',
                'modificaciones.ver',
                'modificaciones.aprobar',
                'modificaciones.rechazar',
                'modificaciones.descargar',
                'alertas.ver',
                'alertas.leer',
                'reportes.ver',
                'reportes.auditoria',
                'dashboard.ver',
            ]);
        }

        // ── consulta → Solo lectura ──────────────────────────────────────────────
        $consulta = Role::where('name', 'consulta')->first();
        if ($consulta) {
            $consulta->syncPermissions([
                'secretarias.ver',
                'unidades.ver',
                'procesos.ver',
                'archivos.descargar',
                'paa.ver',
                'alertas.ver',
                'reportes.ver',
                'reportes.estado_general',
                'reportes.por_dependencia',
                'modificaciones.ver',
                'modificaciones.descargar',
                'dashboard.ver',
            ]);
        }

        // ── ROLES DE WORKFLOW → Permisos específicos por rol ─────────────────────

        // unidad_solicitante → Crea procesos, gestiona documentos
        $unidadSolicitante = Role::where('name', 'unidad_solicitante')->first();
        if ($unidadSolicitante) {
            $unidadSolicitante->syncPermissions([
                'procesos.ver',
                'procesos.crear',
                'procesos.editar',
                'procesos.enviar',
                'archivos.subir',
                'archivos.descargar',
                'archivos.reemplazar',
                'paa.ver',
                'alertas.ver',
                'alertas.leer',
                'modificaciones.ver',
                'modificaciones.crear',
                'dashboard.ver',
            ]);
        }

        // planeacion → Gestiona solicitudes de documentos, RPC
        $planeacionRole = Role::where('name', 'planeacion')->first();
        if ($planeacionRole) {
            $planeacionRole->syncPermissions([
                'procesos.ver',
                'procesos.recibir',
                'procesos.enviar',
                'archivos.subir',
                'archivos.descargar',
                'paa.ver',
                'paa.verificar',
                'alertas.ver',
                'alertas.leer',
                'dashboard.ver',
            ]);
        }

        // hacienda → Expide CDP, RPC, paz y salvos
        $haciendaRole = Role::where('name', 'hacienda')->first();
        if ($haciendaRole) {
            $haciendaRole->syncPermissions([
                'procesos.ver',
                'procesos.recibir',
                'archivos.subir',
                'archivos.descargar',
                'alertas.ver',
                'alertas.leer',
                'dashboard.ver',
            ]);
        }

        // juridica → Revisa, aprueba, asigna número de contrato
        $juridicaRole = Role::where('name', 'juridica')->first();
        if ($juridicaRole) {
            $juridicaRole->syncPermissions([
                'procesos.ver',
                'procesos.recibir',
                'procesos.aprobar',
                'procesos.rechazar',
                'procesos.enviar',
                'archivos.subir',
                'archivos.descargar',
                'archivos.aprobar',
                'archivos.rechazar',
                'modificaciones.ver',
                'modificaciones.aprobar',
                'modificaciones.rechazar',
                'alertas.ver',
                'alertas.leer',
                'reportes.ver',
                'reportes.auditoria',
                'dashboard.ver',
            ]);
        }

        // secop → Publica en SECOP II, gestiona PAA
        $secopRole = Role::where('name', 'secop')->first();
        if ($secopRole) {
            $secopRole->syncPermissions([
                'procesos.ver',
                'procesos.recibir',
                'procesos.enviar',
                'archivos.subir',
                'archivos.descargar',
                'paa.ver',
                'paa.crear',
                'paa.editar',
                'paa.certificado',
                'alertas.ver',
                'alertas.leer',
                'dashboard.ver',
            ]);
        }

        // talento_humano → Expide certificado No Planta
        $talentoHumano = Role::where('name', 'talento_humano')->first();
        if ($talentoHumano) {
            $talentoHumano->syncPermissions([
                'procesos.ver',
                'procesos.recibir',
                'archivos.subir',
                'archivos.descargar',
                'alertas.ver',
                'alertas.leer',
                'dashboard.ver',
            ]);
        }

        $this->command->info('✅ Permisos y roles del sistema creados correctamente.');
    }
}