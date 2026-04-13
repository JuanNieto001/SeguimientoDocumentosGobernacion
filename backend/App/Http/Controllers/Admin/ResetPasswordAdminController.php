<?php
/**
 * Archivo: backend/App/Http/Controllers/Admin/ResetPasswordAdminController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthEvent;
use App\Models\User;
use App\Services\PasswordHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        // Generar contraseña temporal legible y robusta: palabras + número + símbolo.
        $historyService = app(PasswordHistoryService::class);
        $palabras = ['Sol', 'Luna', 'Mar', 'Rio', 'Pez', 'Luz', 'Rey', 'Paz', 'Don', 'Ley'];
        $simbolos = ['!', '@', '#', '$', '%'];

        $tempPassword = '';
        $found = false;

        for ($i = 0; $i < 20; $i++) {
            $parteA = $palabras[array_rand($palabras)];
            $parteB = $palabras[array_rand($palabras)];
            $numero  = rand(10, 99);
            $simbolo = $simbolos[array_rand($simbolos)];
            $candidate = "{$parteA}{$parteB}{$numero}{$simbolo}";

            if (!$historyService->wasRecentlyUsed($usuario, $candidate)) {
                $tempPassword = $candidate;
                $found = true;
                break;
            }
        }

        if (!$found) {
            return redirect()->back()->withErrors([
                'password' => 'No fue posible generar una contraseña temporal que cumpla el historial. Intenta nuevamente.',
            ]);
        }

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

