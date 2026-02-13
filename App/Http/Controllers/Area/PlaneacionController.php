<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;

class PlaneacionController extends Controller
{
    public function index()
    {
        return view('areas.planeacion', [
            'areaName' => 'Secretaría de Planeación',
        ]);
    }
}
