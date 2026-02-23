<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * Controlador para áreas que reciben solicitudes de documentos
 * (compras, contabilidad, presupuesto, rentas, inversiones_publicas, etc.)
 */
class SolicitudDocumentosController extends Controller
{
    /**
     * Mostrar solicitudes pendientes para el área del usuario actual
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Obtener TODOS los roles del usuario que son roles de área de documentos
        $rolesDocumentos = ['compras', 'talento_humano', 'contabilidad', 'rentas', 'inversiones_publicas', 'presupuesto'];
        $userRoles = $user->getRoleNames()->toArray();
        $rolesActivos = array_intersect($userRoles, $rolesDocumentos);

        if (empty($rolesActivos)) {
            abort(403, 'No tienes un rol de área de documentos asignado.');
        }

        // Buscar solicitudes pendientes para TODOS los roles de documentos del usuario
        $solicitudes = DB::table('proceso_documentos_solicitados as pds')
            ->join('procesos as p', 'p.id', '=', 'pds.proceso_id')
            ->join('etapas as e', 'e.id', '=', 'pds.etapa_id')
            ->leftJoin('proceso_etapa_archivos as pea', 'pea.id', '=', 'pds.archivo_id')
            ->select(
                'pds.*',
                'p.codigo as proceso_numero',
                'p.objeto as proceso_descripcion',
                'p.area_actual_role as proceso_area_actual',
                'e.nombre as etapa_nombre',
                'pea.nombre_original as archivo_nombre'
            )
            ->whereIn('pds.area_responsable_rol', $rolesActivos)
            ->orderByDesc('pds.created_at')
            ->get();

        // Agrupar por proceso
        $solicitudesPorProceso = $solicitudes->groupBy('proceso_id');

        // Determinar nombre del área principal
        $areaRole = $rolesActivos[array_key_first($rolesActivos)];

        return view('areas.solicitudes', [
            'areaRole' => $areaRole,
            'areaName' => $this->getRoleName($areaRole),
            'solicitudesPorProceso' => $solicitudesPorProceso,
            'totalSolicitudes' => $solicitudes->where('estado', '!=', 'subido')->count(),
            'totalCompletadas' => $solicitudes->where('estado', 'subido')->count(),
        ]);
    }

    /**
     * Vista detallada de un proceso para subir el documento solicitado
     */
    public function detalle(int $proceso)
    {
        $user = auth()->user();
        
        // Obtener todos los roles de documentos del usuario
        $rolesDocumentos = ['compras', 'talento_humano', 'contabilidad', 'rentas', 'inversiones_publicas', 'presupuesto'];
        $userRoles = $user->getRoleNames()->toArray();
        $rolesActivos = array_intersect($userRoles, $rolesDocumentos);

        if (empty($rolesActivos)) {
            abort(403, 'No tienes un rol de área de documentos asignado.');
        }

        // Cargar proceso
        $proceso = DB::table('procesos')->where('id', $proceso)->first();
        abort_unless($proceso, 404, 'Proceso no encontrado.');

        // Cargar solicitudes de TODOS los roles de documentos del usuario para este proceso
        $solicitudes = DB::table('proceso_documentos_solicitados as pds')
            ->leftJoin('proceso_etapa_archivos as pea', 'pea.id', '=', 'pds.archivo_id')
            ->select('pds.*', 'pea.nombre_original as archivo_nombre', 'pea.ruta as archivo_ruta')
            ->where('pds.proceso_id', $proceso->id)
            ->whereIn('pds.area_responsable_rol', $rolesActivos)
            ->get();

        // Cargar archivos que YA subió esta área para este proceso
        $archivosSubidos = DB::table('proceso_etapa_archivos')
            ->where('proceso_id', $proceso->id)
            ->where('uploaded_by', $user->id)
            ->get();

        $areaRole = $rolesActivos[array_key_first($rolesActivos)];

        return view('areas.solicitud-detalle', [
            'proceso' => $proceso,
            'solicitudes' => $solicitudes,
            'archivosSubidos' => $archivosSubidos,
            'areaRole' => $areaRole,
            'areaName' => $this->getRoleName($areaRole),
        ]);
    }

    /**
     * Obtener nombre legible del rol
     */
    private function getRoleName(string $role): string
    {
        $nombres = [
            'compras' => 'Unidad de Compras',
            'talento_humano' => 'Talento Humano',
            'contabilidad' => 'Contabilidad',
            'rentas' => 'Rentas',
            'inversiones_publicas' => 'Inversiones Públicas',
            'presupuesto' => 'Presupuesto',
        ];

        return $nombres[$role] ?? ucfirst(str_replace('_', ' ', $role));
    }
}
