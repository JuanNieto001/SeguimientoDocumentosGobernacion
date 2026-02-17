<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $base = DB::table('procesos')
            ->leftJoin('workflows', 'workflows.id', '=', 'procesos.workflow_id')
            ->select([
                'procesos.*',
                'workflows.nombre as workflow_nombre',
            ])
            ->orderByDesc('procesos.id');

        // Admin ve todo. Unidad ve lo suyo. Áreas ven lo que esté en su bandeja.
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('unidad_solicitante')) {
                $base->where('procesos.created_by', $user->id);
            } else {
                $rolesArea = ['planeacion', 'hacienda', 'juridica', 'secop'];
                $miRolArea = collect($rolesArea)->first(fn ($r) => $user->hasRole($r));
                if ($miRolArea) {
                    $base->where('procesos.area_actual_role', $miRolArea);
                } else {
                    $base->whereRaw('1=0');
                }
            }
        }

        $all = $base->get();

        $enCurso = $all->where('estado', 'EN_CURSO')->values();
        $finalizados = $all->where('estado', 'FINALIZADO')->values();

        return view('dashboard', compact('enCurso', 'finalizados'));
    }
}
