<?php
/**
 * Archivo: backend/App/Http/Controllers/Admin/EstivenGuideController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EstivenGuide;
use App\Models\EstivenGuideStep;
use App\Support\RoleLabels;
use Illuminate\Http\Request;

class EstivenGuideController extends Controller
{
    public function index(Request $request)
    {
        $filterRole = $request->get('role');

        $query = EstivenGuide::withCount('steps')->orderBy('role')->orderBy('orden');

        if ($filterRole) {
            $query->where('role', $filterRole);
        }

        $guides = $query->get();

        // Roles disponibles para el filtro
        $roles = collect(['_common' => 'Común (todos los roles)'])
            ->merge(RoleLabels::LABELS)
            ->all();

        return view('admin.estiven-guides.index', compact('guides', 'roles', 'filterRole'));
    }

    public function create()
    {
        $roles = collect(['_common' => 'Común (todos los roles)'])
            ->merge(RoleLabels::LABELS)
            ->all();

        return view('admin.estiven-guides.form', [
            'guide' => null,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role'           => 'required|string|max:50',
            'icon'           => 'required|string|max:10',
            'title'          => 'required|string|max:255',
            'orden'          => 'integer|min:0',
            'activo'         => 'boolean',
            'steps'          => 'required|array|min:1',
            'steps.*.content'=> 'required|string',
        ]);

        $guide = EstivenGuide::create([
            'role'   => $data['role'],
            'icon'   => $data['icon'],
            'title'  => $data['title'],
            'orden'  => $data['orden'] ?? 0,
            'activo' => $request->boolean('activo', true),
        ]);

        foreach ($data['steps'] as $i => $step) {
            $guide->steps()->create([
                'step_number' => $i + 1,
                'content'     => $step['content'],
            ]);
        }

        return redirect()->route('admin.estiven-guides.index')
            ->with('success', 'Guía creada correctamente.');
    }

    public function edit(EstivenGuide $estivenGuide)
    {
        $estivenGuide->load('steps');

        $roles = collect(['_common' => 'Común (todos los roles)'])
            ->merge(RoleLabels::LABELS)
            ->all();

        return view('admin.estiven-guides.form', [
            'guide' => $estivenGuide,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, EstivenGuide $estivenGuide)
    {
        $data = $request->validate([
            'role'           => 'required|string|max:50',
            'icon'           => 'required|string|max:10',
            'title'          => 'required|string|max:255',
            'orden'          => 'integer|min:0',
            'activo'         => 'boolean',
            'steps'          => 'required|array|min:1',
            'steps.*.content'=> 'required|string',
        ]);

        $estivenGuide->update([
            'role'   => $data['role'],
            'icon'   => $data['icon'],
            'title'  => $data['title'],
            'orden'  => $data['orden'] ?? 0,
            'activo' => $request->boolean('activo', true),
        ]);

        // Reemplazar pasos
        $estivenGuide->steps()->delete();
        foreach ($data['steps'] as $i => $step) {
            $estivenGuide->steps()->create([
                'step_number' => $i + 1,
                'content'     => $step['content'],
            ]);
        }

        return redirect()->route('admin.estiven-guides.index')
            ->with('success', 'Guía actualizada correctamente.');
    }

    public function destroy(EstivenGuide $estivenGuide)
    {
        $estivenGuide->delete();

        return redirect()->route('admin.estiven-guides.index')
            ->with('success', 'Guía eliminada correctamente.');
    }
}

