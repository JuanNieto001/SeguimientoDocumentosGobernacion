<?php

namespace App\Http\Controllers;

use App\Models\ContratoAplicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContratoAplicacionController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));

        $contratos = ContratoAplicacion::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('aplicacion', 'like', "%{$q}%")
                        ->orWhere('numero_contrato', 'like', "%{$q}%")
                        ->orWhere('proveedor', 'like', "%{$q}%")
                        ->orWhere('secop_proceso_id', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('contratos-aplicaciones.index', compact('contratos', 'q'));
    }

    public function create(): View
    {
        $this->authorizeManage();

        return view('contratos-aplicaciones.create', [
            'contrato' => new ContratoAplicacion(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage();

        $data = $this->validateData($request);
        ContratoAplicacion::create($data);

        return redirect()->route('contratos-aplicaciones.index')
            ->with('success', 'Contrato de aplicación creado correctamente.');
    }

    public function show(ContratoAplicacion $contratosAplicacione): View
    {
        return view('contratos-aplicaciones.show', [
            'contrato' => $contratosAplicacione,
        ]);
    }

    public function edit(ContratoAplicacion $contratosAplicacione): View
    {
        $this->authorizeManage();

        return view('contratos-aplicaciones.edit', [
            'contrato' => $contratosAplicacione,
        ]);
    }

    public function update(Request $request, ContratoAplicacion $contratosAplicacione): RedirectResponse
    {
        $this->authorizeManage();

        $data = $this->validateData($request);
        $contratosAplicacione->update($data);

        return redirect()->route('contratos-aplicaciones.show', $contratosAplicacione)
            ->with('success', 'Contrato de aplicación actualizado correctamente.');
    }

    public function destroy(ContratoAplicacion $contratosAplicacione): RedirectResponse
    {
        $this->authorizeManage();
        $contratosAplicacione->delete();

        return redirect()->route('contratos-aplicaciones.index')
            ->with('success', 'Contrato de aplicación eliminado.');
    }

    private function authorizeManage(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasAnyRole(['admin', 'admin_general', 'admin_secretaria']), 403);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'aplicacion' => ['required', 'string', 'max:160'],
            'numero_contrato' => ['nullable', 'string', 'max:120'],
            'proveedor' => ['nullable', 'string', 'max:180'],
            'objeto' => ['nullable', 'string'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'valor_total' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', 'string', 'max:40'],
            'secop_proceso_id' => ['nullable', 'string', 'max:160'],
            'secop_url' => ['nullable', 'url', 'max:500'],
            'responsable' => ['nullable', 'string', 'max:150'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $data['activo'] = $request->boolean('activo');

        return $data;
    }
}
