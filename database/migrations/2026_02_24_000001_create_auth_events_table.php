<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email', 255)->nullable(); // Para intentos fallidos donde no hay user todavía
            $table->enum('event_type', [
                'login_success',
                'login_failed',
                'logout',
                'password_changed',
                'password_reset',
                'account_disabled',
                'session_expired',
            ]);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('guard', 30)->default('web');
            $table->json('extra')->nullable(); // datos adicionales
            $table->timestamps();

            $table->index('user_id');
            $table->index('event_type');
            $table->index('created_at');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_events');
    }
};
