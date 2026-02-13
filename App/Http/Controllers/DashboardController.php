<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect('/admin/usuarios');
        }

        if ($user->hasRole('unidad_solicitante')) {
            return redirect('/unidad');
        }

        if ($user->hasRole('planeacion')) {
            return redirect('/planeacion');
        }

        if ($user->hasRole('hacienda')) {
            return redirect('/hacienda');
        }

        if ($user->hasRole('juridica')) {
            return redirect('/juridica');
        }

        if ($user->hasRole('secop')) {
            return redirect('/secop');
        }

        // fallback
        return view('dashboard');
    }
}
