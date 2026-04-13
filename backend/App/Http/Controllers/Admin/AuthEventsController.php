<?php
/**
 * Archivo: backend/App/Http/Controllers/Admin/AuthEventsController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthEvent;
use App\Models\User;
use Illuminate\Http\Request;

class AuthEventsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $query = AuthEvent::with('user')->orderByDesc('created_at');

        // Filtros
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', "%{$request->ip}%");
        }
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        if ($request->input('formato') === 'csv') {
            $rows = (clone $query)->limit(10000)->get();

            return response()->streamDownload(function () use ($rows) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Fecha', 'Evento', 'Usuario', 'Email', 'IP', 'User Agent']);

                foreach ($rows as $ev) {
                    fputcsv($handle, [
                        optional($ev->created_at)->format('Y-m-d H:i:s'),
                        $ev->event_type,
                        $ev->user?->name,
                        $ev->email ?? $ev->user?->email,
                        $ev->ip_address,
                        $ev->user_agent,
                    ]);
                }

                fclose($handle);
            }, 'auth-events.csv');
        }

        $eventos = $query->paginate(50)->withQueryString();

        // Stats rápidas últimas 24h
        $stats = [
            'total_hoy'       => AuthEvent::whereDate('created_at', today())->count(),
            'exitosos_hoy'    => AuthEvent::whereDate('created_at', today())->where('event_type', 'login_success')->count(),
            'fallidos_hoy'    => AuthEvent::whereDate('created_at', today())->where('event_type', 'login_failed')->count(),
            'usuarios_activos'=> AuthEvent::where('event_type', 'login_success')
                                    ->whereDate('created_at', today())
                                    ->distinct('user_id')
                                    ->count('user_id'),
        ];

        // IPs con más intentos fallidos (últimas 24h)
        $ipsSospechosas = AuthEvent::where('event_type', 'login_failed')
            ->where('created_at', '>=', now()->subDay())
            ->selectRaw('ip_address, COUNT(*) as intentos')
            ->groupBy('ip_address')
            ->having('intentos', '>=', 3)
            ->orderByDesc('intentos')
            ->limit(5)
            ->get();

        $tipos = AuthEvent::select('event_type')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type')
            ->values();
        $usuarios = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.auth-events', compact('eventos', 'stats', 'ipsSospechosas', 'tipos', 'usuarios'));
    }
}

