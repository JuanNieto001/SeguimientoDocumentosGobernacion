<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EstivenHelpController extends Controller
{
    public function solicitarAyuda(Request $request)
    {
        $request->validate([
            'asunto'  => ['required', 'string', 'max:150'],
            'mensaje' => ['required', 'string', 'max:1500'],
        ]);

        $user      = $request->user();
        $roleName  = $user->roles->pluck('name')->first() ?? 'Sin rol';
        $asunto    = $request->input('asunto');
        $mensaje   = $request->input('mensaje');
        $appName   = config('app.name', 'Seguimiento Contratación');
        $correoSoporte = config('mail.from.address', 'Smhernandezl@gobernaciondecaldas.gov.co');

        Mail::send([], [], function ($mail) use ($user, $roleName, $asunto, $mensaje, $appName, $correoSoporte) {
            $mail->to($correoSoporte)
                ->replyTo($user->email, $user->name)
                ->subject("[Ayuda] {$asunto} – {$appName}")
                ->html("
<div style='font-family:Inter,Arial,sans-serif;max-width:580px;margin:0 auto;padding:32px 24px;background:#f8fafc;border-radius:16px'>
    <div style='background:#14532d;border-radius:12px;padding:24px;text-align:center;margin-bottom:24px'>
        <h1 style='color:#fff;font-size:18px;margin:0'>Solicitud de Ayuda</h1>
        <p style='color:#bbf7d0;font-size:12px;margin:6px 0 0'>Agente Estiven – {$appName}</p>
    </div>
    <div style='background:#fff;border-radius:12px;padding:24px;border:1px solid #e2e8f0'>
        <table style='width:100%;font-size:13px;color:#475569;margin-bottom:16px'>
            <tr><td style='padding:4px 8px 4px 0;font-weight:600;color:#1e293b'>Usuario:</td><td>{$user->name}</td></tr>
            <tr><td style='padding:4px 8px 4px 0;font-weight:600;color:#1e293b'>Correo:</td><td>{$user->email}</td></tr>
            <tr><td style='padding:4px 8px 4px 0;font-weight:600;color:#1e293b'>Rol:</td><td>{$roleName}</td></tr>
        </table>
        <hr style='border:none;border-top:1px solid #f1f5f9;margin:16px 0'>
        <h2 style='font-size:15px;color:#1e293b;margin:0 0 8px'>Asunto: " . e($asunto) . "</h2>
        <p style='font-size:13px;color:#475569;line-height:1.7;margin:0;white-space:pre-wrap'>" . e($mensaje) . "</p>
        <hr style='border:none;border-top:1px solid #f1f5f9;margin:16px 0'>
        <p style='font-size:11px;color:#94a3b8;margin:0'>Puede responder directamente a este correo para contactar al usuario.</p>
    </div>
</div>
");
        });

        return response()->json(['success' => true, 'message' => 'Solicitud enviada correctamente.']);
    }
}
