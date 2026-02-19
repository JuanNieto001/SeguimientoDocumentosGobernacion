<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;

echo "=== ROLES EXISTENTES ===\n\n";

$roles = Role::orderBy('name')->get();

foreach ($roles as $role) {
    echo "- {$role->name}\n";
}

echo "\n";
