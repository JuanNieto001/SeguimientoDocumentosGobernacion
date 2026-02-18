<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposArchivoSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncateIfExists('tipos_archivo_por_etapa');

        // Obtener workflows
        $workflows = DB::table('workflows')->get()->keyBy('codigo');

        // ======================================
        // TIPOS DE ARCHIVO PARA CD_PN
        // ======================================
        $this->seedTiposCD_PN($workflows['CD_PN']->id);

        // ======================================
        // TIPOS DE ARCHIVO PARA MC, SA, LP, CM
        // ======================================
        $this->seedTiposMC($workflows['MC']->id);
        $this->seedTiposSA($workflows['SA']->id);
        $this->seedTiposLP($workflows['LP']->id);
        $this->seedTiposCM($workflows['CM']->id);

        $this->command->info('✅ Tipos de archivo por etapa creados correctamente.');
    }

    private function seedTiposCD_PN($workflowId)
    {
        // Etapa 0A: Unidad Solicitante
        $etapa0A = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 0)->first();
        if ($etapa0A) {
            $this->insertTipos($etapa0A->id, [
                ['tipo' => 'borrador_estudios_previos', 'label' => 'Borrador de Estudios Previos', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'formato_necesidades', 'label' => 'Formato de Necesidades', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'cotizaciones', 'label' => 'Cotizaciones (mínimo 3)', 'requerido' => true, 'orden' => 3],
            ]);
        }

        // Etapa 1 (Planeación): Estudios Previos
        $etapa1 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 2)->first();
        if ($etapa1) {
            $this->insertTipos($etapa1->id, [
                ['tipo' => 'estudios_previos_finales', 'label' => 'Estudios Previos Finales', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'analisis_sector', 'label' => 'Análisis del Sector', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'matriz_riesgos', 'label' => 'Matriz de Riesgos', 'requerido' => true, 'orden' => 3],
            ]);
        }

        // Etapa 2 (Planeación): Documentos presupuestales
        $etapa2 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 3)->first();
        if ($etapa2) {
            $this->insertTipos($etapa2->id, [
                ['tipo' => 'cdp', 'label' => 'Certificado de Disponibilidad Presupuestal (CDP)', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'certificado_compatibilidad', 'label' => 'Certificado de Compatibilidad', 'requerido' => true, 'orden' => 2],
            ]);
        }

        // Etapa 3 (Hacienda): Viabilidad Económica
        $etapa3 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 4)->first();
        if ($etapa3) {
            $this->insertTipos($etapa3->id, [
                ['tipo' => 'viabilidad_economica', 'label' => 'Viabilidad Económica', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // Etapa 4: Verificación del contratista
        $etapa4 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 5)->first();
        if ($etapa4) {
            $this->insertTipos($etapa4->id, [
                ['tipo' => 'hoja_vida', 'label' => 'Hoja de Vida (SIGEP)', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'soportes_academicos', 'label' => 'Soportes Académicos', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'certificados_experiencia', 'label' => 'Certificados de Experiencia', 'requerido' => true, 'orden' => 3],
                ['tipo' => 'antecedentes_disciplinarios', 'label' => 'Antecedentes Disciplinarios (Procuraduría)', 'requerido' => true, 'orden' => 4],
                ['tipo' => 'antecedentes_judiciales', 'label' => 'Antecedentes Judiciales (Policía)', 'requerido' => true, 'orden' => 5],
                ['tipo' => 'antecedentes_fiscales', 'label' => 'Antecedentes Fiscales (Contraloría)', 'requerido' => true, 'orden' => 6],
                ['tipo' => 'seguridad_social', 'label' => 'Aportes a Seguridad Social', 'requerido' => true, 'orden' => 7],
            ]);
        }

        // Etapa 5: Proyección del contrato
        $etapa5 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 6)->first();
        if ($etapa5) {
            $this->insertTipos($etapa5->id, [
                ['tipo' => 'minuta_contrato', 'label' => 'Minuta del Contrato (borrador)', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'solicitud_contratacion', 'label' => 'Solicitud de Contratación', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'designacion_supervisor', 'label' => 'Designación de Supervisor', 'requerido' => true, 'orden' => 3],
            ]);
        }

        // Etapa 7: Ajustado a Derecho
        $etapa7 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 8)->first();
        if ($etapa7) {
            $this->insertTipos($etapa7->id, [
                ['tipo' => 'ajustado_derecho', 'label' => 'Ajustado a Derecho', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // Etapa 8: SECOP - Estructuración
        $etapa8 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 9)->first();
        if ($etapa8) {
            $this->insertTipos($etapa8->id, [
                ['tipo' => 'proceso_secop', 'label' => 'Proceso Estructurado en SECOP', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // Etapa 9: Firma del contrato
        $etapa9 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 10)->first();
        if ($etapa9) {
            $this->insertTipos($etapa9->id, [
                ['tipo' => 'contrato_firmado', 'label' => 'Contrato Firmado', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'contrato_electronico', 'label' => 'Contrato Electrónico SECOP', 'requerido' => true, 'orden' => 2],
            ]);
        }

        // Etapa 10: Registro Presupuestal
        $etapa10 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 11)->first();
        if ($etapa10) {
            $this->insertTipos($etapa10->id, [
                ['tipo' => 'registro_presupuestal', 'label' => 'Registro Presupuestal (RP)', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // Etapa 11: Radicación
        $etapa11 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 12)->first();
        if ($etapa11) {
            $this->insertTipos($etapa11->id, [
                ['tipo' => 'comprobante_radicacion', 'label' => 'Comprobante de Radicación', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // Etapa 12: Pólizas
        $etapa12 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 13)->first();
        if ($etapa12) {
            $this->insertTipos($etapa12->id, [
                ['tipo' => 'polizas', 'label' => 'Pólizas de Garantía', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // Etapa 13: Acta de Inicio
        $etapa13 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 14)->first();
        if ($etapa13) {
            $this->insertTipos($etapa13->id, [
                ['tipo' => 'acta_inicio', 'label' => 'Acta de Inicio', 'requerido' => true, 'orden' => 1],
            ]);
        }

        // Etapa 14: Ejecución y Cierre
        $etapa14 = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 15)->first();
        if ($etapa14) {
            $this->insertTipos($etapa14->id, [
                ['tipo' => 'informes_supervision', 'label' => 'Informes de Supervisión', 'requerido' => false, 'orden' => 1],
                ['tipo' => 'acta_terminacion', 'label' => 'Acta de Terminación', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'acta_liquidacion', 'label' => 'Acta de Liquidación (si aplica)', 'requerido' => false, 'orden' => 3],
            ]);
        }
    }

    private function seedTiposMC($workflowId)
    {
        // Etapa 0A: Unidad Solicitante (igual que CD_PN)
        $etapa0A = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 0)->first();
        if ($etapa0A) {
            $this->insertTipos($etapa0A->id, [
                ['tipo' => 'borrador_estudios_previos', 'label' => 'Borrador de Estudios Previos', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'formato_necesidades', 'label' => 'Formato de Necesidades', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'cotizaciones', 'label' => 'Cotizaciones (mínimo 3)', 'requerido' => true, 'orden' => 3],
            ]);
        }

        // Agregar tipos específicos para MC...
        // (Similar estructura, diferentes etapas según modalidad)
    }

    private function seedTiposSA($workflowId)
    {
        // Similar a MC pero con prepliegos
        $etapa0A = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 0)->first();
        if ($etapa0A) {
            $this->insertTipos($etapa0A->id, [
                ['tipo' => 'borrador_estudios_previos', 'label' => 'Borrador de Estudios Previos', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'formato_necesidades', 'label' => 'Formato de Necesidades', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'cotizaciones', 'label' => 'Cotizaciones (mínimo 3)', 'requerido' => true, 'orden' => 3],
            ]);
        }
    }

    private function seedTiposLP($workflowId)
    {
        // Incluye documentos de audiencias exclusivas
        $etapa0A = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 0)->first();
        if ($etapa0A) {
            $this->insertTipos($etapa0A->id, [
                ['tipo' => 'borrador_estudios_previos', 'label' => 'Borrador de Estudios Previos', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'formato_necesidades', 'label' => 'Formato de Necesidades', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'cotizaciones', 'label' => 'Cotizaciones (mínimo 3)', 'requerido' => true, 'orden' => 3],
            ]);
        }

        // Etapa 10: Audiencia de Riesgos (exclusiva LP)
        $etapaRiesgos = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 11)->first();
        if ($etapaRiesgos) {
            $this->insertTipos($etapaRiesgos->id, [
                ['tipo' => 'acta_audiencia_riesgos', 'label' => 'Acta de Audiencia de Riesgos', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'matriz_riesgos_ajustada', 'label' => 'Matriz de Riesgos Ajustada', 'requerido' => true, 'orden' => 2],
            ]);
        }

        // Etapa 15: Audiencia Pública de Adjudicación (exclusiva LP)
        $etapaAudiencia = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 16)->first();
        if ($etapaAudiencia) {
            $this->insertTipos($etapaAudiencia->id, [
                ['tipo' => 'acta_audiencia_adjudicacion', 'label' => 'Acta de Audiencia Pública', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'resolucion_adjudicacion', 'label' => 'Resolución de Adjudicación', 'requerido' => true, 'orden' => 2],
            ]);
        }
    }

    private function seedTiposCM($workflowId)
    {
        // CM: NO requiere Viabilidad Económica al inicio
        $etapa0A = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 0)->first();
        if ($etapa0A) {
            $this->insertTipos($etapa0A->id, [
                ['tipo' => 'borrador_estudios_previos', 'label' => 'Borrador de Estudios Previos', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'formato_necesidades', 'label' => 'Formato de Necesidades', 'requerido' => true, 'orden' => 2],
                ['tipo' => 'cotizaciones', 'label' => 'Cotizaciones (mínimo 3)', 'requerido' => true, 'orden' => 3],
            ]);
        }

        // Etapa 11: Evaluación Técnica (exclusiva CM - sin precio)
        $etapaEvalTecnica = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 12)->first();
        if ($etapaEvalTecnica) {
            $this->insertTipos($etapaEvalTecnica->id, [
                ['tipo' => 'informe_evaluacion_tecnica', 'label' => 'Informe de Evaluación Técnica', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'puntajes_tecnicos', 'label' => 'Cuadro de Puntajes Técnicos', 'requerido' => true, 'orden' => 2],
            ]);
        }

        // Etapa 14: Negociación (exclusiva CM)
        $etapaNegociacion = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 15)->first();
        if ($etapaNegociacion) {
            $this->insertTipos($etapaNegociacion->id, [
                ['tipo' => 'acta_negociacion', 'label' => 'Acta de Negociación', 'requerido' => true, 'orden' => 1],
                ['tipo' => 'propuesta_economica', 'label' => 'Propuesta Económica Negociada', 'requerido' => true, 'orden' => 2],
            ]);
        }

        // Etapa 15: Viabilidad Económica DESPUÉS de negociación (exclusivo CM)
        $etapaViabilidad = DB::table('etapas')->where('workflow_id', $workflowId)->where('orden', 16)->first();
        if ($etapaViabilidad) {
            $this->insertTipos($etapaViabilidad->id, [
                ['tipo' => 'viabilidad_economica_negociada', 'label' => 'Viabilidad Económica del Valor Negociado', 'requerido' => true, 'orden' => 1],
            ]);
        }
    }

    private function insertTipos($etapaId, $tipos)
    {
        foreach ($tipos as $tipo) {
            DB::table('tipos_archivo_por_etapa')->insert([
                'etapa_id' => $etapaId,
                'tipo_archivo' => $tipo['tipo'],
                'label' => $tipo['label'],
                'requerido' => $tipo['requerido'],
                'orden' => $tipo['orden'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function truncateIfExists(string $table): void
    {
        try {
            DB::statement("DELETE FROM {$table}");
            if (DB::getDriverName() === 'sqlite') {
                DB::statement("DELETE FROM sqlite_sequence WHERE name='{$table}'");
            }
        } catch (\Exception $e) {
            // Tabla no existe, ignorar
        }
    }
}
