<?php
/**
 * Archivo: backend/App/Http/Controllers/Auth/PasswordResetLinkController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     * Flujo personalizado:
     *   1. Verifica que el usuario identificado por 'usuario_email' exista
     *   2. Genera el token de reset para ESE usuario
     *   3. Envía el enlace al 'correo_destino' que el usuario indicó
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'usuario_email'   => ['required', 'string'],
            'correo_destino'  => ['required', 'email'],
        ], [
            'usuario_email.required'  => 'Debes ingresar tu usuario o correo registrado.',
            'correo_destino.required' => 'Debes ingresar el correo donde recibirás el enlace.',
            'correo_destino.email'    => 'El correo de destino no tiene un formato válido.',
        ]);

        // Buscar usuario por email o por nombre
        $usuario = User::where('email', $request->usuario_email)
            ->orWhere('name', $request->usuario_email)
            ->first();

        if (!$usuario) {
            return back()
                ->withInput($request->only('usuario_email', 'correo_destino'))
                ->withErrors(['usuario_email' => 'No encontramos ningún usuario con ese correo o nombre.']);
        }

        // Generar token de reset usando el broker de Laravel
        $token = app('auth.password.broker')->createToken($usuario);

        // URL de reset apuntando al correo DEL USUARIO (no al de destino)
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $usuario->email,
        ], false));

        // Enviar el correo al destino indicado
        $correoDestino = $request->correo_destino;
        $nombreUsuario = $usuario->name;
        $appName = config('app.name');

        Mail::send([], [], function ($message) use ($correoDestino, $nombreUsuario, $resetUrl, $appName) {
            $message->to($correoDestino)
                ->subject("Restablecer contraseña – {$appName}")
                ->html("
<div style='font-family:Inter,Arial,sans-serif;max-width:520px;margin:0 auto;padding:32px 24px;background:#f8fafc;border-radius:16px'>
    <div style='background:#14532d;border-radius:12px;padding:24px;text-align:center;margin-bottom:24px'>
        <h1 style='color:#fff;font-size:20px;margin:0'>Gobernación de Caldas</h1>
        <p style='color:#bbf7d0;font-size:13px;margin:6px 0 0'>Sistema de Seguimiento de Contratación</p>
    </div>
    <div style='background:#fff;border-radius:12px;padding:28px;border:1px solid #e2e8f0'>
        <h2 style='color:#1e293b;font-size:18px;margin:0 0 12px'>Restablecer contraseña</h2>
        <p style='color:#475569;font-size:14px;line-height:1.6;margin:0 0 8px'>Hola, se solicitó restablecer la contraseña del usuario <strong>{$nombreUsuario}</strong>.</p>
        <p style='color:#475569;font-size:14px;line-height:1.6;margin:0 0 24px'>Haz clic en el botón para crear una nueva contraseña. El enlace expira en <strong>60 minutos</strong>.</p>
        <div style='text-align:center;margin-bottom:24px'>
            <a href='{$resetUrl}' style='display:inline-block;background:#14532d;color:#fff;text-decoration:none;padding:14px 32px;border-radius:10px;font-weight:600;font-size:15px'>Restablecer contraseña</a>
        </div>
        <p style='color:#94a3b8;font-size:12px;line-height:1.5;margin:0'>Si no solicitaste este cambio, ignora este correo. Tu contraseña no será modificada.</p>
        <hr style='border:none;border-top:1px solid #f1f5f9;margin:20px 0'>
        <p style='color:#cbd5e1;font-size:11px;margin:0'>O copia este enlace en tu navegador:<br><span style='color:#2563eb;word-break:break-all'>{$resetUrl}</span></p>
    </div>
</div>
");
        });

        return back()->with('status', '¡Enlace enviado! Revisa el correo ' . $correoDestino . ' para restablecer tu contraseña.');
    }
}

