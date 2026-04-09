<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('db')->table('migrations')->insert([
    'migration' => '2026_04_04_170001_create_dashboard_assignment_tables',
    'batch' => 5
]);
echo "✓ Migración marcada como ejecutada\n";
