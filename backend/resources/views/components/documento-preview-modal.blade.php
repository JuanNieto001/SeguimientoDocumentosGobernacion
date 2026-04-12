{{-- Archivo: backend/resources/views/components/documento-preview-modal.blade.php | Proposito: Vista documentada para mantenimiento. | @documentado-copilot 2026-04-11 --}}
{{-- 
    Componente de previsualización y control de versiones de documentos
    Uso: @include('components.documento-preview-modal')
    
    Requiere Alpine.js (ya incluido en el layout)
    Se activa con: window.dispatchEvent(new CustomEvent('abrir-preview', { detail: archivoId }))
--}}

<div x-data="documentoPreview()" 
     x-on:abrir-preview.window="abrir($event.detail)"
     x-show="abierto" 
     x-cloak
     class="fixed inset-0 z-50 overflow-hidden"
     style="display:none">
    
    {{-- Overlay --}}
    <div x-show="abierto" 
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="cerrar()" 
         class="fixed inset-0" style="background:rgba(0,0,0,.6)"></div>
    
    {{-- Panel --}}
    <div x-show="abierto" 
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed inset-0 flex items-center justify-center p-4 sm:p-8"
         style="z-index:51">
        <div class="w-full h-full max-w-[1400px] max-h-[calc(100vh-64px)] flex flex-col rounded-2xl shadow-2xl overflow-hidden bg-white">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-3 border-b" style="background:#f8fafc;border-color:#e2e8f0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center" style="background:#dbeafe">
                    <svg class="w-4 h-4" style="color:#2563eb" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-semibold truncate" style="color:#1e293b" x-text="archivo?.nombre_original || 'Documento'"></h3>
                    <div class="flex items-center gap-2 text-xs" style="color:#64748b">
                        <span x-text="'v' + (archivo?.version || 1)"></span>
                        <span>·</span>
                        <span x-text="archivo?.uploaded_by"></span>
                        <span>·</span>
                        <span x-text="archivo?.uploaded_at"></span>
                        <span>·</span>
                        <span x-text="archivo?.tamanio"></span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Estado badge --}}
                <template x-if="archivo?.estado === 'aprobado'">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium" style="background:#dcfce7;color:#166534">Aprobado</span>
                </template>
                <template x-if="archivo?.estado === 'pendiente'">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium" style="background:#fef9c3;color:#854d0e">Pendiente</span>
                </template>
                <template x-if="archivo?.estado === 'rechazado'">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium" style="background:#fee2e2;color:#991b1b">Rechazado</span>
                </template>
                
                {{-- Bloqueado badge --}}
                <template x-if="bloqueado">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium flex items-center gap-1" style="background:#fef2f2;color:#991b1b">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        Bloqueado
                    </span>
                </template>

                {{-- Botón descargar --}}
                <a :href="archivo?.download_url" target="_blank"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium transition"
                   style="background:#eff6ff;color:#2563eb">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar
                </a>

                {{-- Botón cerrar --}}
                <button @click="cerrar()" class="p-1.5 rounded-lg hover:bg-gray-100 transition" title="Cerrar (Esc)">
                    <svg class="w-5 h-5" style="color:#64748b" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- Cuerpo: Preview + Panel lateral --}}
            <div class="flex flex-1 overflow-hidden min-h-0">
            
            {{-- Área de preview --}}
            <div class="flex-1 flex items-center justify-center overflow-auto" style="background:#f1f5f9">
                
                {{-- Loading --}}
                <template x-if="cargando">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-sm" style="color:#64748b">Cargando previsualización...</span>
                    </div>
                </template>

                {{-- Preview: PDF --}}
                <template x-if="!cargando && archivo?.es_previsualizable && archivo?.mime_type === 'application/pdf'">
                    <iframe :src="archivo?.preview_url" class="w-full h-full border-0"></iframe>
                </template>

                {{-- Preview: Imagen --}}
                <template x-if="!cargando && archivo?.es_previsualizable && archivo?.mime_type?.startsWith('image/')">
                    <div class="p-4 flex items-center justify-center w-full h-full">
                        <img :src="archivo?.preview_url" :alt="archivo?.nombre_original" 
                             class="max-w-full max-h-full object-contain rounded-lg shadow-sm">
                    </div>
                </template>

                {{-- Preview: Texto --}}
                <template x-if="!cargando && archivo?.es_previsualizable && archivo?.mime_type?.startsWith('text/')">
                    <iframe :src="archivo?.preview_url" class="w-full h-full border-0" style="background:#fff"></iframe>
                </template>

                {{-- No previsualizable --}}
                <template x-if="!cargando && !archivo?.es_previsualizable">
                    <div class="flex flex-col items-center gap-4 p-8 text-center">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center" style="background:#e2e8f0">
                            <svg class="w-8 h-8" style="color:#64748b" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium" style="color:#334155">Este tipo de archivo no se puede previsualizar</p>
                            <p class="text-xs mt-1" style="color:#94a3b8" x-text="archivo?.mime_type"></p>
                        </div>
                        <a :href="archivo?.download_url" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition"
                           style="background:#2563eb;color:#fff">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Descargar archivo
                        </a>
                    </div>
                </template>
            </div>

            {{-- Panel lateral: Versiones + Acciones --}}
            <div class="w-96 border-l flex flex-col overflow-hidden" style="border-color:#e2e8f0;background:#fff">
                
                {{-- Tabs --}}
                <div class="flex border-b" style="border-color:#e2e8f0">
                    <button @click="tab = 'versiones'" 
                            :class="tab === 'versiones' ? 'border-b-2 text-blue-600 font-semibold' : 'text-gray-500'"
                            class="flex-1 px-3 py-2.5 text-xs text-center transition" style="border-color:#2563eb">
                        Versiones <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs" 
                                        :style="tab === 'versiones' ? 'background:#dbeafe;color:#2563eb' : 'background:#f1f5f9;color:#64748b'"
                                        x-text="versiones.length"></span>
                    </button>
                    <button @click="tab = 'acciones'" 
                            :class="tab === 'acciones' ? 'border-b-2 text-blue-600 font-semibold' : 'text-gray-500'"
                            class="flex-1 px-3 py-2.5 text-xs text-center transition" style="border-color:#2563eb">
                        Acciones
                    </button>
                </div>

                {{-- Tab: Versiones --}}
                <div x-show="tab === 'versiones'" class="flex-1 overflow-y-auto p-3 space-y-2">
                    <template x-for="ver in versiones" :key="ver.id">
                        <div class="p-2.5 rounded-xl border cursor-pointer transition hover:shadow-sm"
                             :style="ver.id === archivo?.id 
                                ? 'background:#eff6ff;border-color:#93c5fd' 
                                : 'background:#fff;border-color:#e2e8f0'"
                             @click="cargarVersion(ver.id)">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-semibold" 
                                      :style="ver.id === archivo?.id ? 'color:#2563eb' : 'color:#334155'"
                                      x-text="'Versión ' + ver.version"></span>
                                <span class="text-xs px-1.5 py-0.5 rounded-full"
                                      :style="ver.estado === 'aprobado' ? 'background:#dcfce7;color:#166534' : 
                                              ver.estado === 'rechazado' ? 'background:#fee2e2;color:#991b1b' : 
                                              'background:#fef9c3;color:#854d0e'"
                                      x-text="ver.estado"></span>
                            </div>
                            <p class="text-xs truncate" style="color:#64748b" x-text="ver.nombre_original"></p>
                            <div class="flex items-center gap-1 mt-1 text-xs" style="color:#94a3b8">
                                <span x-text="ver.uploaded_by"></span>
                                <span>·</span>
                                <span x-text="ver.uploaded_at"></span>
                            </div>
                            <template x-if="ver.es_reemplazo_admin">
                                <div class="mt-1.5 flex items-center gap-1 text-xs px-2 py-1 rounded-lg" style="background:#fef2f2;color:#991b1b">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    Reemplazo admin
                                </div>
                            </template>
                            <template x-if="ver.motivo_reemplazo">
                                <p class="mt-1 text-xs italic" style="color:#94a3b8" x-text="'Motivo: ' + ver.motivo_reemplazo"></p>
                            </template>
                        </div>
                    </template>
                    <template x-if="versiones.length === 0">
                        <p class="text-xs text-center py-4" style="color:#94a3b8">Sin historial de versiones</p>
                    </template>
                </div>

                {{-- Tab: Acciones --}}
                <div x-show="tab === 'acciones'" class="flex-1 overflow-y-auto p-3 space-y-3">
                    
                    {{-- Reemplazar documento --}}
                    <template x-if="puedeReemplazar">
                        <div class="rounded-xl border p-3" style="border-color:#e2e8f0">
                            <h4 class="text-sm font-semibold mb-3" style="color:#0f172a">
                                <template x-if="bloqueado">
                                    <span class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4" style="color:#dc2626" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                        Reemplazo administrativo
                                    </span>
                                </template>
                                <template x-if="!bloqueado">
                                    <span class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4" style="color:#2563eb" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7M16 3v4M8 3v4"/></svg>
                                        Reemplazar documento
                                    </span>
                                </template>
                            </h4>

                            <form :action="'/workflow/procesos/archivos/' + archivo?.id + '/reemplazar'" 
                                  method="POST" enctype="multipart/form-data"
                                  @submit="enviando = true">
                                <input type="hidden" name="_token" :value="csrfToken">

                                <div class="mb-3">
                                    <label class="text-xs font-medium mb-1 block" style="color:#475569">Archivo nuevo</label>
                                    <div class="border-dashed border-2 rounded-lg p-3 text-center transition hover:shadow-sm" 
                                         :class="selectedFileName ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-white'"
                                         @click="$refs.fileInput.click()" style="cursor:pointer">
                                        <input x-ref="fileInput" type="file" name="archivo" required class="hidden" @change="handleFileInput($event)">

                                        <template x-if="!selectedFileName">
                                            <div class="flex flex-col items-center gap-2">
                                                <svg class="w-8 h-8" style="color:#2563eb" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M16 3v4M8 3v4"/></svg>
                                                <p class="text-sm" style="color:#0f172a">Arrastra el archivo aquí o haz clic para seleccionar</p>
                                                <p class="text-xs text-gray-400">PDF, imágenes; máximo 10 MB</p>
                                            </div>
                                        </template>

                                        <template x-if="selectedFileName">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-sm font-medium truncate" style="color:#0f172a" x-text="selectedFileName"></p>
                                                    <p class="text-xs text-gray-500 mt-0.5" x-text="selectedFileSize"></p>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click.prevent="clearSelectedFile()" class="text-xs px-3 py-1 rounded-lg border" style="border-color:#e2e8f0">Quitar</button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Motivo obligatorio si está bloqueado (admin) --}}
                                <template x-if="bloqueado">
                                    <div class="mb-3">
                                        <label class="text-xs font-medium block mb-1" style="color:#64748b">Motivo del reemplazo *</label>
                                        <textarea name="motivo_reemplazo" required minlength="10" rows="3"
                                                  class="w-full text-sm rounded-lg border p-2 resize-none"
                                                  style="border-color:#e2e8f0"
                                                  placeholder="Explique el motivo del reemplazo administrativo..."></textarea>
                                    </div>
                                </template>

                                <div class="flex items-center gap-2">
                                    <button type="button" @click.prevent="cerrar()" class="flex-1 py-2 rounded-lg text-sm font-medium border" style="border-color:#e2e8f0;color:#374151">Cancelar</button>
                                    <button type="submit" :disabled="enviando || !selectedFileName" 
                                            class="flex-1 py-2 rounded-lg text-sm font-semibold text-white"
                                            :style="bloqueado ? 'background:#dc2626' : 'background:#14532d'">
                                        <span x-text="enviando ? 'Subiendo...' : (bloqueado ? 'Reemplazar (Admin)' : 'Reemplazar')"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </template>
                    
                    <template x-if="!puedeReemplazar">
                        <div class="rounded-xl p-3 text-center" style="background:#fef2f2">
                            <svg class="w-6 h-6 mx-auto mb-1" style="color:#991b1b" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-xs font-medium" style="color:#991b1b">Documento bloqueado</p>
                            <p class="text-xs mt-1" style="color:#b91c1c">La siguiente área ya recibió este documento. Solo un administrador puede reemplazarlo.</p>
                        </div>
                    </template>

                    {{-- Info del archivo --}}
                    <div class="rounded-xl border p-3 space-y-2" style="border-color:#e2e8f0">
                        <h4 class="text-xs font-semibold" style="color:#334155">Información</h4>
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-xs">
                                <span style="color:#94a3b8">Tipo</span>
                                <span style="color:#334155" x-text="(archivo?.tipo_archivo || '').replace(/_/g, ' ')"></span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span style="color:#94a3b8">MIME</span>
                                <span style="color:#334155" x-text="archivo?.mime_type"></span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span style="color:#94a3b8">Tamaño</span>
                                <span style="color:#334155" x-text="archivo?.tamanio"></span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span style="color:#94a3b8">Versión</span>
                                <span style="color:#334155" x-text="'v' + (archivo?.version || 1)"></span>
                            </div>
                            <template x-if="archivo?.observaciones">
                                <div class="pt-1 border-t" style="border-color:#f1f5f9">
                                    <span class="text-xs" style="color:#94a3b8">Observaciones:</span>
                                    <p class="text-xs mt-0.5" style="color:#64748b" x-text="archivo.observaciones"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Keyboard shortcut: Escape to close --}}
