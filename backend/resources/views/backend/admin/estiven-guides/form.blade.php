{{-- Archivo: backend/resources/views/backend/admin/estiven-guides/form.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estiven-guides.index') }}"
               class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">
                    {{ $guide ? 'Editar' : 'Nueva' }} Gu&iacute;a de Marsetiv
                </h1>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $guide ? 'Modifica la guía y sus pasos' : 'Crea una nueva guía de ayuda para Marsetiv bot' }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="p-6" style="background:#f1f5f9;min-height:calc(100vh - 65px)">
        <div class="max-w-3xl mx-auto"
             x-data="{
                iconPreview: {{ Js::from($guide?->icon_image_url) }},
                iconFileName: '',
                removeIconImage: false,
                steps: {{ Js::from($guide ? $guide->steps->map(function($s) {
                    return [
                        'content' => $s->content,
                        'image_caption' => $s->image_caption,
                        'existing_image_path' => $s->image_path,
                        'existing_image_caption' => $s->image_caption,
                        'preview_url' => $s->image_url,
                        'image_file_name' => $s->image_path ? 'Imagen actual cargada' : '',
                        'remove_image' => false,
                    ];
                })->values() : [[
                    'content' => '',
                    'image_caption' => '',
                    'existing_image_path' => '',
                    'existing_image_caption' => '',
                    'preview_url' => '',
                    'image_file_name' => '',
                    'remove_image' => false,
                ]]) }},
                addStep() {
                    this.steps.push({
                        content: '',
                        image_caption: '',
                        existing_image_path: '',
                        existing_image_caption: '',
                        preview_url: '',
                        image_file_name: '',
                        remove_image: false,
                    });
                },
                removeStep(idx) {
                    if (this.steps.length > 1) this.steps.splice(idx, 1);
                },
                moveStep(idx, dir) {
                    const newIdx = idx + dir;
                    if (newIdx < 0 || newIdx >= this.steps.length) return;
                    const tmp = this.steps[idx];
                    this.steps[idx] = this.steps[newIdx];
                    this.steps[newIdx] = tmp;
                },
                onImageChange(event, idx) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.steps[idx].preview_url = URL.createObjectURL(file);
                    this.steps[idx].image_file_name = file.name;
                    this.steps[idx].remove_image = false;
                },
                onIconImageChange(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.iconPreview = URL.createObjectURL(file);
                    this.iconFileName = file.name;
                    this.removeIconImage = false;
                }
             }">

            @if($errors->any())
            <div class="mb-5 p-4 rounded-xl border text-sm" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c">
                <p class="font-semibold mb-1">Hay errores en el formulario:</p>
                <ul class="list-disc ml-4 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST"
                  action="{{ $guide ? route('admin.estiven-guides.update', $guide) : route('admin.estiven-guides.store') }}"
                enctype="multipart/form-data"
                  class="space-y-6">
                @csrf
                @if($guide) @method('PUT') @endif

                {{-- Datos generales --}}
                <div class="bg-white rounded-2xl p-6 space-y-5" style="border:1px solid #e2e8f0">
                    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Informaci&oacute;n de la gu&iacute;a</h2>

                    @php
                        $iconCatalog = [
                            '📋' => 'Lista / tareas',
                            '🆕' => 'Nuevo / crear',
                            '🔔' => 'Notificaciones',
                            '👁️' => 'Ver / consulta',
                            '📎' => 'Adjuntos / documentos',
                            '🔄' => 'Flujo / seguimiento',
                            '✅' => 'Aprobado / validado',
                            '📅' => 'Calendario / PAA',
                            '💰' => 'Financiero / presupuesto',
                            '⚖️' => 'Jurídico / legal',
                            '🌐' => 'SECOP / web',
                            '👥' => 'Usuarios / roles',
                            '⚙️' => 'Configuración',
                            '📊' => 'Reportes / indicadores',
                            '📦' => 'Repositorio / paquete',
                            '🔒' => 'Seguridad / contraseña',
                            '📧' => 'Ayuda / correo',
                        ];
                        $currentIcon = old('icon', $guide?->icon ?? '📋');
                        $isCustomIcon = !array_key_exists($currentIcon, $iconCatalog);
                    @endphp

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                        <div class="lg:col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Rol destinatario</label>
                            <select name="role" required
                                    class="w-full h-11 rounded-xl border-gray-300 text-sm focus:ring-green-500 focus:border-green-500">
                                @foreach($roles as $key => $label)
                                    <option value="{{ $key }}" @selected(old('role', $guide?->role) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Icono</label>
                            <select name="icon" required
                                    class="w-full h-11 rounded-xl border-gray-300 text-sm focus:ring-green-500 focus:border-green-500">
                                @if($isCustomIcon)
                                <option value="{{ $currentIcon }}" selected>
                                    {{ $currentIcon }} - Personalizado actual
                                </option>
                                @endif
                                @foreach($iconCatalog as $iconValue => $iconLabel)
                                <option value="{{ $iconValue }}" @selected($currentIcon === $iconValue)>
                                    {{ $iconValue }} - {{ $iconLabel }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Orden</label>
                            <input type="number" name="orden" value="{{ old('orden', $guide?->orden ?? 0) }}" min="0"
                                   class="w-full h-11 rounded-xl border-gray-300 text-sm focus:ring-green-500 focus:border-green-500">
                        </div>

                        <div class="lg:col-span-2">
                            <span class="block text-sm font-medium text-gray-700 mb-1.5">Estado</span>
                            <label class="inline-flex items-center gap-2.5 cursor-pointer select-none h-11">
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" name="activo" value="1"
                                       @checked(old('activo', $guide?->activo ?? true))
                                       class="peer sr-only">

                                <span class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-300 transition-colors peer-checked:bg-green-600">
                                    <span class="h-5 w-5 rounded-full bg-white shadow transition-transform translate-x-0.5 peer-checked:translate-x-5"></span>
                                </span>

                                <span class="text-sm font-medium text-gray-700">Activa</span>
                            </label>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400 -mt-1">Selecciona &quot;Com&uacute;n&quot; para que aplique a todos los roles. El icono se elige desde la lista.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Icono personalizado (opcional)</label>
                            <div class="flex items-center gap-2 rounded-xl border border-gray-200 px-2 py-2" style="background:#f8fafc">
                                <input id="icon_image_input" type="file" name="icon_image" accept="image/*" @change="onIconImageChange($event)" class="sr-only">
                                <label for="icon_image_input"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-gray-700 cursor-pointer hover:bg-white transition-colors"
                                       style="border:1px solid #d1d5db;background:#fff;white-space:nowrap;">
                                    Seleccionar archivo
                                </label>
                                <span class="min-w-0 flex-1 text-xs text-gray-500 truncate"
                                      x-text="removeIconImage ? 'Se quitará al guardar' : (iconFileName || (iconPreview ? 'Imagen actual cargada' : 'Sin archivo seleccionado'))"></span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Si subes una imagen, reemplaza visualmente el emoji en la lista de guias y en Marsetiv.</p>

                            <template x-if="iconPreview && !removeIconImage">
                                <div class="mt-2.5 p-2 rounded-lg inline-flex" style="border:1px solid #e2e8f0;background:#f8fafc">
                                    <img :src="iconPreview" alt="Icono personalizado" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                                </div>
                            </template>
                        </div>

                        <div class="md:pt-7">
                            <label class="inline-flex items-center gap-2.5 text-xs text-gray-600 cursor-pointer select-none">
                                <input type="checkbox" name="remove_icon_image" value="1" x-model="removeIconImage" class="sr-only">

                                <span class="inline-flex h-5 w-9 items-center rounded-full transition-colors"
                                      :style="removeIconImage ? 'background:#16a34a' : 'background:#d1d5db'">
                                    <span class="h-4 w-4 rounded-full bg-white shadow transition-transform"
                                          :class="removeIconImage ? 'translate-x-4' : 'translate-x-0.5'"></span>
                                </span>

                                <span>Quitar icono personalizado y usar emoji</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">T&iacute;tulo de la gu&iacute;a</label>
                        <input type="text" name="title" value="{{ old('title', $guide?->title) }}" required maxlength="255"
                               placeholder="Ej: Cómo crear una solicitud"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>

                {{-- Pasos --}}
                <div class="bg-white rounded-2xl p-6 space-y-4" style="border:1px solid #e2e8f0">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Pasos de la gu&iacute;a</h2>
                        <button type="button" @click="addStep()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            Agregar paso
                        </button>
                    </div>

                    <p class="text-xs text-gray-400">Puedes usar HTML b&aacute;sico en texto y opcionalmente adjuntar imagen por paso.</p>

                    <div class="space-y-3">
                        <template x-for="(step, idx) in steps" :key="idx">
                            <div class="flex items-start gap-3 group">
                                {{-- Número de paso --}}
                                <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white mt-1"
                                     style="background:linear-gradient(135deg,#15803d,#14532d);">
                                    <span x-text="idx + 1"></span>
                                </div>

                                {{-- Campo de contenido --}}
                                <div class="flex-1">
                                    <input type="hidden"
                                           :name="'steps[' + idx + '][existing_image_path]'"
                                           x-model="step.existing_image_path">
                                    <input type="hidden"
                                           :name="'steps[' + idx + '][existing_image_caption]'"
                                           x-model="step.existing_image_caption">

                                    <textarea x-model="step.content"
                                              :name="'steps[' + idx + '][content]'"
                                              rows="2" required
                                              placeholder="Describe el paso..."
                                              class="w-full rounded-lg border-gray-300 text-sm focus:ring-green-500 focus:border-green-500 resize-none"></textarea>

                                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div>
                                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 px-2 py-2" style="background:#f8fafc">
                                                <input type="file"
                                                       :id="'step_image_' + idx"
                                                       :name="'steps[' + idx + '][image]'"
                                                       accept="image/*"
                                                       @change="onImageChange($event, idx)"
                                                       class="sr-only">
                                                <label :for="'step_image_' + idx"
                                                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-gray-700 cursor-pointer hover:bg-white transition-colors"
                                                       style="border:1px solid #d1d5db;background:#fff;white-space:nowrap;">
                                                    Seleccionar archivo
                                                </label>
                                                <span class="min-w-0 flex-1 text-xs text-gray-500 truncate"
                                                      x-text="step.remove_image ? 'Se quitará al guardar' : (step.image_file_name || (step.preview_url ? 'Imagen actual cargada' : 'Sin archivo seleccionado'))"></span>
                                            </div>
                                        </div>
                                        <div>
                                            <input type="text"
                                                   x-model="step.image_caption"
                                                   :name="'steps[' + idx + '][image_caption]'"
                                                   maxlength="255"
                                                   placeholder="Pie de imagen (opcional)"
                                                   class="w-full rounded-lg border-gray-300 text-xs focus:ring-green-500 focus:border-green-500">
                                        </div>
                                    </div>

                                    <template x-if="step.preview_url && !step.remove_image">
                                        <div class="mt-2 p-2 rounded-lg" style="border:1px solid #e2e8f0;background:#f8fafc">
                                            <img :src="step.preview_url" alt="Previsualizacion" class="max-h-40 rounded-md border border-gray-200">
                                        </div>
                                    </template>

                                    <label class="mt-2 inline-flex items-center gap-2.5 text-xs text-gray-600 cursor-pointer select-none">
                                        <input type="checkbox"
                                               value="1"
                                               :name="'steps[' + idx + '][remove_image]'"
                                               x-model="step.remove_image"
                                               class="sr-only">

                                        <span class="inline-flex h-5 w-9 items-center rounded-full transition-colors"
                                              :style="step.remove_image ? 'background:#16a34a' : 'background:#d1d5db'">
                                            <span class="h-4 w-4 rounded-full bg-white shadow transition-transform"
                                                  :class="step.remove_image ? 'translate-x-4' : 'translate-x-0.5'"></span>
                                        </span>

                                        <span>Quitar imagen de este paso</span>
                                    </label>
                                </div>

                                {{-- Controles --}}
                                <div class="shrink-0 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click="moveStep(idx, -1)" title="Subir"
                                            class="p-1 rounded hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" @click="moveStep(idx, 1)" title="Bajar"
                                            class="p-1 rounded hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <button type="button" @click="removeStep(idx)" title="Eliminar"
                                            x-show="steps.length > 1"
                                            class="p-1 rounded hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.estiven-guides.index') }}"
                       class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-white transition-all"
                       style="border:1px solid #e2e8f0">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm hover:opacity-95 transition-all"
                            style="background:linear-gradient(135deg,#15803d,#14532d)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $guide ? 'Guardar cambios' : 'Crear guía' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

