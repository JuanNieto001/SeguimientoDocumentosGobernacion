<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estiven-guides.index') }}"
               class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">
                    {{ $guide ? 'Editar' : 'Nueva' }} Gu&iacute;a de Estiven
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
                steps: {{ Js::from($guide ? $guide->steps->map(fn($s) => ['content' => $s->content])->values() : [['content' => '']]) }},
                addStep() {
                    this.steps.push({ content: '' });
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
                  class="space-y-6">
                @csrf
                @if($guide) @method('PUT') @endif

                {{-- Datos generales --}}
                <div class="bg-white rounded-2xl p-6 space-y-5" style="border:1px solid #e2e8f0">
                    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Informaci&oacute;n de la gu&iacute;a</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Rol destinatario</label>
                            <select name="role" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-green-500 focus:border-green-500">
                                @foreach($roles as $key => $label)
                                    <option value="{{ $key }}" @selected(old('role', $guide?->role) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Selecciona &quot;Com&uacute;n&quot; para que aplique a todos los roles.</p>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Icono (emoji)</label>
                                <input type="text" name="icon" value="{{ old('icon', $guide?->icon ?? '📋') }}" required maxlength="10"
                                       class="w-full rounded-lg border-gray-300 text-center text-2xl focus:ring-green-500 focus:border-green-500 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Orden</label>
                                <input type="number" name="orden" value="{{ old('orden', $guide?->orden ?? 0) }}" min="0"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div class="flex items-end pb-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="activo" value="0">
                                    <input type="checkbox" name="activo" value="1"
                                           @checked(old('activo', $guide?->activo ?? true))
                                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                    <span class="text-sm font-medium text-gray-700">Activa</span>
                                </label>
                            </div>
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

                    <p class="text-xs text-gray-400">Puedes usar HTML b&aacute;sico: &lt;strong&gt;negrita&lt;/strong&gt;, &lt;em&gt;cursiva&lt;/em&gt;</p>

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
                                    <textarea x-model="step.content"
                                              :name="'steps[' + idx + '][content]'"
                                              rows="2" required
                                              placeholder="Describe el paso..."
                                              class="w-full rounded-lg border-gray-300 text-sm focus:ring-green-500 focus:border-green-500 resize-none"></textarea>
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
