<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;

class JuridicaController extends Controller
{
    public function index()
    {
        return view('areas.juridica', [
            'areaName' => 'Secretaría Jurídica',
        ]);
    }
}
