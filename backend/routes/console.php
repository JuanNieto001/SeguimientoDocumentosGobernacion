<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar generación de alertas automáticas cada hora
Schedule::command('alertas:generar')->hourly();

// Mantener contratos de aplicaciones sincronizados con SECOP
Schedule::command('contratos-aplicaciones:sync-secop')->everySixHours();
