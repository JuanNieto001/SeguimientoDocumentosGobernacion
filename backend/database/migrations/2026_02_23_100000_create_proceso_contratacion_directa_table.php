<?php
/**
 * Archivo: backend/database/migrations/2026_02_23_100000_create_proceso_contratacion_directa_table.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proceso_contratacion_directa', function (Blueprint $table) {
            $table->id();

            // ── Identificación del proceso ──
            $table->string('codigo')->unique()->comment('CD-PS-XX-2026');
            $table->string('estado')->default('borrador')->index();
            $table->unsignedTinyInteger('etapa_actual')->default(1);

            // ── Datos básicos (Etapa 1 – Estudios Previos) ──
            $table->text('objeto');
            $table->decimal('valor', 18, 2);
            $table->unsignedSmallInteger('plazo_meses')->comment('Solo entero en meses');
            $table->string('estudio_previo_path')->nullable()->comment('Ruta al archivo de estudios previos');

            // ── Contratista ──
            $table->string('contratista_nombre')->nullable();
            $table->string('contratista_tipo_documento', 10)->nullable();
            $table->string('contratista_documento', 50)->nullable();
            $table->string('contratista_email')->nullable();
            $table->string('contratista_telefono', 20)->nullable();

            // ── Origen de la solicitud ──
            $table->foreignId('secretaria_id')->constrained('secretarias');
            $table->foreignId('unidad_id')->constrained('unidades');

            // ── Etapa 2: Validaciones Presupuestales ──
            $table->boolean('paa_solicitado')->default(false);
            $table->boolean('certificado_no_planta')->default(false);
            $table->boolean('paz_salvo_rentas')->default(false);
            $table->boolean('paz_salvo_contabilidad')->default(false);
            $table->boolean('compatibilidad_gasto')->default(false);
            $table->boolean('compatibilidad_aprobada')->default(false);
            $table->string('numero_cdp', 50)->nullable();
            $table->decimal('valor_cdp', 18, 2)->nullable();
            $table->boolean('cdp_aprobado')->default(false);

            // ── Etapa 3: Hoja de Vida ──
            $table->boolean('hoja_vida_cargada')->default(false);
            $table->boolean('documentos_contratista_completos')->default(false);
            $table->boolean('checklist_validado')->default(false);
            $table->text('resultado_validacion')->nullable();

            // ── Etapa 4: Revisión Jurídica ──
            $table->string('numero_proceso_juridica', 50)->nullable()->comment('CD-PS-XX-2026');
            $table->boolean('revision_juridica_aprobada')->default(false);
            $table->text('observaciones_juridica')->nullable();

            // ── Etapa 5: Contrato ──
            $table->string('contrato_electronico_path')->nullable();
            $table->boolean('firma_contratista')->default(false);
            $table->boolean('firma_ordenador_gasto')->default(false);
            $table->text('observaciones_devolucion')->nullable();

            // ── Etapa 6: RPC ──
            $table->string('numero_rpc', 50)->nullable();
            $table->boolean('rpc_firmado_flag')->default(false);
            $table->boolean('expediente_radicado_flag')->default(false);

            // ── Etapa 7: Inicio de Ejecución ──
            $table->string('numero_contrato', 50)->nullable();
            $table->boolean('arl_solicitada')->default(false);
            $table->string('acta_inicio_path')->nullable();
            $table->boolean('acta_inicio_firmada')->default(false);
            $table->date('fecha_inicio_ejecucion')->nullable();

            // ── Actores asignados ──
            $table->foreignId('creado_por')->constrained('users');
            $table->foreignId('supervisor_id')->nullable()->constrained('users');
            $table->foreignId('ordenador_gasto_id')->nullable()->constrained('users');
            $table->foreignId('jefe_unidad_id')->nullable()->constrained('users');
            $table->foreignId('abogado_unidad_id')->nullable()->constrained('users');

            // ── Metadatos ──
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // ── Índices ──
            $table->index(['estado', 'etapa_actual']);
            $table->index('creado_por');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_contratacion_directa');
    }
};

