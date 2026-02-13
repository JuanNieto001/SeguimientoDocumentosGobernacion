<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Admin: vista principal del sistema (procesos)
        if ($user->hasRole('admin')) {
            return redirect()->route('procesos.index');
        }

        // Áreas (cada una a su bandeja)
        if ($user->hasRole('unidad_solicitante')) {
            return redirect()->route('unidad.index');
        }

        if ($user->hasRole('planeacion')) {
            return redirect()->route('planeacion.index');
        }

        if ($user->hasRole('hacienda')) {
            return redirect()->route('hacienda.index');
        }

        if ($user->hasRole('juridica')) {
            return redirect()->route('juridica.index');
        }

        if ($user->hasRole('secop')) {
            return redirect()->route('secop.index');
        }

        // Si entra aquí: no tiene rol asignado
        abort(403, 'No tienes un rol asignado.');
    }
}

