<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;

class HaciendaController extends Controller
{
    public function index()
    {
        return view('areas.hacienda', [
            'areaName' => 'Secretaría de Hacienda',
        ]);
    }
}
