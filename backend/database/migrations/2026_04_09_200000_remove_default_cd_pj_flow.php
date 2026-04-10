<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        // Motor de flujos (flujos)
        $flujoIds = DB::table('flujos')->where('codigo', 'CD_PJ')->pluck('id');
        if ($flujoIds->isNotEmpty()) {
            $versionIds = DB::table('flujo_versiones')->whereIn('flujo_id', $flujoIds)->pluck('id');
            $pasoIds = DB::table('flujo_pasos')->whereIn('flujo_version_id', $versionIds)->pluck('id');
            $instanciaIds = DB::table('flujo_instancias')->whereIn('flujo_id', $flujoIds)->pluck('id');

            DB::table('procesos')->whereIn('flujo_id', $flujoIds)->update(['flujo_id' => null]);

            DB::table('flujo_instancia_docs')->whereIn('flujo_instancia_id', $instanciaIds)->delete();
            DB::table('flujo_instancia_pasos')->whereIn('flujo_instancia_id', $instanciaIds)->delete();
            DB::table('flujo_instancias')->whereIn('id', $instanciaIds)->delete();

            DB::table('flujo_paso_responsables')->whereIn('flujo_paso_id', $pasoIds)->delete();
            DB::table('flujo_paso_documentos')->whereIn('flujo_paso_id', $pasoIds)->delete();
            DB::table('flujo_paso_condiciones')->whereIn('flujo_paso_id', $pasoIds)->delete();
            DB::table('flujo_pasos')->whereIn('id', $pasoIds)->delete();
            DB::table('flujo_versiones')->whereIn('id', $versionIds)->delete();
            DB::table('flujos')->whereIn('id', $flujoIds)->delete();
        }

        // Workflow legacy (workflows)
        $workflowIds = DB::table('workflows')->where('codigo', 'CD_PJ')->pluck('id');
        if ($workflowIds->isNotEmpty()) {
            DB::table('procesos')->whereIn('workflow_id', $workflowIds)->update([
                'workflow_id' => null,
                'etapa_actual_id' => null,
            ]);

            $etapaIds = DB::table('etapas')->whereIn('workflow_id', $workflowIds)->pluck('id');
            $procesoEtapaIds = DB::table('proceso_etapas')->whereIn('etapa_id', $etapaIds)->pluck('id');

            DB::table('proceso_etapa_checks')->whereIn('proceso_etapa_id', $procesoEtapaIds)->delete();
            DB::table('proceso_etapa_archivos')->whereIn('proceso_etapa_id', $procesoEtapaIds)->delete();
            DB::table('proceso_etapas')->whereIn('id', $procesoEtapaIds)->delete();

            DB::table('etapa_items')->whereIn('etapa_id', $etapaIds)->delete();
            DB::table('etapas')->whereIn('id', $etapaIds)->delete();
            DB::table('workflows')->whereIn('id', $workflowIds)->delete();
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }
    }

    public function down(): void
    {
        // No-op: el flujo CD_PJ se puede crear manualmente cuando se habilite.
    }
};
