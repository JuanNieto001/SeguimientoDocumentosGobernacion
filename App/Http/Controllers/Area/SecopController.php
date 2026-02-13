<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;

class SecopController extends Controller
{
    public function index()
    {
        return view('areas.secop', [
            'areaName' => 'SECOP (Estructurador)',
        ]);
    }
}
