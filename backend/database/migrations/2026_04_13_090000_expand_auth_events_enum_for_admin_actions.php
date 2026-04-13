<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('auth_events')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE auth_events MODIFY COLUMN event_type ENUM(
            'login_success',
            'login_failed',
            'logout',
            'password_changed',
            'password_reset',
            'account_disabled',
            'session_expired',
            'session_forced_logout',
            'user_created',
            'user_updated',
            'user_deleted',
            'user_activated',
            'user_deactivated',
            'roles_updated'
        ) NOT NULL");
    }

    public function down(): void
    {
        if (!Schema::hasTable('auth_events')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE auth_events MODIFY COLUMN event_type ENUM(
            'login_success',
            'login_failed',
            'logout',
            'password_changed',
            'password_reset',
            'account_disabled',
            'session_expired'
        ) NOT NULL");
    }
};
