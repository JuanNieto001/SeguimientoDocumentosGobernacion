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
        Schema::table('proceso_etapa_archivos', function (Blueprint $table) {
            // Estado del documento
            if (!Schema::hasColumn('proceso_etapa_archivos', 'estado')) {
                $table->enum('estado', ['pendiente', 'aprobado', 'rechazado', 'vencido'])
                    ->default('pendiente')
                    ->after('mime_type');
            }
            
            // Observaciones al rechazar o aprobar
            if (!Schema::hasColumn('proceso_etapa_archivos', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('estado');
            }
            
            // Fecha de vigencia (para certificados)
            if (!Schema::hasColumn('proceso_etapa_archivos', 'fecha_vigencia')) {
                $table->date('fecha_vigencia')->nullable()->after('observaciones');
            }
            
            // Usuario que aprobó o rechazó
            if (!Schema::hasColumn('proceso_etapa_archivos', 'aprobado_por')) {
                $table->foreignId('aprobado_por')->nullable()->constrained('users')->after('fecha_vigencia');
            }
            
            // Fecha de aprobación o rechazo
            if (!Schema::hasColumn('proceso_etapa_archivos', 'aprobado_at')) {
                $table->timestamp('aprobado_at')->nullable()->after('aprobado_por');
            }
            
            // Versión del documento (para control de cambios)
            if (!Schema::hasColumn('proceso_etapa_archivos', 'version')) {
                $table->integer('version')->default(1)->after('aprobado_at');
            }
            
            // Referencia al archivo anterior (si es una nueva versión)
            if (!Schema::hasColumn('proceso_etapa_archivos', 'archivo_anterior_id')) {
                $table->foreignId('archivo_anterior_id')->nullable()
                    ->constrained('proceso_etapa_archivos')
                    ->after('version');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proceso_etapa_archivos', function (Blueprint $table) {
            if (Schema::hasColumn('proceso_etapa_archivos', 'archivo_anterior_id')) {
                $table->dropForeign(['archivo_anterior_id']);
                $table->dropColumn('archivo_anterior_id');
            }
            if (Schema::hasColumn('proceso_etapa_archivos', 'version')) {
                $table->dropColumn('version');
            }
            if (Schema::hasColumn('proceso_etapa_archivos', 'aprobado_at')) {
                $table->dropColumn('aprobado_at');
            }
            if (Schema::hasColumn('proceso_etapa_archivos', 'aprobado_por')) {
                $table->dropForeign(['aprobado_por']);
                $table->dropColumn('aprobado_por');
            }
            if (Schema::hasColumn('proceso_etapa_archivos', 'fecha_vigencia')) {
                $table->dropColumn('fecha_vigencia');
            }
            if (Schema::hasColumn('proceso_etapa_archivos', 'observaciones')) {
                $table->dropColumn('observaciones');
            }
            if (Schema::hasColumn('proceso_etapa_archivos', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }
};
