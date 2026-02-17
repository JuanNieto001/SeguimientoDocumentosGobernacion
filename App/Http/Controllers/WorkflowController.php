<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    private function loadProcesoOrFail(int $procesoId)
    {
        $proceso = DB::table('procesos')->where('id', $procesoId)->first();
        abort_unless($proceso, 404, 'Proceso no encontrado.');
        return $proceso;
    }

    private function authorizeAreaOrAdmin($proceso): void
    {
        $user = auth()->user();
        if ($user && $user->hasRole('admin')) return;

        abort_unless(
            $proceso->area_actual_role && $user && $user->hasRole($proceso->area_actual_role),
            403
        );
    }

    private function getProcesoEtapaActual($proceso)
    {
        $procesoEtapa = DB::table('proceso_etapas')
            ->where('proceso_id', $proceso->id)
            ->where('etapa_id', $proceso->etapa_actual_id)
            ->first();

        if ($procesoEtapa) return $procesoEtapa;

        $id = DB::table('proceso_etapas')->insertGetId([
            'proceso_id' => $proceso->id,
            'etapa_id'   => $proceso->etapa_actual_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('proceso_etapas')->where('id', $id)->first();
    }

    private function seedChecksSiFaltan($procesoEtapaId, $etapaId): void
    {
        $count = DB::table('proceso_etapa_checks')
            ->where('proceso_etapa_id', $procesoEtapaId)
            ->count();

        if ($count > 0) return;

        $items = DB::table('etapa_items')
            ->where('etapa_id', $etapaId)
            ->orderBy('orden')
            ->get(['id']);

        foreach ($items as $item) {
            DB::table('proceso_etapa_checks')->insert([
                'proceso_etapa_id' => $procesoEtapaId,
                'etapa_item_id'    => $item->id,
                'checked'          => false,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    /**
     * Unidad: exigir archivos mínimos antes de enviar
     * tipos permitidos: formato_necesidades | borrador_estudios | anexo
     * regla mínima: 1 borrador + 1 formato_necesidades
     */
    private function validateArchivosUnidad(int $procesoEtapaId): void
    {
        $tieneBorrador = DB::table('proceso_etapa_archivos')
            ->where('proceso_etapa_id', $procesoEtapaId)
            ->where('tipo', 'borrador_estudios')
            ->exists();

        $tieneFormato = DB::table('proceso_etapa_archivos')
            ->where('proceso_etapa_id', $procesoEtapaId)
            ->where('tipo', 'formato_necesidades')
            ->exists();

        abort_unless(
            $tieneBorrador && $tieneFormato,
            422,
            'No puedes enviar: debes cargar el borrador de estudios previos y el formato de necesidades.'
        );
    }

    public function recibir(Request $request, int $proceso)
    {
        $proceso = $this->loadProcesoOrFail($proceso);
        $this->authorizeAreaOrAdmin($proceso);

        // Unidad NO usa recibido por ahora
        if ($proceso->area_actual_role === 'unidad_solicitante') {
            return back()->with('success', 'Unidad no usa "Recibí" por ahora.');
        }

        return DB::transaction(function () use ($proceso) {
            $procesoEtapa = $this->getProcesoEtapaActual($proceso);

            $this->seedChecksSiFaltan($procesoEtapa->id, $proceso->etapa_actual_id);

            if (!$procesoEtapa->recibido) {
                DB::table('proceso_etapas')->where('id', $procesoEtapa->id)->update([
                    'recibido'     => true,
                    'recibido_por' => auth()->id(),
                    'recibido_at'  => now(),
                    'updated_at'   => now(),
                ]);
            }

            return back()->with('success', 'Documento recibido.');
        });
    }

    public function toggleCheck(Request $request, int $proceso, int $check)
    {
        $proceso = $this->loadProcesoOrFail($proceso);
        $this->authorizeAreaOrAdmin($proceso);

        // Unidad NO usa checklist por ahora
        abort_unless(
            $proceso->area_actual_role !== 'unidad_solicitante',
            403,
            'Unidad no usa checklist por ahora.'
        );

        return DB::transaction(function () use ($proceso, $check) {
            $procesoEtapa = $this->getProcesoEtapaActual($proceso);

            $row = DB::table('proceso_etapa_checks')
                ->where('id', $check)
                ->where('proceso_etapa_id', $procesoEtapa->id)
                ->first();

            abort_unless($row, 404, 'Check no encontrado para esta etapa.');

            $newValue = !$row->checked;

            DB::table('proceso_etapa_checks')->where('id', $check)->update([
                'checked'    => $newValue,
                'checked_by' => auth()->id(),
                'checked_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', $newValue ? 'Check marcado.' : 'Check desmarcado.');
        });
    }

    public function enviar(Request $request, int $proceso)
    {
        $proceso = $this->loadProcesoOrFail($proceso);
        $this->authorizeAreaOrAdmin($proceso);

        return DB::transaction(function () use ($proceso) {
            $procesoEtapa = $this->getProcesoEtapaActual($proceso);

            // UNIDAD: NO recibido, NO checklist. Solo archivos mínimos.
            if ($proceso->area_actual_role === 'unidad_solicitante') {
                $this->validateArchivosUnidad($procesoEtapa->id);
            } else {
                // OTRAS ÁREAS: reglas normales
                abort_unless((bool)$procesoEtapa->recibido, 422, 'No puedes enviar: primero debes marcar "Recibí".');

                $faltantes = DB::table('proceso_etapa_checks as pec')
                    ->join('etapa_items as ei', 'ei.id', '=', 'pec.etapa_item_id')
                    ->where('pec.proceso_etapa_id', $procesoEtapa->id)
                    ->where('ei.requerido', 1)
                    ->where('pec.checked', 0)
                    ->count();

                abort_unless($faltantes === 0, 422, 'No puedes enviar: faltan checks requeridos.');

                if (!$procesoEtapa->enviado) {
                    DB::table('proceso_etapas')->where('id', $procesoEtapa->id)->update([
                        'enviado'     => true,
                        'enviado_por' => auth()->id(),
                        'enviado_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }

            // Etapa actual y siguiente
            $etapaActual = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
            abort_unless($etapaActual, 422, 'Etapa actual inválida.');
            abort_unless((int)$etapaActual->workflow_id === (int)$proceso->workflow_id, 422, 'Inconsistencia: workflow no coincide.');

            $nextEtapaId = $etapaActual->next_etapa_id;

            // FINALIZAR
            if (!$nextEtapaId) {
                DB::table('procesos')->where('id', $proceso->id)->update([
                    'estado'     => 'FINALIZADO',
                    'updated_at' => now(),
                ]);

                // ✅ redirigir al dashboard (historial)
                return redirect()->route('dashboard')->with('success', 'Proceso finalizado.');
            }

            $nextEtapa = DB::table('etapas')->where('id', $nextEtapaId)->first();
            abort_unless($nextEtapa, 422, 'Siguiente etapa inválida.');
            abort_unless((int)$nextEtapa->workflow_id === (int)$proceso->workflow_id, 422, 'Inconsistencia: siguiente etapa de otro workflow.');

            // Crear instancia de la siguiente etapa si no existe
            $nextProcesoEtapa = DB::table('proceso_etapas')
                ->where('proceso_id', $proceso->id)
                ->where('etapa_id', $nextEtapa->id)
                ->first();

            if (!$nextProcesoEtapa) {
                $nextProcesoEtapaId = DB::table('proceso_etapas')->insertGetId([
                    'proceso_id' => $proceso->id,
                    'etapa_id'   => $nextEtapa->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $nextProcesoEtapaId = $nextProcesoEtapa->id;
            }

            // Checks solo si NO es unidad
            if ($nextEtapa->area_role !== 'unidad_solicitante') {
                $this->seedChecksSiFaltan($nextProcesoEtapaId, $nextEtapa->id);
            }

            // Actualizar proceso a la siguiente etapa
            DB::table('procesos')->where('id', $proceso->id)->update([
                'etapa_actual_id'  => $nextEtapa->id,
                'area_actual_role' => $nextEtapa->area_role,
                'updated_at'       => now(),
            ]);

            // ✅ redirigir a la bandeja de la siguiente etapa
            $url = match ($nextEtapa->area_role) {
                'unidad_solicitante' => url('/unidad?proceso_id=' . $proceso->id),
                'planeacion'         => url('/planeacion?proceso_id=' . $proceso->id),
                'hacienda'           => url('/hacienda?proceso_id=' . $proceso->id),
                'juridica'           => url('/juridica?proceso_id=' . $proceso->id),
                'secop'              => url('/secop?proceso_id=' . $proceso->id),
                default              => route('dashboard'),
            };

            return redirect($url)->with('success', 'Enviado y avanzado a la siguiente etapa.');
        });
    }
}
