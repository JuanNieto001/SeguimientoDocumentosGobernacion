<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;

class SecopController extends Controller
{
    public function index()
    {
        return view('areas.inbox', [
            'areaName' => 'SECOP (Estructurador)',
        ]);
    }
}
