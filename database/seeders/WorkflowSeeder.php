<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $driver = DB::getDriverName();
        $isMysql = ($driver === 'mysql');

        // SOLO en local/testing se permite limpieza total (modo demo)
        $isDevReset = app()->environment(['local', 'testing']);

        if ($isMysql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        try {
            if ($isDevReset) {
                // Limpieza total (fresh+seed)
                $this->truncateIfExists('proceso_etapa_checks');
                $this->truncateIfExists('proceso_etapas');
                $this->truncateIfExists('etapa_items');
                $this->truncateIfExists('etapas');
                $this->truncateIfExists('procesos');
                $this->truncateIfExists('workflows');
            } else {
                // Ambiente real: NO tocar tablas operativas
                // Solo catálogo de flujos
                $this->truncateIfExists('etapa_items');
                $this->truncateIfExists('etapas');
                $this->truncateIfExists('workflows');
            }
        } finally {
            if ($isMysql) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }

        $workflows = $this->loadWorkflowsFromFiles();

        foreach ($workflows as $wf) {
            $this->seedWorkflow(
                $wf['codigo'],
                $wf['nombre'],
                (bool)($wf['activo'] ?? true),
                $wf['etapas'] ?? []
            );
        }
    }

    /**
     * Carga definiciones desde database/seeders/workflows/*.php
     */
    private function loadWorkflowsFromFiles(): array
    {
        $dir = database_path('seeders/workflows');

        $files = [
            $dir . '/CD_PN.php',
            $dir . '/MC.php',
            $dir . '/SA.php',
            $dir . '/LP.php',
            $dir . '/CM.php',
        ];

        $workflows = [];
        foreach ($files as $file) {
            if (!file_exists($file)) {
                throw new \RuntimeException("No existe el archivo de workflow: {$file}");
            }

            $wf = require $file;

            if (!is_array($wf) || empty($wf['codigo']) || empty($wf['nombre'])) {
                throw new \RuntimeException("Archivo de workflow inválido: {$file}");
            }

            // Regla: no negativos (porque etapas.orden es UNSIGNED)
            foreach (($wf['etapas'] ?? []) as $et) {
                if (isset($et['orden']) && (int)$et['orden'] < 0) {
                    throw new \RuntimeException("Orden negativo no permitido en workflow {$wf['codigo']}");
                }
            }

            $workflows[] = $wf;
        }

        return $workflows;
    }

    /**
     * Se deja igual: crea workflow, etapas, items y enlaza next_etapa_id por orden.
     */
    private function seedWorkflow(string $codigo, string $nombre, bool $activo, array $etapas): void
    {
        $workflowId = DB::table('workflows')->insertGetId([
            'codigo'     => $codigo,
            'nombre'     => $nombre,
            'activo'     => $activo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $insertedEtapas = [];

        foreach ($etapas as $etapaData) {
            $etapaId = DB::table('etapas')->insertGetId([
                'workflow_id'   => $workflowId,
                'orden'         => (int)$etapaData['orden'],
                'nombre'        => $etapaData['nombre'],
                'area_role'     => $etapaData['area_role'],
                'next_etapa_id' => null,
                'activa'        => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $insertedEtapas[] = ['orden' => (int)$etapaData['orden'], 'id' => $etapaId];

            $ordenItem = 1;
            foreach (($etapaData['items'] ?? []) as $label) {
                DB::table('etapa_items')->insert([
                    'etapa_id'   => $etapaId,
                    'orden'      => $ordenItem++,
                    'label'      => $label,
                    'requerido'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        usort($insertedEtapas, fn($a, $b) => $a['orden'] <=> $b['orden']);

        $count = count($insertedEtapas);
        for ($i = 0; $i < $count; $i++) {
            $currentId = $insertedEtapas[$i]['id'];
            $nextId    = ($i < $count - 1) ? $insertedEtapas[$i + 1]['id'] : null;

            DB::table('etapas')->where('id', $currentId)->update([
                'next_etapa_id' => $nextId,
                'updated_at'    => now(),
            ]);
        }
    }

    private function truncateIfExists(string $table): void
    {
        $driver = DB::getDriverName();

        try {
            if ($driver === 'sqlite') {
                DB::table($table)->delete();
            } else {
                DB::table($table)->truncate();
            }
        } catch (\Throwable $e) {
            // Si no existe, no hacemos nada
        }
    }
}
