<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_health_checks', function (Blueprint $table) {
            $table->id();
            $table->timestamp('checked_at')->index();
            $table->enum('status', ['ok', 'degraded', 'down'])->index();
            $table->unsignedInteger('response_ms')->nullable();
            $table->unsignedInteger('active_sessions')->default(0);
            $table->unsignedInteger('target_concurrent_users')->default(100);
            $table->json('checks')->nullable();
            $table->string('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_health_checks');
    }
};
