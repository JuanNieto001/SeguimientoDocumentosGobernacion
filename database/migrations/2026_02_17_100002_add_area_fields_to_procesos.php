<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('procesos', function (Blueprint $table) {
            // Campos de Planeación
            if (!Schema::hasColumn('procesos', 'paa_verificado')) {
                $table->boolean('paa_verificado')->default(false)->after('estado');
            }
            if (!Schema::hasColumn('procesos', 'paa_id')) {
                $table->foreignId('paa_id')->nullable()->constrained('plan_anual_adquisiciones')->after('paa_verificado');
            }
            if (!Schema::hasColumn('procesos', 'aprobado_planeacion')) {
                $table->boolean('aprobado_planeacion')->default(false)->after('paa_id');
            }
            if (!Schema::hasColumn('procesos', 'observaciones_planeacion')) {
                $table->text('observaciones_planeacion')->nullable()->after('aprobado_planeacion');
            }

            // Campos de Hacienda
            if (!Schema::hasColumn('procesos', 'numero_cdp')) {
                $table->string('numero_cdp')->nullable()->after('observaciones_planeacion');
            }
            if (!Schema::hasColumn('procesos', 'valor_cdp')) {
                $table->decimal('valor_cdp', 15, 2)->nullable()->after('numero_cdp');
            }
            if (!Schema::hasColumn('procesos', 'rubro_presupuestal')) {
                $table->string('rubro_presupuestal')->nullable()->after('valor_cdp');
            }
            if (!Schema::hasColumn('procesos', 'cdp_emitido')) {
                $table->boolean('cdp_emitido')->default(false)->after('rubro_presupuestal');
            }
            if (!Schema::hasColumn('procesos', 'numero_rp')) {
                $table->string('numero_rp')->nullable()->after('cdp_emitido');
            }
            if (!Schema::hasColumn('procesos', 'valor_rp')) {
                $table->decimal('valor_rp', 15, 2)->nullable()->after('numero_rp');
            }
            if (!Schema::hasColumn('procesos', 'rp_emitido')) {
                $table->boolean('rp_emitido')->default(false)->after('valor_rp');
            }
            if (!Schema::hasColumn('procesos', 'aprobado_hacienda')) {
                $table->boolean('aprobado_hacienda')->default(false)->after('rp_emitido');
            }
            if (!Schema::hasColumn('procesos', 'observaciones_hacienda')) {
                $table->text('observaciones_hacienda')->nullable()->after('aprobado_hacienda');
            }

            // Campos de Jurídica
            if (!Schema::hasColumn('procesos', 'ajustado_emitido')) {
                $table->boolean('ajustado_emitido')->default(false)->after('observaciones_hacienda');
            }
            if (!Schema::hasColumn('procesos', 'numero_ajustado')) {
                $table->string('numero_ajustado')->nullable()->after('ajustado_emitido');
            }
            if (!Schema::hasColumn('procesos', 'contratista_verificado')) {
                $table->boolean('contratista_verificado')->default(false)->after('numero_ajustado');
            }
            if (!Schema::hasColumn('procesos', 'polizas_aprobadas')) {
                $table->boolean('polizas_aprobadas')->default(false)->after('contratista_verificado');
            }
            if (!Schema::hasColumn('procesos', 'aprobado_juridica')) {
                $table->boolean('aprobado_juridica')->default(false)->after('polizas_aprobadas');
            }
            if (!Schema::hasColumn('procesos', 'observaciones_juridica')) {
                $table->text('observaciones_juridica')->nullable()->after('aprobado_juridica');
            }

            // Campos de SECOP
            if (!Schema::hasColumn('procesos', 'secop_publicado')) {
                $table->boolean('secop_publicado')->default(false)->after('observaciones_juridica');
            }
            if (!Schema::hasColumn('procesos', 'secop_codigo')) {
                $table->string('secop_codigo')->nullable()->after('secop_publicado');
            }
            if (!Schema::hasColumn('procesos', 'contrato_registrado')) {
                $table->boolean('contrato_registrado')->default(false)->after('secop_codigo');
            }
            if (!Schema::hasColumn('procesos', 'numero_contrato')) {
                $table->string('numero_contrato')->nullable()->after('contrato_registrado');
            }
            if (!Schema::hasColumn('procesos', 'acta_inicio_registrada')) {
                $table->boolean('acta_inicio_registrada')->default(false)->after('numero_contrato');
            }
            if (!Schema::hasColumn('procesos', 'fecha_acta_inicio')) {
                $table->date('fecha_acta_inicio')->nullable()->after('acta_inicio_registrada');
            }

            // Campos generales
            if (!Schema::hasColumn('procesos', 'rechazado_por_area')) {
                $table->string('rechazado_por_area')->nullable()->after('fecha_acta_inicio');
            }
            if (!Schema::hasColumn('procesos', 'observaciones_rechazo')) {
                $table->text('observaciones_rechazo')->nullable()->after('rechazado_por_area');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procesos', function (Blueprint $table) {
            $columns = [
                'observaciones_rechazo', 'rechazado_por_area', 'fecha_acta_inicio', 
                'acta_inicio_registrada', 'numero_contrato', 'contrato_registrado', 
                'secop_codigo', 'secop_publicado', 'observaciones_juridica', 
                'aprobado_juridica', 'polizas_aprobadas', 'contratista_verificado', 
                'numero_ajustado', 'ajustado_emitido', 'observaciones_hacienda', 
                'aprobado_hacienda', 'rp_emitido', 'valor_rp', 'numero_rp', 
                'cdp_emitido', 'rubro_presupuestal', 'valor_cdp', 'numero_cdp', 
                'observaciones_planeacion', 'aprobado_planeacion', 'paa_id', 'paa_verificado'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('procesos', $column)) {
                    if ($column === 'paa_id') {
                        $table->dropForeign(['paa_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