<div x-data @keydown.escape.window="$dispatch('close-preview')"></div>

<script>
function documentoPreview() {
    return {
    abierto: false,
    cargando: false,
    archivo: null,
    versiones: [],
    bloqueado: false,
    puedeReemplazar: false,
    tab: 'versiones',
    enviando: false,
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
    // file selection state for replace form
    selectedFileName: null,
    selectedFileSize: null,

        handleFileInput(e) {
            const f = e.target.files && e.target.files[0];
            if (!f) {
                this.selectedFileName = null;
                this.selectedFileSize = null;
                return;
            }
            this.selectedFileName = f.name;
            this.selectedFileSize = this.humanFileSize(f.size);
        },

        clearSelectedFile() {
            this.selectedFileName = null;
            this.selectedFileSize = null;
            if (this.$refs && this.$refs.fileInput) this.$refs.fileInput.value = null;
        },

        humanFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B','KB','MB','GB','TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        abrir(archivoId) {
            this.abierto = true;
            this.cargando = true;
            this.tab = 'versiones';
            this.enviando = false;
            
            fetch(`/workflow/procesos/archivos/${archivoId}/preview`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.archivo = data.archivo;
                    this.versiones = data.versiones;
                    this.bloqueado = data.bloqueado;
                    this.puedeReemplazar = data.puede_reemplazar;
                }
                this.cargando = false;
            })
            .catch(() => {
                this.cargando = false;
            });
        },

        cargarVersion(archivoId) {
            this.cargando = true;
            fetch(`/workflow/procesos/archivos/${archivoId}/preview`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.archivo = data.archivo;
                    this.versiones = data.versiones;
                    this.bloqueado = data.bloqueado;
                    this.puedeReemplazar = data.puede_reemplazar;
                }
                this.cargando = false;
            })
            .catch(() => {
                this.cargando = false;
            });
        },

        cerrar() {
            this.abierto = false;
            this.archivo = null;
            this.versiones = [];
            // reset file selection when modal closes
            this.selectedFileName = null;
            this.selectedFileSize = null;
            if (this.$refs && this.$refs.fileInput) {
                this.$refs.fileInput.value = null;
            }
        }
    };
}
</script>

