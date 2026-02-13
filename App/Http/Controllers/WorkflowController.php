<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    private function assertCanTouchProceso(object $proceso, string $areaRole): void
    {
        $user = Auth::user();

        // Admin puede todo
        if ($user->hasRole('admin')) return;

        // El usuario debe tener el rol del área actual del proceso
        if (!$user->hasRole($areaRole) || $proceso->area_actual_role !== $areaRole) {
            abort(403);
        }
    }

    // Marcar "Recibí el documento"
    public function recibir(Request $request, int $proceso)
    {
        $areaRole = $request->input('area_role');
        if (!$areaRole) abort(400, 'area_role requerido');

        $proc = DB::table('procesos')->where('id', $proceso)->first();
        if (!$proc) abort(404);

        $this->assertCanTouchProceso($proc, $areaRole);

        return DB::transaction(function () use ($proc) {
            $pe = DB::table('proceso_etapas')
                ->where('proceso_id', $proc->id)
                ->where('etapa_id', $proc->etapa_actual_id)
                ->first();

            if (!$pe) abort(500, 'proceso_etapa no existe');

            if (!$pe->recibido) {
                DB::table('proceso_etapas')
                    ->where('id', $pe->id)
                    ->update([
                        'recibido' => true,
                        'recibido_por' => Auth::id(),
                        'recibido_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            return back()->with('success', 'Marcado como recibido.');
        });
    }

    // Toggle check item
    public function toggleCheck(Request $request, int $proceso, int $check)
    {
        $areaRole = $request->input('area_role');
        if (!$areaRole) abort(400, 'area_role requerido');

        $proc = DB::table('procesos')->where('id', $proceso)->first();
        if (!$proc) abort(404);

        $this->assertCanTouchProceso($proc, $areaRole);

        return DB::transaction(function () use ($proc, $check) {

            $pe = DB::table('proceso_etapas')
                ->where('proceso_id', $proc->id)
                ->where('etapa_id', $proc->etapa_actual_id)
                ->first();

            if (!$pe) abort(500, 'proceso_etapa no existe');

            // Regla: no se puede marcar checklist si no ha recibido
            if (!$pe->recibido) {
                return back()->with('error', 'Primero debes marcar "Recibí el documento".');
            }

            $row = DB::table('proceso_etapa_checks')
                ->where('id', $check)
                ->where('proceso_etapa_id', $pe->id)
                ->first();

            if (!$row) abort(404);

            $new = !$row->checked;

            DB::table('proceso_etapa_checks')
                ->where('id', $row->id)
                ->update([
                    'checked' => $new,
                    'checked_by' => $new ? Auth::id() : null,
                    'checked_at' => $new ? now() : null,
                    'updated_at' => now(),
                ]);

            return back();
        });
    }

    // Marcar "Envié" y avanzar a la siguiente etapa
    public function enviar(Request $request, int $proceso)
    {
        $areaRole = $request->input('area_role');
        if (!$areaRole) abort(400, 'area_role requerido');

        $proc = DB::table('procesos')->where('id', $proceso)->first();
        if (!$proc) abort(404);

        $this->assertCanTouchProceso($proc, $areaRole);

        return DB::transaction(function () use ($proc) {

            $pe = DB::table('proceso_etapas')
                ->where('proceso_id', $proc->id)
                ->where('etapa_id', $proc->etapa_actual_id)
                ->first();

            if (!$pe) abort(500, 'proceso_etapa no existe');

            // Regla: debe estar recibido
            if (!$pe->recibido) {
                return back()->with('error', 'Primero marca "Recibí el documento".');
            }

            // Regla: todos los items requeridos deben estar checked
            $faltantes = DB::table('proceso_etapa_checks as pc')
                ->join('etapa_items as ei', 'ei.id', '=', 'pc.etapa_item_id')
                ->where('pc.proceso_etapa_id', $pe->id)
                ->where('ei.requerido', 1)
                ->where('pc.checked', 0)
                ->count();

            if ($faltantes > 0) {
                return back()->with('error', 'Completa el checklist requerido antes de enviar.');
            }

            // Marcar enviado en la etapa actual
            if (!$pe->enviado) {
                DB::table('proceso_etapas')
                    ->where('id', $pe->id)
                    ->update([
                        'enviado' => true,
                        'enviado_por' => Auth::id(),
                        'enviado_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            // Buscar siguiente etapa por orden
            $etapaActual = DB::table('etapas')->where('id', $proc->etapa_actual_id)->first();
            if (!$etapaActual) abort(500);

            $next = DB::table('etapas')
                ->where('activa', 1)
                ->where('orden', '>', $etapaActual->orden)
                ->orderBy('orden')
                ->first();

            // Si no hay siguiente, finaliza
            if (!$next) {
                DB::table('procesos')->where('id', $proc->id)->update([
                    'estado' => 'FINALIZADO',
                    'updated_at' => now(),
                ]);
                return back()->with('success', 'Proceso finalizado.');
            }

            // Mover proceso a la siguiente etapa
            DB::table('procesos')->where('id', $proc->id)->update([
                'etapa_actual_id' => $next->id,
                'area_actual_role' => $next->area_role,
                'updated_at' => now(),
            ]);

            return redirect()->route('dashboard')->with('success', 'Enviado a la siguiente secretaría.');
        });
    }
}
