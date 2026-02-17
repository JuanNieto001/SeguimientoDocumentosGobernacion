<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WorkflowFilesController extends Controller
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
        abort_unless($user, 403);

        if ($user->hasRole('admin')) return;

        // Solo el rol del área actual puede operar sobre el proceso
        abort_unless($proceso->area_actual_role && $user->hasRole($proceso->area_actual_role), 403);
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

    /**
     * POST /workflow/procesos/{proceso}/archivos
     * Unidad sube: formato_necesidades | borrador_estudios | anexo
     */
    public function store(Request $request, int $proceso)
    {
        $proceso = $this->loadProcesoOrFail($proceso);
        $this->authorizeAreaOrAdmin($proceso);

        $user = auth()->user();

        // ✅ Por ahora SOLO Unidad (o admin) puede subir
        if (!$user->hasRole('admin')) {
            abort_unless(
                $proceso->area_actual_role === 'unidad_solicitante',
                403,
                'Solo Unidad puede cargar archivos en este momento.'
            );
        }

        // ✅ Validaciones
        $data = $request->validate([
            'tipo'    => ['required', 'in:formato_necesidades,borrador_estudios,anexo'],
            'files'   => ['required', 'array'],
            'files.*' => [
                'file',
                'max:10240', // 10MB
                // básico anti-ejecutables: deja docs e imágenes comunes
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,txt,zip,rar',
            ],
        ]);

        return DB::transaction(function () use ($proceso, $data, $request, $user) {

            $procesoEtapa = $this->getProcesoEtapaActual($proceso);

            $stored = 0;

            foreach ($request->file('files') as $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }

                // Ruta ordenada
                $path = $file->store(
                    "workflow/procesos/{$proceso->id}/etapa/{$procesoEtapa->id}",
                    'public'
                );

                DB::table('proceso_etapa_archivos')->insert([
                    'proceso_etapa_id' => $procesoEtapa->id,
                    'tipo'             => $data['tipo'],
                    'nombre_original'  => $file->getClientOriginalName(),
                    'path'             => $path,
                    'mime'             => $file->getClientMimeType(),
                    'size'             => (int)$file->getSize(),
                    'uploaded_by'      => $user->id,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $stored++;
            }

            abort_unless($stored > 0, 422, 'No se pudo cargar ningún archivo.');

            return back()->with('success', "Archivos cargados: {$stored}.");
        });
    }

    public function download(Request $request, int $proceso, int $archivo)
    {
        $proceso = $this->loadProcesoOrFail($proceso);
        $this->authorizeAreaOrAdmin($proceso);

        $row = DB::table('proceso_etapa_archivos as pea')
            ->join('proceso_etapas as pe', 'pe.id', '=', 'pea.proceso_etapa_id')
            ->where('pea.id', $archivo)
            ->where('pe.proceso_id', $proceso->id)
            ->select('pea.*')
            ->first();

        abort_unless($row, 404, 'Archivo no encontrado.');

        abort_unless(Storage::disk('public')->exists($row->path), 404, 'El archivo no existe en storage.');

        return Storage::disk('public')->download($row->path, $row->nombre_original);
    }

    public function destroy(Request $request, int $proceso, int $archivo)
    {
        $proceso = $this->loadProcesoOrFail($proceso);
        $this->authorizeAreaOrAdmin($proceso);

        $user = auth()->user();

        $row = DB::table('proceso_etapa_archivos as pea')
            ->join('proceso_etapas as pe', 'pe.id', '=', 'pea.proceso_etapa_id')
            ->where('pea.id', $archivo)
            ->where('pe.proceso_id', $proceso->id)
            ->select('pea.*', 'pe.etapa_id')
            ->first();

        abort_unless($row, 404, 'Archivo no encontrado.');

        // ✅ Por ahora SOLO Unidad (o admin) puede eliminar
        if (!$user->hasRole('admin')) {
            abort_unless(
                $proceso->area_actual_role === 'unidad_solicitante',
                403,
                'Solo Unidad puede eliminar archivos en este momento.'
            );

            // no-admin solo puede borrar en la etapa actual (seguridad extra)
            abort_unless((int)$row->etapa_id === (int)$proceso->etapa_actual_id, 403);
        }

        return DB::transaction(function () use ($row) {
            try {
                Storage::disk('public')->delete($row->path);
            } catch (\Throwable $e) {
                // no rompe si el archivo ya no existe
            }

            DB::table('proceso_etapa_archivos')->where('id', $row->id)->delete();

            return back()->with('success', 'Archivo eliminado.');
        });
    }
}
