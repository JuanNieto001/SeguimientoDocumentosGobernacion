<?php
/**
 * Archivo: backend/App/Http/Controllers/Admin/EstivenGuideController.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EstivenGuide;
use App\Support\RoleLabels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'steps.*.image'  => 'nullable|image|max:4096',
            'steps.*.image_caption' => 'nullable|string|max:255',
        ]);

        $guide = EstivenGuide::create([
            'role'   => $data['role'],
            'icon'   => $data['icon'],
            'title'  => $data['title'],
            'orden'  => $data['orden'] ?? 0,
            'activo' => $request->boolean('activo', true),
        ]);

        foreach ($data['steps'] as $i => $step) {
            $imagePath = null;
            $imageFile = $request->file("steps.$i.image");
            if ($imageFile) {
                $imagePath = $imageFile->store("estiven-guides/{$guide->id}", 'public');
            }

            $guide->steps()->create([
                'step_number' => $i + 1,
                'content'     => $step['content'],
                'image_path'  => $imagePath,
                'image_caption' => $step['image_caption'] ?? null,
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
            'steps.*.image'  => 'nullable|image|max:4096',
            'steps.*.image_caption' => 'nullable|string|max:255',
            'steps.*.existing_image_path' => 'nullable|string|max:500',
            'steps.*.existing_image_caption' => 'nullable|string|max:255',
            'steps.*.remove_image' => 'nullable|boolean',
        ]);

        $estivenGuide->update([
            'role'   => $data['role'],
            'icon'   => $data['icon'],
            'title'  => $data['title'],
            'orden'  => $data['orden'] ?? 0,
            'activo' => $request->boolean('activo', true),
        ]);

        // Reemplazar pasos
        $previousImages = $estivenGuide->steps()
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->all();

        $usedImages = [];

        $estivenGuide->steps()->delete();
        foreach ($data['steps'] as $i => $step) {
            $removeImage = (bool) ($step['remove_image'] ?? false);
            $imageFile = $request->file("steps.$i.image");
            $imagePath = null;

            if ($imageFile) {
                $imagePath = $imageFile->store("estiven-guides/{$estivenGuide->id}", 'public');
            } elseif (!$removeImage && !empty($step['existing_image_path'])) {
                $imagePath = $step['existing_image_path'];
            }

            if ($imagePath) {
                $usedImages[] = $imagePath;
            }

            $caption = $step['image_caption'] ?? null;
            if ((!$caption || trim((string) $caption) === '') && !empty($step['existing_image_caption'])) {
                $caption = $step['existing_image_caption'];
            }
            if (!$imagePath) {
                $caption = null;
            }

            $estivenGuide->steps()->create([
                'step_number' => $i + 1,
                'content'     => $step['content'],
                'image_path'  => $imagePath,
                'image_caption' => $caption,
            ]);
        }

        $toDelete = array_diff($previousImages, $usedImages);
        if (!empty($toDelete)) {
            Storage::disk('public')->delete($toDelete);
        }

        return redirect()->route('admin.estiven-guides.index')
            ->with('success', 'Guía actualizada correctamente.');
    }

    public function destroy(EstivenGuide $estivenGuide)
    {
        $images = $estivenGuide->steps()
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->all();

        $estivenGuide->delete();

        if (!empty($images)) {
            Storage::disk('public')->delete($images);
        }

        return redirect()->route('admin.estiven-guides.index')
            ->with('success', 'Guía eliminada correctamente.');
    }
}

