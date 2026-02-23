<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProcesoAuditoria;

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
        if ($user->hasRole('admin')) return;

        // ✅ MODIFICADO: Permitir al creador operar si es su proceso
        if ($proceso->created_by == $user->id) return;

        // Solo el rol del área actual puede operar el workflow de ese proceso
        abort_unless($proceso->area_actual_role && $user->hasRole($proceso->area_actual_role), 403, 'Este proceso no está actualmente en tu área.');
    }

    private function getProcesoEtapaActual($proceso)
    {
        // Trae la instancia de la etapa actual (si no existe, la crea)
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

        $items = DB::table('etapa_items')->where('etapa_id', $etapaId)->orderBy('orden')->get(['id']);

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

    public function recibir(Request $request, int $proceso)
    {
        $proceso = $this->loadProcesoOrFail($proceso);
        $this->authorizeAreaOrAdmin($proceso);

        return DB::transaction(function () use ($proceso) {

            $procesoEtapa = $this->getProcesoEtapaActual($proceso);

            // Crear checks si faltan
            $this->seedChecksSiFaltan($procesoEtapa->id, $proceso->etapa_actual_id);

            // Marcar recibido si no lo está
            if (!$procesoEtapa->recibido) {
                DB::table('proceso_etapas')->where('id', $procesoEtapa->id)->update([
                    'recibido'     => true,
                    'recibido_por' => auth()->id(),
                    'recibido_at'  => now(),
                    'updated_at'   => now(),
                ]);
                
                // ✅ NUEVO: Si es Etapa 1 (Descentralización), solicitar documentos automáticamente
                $etapa = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
                if ($etapa && $etapa->orden == 1) {
                    $this->solicitarDocumentosEtapa1($proceso);
                }
                
                // Registrar auditoría
                $descripcionAuditoria = "Documento recibido en {$etapa->nombre} ({$proceso->area_actual_role})";
                ProcesoAuditoria::registrar(
                    $proceso->id,
                    'documento_recibido',
                    $descripcionAuditoria,
                    $etapa->id,
                    null,
                    ['mensaje' => $descripcionAuditoria]
                );
            }

            return back()->with('success', 'Documento recibido.');
        });
    }

    public function toggleCheck(Request $request, int $proceso, int $check)
    {
        $proceso = $this->loadProcesoOrFail($proceso);
        $this->authorizeAreaOrAdmin($proceso);

        return DB::transaction(function () use ($proceso, $check) {

            $procesoEtapa = $this->getProcesoEtapaActual($proceso);

            // Verifica que el check pertenezca a la etapa actual
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
            
            // Registrar auditoría
            $etapaItem = DB::table('etapa_items')->where('id', $row->etapa_item_id)->first();
            $etapa = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
            $descripcionAuditoria = ($newValue ? 'Check marcado: ' : 'Check desmarcado: ') . ($etapaItem->label ?? $etapaItem->nombre ?? 'ítem #'.$row->etapa_item_id);
            ProcesoAuditoria::registrar(
                $proceso->id,
                $newValue ? 'check_marcado' : 'check_desmarcado',
                $descripcionAuditoria,
                $etapa->id,
                null,
                ['mensaje' => $descripcionAuditoria]
            );

            return back()->with('success', $newValue ? 'Check marcado.' : 'Check desmarcado.');
        });
    }

    public function enviar(Request $request, int $proceso)
    {
        $proceso = $this->loadProcesoOrFail($proceso);
        $this->authorizeAreaOrAdmin($proceso);

        return DB::transaction(function () use ($proceso) {

            $procesoEtapa = $this->getProcesoEtapaActual($proceso);
            $etapaActual = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
            abort_unless($etapaActual, 422, 'Etapa actual inválida.');

            // ========== VALIDACIONES ESPECÍFICAS POR ÁREA ==========

            // 1) UNIDAD SOLICITANTE: validar archivos requeridos según etapa
            if ($etapaActual->area_role === 'unidad_solicitante') {
                
                // Determinar archivos requeridos según la etapa
                $tiposRequeridos = [];
                
                if ($etapaActual->orden == 0) {
                    // ETAPA 0: Solo Estudios Previos
                    $tiposRequeridos = ['estudios_previos'];
                } elseif ($etapaActual->orden == 2) {
                    // ETAPA 2: Documentos del contratista (al menos 1)
                    $tiposRequeridos = []; // Validar que haya al menos 1 archivo
                } elseif ($etapaActual->orden == 3) {
                    // ETAPA 3: Documentos contractuales (al menos 1)
                    $tiposRequeridos = []; // Validar que haya al menos 1 archivo
                } elseif ($etapaActual->orden == 4) {
                    // ETAPA 4: Carpeta precontractual
                    $tiposRequeridos = ['carpeta_precontractual'];
                } elseif ($etapaActual->orden == 9) {
                    // ETAPA 9: ARL y Acta de Inicio
                    $tiposRequeridos = ['acta_inicio'];
                }
                
                // Si hay tipos específicos requeridos, validarlos
                if (!empty($tiposRequeridos)) {
                    foreach ($tiposRequeridos as $tipo) {
                        $archivo = DB::table('proceso_etapa_archivos')
                            ->where('proceso_id', $proceso->id)
                            ->where('etapa_id', $proceso->etapa_actual_id)
                            ->where('tipo_archivo', $tipo)
                            ->first();
                        
                        if (!$archivo) {
                            $label = match($tipo) {
                                'estudios_previos' => 'Estudios Previos',
                                'carpeta_precontractual' => 'Carpeta Precontractual',
                                'acta_inicio' => 'Acta de Inicio',
                                default => ucfirst(str_replace('_', ' ', $tipo))
                            };
                            abort(422, "No puedes enviar: falta el archivo requerido '{$label}'.");
                        }
                        // Si no hay flujo de aprobación, normalizar estado a aprobado
                        if (!isset($archivo->estado) || $archivo->estado === null || $archivo->estado === 'pendiente') {
                            DB::table('proceso_etapa_archivos')
                                ->where('id', $archivo->id)
                                ->update(['estado' => 'aprobado', 'updated_at' => now()]);
                            $archivo->estado = 'aprobado';
                        }
                        
                        // Validar que esté aprobado (si existe el campo estado)
                        if (isset($archivo->estado) && $archivo->estado !== 'aprobado') {
                            $label = match($tipo) {
                                'estudios_previos' => 'Estudios Previos',
                                'carpeta_precontractual' => 'Carpeta Precontractual',
                                'acta_inicio' => 'Acta de Inicio',
                                default => ucfirst(str_replace('_', ' ', $tipo))
                            };
                            
                            if ($archivo->estado === 'rechazado') {
                                abort(422, "No puedes enviar: el archivo '{$label}' fue rechazado. Debes reemplazarlo.");
                            } else {
                                abort(422, "No puedes enviar: el archivo '{$label}' está pendiente de aprobación.");
                            }
                        }
                    }
                } else {
                    // Si no hay tipos específicos, validar que haya al menos 1 archivo
                    $archivosCount = DB::table('proceso_etapa_archivos')
                        ->where('proceso_id', $proceso->id)
                        ->where('etapa_id', $proceso->etapa_actual_id)
                        ->count();
                    
                    if ($archivosCount === 0) {
                        abort(422, "No puedes enviar: debes subir al menos un documento.");
                    }
                }
                
                // Unidad NO requiere "recibir" ni checks
                // Solo requiere archivos aprobados
                
            } else {
                
                // 2) OTRAS ÁREAS: validar recibido + checks + archivos aprobados
                
                // Debe estar recibido
                if (!(bool)$procesoEtapa->recibido) {
                    return redirect()->back()->with('error', 'Debes marcar "Recibí" antes de poder enviar a la siguiente secretaría.');
                }

                // Validar checks requeridos
                $faltantes = DB::table('proceso_etapa_checks as pec')
                    ->join('etapa_items as ei', 'ei.id', '=', 'pec.etapa_item_id')
                    ->where('pec.proceso_etapa_id', $procesoEtapa->id)
                    ->where('ei.requerido', 1)
                    ->where('pec.checked', 0)
                    ->count();

                if ($faltantes > 0) {
                    return redirect()->back()->with('error', "No puedes enviar: faltan {$faltantes} ítem(s) requerido(s) en el checklist.");
                }
                
                // Validar que todos los archivos de la etapa estén aprobados
                $archivosNoAprobados = DB::table('proceso_etapa_archivos')
                    ->where('proceso_id', $proceso->id)
                    ->where('etapa_id', $proceso->etapa_actual_id)
                    ->whereIn('estado', ['pendiente', 'rechazado'])
                    ->count();
                
                if ($archivosNoAprobados > 0) {
                    return redirect()->back()->with('error', 'No puedes enviar: hay documentos pendientes de aprobación o rechazados.');
                }
            }

            // ========== FIN VALIDACIONES ESPECÍFICAS ==========

            // Marcar enviado si no lo está
            if (!$procesoEtapa->enviado) {
                DB::table('proceso_etapas')->where('id', $procesoEtapa->id)->update([
                    'enviado'     => true,
                    'enviado_por' => auth()->id(),
                    'enviado_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // ✅ Seguridad: el proceso debe pertenecer al mismo workflow
            abort_unless((int)$etapaActual->workflow_id === (int)$proceso->workflow_id, 422, 'Inconsistencia: workflow no coincide.');

            $nextEtapaId = $etapaActual->next_etapa_id;

            // Si no hay siguiente, se finaliza
            if (!$nextEtapaId) {
                DB::table('procesos')->where('id', $proceso->id)->update([
                    'estado'     => 'FINALIZADO',
                    'updated_at' => now(),
                ]);
                
                // Registrar auditoría de finalización
                $descripcionAuditoria = "Proceso completado exitosamente. Última etapa: {$etapaActual->nombre}";
                ProcesoAuditoria::registrar(
                    $proceso->id,
                    'proceso_finalizado',
                    $descripcionAuditoria,
                    $etapaActual->id,
                    null,
                    ['mensaje' => $descripcionAuditoria]
                );

                return back()->with('success', 'Proceso finalizado.');
            }

            $nextEtapa = DB::table('etapas')->where('id', $nextEtapaId)->first();
            abort_unless($nextEtapa, 422, 'Siguiente etapa inválida.');

            // ✅ Seguridad: la siguiente etapa debe ser del mismo workflow
            abort_unless((int)$nextEtapa->workflow_id === (int)$proceso->workflow_id, 422, 'Inconsistencia: siguiente etapa de otro workflow.');

            // Crear proceso_etapa de la siguiente etapa si no existe
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

            // Seed de checks para la nueva etapa
            $this->seedChecksSiFaltan($nextProcesoEtapaId, $nextEtapa->id);

            // Actualizar proceso a la siguiente etapa
            DB::table('procesos')->where('id', $proceso->id)->update([
                'etapa_actual_id'  => $nextEtapa->id,
                'area_actual_role' => $nextEtapa->area_role,
                'updated_at'       => now(),
            ]);
            
            // Registrar auditoría
            $descripcionAuditoria = "Proceso enviado desde {$etapaActual->nombre} hacia {$nextEtapa->nombre} (área: {$nextEtapa->area_role})";
            ProcesoAuditoria::registrar(
                $proceso->id,
                'etapa_avanzada',
                $descripcionAuditoria,
                $nextEtapa->id,
                ['etapa_anterior' => $etapaActual->nombre, 'area_anterior' => $etapaActual->area_role],
                ['etapa_siguiente' => $nextEtapa->nombre, 'area_siguiente' => $nextEtapa->area_role]
            );

            $areaLabel = match($nextEtapa->area_role) {
                'unidad_solicitante' => 'Unidad Solicitante',
                'planeacion'         => 'Planeación',
                'hacienda'           => 'Hacienda',
                'juridica'           => 'Jurídica',
                'secop'              => 'SECOP',
                default              => ucfirst($nextEtapa->area_role),
            };
            return back()->with('success', "Proceso enviado a: {$nextEtapa->nombre} → Área: {$areaLabel}.");
        });
    }

    /**
     * Solicitar documentos a múltiples áreas (Etapa 1)
     * Descentralización coordina la solicitud de 7 documentos
     */
    private function solicitarDocumentosEtapa1($proceso): void
    {
        $etapa = DB::table('etapas')->where('id', $proceso->etapa_actual_id)->first();
        $user = auth()->user();

        // Definir documentos a solicitar
        $documentos = [
            [
                'tipo_documento' => 'paa',
                'nombre_documento' => 'Plan Anual de Adquisiciones (PAA)',
                'area_responsable_rol' => 'compras',
                'area_responsable_nombre' => 'Unidad de Compras y Suministros',
                'secretaria_nombre' => 'Secretaría General',
                'puede_subir' => true,
            ],
            [
                'tipo_documento' => 'no_planta',
                'nombre_documento' => 'Certificado No Planta',
                'area_responsable_rol' => 'talento_humano',
                'area_responsable_nombre' => 'Jefatura de Gestión del Talento Humano',
                'secretaria_nombre' => 'Secretaría General',
                'puede_subir' => true,
            ],
            [
                'tipo_documento' => 'paz_salvo_rentas',
                'nombre_documento' => 'Paz y Salvo de Rentas',
                'area_responsable_rol' => 'rentas',
                'area_responsable_nombre' => 'Unidad de Rentas',
                'secretaria_nombre' => 'Secretaría de Hacienda',
                'puede_subir' => true,
            ],
            [
                'tipo_documento' => 'paz_salvo_contabilidad',
                'nombre_documento' => 'Paz y Salvo de Contabilidad',
                'area_responsable_rol' => 'contabilidad',
                'area_responsable_nombre' => 'Unidad de Contabilidad',
                'secretaria_nombre' => 'Secretaría de Hacienda',
                'puede_subir' => true,
            ],
            [
                'tipo_documento' => 'compatibilidad_gasto',
                'nombre_documento' => 'Compatibilidad del Gasto',
                'area_responsable_rol' => 'inversiones_publicas',
                'area_responsable_nombre' => 'Unidad de Regalías e Inversiones Públicas',
                'secretaria_nombre' => 'Secretaría de Planeación',
                'puede_subir' => true,
                'es_requerido_para_cdp' => true, // Marcador especial
            ],
            [
                'tipo_documento' => 'sigep',
                'nombre_documento' => 'SIGEP Validado',
                'area_responsable_rol' => 'juridica',
                'area_responsable_nombre' => 'Oficina de Radicación',
                'secretaria_nombre' => 'Secretaría Jurídica',
                'puede_subir' => true,
            ],
        ];

        // Insertar solicitudes (excepto CDP que se inserta después)
        $solicitudCompatibilidadId = null;

        foreach ($documentos as $doc) {
            // Buscar secretaría por nombre
            $secretaria = DB::table('secretarias')->where('nombre', 'like', '%' . $doc['secretaria_nombre'] . '%')->first();

            $solicitudId = DB::table('proceso_documentos_solicitados')->insertGetId([
                'proceso_id' => $proceso->id,
                'etapa_id' => $etapa->id,
                'tipo_documento' => $doc['tipo_documento'],
                'nombre_documento' => $doc['nombre_documento'],
                'area_responsable_rol' => $doc['area_responsable_rol'],
                'area_responsable_nombre' => $doc['area_responsable_nombre'],
                'secretaria_responsable_id' => $secretaria?->id,
                'estado' => 'pendiente',
                'puede_subir' => $doc['puede_subir'],
                'solicitado_por' => $user->id,
                'solicitado_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Guardar ID de Compatibilidad para usarlo en CDP
            if (isset($doc['es_requerido_para_cdp']) && $doc['es_requerido_para_cdp']) {
                $solicitudCompatibilidadId = $solicitudId;
            }
        }

        // Ahora insertar CDP que DEPENDE de Compatibilidad
        $secHacienda = DB::table('secretarias')->where('nombre', 'like', '%Hacienda%')->first();

        DB::table('proceso_documentos_solicitados')->insert([
            'proceso_id' => $proceso->id,
            'etapa_id' => $etapa->id,
            'tipo_documento' => 'cdp',
            'nombre_documento' => 'Certificado de Disponibilidad Presupuestal (CDP)',
            'area_responsable_rol' => 'presupuesto',
            'area_responsable_nombre' => 'Unidad de Presupuesto',
            'secretaria_responsable_id' => $secHacienda?->id,
            'estado' => 'pendiente',
            'depende_de_solicitud_id' => $solicitudCompatibilidadId, // ⚠️ DEPENDENCIA
            'puede_subir' => false, // Bloqueado hasta que suban Compatibilidad
            'solicitado_por' => $user->id,
            'solicitado_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Registrar auditoría
        ProcesoAuditoria::registrar(
            $proceso->id,
            'documentos_solicitados',
            'Descentralización',
            $etapa->nombre,
            null,
            'Se solicitaron 7 documentos a múltiples áreas: PAA, No Planta, Paz y Salvos, Compatibilidad, CDP, SIGEP'
        );
    }
}
