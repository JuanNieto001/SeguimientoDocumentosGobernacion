<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordAdminController extends Controller
{
    /**
     * Muestra el formulario de reset para un usuario
     */
    public function show(User $usuario)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        abort_if(auth()->id() === $usuario->id, 403, 'No puedes resetear tu propia contraseña desde aquí.');

        return view('admin.users.reset-password', compact('usuario'));
    }

    /**
     * Genera y guarda nueva contraseña temporal – la muestra en pantalla
     */
    public function generate(User $usuario)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        abort_if(auth()->id() === $usuario->id, 403, 'No puedes resetear tu propia contraseña desde aquí.');

        // Generar contraseña temporal legible: 4 palabras cortas + número
        $palabras = ['Sol', 'Luna', 'Mar', 'Rio', 'Pez', 'Luz', 'Rey', 'Paz', 'Don', 'Ley'];
        $parteA = $palabras[array_rand($palabras)];
        $parteB = $palabras[array_rand($palabras)];
        $numero  = rand(10, 99);
        $tempPassword = "{$parteA}{$parteB}{$numero}";

        $usuario->update([
            'password' => Hash::make($tempPassword),
        ]);

        // Registrar evento
        AuthEvent::registrar(
            'password_reset',
            $usuario->id,
            $usuario->email,
            ['reset_by' => auth()->id(), 'reset_by_name' => auth()->user()->name]
        );

        return view('admin.users.reset-password', compact('usuario', 'tempPassword'));
    }
}
