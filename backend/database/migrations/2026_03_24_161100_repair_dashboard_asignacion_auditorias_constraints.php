<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dashboard_asignacion_auditorias')) {
            return;
        }

        $statements = [
            "ALTER TABLE dashboard_asignacion_auditorias ADD INDEX dash_aud_tipo_role_idx (tipo_objetivo, role_name)",
            "ALTER TABLE dashboard_asignacion_auditorias ADD INDEX dash_aud_tipo_user_idx (tipo_objetivo, target_user_id)",
            "ALTER TABLE dashboard_asignacion_auditorias ADD INDEX dash_aud_created_idx (created_at)",
            "ALTER TABLE dashboard_asignacion_auditorias ADD CONSTRAINT dash_aud_actor_fk FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL",
            "ALTER TABLE dashboard_asignacion_auditorias ADD CONSTRAINT dash_aud_target_fk FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL",
            "ALTER TABLE dashboard_asignacion_auditorias ADD CONSTRAINT dash_aud_prev_tpl_fk FOREIGN KEY (dashboard_plantilla_anterior_id) REFERENCES dashboard_plantillas(id) ON DELETE SET NULL",
            "ALTER TABLE dashboard_asignacion_auditorias ADD CONSTRAINT dash_aud_new_tpl_fk FOREIGN KEY (dashboard_plantilla_nueva_id) REFERENCES dashboard_plantillas(id) ON DELETE SET NULL",
        ];

        foreach ($statements as $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                // Ignorar si ya existe el indice/constraint o si el motor no permite la operación.
            }
        }
    }

    public function down(): void
    {
        // No-op para evitar errores en entornos donde constraints/indices puedan variar.
    }
};
