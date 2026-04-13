<?php
/**
 * Archivo: backend/App/Http/Controllers/Auth/AuthenticatedSessionController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuthEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $this->enforceConcurrentSessionLimit($request);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Limita sesiones simultáneas por usuario cuando el driver de sesión es DB.
     */
    private function enforceConcurrentSessionLimit(Request $request): void
    {
        $maxSessions = max((int) config('session.max_concurrent', 0), 0);
        if ($maxSessions === 0 || config('session.driver') !== 'database') {
            return;
        }

        $user = $request->user();
        if (!$user) {
            return;
        }

        $table = (string) config('session.table', 'sessions');
        $currentSessionId = $request->session()->getId();

        $sessions = DB::table($table)
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get(['id']);

        if ($sessions->count() <= $maxSessions) {
            return;
        }

        $idsToClose = $sessions
            ->skip($maxSessions)
            ->pluck('id')
            ->filter(fn($id) => $id !== $currentSessionId)
            ->values();

        if ($idsToClose->isEmpty()) {
            return;
        }

        DB::table($table)->whereIn('id', $idsToClose->all())->delete();

        AuthEvent::registrar(
            'session_forced_logout',
            $user->id,
            $user->email,
            [
                'reason' => 'max_concurrent_limit',
                'max_sessions' => $maxSessions,
                'sessions_closed' => $idsToClose->count(),
            ]
        );
    }
}

