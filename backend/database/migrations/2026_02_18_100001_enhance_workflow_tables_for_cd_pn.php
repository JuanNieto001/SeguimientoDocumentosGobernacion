<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade campos descriptivos a etapas y etapa_items para documentar
     * de forma precisa el flujo real de contratación de la Gobernación.
     */
    public function up(): void
    {
        Schema::table('etapas', function (Blueprint $table) {
            if (!Schema::hasColumn('etapas', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('nombre')
                    ->comment('Descripción detallada de lo que ocurre en esta etapa');
            }
            if (!Schema::hasColumn('etapas', 'responsable_unidad')) {
                $table->string('responsable_unidad')->nullable()->after('area_role')
                    ->comment('Unidad específica responsable (ej: Unidad de Descentralización)');
            }
            if (!Schema::hasColumn('etapas', 'responsable_secretaria')) {
                $table->string('responsable_secretaria')->nullable()->after('responsable_unidad')
                    ->comment('Secretaría a la que pertenece la unidad responsable');
            }
            if (!Schema::hasColumn('etapas', 'es_paralelo')) {
                $table->boolean('es_paralelo')->default(false)->after('responsable_secretaria')
                    ->comment('Si true, los ítems de esta etapa se pueden gestionar en paralelo');
            }
            if (!Schema::hasColumn('etapas', 'notas')) {
                $table->text('notas')->nullable()->after('es_paralelo')
                    ->comment('Notas importantes, restricciones o dependencias');
            }
        });

        Schema::table('etapa_items', function (Blueprint $table) {
            if (!Schema::hasColumn('etapa_items', 'responsable_unidad')) {
                $table->string('responsable_unidad')->nullable()->after('label')
                    ->comment('Unidad específica que emite/gestiona este documento');
            }
            if (!Schema::hasColumn('etapa_items', 'responsable_secretaria')) {
                $table->string('responsable_secretaria')->nullable()->after('responsable_unidad')
                    ->comment('Secretaría de la unidad responsable');
            }
            if (!Schema::hasColumn('etapa_items', 'notas')) {
                $table->text('notas')->nullable()->after('responsable_secretaria')
                    ->comment('Notas: dependencias, requisitos previos, vigencias');
            }
            if (!Schema::hasColumn('etapa_items', 'tipo_documento')) {
                $table->string('tipo_documento', 50)->nullable()->after('notas')
                    ->comment('Tipo: solicitud, certificado, formato, documento_contratista, checklist');
            }
        });

        // Añadir campos de contratista y datos de proceso a procesos
        Schema::table('procesos', function (Blueprint $table) {
            if (!Schema::hasColumn('procesos', 'contratista_nombre')) {
                $table->string('contratista_nombre')->nullable()->after('descripcion');
            }
            if (!Schema::hasColumn('procesos', 'contratista_documento')) {
                $table->string('contratista_documento')->nullable()->after('contratista_nombre');
            }
            if (!Schema::hasColumn('procesos', 'contratista_tipo_documento')) {
                $table->string('contratista_tipo_documento', 10)->nullable()->after('contratista_documento');
            }
            if (!Schema::hasColumn('procesos', 'valor_estimado')) {
                $table->decimal('valor_estimado', 18, 2)->nullable()->after('contratista_tipo_documento');
            }
            if (!Schema::hasColumn('procesos', 'plazo_ejecucion')) {
                $table->string('plazo_ejecucion')->nullable()->after('valor_estimado')
                    ->comment('Ej: 6 meses, 120 días');
            }
            if (!Schema::hasColumn('procesos', 'numero_proceso_juridica')) {
                $table->string('numero_proceso_juridica')->nullable()->after('plazo_ejecucion')
                    ->comment('Número asignado al radicar en Jurídica: CD-SP-XX-2026');
            }
            if (!Schema::hasColumn('procesos', 'numero_contrato')) {
                $table->string('numero_contrato')->nullable()->after('numero_proceso_juridica')
                    ->comment('Número de contrato asignado al radicar expediente');
            }
            if (!Schema::hasColumn('procesos', 'secretaria_origen_id')) {
                $table->foreignId('secretaria_origen_id')->nullable()->after('numero_contrato')
                    ->constrained('secretarias')->nullOnDelete()
                    ->comment('Secretaría de donde nace la solicitud');
            }
            if (!Schema::hasColumn('procesos', 'unidad_origen_id')) {
                $table->foreignId('unidad_origen_id')->nullable()->after('secretaria_origen_id')
                    ->constrained('unidades')->nullOnDelete()
                    ->comment('Unidad solicitante que inicia el proceso');
            }
        });
    }

    public function down(): void
    {
        Schema::table('etapas', function (Blueprint $table) {
            $cols = ['descripcion', 'responsable_unidad', 'responsable_secretaria', 'es_paralelo', 'notas'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('etapas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('etapa_items', function (Blueprint $table) {
            $cols = ['responsable_unidad', 'responsable_secretaria', 'notas', 'tipo_documento'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('etapa_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('procesos', function (Blueprint $table) {
            $cols = ['contratista_nombre', 'contratista_documento', 'contratista_tipo_documento',
                     'valor_estimado', 'plazo_ejecucion', 'numero_proceso_juridica',
                     'numero_contrato', 'secretaria_origen_id', 'unidad_origen_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('procesos', $col)) {
                    if (in_array($col, ['secretaria_origen_id', 'unidad_origen_id'])) {
                        try { $table->dropForeign([$col]); } catch (\Throwable $e) {}
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
