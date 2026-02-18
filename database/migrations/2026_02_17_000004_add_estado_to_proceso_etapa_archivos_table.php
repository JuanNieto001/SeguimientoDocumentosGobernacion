<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proceso_etapa_archivos', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente')->after('uploaded_by');
            $table->text('observaciones')->nullable()->after('estado');
            $table->foreignId('revisado_por')->nullable()->after('observaciones')->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable()->after('revisado_por');
            
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::table('proceso_etapa_archivos', function (Blueprint $table) {
            $table->dropForeign(['revisado_por']);
            $table->dropColumn(['estado', 'observaciones', 'revisado_por', 'revisado_at']);
        });
    }
};
