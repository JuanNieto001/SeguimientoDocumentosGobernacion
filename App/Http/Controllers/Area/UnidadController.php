<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;

class UnidadController extends Controller
{
    public function index()
    {
        return view('areas.inbox', [
            'areaName' => 'Unidad Solicitante',
        ]);
    }
}
