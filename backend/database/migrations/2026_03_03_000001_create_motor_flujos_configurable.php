<?php
/**
 * Archivo: backend/database/migrations/2026_03_03_000001_create_motor_flujos_configurable.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ╔══════════════════════════════════════════════════════════════════════════════╗
 * ║  MOTOR DE FLUJOS CONFIGURABLE POR SECRETARÍA                              ║
 * ║──────────────────────────────────────────────────────────────────────────────║
 * ║  Permite que cada Secretaría defina sus propios flujos de contratación     ║
 * ║  con pasos, orden, responsables, documentos y condiciones diferentes.      ║
 * ║  Los cambios se hacen SOLO en base de datos, sin tocar código.            ║
 * ╚══════════════════════════════════════════════════════════════════════════════╝
 *
 * TABLAS:
 *  1. catalogo_pasos          → Catálogo general de pasos reutilizables
 *  2. flujos                  → Flujos de contratación (1:N por Secretaría)
 *  3. flujo_versiones         → Versionado de flujos (historial de cambios)
 *  4. flujo_pasos             → Pasos asignados a un flujo con orden específico
 *  5. flujo_paso_condiciones  → Condiciones opcionales para activar un paso
 *  6. flujo_paso_responsables → Responsables asignados por Secretaría/paso
 *  7. flujo_paso_documentos   → Documentos requeridos por paso
 *  8. flujo_instancias        → Instancias de ejecución de un flujo (procesos)
 *  9. flujo_instancia_pasos   → Estado de cada paso en una instancia
 * 10. flujo_instancia_docs    → Documentos subidos en cada paso de instancia
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────────────────────
        // 1) CATÁLOGO GENERAL DE PASOS REUTILIZABLES
        // ─────────────────────────────────────────────────────────────
        Schema::create('catalogo_pasos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();           // Ej: DEF_NECESIDAD, VAL_CONTRATISTA
            $table->string('nombre');                          // Ej: "Definición de la Necesidad"
            $table->text('descripcion')->nullable();
            $table->string('icono', 50)->nullable();           // Ej: "FileText", "UserCheck"
            $table->string('color', 20)->nullable();           // Ej: "#3B82F6"
            $table->string('tipo', 30)->default('secuencial'); // secuencial, paralelo, condicional
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ─────────────────────────────────────────────────────────────
        // 2) FLUJOS DE CONTRATACIÓN (por Secretaría)
        // ─────────────────────────────────────────────────────────────
        Schema::create('flujos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();            // Ej: CD_PN_PLANEACION
            $table->string('nombre');                           // Ej: "CD Persona Natural - Sec. Planeación"
            $table->text('descripcion')->nullable();
            $table->string('tipo_contratacion', 50);           // cd_pn, cd_pj, lp, mc, etc.

            $table->foreignId('secretaria_id')
                  ->constrained('secretarias')
                  ->cascadeOnDelete();

            $table->unsignedBigInteger('version_activa_id')->nullable(); // FK a flujo_versiones

            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['secretaria_id', 'tipo_contratacion']);
        });

        // ─────────────────────────────────────────────────────────────
        // 3) VERSIONADO DE FLUJOS
        // ─────────────────────────────────────────────────────────────
        Schema::create('flujo_versiones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('flujo_id')
                  ->constrained('flujos')
                  ->cascadeOnDelete();

            $table->unsignedInteger('numero_version');         // 1, 2, 3...
            $table->string('motivo_cambio')->nullable();       // "Se agregó paso de revisión jurídica"
            $table->enum('estado', ['borrador', 'activa', 'archivada'])->default('borrador');

            $table->foreignId('creado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('publicada_at')->nullable();     // Fecha en que se activó
            $table->timestamps();

            $table->unique(['flujo_id', 'numero_version']);
        });

        // FK circular: flujos.version_activa_id → flujo_versiones.id
        Schema::table('flujos', function (Blueprint $table) {
            $table->foreign('version_activa_id')
                  ->references('id')
                  ->on('flujo_versiones')
                  ->nullOnDelete();
        });

        // ─────────────────────────────────────────────────────────────
        // 4) PASOS ASIGNADOS A UN FLUJO (con orden específico)
        // ─────────────────────────────────────────────────────────────
        Schema::create('flujo_pasos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('flujo_version_id')
                  ->constrained('flujo_versiones')
                  ->cascadeOnDelete();

            $table->foreignId('catalogo_paso_id')
                  ->constrained('catalogo_pasos')
                  ->cascadeOnDelete();

            $table->unsignedInteger('orden');                  // Posición en el flujo
            $table->string('nombre_personalizado')->nullable();// Sobrescribir nombre del catálogo
            $table->text('instrucciones')->nullable();         // Instrucciones específicas
            $table->boolean('es_obligatorio')->default(true);
            $table->boolean('es_paralelo')->default(false);    // ¿Se ejecuta en paralelo con el siguiente?
            $table->unsignedInteger('dias_estimados')->nullable();
            $table->string('area_responsable_default', 100)->nullable(); // Rol por defecto

            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['flujo_version_id', 'orden']);
            $table->index(['flujo_version_id', 'activo']);
        });

        // ─────────────────────────────────────────────────────────────
        // 5) CONDICIONES OPCIONALES POR PASO
        // ─────────────────────────────────────────────────────────────
        Schema::create('flujo_paso_condiciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('flujo_paso_id')
                  ->constrained('flujo_pasos')
                  ->cascadeOnDelete();

            $table->string('campo');                           // Ej: "monto_estimado", "tipo_persona"
            $table->string('operador', 20);                    // >, <, >=, <=, ==, !=, in, not_in, between
            $table->text('valor');                              // Ej: "50000000", "['natural','juridica']"
            $table->enum('accion', [
                'requerido',       // El paso se vuelve obligatorio
                'omitir',          // Se salta el paso
                'agregar_paso',    // Se inserta un paso adicional
                'notificar',       // Se envía notificación
            ])->default('requerido');
            $table->text('descripcion')->nullable();           // "Si monto > 50M, requiere revisión jurídica"
            $table->unsignedInteger('prioridad')->default(0);  // Orden de evaluación
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['flujo_paso_id', 'activo']);
        });

        // ─────────────────────────────────────────────────────────────
        // 6) RESPONSABLES POR SECRETARÍA Y PASO
        // ─────────────────────────────────────────────────────────────
        Schema::create('flujo_paso_responsables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('flujo_paso_id')
                  ->constrained('flujo_pasos')
                  ->cascadeOnDelete();

            $table->string('rol', 100);                        // Ej: "jefe_unidad", "abogado_enlace"
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('unidad_id')
                  ->nullable()
                  ->constrained('unidades')
                  ->nullOnDelete();

            $table->enum('tipo', ['ejecutor', 'revisor', 'aprobador', 'observador'])
                  ->default('ejecutor');
            $table->boolean('es_principal')->default(false);   // ¿Es el responsable principal?
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['flujo_paso_id', 'activo']);
        });

        // ─────────────────────────────────────────────────────────────
        // 7) DOCUMENTOS REQUERIDOS POR PASO
        // ─────────────────────────────────────────────────────────────
        Schema::create('flujo_paso_documentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('flujo_paso_id')
                  ->constrained('flujo_pasos')
                  ->cascadeOnDelete();

            $table->string('nombre');                          // "Estudios Previos"
            $table->text('descripcion')->nullable();
            $table->string('tipo_archivo', 50)->nullable();    // pdf, docx, xlsx, imagen
            $table->boolean('es_obligatorio')->default(true);
            $table->unsignedInteger('max_archivos')->default(1);
            $table->unsignedInteger('max_tamano_mb')->default(10);
            $table->string('plantilla_url')->nullable();       // URL a plantilla descargable
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['flujo_paso_id', 'activo']);
        });

        // ─────────────────────────────────────────────────────────────
        // 8) INSTANCIAS DE FLUJO (procesos en ejecución)
        // ─────────────────────────────────────────────────────────────
        Schema::create('flujo_instancias', function (Blueprint $table) {
            $table->id();

            $table->string('codigo_proceso')->unique();        // Ej: CD-PN-SEC-001-2026

            $table->foreignId('flujo_id')
                  ->constrained('flujos')
                  ->restrictOnDelete();

            $table->unsignedBigInteger('flujo_version_id');    // Versión con la que se inició
            $table->foreign('flujo_version_id')
                  ->references('id')
                  ->on('flujo_versiones')
                  ->restrictOnDelete();

            $table->foreignId('secretaria_id')
                  ->constrained('secretarias')
                  ->restrictOnDelete();

            $table->foreignId('unidad_id')
                  ->nullable()
                  ->constrained('unidades')
                  ->nullOnDelete();

            // Datos del proceso
            $table->text('objeto');
            $table->decimal('monto_estimado', 15, 2)->nullable();
            $table->unsignedInteger('plazo_dias')->nullable();
            $table->json('metadata')->nullable();              // Datos adicionales dinámicos

            // Estado
            $table->enum('estado', [
                'borrador',
                'en_curso',
                'pausado',
                'completado',
                'cancelado',
                'devuelto',
            ])->default('borrador');

            $table->unsignedBigInteger('paso_actual_id')->nullable();

            $table->foreignId('creado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('iniciado_at')->nullable();
            $table->timestamp('completado_at')->nullable();
            $table->timestamps();

            $table->index(['secretaria_id', 'estado']);
            $table->index(['flujo_id', 'estado']);
        });

        // FK flujo_instancias.paso_actual_id → flujo_pasos.id
        Schema::table('flujo_instancias', function (Blueprint $table) {
            $table->foreign('paso_actual_id')
                  ->references('id')
                  ->on('flujo_pasos')
                  ->nullOnDelete();
        });

        // ─────────────────────────────────────────────────────────────
        // 9) ESTADO DE CADA PASO EN UNA INSTANCIA
        // ─────────────────────────────────────────────────────────────
        Schema::create('flujo_instancia_pasos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('instancia_id')
                  ->constrained('flujo_instancias')
                  ->cascadeOnDelete();

            $table->foreignId('flujo_paso_id')
                  ->constrained('flujo_pasos')
                  ->cascadeOnDelete();

            $table->unsignedInteger('orden');                  // Copia del orden (snapshot)

            $table->enum('estado', [
                'pendiente',
                'en_progreso',
                'completado',
                'omitido',
                'devuelto',
                'bloqueado',
            ])->default('pendiente');

            $table->boolean('omitido_por_condicion')->default(false);
            $table->text('observaciones')->nullable();

            $table->foreignId('recibido_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('recibido_at')->nullable();

            $table->foreignId('completado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('completado_at')->nullable();

            $table->foreignId('devuelto_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('devuelto_at')->nullable();
            $table->text('motivo_devolucion')->nullable();

            $table->timestamps();

            $table->unique(['instancia_id', 'flujo_paso_id']);
            $table->index(['instancia_id', 'estado']);
        });

        // ─────────────────────────────────────────────────────────────
        // 10) DOCUMENTOS SUBIDOS EN CADA PASO DE INSTANCIA
        // ─────────────────────────────────────────────────────────────
        Schema::create('flujo_instancia_docs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('instancia_paso_id')
                  ->constrained('flujo_instancia_pasos')
                  ->cascadeOnDelete();

            $table->foreignId('flujo_paso_documento_id')
                  ->nullable()
                  ->constrained('flujo_paso_documentos')
                  ->nullOnDelete();

            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->string('tipo_mime', 100)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();

            $table->foreignId('subido_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index('instancia_paso_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flujo_instancia_docs');
        Schema::dropIfExists('flujo_instancia_pasos');
        Schema::dropIfExists('flujo_instancias');
        Schema::dropIfExists('flujo_paso_documentos');
        Schema::dropIfExists('flujo_paso_responsables');
        Schema::dropIfExists('flujo_paso_condiciones');
        Schema::dropIfExists('flujo_pasos');

        // Quitar FK circular antes de drop
        Schema::table('flujos', function (Blueprint $table) {
            $table->dropForeign(['version_activa_id']);
        });

        Schema::dropIfExists('flujo_versiones');
        Schema::dropIfExists('flujos');
        Schema::dropIfExists('catalogo_pasos');
    }
};

