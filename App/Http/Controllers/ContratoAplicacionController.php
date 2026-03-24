<?php

namespace App\Http\Controllers;

use App\Models\ContratoAplicacion;
use App\Models\Secretaria;
use App\Models\Unidad;
use Illuminate\Http\Request;

class ContratoAplicacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:contratos_app.ver')->only(['index', 'show']);
        $this->middleware('permission:contratos_app.crear')->only(['create', 'store']);
        $this->middleware('permission:contratos_app.editar')->only(['edit', 'update']);
        $this->middleware('permission:contratos_app.eliminar')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = ContratoAplicacion::with(['secretaria', 'unidad'])
            ->orderByRaw("CASE estado WHEN 'activo' THEN 0 WHEN 'vencido' THEN 2 ELSE 3 END")
            ->orderBy('fecha_fin');

        // Filtrar por secretaría si el usuario es secretario o jefe_unidad
        $user = auth()->user();
        if ($user->hasRole('secretario') && $user->secretaria_id) {
            $query->where('secretaria_id', $user->secretaria_id);
        } elseif ($user->hasRole('jefe_unidad') && $user->unidad_id) {
            $query->where('unidad_id', $user->unidad_id);
        }

        // Filtros de búsqueda
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre_aplicacion', 'like', "%{$q}%")
                    ->orWhere('proveedor', 'like', "%{$q}%")
                    ->orWhere('numero_contrato', 'like', "%{$q}%")
                    ->orWhere('secop_id', 'like', "%{$q}%");
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->estado;
            if ($estado === 'por_vencer') {
                $query->where('estado', 'activo')
                    ->whereBetween('fecha_fin', [now(), now()->addDays(30)]);
            } else {
                $query->where('estado', $estado);
            }
        }

        if ($request->filled('secretaria_id')) {
            $query->where('secretaria_id', $request->secretaria_id);
        }

        $contratos    = $query->paginate(15)->withQueryString();
        $secretarias  = Secretaria::where('activo', true)->orderBy('nombre')->get();
        $totalActivos = ContratoAplicacion::where('estado', 'activo')->count();
        $proxVencer   = ContratoAplicacion::proximosAVencer(30)->count();
        $vencidos     = ContratoAplicacion::where(function ($q) {
            $q->where('estado', 'vencido')
              ->orWhere(fn ($s) => $s->where('estado', 'activo')->where('fecha_fin', '<', now()));
        })->count();

        return view('contratos-app.index', compact(
            'contratos', 'secretarias', 'totalActivos', 'proxVencer', 'vencidos'
        ));
    }

    public function create()
    {
        $secretarias = Secretaria::where('activo', true)->orderBy('nombre')->get();
        $unidades    = Unidad::where('activo', true)->orderBy('nombre')->get();
        $modalidades = $this->modalidades();

        return view('contratos-app.create', compact('secretarias', 'unidades', 'modalidades'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_aplicacion'    => 'required|string|max:255',
            'proveedor'            => 'nullable|string|max:255',
            'descripcion'          => 'nullable|string',
            'fecha_inicio'         => 'required|date',
            'fecha_fin'            => 'required|date|after_or_equal:fecha_inicio',
            'numero_contrato'      => 'nullable|string|max:100',
            'valor_contrato'       => 'nullable|numeric|min:0',
            'modalidad_contratacion' => 'nullable|string|max:100',
            'estado'               => 'required|in:activo,vencido,cancelado',
            'secop_id'             => 'nullable|string|max:100',
            'secop_url'            => 'nullable|url|max:500',
            'secretaria_id'        => 'nullable|exists:secretarias,id',
            'unidad_id'            => 'nullable|exists:unidades,id',
        ]);

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $contrato = ContratoAplicacion::create($data);

        return redirect()->route('contratos-app.show', $contrato)
            ->with('success', 'Contrato de aplicación registrado correctamente.');
    }

    public function show(ContratoAplicacion $contratosApp)
    {
        $contratosApp->load(['secretaria', 'unidad', 'creadoPor', 'actualizadoPor']);
        return view('contratos-app.show', ['contrato' => $contratosApp]);
    }

    public function edit(ContratoAplicacion $contratosApp)
    {
        $secretarias = Secretaria::where('activo', true)->orderBy('nombre')->get();
        $unidades    = Unidad::where('activo', true)->orderBy('nombre')->get();
        $modalidades = $this->modalidades();

        return view('contratos-app.edit', [
            'contrato'   => $contratosApp,
            'secretarias' => $secretarias,
            'unidades'   => $unidades,
            'modalidades' => $modalidades,
        ]);
    }

    public function update(Request $request, ContratoAplicacion $contratosApp)
    {
        $data = $request->validate([
            'nombre_aplicacion'    => 'required|string|max:255',
            'proveedor'            => 'nullable|string|max:255',
            'descripcion'          => 'nullable|string',
            'fecha_inicio'         => 'required|date',
            'fecha_fin'            => 'required|date|after_or_equal:fecha_inicio',
            'numero_contrato'      => 'nullable|string|max:100',
            'valor_contrato'       => 'nullable|numeric|min:0',
            'modalidad_contratacion' => 'nullable|string|max:100',
            'estado'               => 'required|in:activo,vencido,cancelado',
            'secop_id'             => 'nullable|string|max:100',
            'secop_url'            => 'nullable|url|max:500',
            'secretaria_id'        => 'nullable|exists:secretarias,id',
            'unidad_id'            => 'nullable|exists:unidades,id',
        ]);

        $data['updated_by'] = auth()->id();

        $contratosApp->update($data);

        return redirect()->route('contratos-app.show', $contratosApp)
            ->with('success', 'Contrato de aplicación actualizado correctamente.');
    }

    public function destroy(ContratoAplicacion $contratosApp)
    {
        $contratosApp->delete();

        return redirect()->route('contratos-app.index')
            ->with('success', 'Contrato de aplicación eliminado.');
    }

    private function modalidades(): array
    {
        return [
            'Contratación Directa',
            'Selección Abreviada',
            'Licitación Pública',
            'Concurso de Méritos',
            'Mínima Cuantía',
            'Régimen Especial',
        ];
    }
}
