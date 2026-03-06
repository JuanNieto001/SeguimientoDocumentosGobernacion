{{--
    Agente Estiven – Asistente flotante de ayuda contextual.
    Se incluye dentro del layout principal (app.blade.php).
    Usa Alpine.js (ya disponible globalmente).
--}}
@auth
@php
    $user       = auth()->user();
    $roleName   = $user->roles->pluck('name')->first() ?? '';
    $userName   = explode(' ', $user->name)[0];

    /* ─────────────────────────────────────────────
     *  Guías por rol – cada guía tiene:
     *    icon   → emoji / icono corto
     *    title  → nombre de la acción
     *    steps  → arreglo de pasos (string)
     * ───────────────────────────────────────────── */

    // ── Guías comunes a todos los roles ──
    $commonGuides = [
        [
            'icon'  => '📋',
            'title' => 'Ver mis tareas pendientes',
            'steps' => [
                'Ingresa con tu usuario y contraseña.',
                'En el menú lateral busca la sección <strong>"Mi Área"</strong> o <strong>"Mis Solicitudes"</strong>.',
                'Allí verás los procesos que requieren tu acción. Los que estén resaltados son los urgentes.',
                'Haz clic en un proceso para ver su detalle y las acciones disponibles.',
            ],
        ],
        [
            'icon'  => '🔔',
            'title' => 'Revisar notificaciones',
            'steps' => [
                'Haz clic en el ícono de campana 🔔 en la parte superior derecha.',
                'Verás las alertas nuevas resaltadas. Haz clic en una para ir al detalle.',
                'Puedes marcarlas como leídas o eliminarlas individualmente.',
                'También puedes ir a <strong>Notificaciones</strong> en el menú lateral para ver el historial completo.',
            ],
        ],
        [
            'icon'  => '🔒',
            'title' => 'Cambiar mi contraseña',
            'steps' => [
                'Solicita al administrador del sistema un restablecimiento de contraseña.',
                'El administrador puede hacerlo desde <strong>Usuarios → Editar → Restablecer contraseña</strong>.',
                'Recibirás las nuevas credenciales para iniciar sesión.',
            ],
        ],
    ];

    // ── Guías específicas por rol ──
    $roleGuides = [];

    // ADMIN / ADMIN_GENERAL
    if (in_array($roleName, ['admin', 'admin_general'])) {
        $roleGuides = [
            [
                'icon'  => '🆕',
                'title' => 'Cómo crear un proceso',
                'steps' => [
                    'Ve al menú lateral y haz clic en <strong>"Nueva solicitud"</strong> dentro de la sección Procesos.',
                    'Selecciona el <strong>tipo de proceso</strong> (Contratación Directa Persona Natural, etc.).',
                    'Elige la <strong>Secretaría</strong> y la <strong>Unidad solicitante</strong>.',
                    'Llena los campos requeridos: objeto contractual, valor estimado y justificación.',
                    'Haz clic en <strong>"Crear proceso"</strong>. El sistema asignará automáticamente las etapas del flujo.',
                ],
            ],
            [
                'icon'  => '🏢',
                'title' => 'Asignar dependencias o secretarías',
                'steps' => [
                    'Ve a <strong>Administración → Secretarías</strong> en el menú lateral.',
                    'Puedes crear nuevas secretarías con el botón <strong>"+ Nueva secretaría"</strong>.',
                    'Dentro de cada secretaría, haz clic para gestionar sus <strong>Unidades</strong>.',
                    'Para asignar usuarios a una secretaría, ve a <strong>Usuarios → Editar</strong> y selecciona la secretaría/unidad correspondiente.',
                ],
            ],
            [
                'icon'  => '📎',
                'title' => 'Cómo cargar documentos requeridos',
                'steps' => [
                    'Abre el proceso desde la bandeja o desde <strong>"Ver todos"</strong> los procesos.',
                    'En el detalle del proceso, busca la etapa actual.',
                    'Verás los documentos requeridos con su estado (pendiente, cargado, aprobado).',
                    'Haz clic en <strong>"Subir archivo"</strong> junto al documento deseado.',
                    'Selecciona el archivo PDF o imagen y confirma. El documento quedará en estado "Cargado" hasta que sea aprobado.',
                ],
            ],
            [
                'icon'  => '🔄',
                'title' => 'Seguimiento de un flujo de contratación',
                'steps' => [
                    'Abre cualquier proceso desde la sección <strong>"Ver todos"</strong>.',
                    'Verás un resumen visual de todas las etapas del flujo y en cuál se encuentra.',
                    'Las etapas completadas se muestran en verde ✅, la actual en azul 🔵 y las pendientes en gris.',
                    'Dentro de cada etapa puedes ver los documentos, responsables y fechas.',
                    'En la pestaña de <strong>Auditoría</strong> puedes revisar cada acción realizada en el proceso.',
                ],
            ],
            [
                'icon'  => '⚙️',
                'title' => 'Configurar flujos en el Motor de Flujos',
                'steps' => [
                    'Ve a <strong>Motor de Flujos</strong> en el menú lateral.',
                    'Selecciona una secretaría para ver sus flujos configurados.',
                    'Puedes crear un nuevo flujo o editar uno existente pulsando <strong>"Editar en Canvas"</strong>.',
                    'Cada paso del flujo define: nombre, área responsable, documentos y días estimados.',
                    'Cuando termines, publica la versión para que aplique a los nuevos procesos.',
                ],
            ],
            [
                'icon'  => '👥',
                'title' => 'Gestionar usuarios y roles',
                'steps' => [
                    'Ve a <strong>Administración → Usuarios</strong>.',
                    'Puedes usar los filtros para buscar por secretaría, unidad, rol o nombre.',
                    'Para crear un usuario nuevo, haz clic en <strong>"+ Nuevo usuario"</strong>.',
                    'Asígnale un nombre, email, contraseña, rol, secretaría y unidad.',
                    'Los roles determinan qué puede ver y hacer cada usuario en el sistema.',
                ],
            ],
            [
                'icon'  => '📊',
                'title' => 'Ver reportes y estadísticas',
                'steps' => [
                    'Ve a <strong>Análisis → Reportes</strong> en el menú lateral.',
                    'Podrás ver reportes de estado general, por dependencia, actividad por actor y auditoría.',
                    'Usa los filtros de fecha y secretaría para acotar la información.',
                    'Los gráficos te muestran la distribución de procesos por estado y área.',
                ],
            ],
        ];
    }

    // UNIDAD SOLICITANTE
    elseif ($roleName === 'unidad_solicitante') {
        $roleGuides = [
            [
                'icon'  => '🆕',
                'title' => 'Cómo crear una solicitud',
                'steps' => [
                    'Haz clic en <strong>"Nueva solicitud"</strong> en el menú lateral.',
                    'Selecciona el tipo de proceso de contratación.',
                    'Completa los datos: objeto contractual, valor estimado y justificación.',
                    'Adjunta los documentos iniciales si están disponibles.',
                    'Envía la solicitud. Pasará a la siguiente etapa del flujo (normalmente Planeación).',
                ],
            ],
            [
                'icon'  => '📎',
                'title' => 'Cargar documentos de mi etapa',
                'steps' => [
                    'Entra a <strong>"Mi bandeja"</strong> y abre el proceso.',
                    'Verás los documentos requeridos en tu etapa actual.',
                    'Haz clic en <strong>"Subir archivo"</strong> junto a cada documento.',
                    'Sube archivos PDF, imágenes o documentos según corresponda.',
                    'Cuando todos los documentos estén cargados, podrás enviar el proceso a la siguiente etapa.',
                ],
            ],
            [
                'icon'  => '🔄',
                'title' => 'Ver el estado de mis procesos',
                'steps' => [
                    'Ve a <strong>"Mi bandeja"</strong> para ver los procesos asignados a tu unidad.',
                    'Cada proceso muestra su estado actual y la etapa en que se encuentra.',
                    'Haz clic en un proceso para ver el detalle completo con todas las etapas.',
                    'Los procesos que requieren tu acción aparecerán resaltados.',
                ],
            ],
            [
                'icon'  => '✅',
                'title' => 'Validar información del contratista',
                'steps' => [
                    'Cuando el proceso llegue a la etapa de <strong>"Validación del Contratista"</strong>, recibirás una notificación.',
                    'Abre el proceso y verifica los documentos: Hoja de Vida SIGEP, Certificados, Antecedentes, etc.',
                    'Marca cada check de verificación cuando esté correcto.',
                    'Si falta información, puedes devolver el proceso con observaciones.',
                ],
            ],
        ];
    }

    // PLANEACIÓN
    elseif ($roleName === 'planeacion') {
        $roleGuides = [
            [
                'icon'  => '📋',
                'title' => 'Revisar procesos en mi bandeja',
                'steps' => [
                    'Ve a <strong>"Mi bandeja"</strong> en el menú lateral.',
                    'Verás los procesos que requieren verificación de Planeación.',
                    'Revisa que el proceso esté alineado con el PAA.',
                    'Verifica los documentos cargados y apruébalos o recházalos.',
                ],
            ],
            [
                'icon'  => '📅',
                'title' => 'Gestionar el Plan Anual (PAA)',
                'steps' => [
                    'Ve a <strong>"Plan Anual (PAA)"</strong> en el menú lateral.',
                    'Aquí puedes ver los registros del PAA vigente.',
                    'Puedes crear nuevos registros, editarlos, verificarlos o generar certificados.',
                    'El PAA se vincula a los procesos para validar disponibilidad.',
                ],
            ],
            [
                'icon'  => '🔄',
                'title' => 'Aprobar o devolver un proceso',
                'steps' => [
                    'Abre el proceso desde tu bandeja.',
                    'Revisa toda la documentación de la etapa actual.',
                    'Si todo está correcto, haz clic en <strong>"Aprobar y avanzar"</strong>.',
                    'Si hay observaciones, usa <strong>"Devolver"</strong> e indica el motivo.',
                ],
            ],
        ];
    }

    // HACIENDA
    elseif ($roleName === 'hacienda') {
        $roleGuides = [
            [
                'icon'  => '💰',
                'title' => 'Emitir CDP / Certificado de Disponibilidad',
                'steps' => [
                    'Ve a <strong>"Mi bandeja"</strong> para ver los procesos asignados a Hacienda.',
                    'Abre el proceso y revisa los datos financieros.',
                    'Carga el documento del CDP cuando esté listo.',
                    'Aprueba para que el proceso avance a la siguiente etapa.',
                ],
            ],
            [
                'icon'  => '📄',
                'title' => 'Revisar viabilidad económica',
                'steps' => [
                    'En el detalle del proceso, revisa el valor estimado y la justificación.',
                    'Verifica la disponibilidad presupuestal.',
                    'Si es viable, aprueba y carga los soportes correspondientes.',
                    'Si no es viable, devuelve con observaciones detalladas.',
                ],
            ],
        ];
    }

    // JURÍDICA
    elseif ($roleName === 'juridica') {
        $roleGuides = [
            [
                'icon'  => '⚖️',
                'title' => 'Verificar documentos legales',
                'steps' => [
                    'Abre el proceso desde <strong>"Mi bandeja"</strong>.',
                    'Revisa los documentos del contratista: antecedentes, certificados, pólizas.',
                    'Emite el concepto de <strong>"Ajustado a Derecho"</strong> si todo está correcto.',
                    'Carga la minuta del contrato cuando corresponda.',
                ],
            ],
            [
                'icon'  => '📝',
                'title' => 'Gestionar contratos y pólizas',
                'steps' => [
                    'Una vez aprobados los documentos, prepara la minuta del contrato.',
                    'Carga la minuta firmada en el sistema.',
                    'Verifica las pólizas de cumplimiento y apruébalas.',
                    'El proceso pasará automáticamente a la siguiente etapa (SECOP).',
                ],
            ],
        ];
    }

    // SECOP
    elseif ($roleName === 'secop') {
        $roleGuides = [
            [
                'icon'  => '🌐',
                'title' => 'Publicar proceso en SECOP II',
                'steps' => [
                    'Abre el proceso desde <strong>"Mi bandeja"</strong>.',
                    'Verifica que todos los documentos anteriores estén aprobados.',
                    'Publica el proceso en la plataforma SECOP II.',
                    'Registra el número de contrato electrónico en el sistema.',
                    'Genera el acta de inicio y cárgala al proceso.',
                ],
            ],
        ];
    }

    // PROFESIONAL DE CONTRATACIÓN
    elseif ($roleName === 'profesional_contratacion') {
        $roleGuides = [
            [
                'icon'  => '📋',
                'title' => 'Elaborar documentos contractuales',
                'steps' => [
                    'Revisa los procesos asignados en <strong>"Documentos Pendientes"</strong>.',
                    'Prepara los estudios previos, análisis del sector y demás documentos requeridos.',
                    'Carga cada documento en la etapa correspondiente.',
                    'Envía para revisión cuando estén completos.',
                ],
            ],
        ];
    }

    // REVISOR JURÍDICO
    elseif ($roleName === 'revisor_juridico') {
        $roleGuides = [
            [
                'icon'  => '🔍',
                'title' => 'Revisar documentos asignados',
                'steps' => [
                    'Ve a <strong>"Documentos Pendientes"</strong> en el menú lateral.',
                    'Abre cada solicitud y revisa los documentos cargados.',
                    'Emite tu concepto: aprobado o con observaciones.',
                    'Si hay correcciones, detalla los ajustes necesarios.',
                ],
            ],
        ];
    }

    // CONSULTA
    elseif ($roleName === 'consulta') {
        $roleGuides = [
            [
                'icon'  => '👁️',
                'title' => 'Consultar procesos',
                'steps' => [
                    'Ve a <strong>"Ver procesos"</strong> en el menú lateral.',
                    'Usa los filtros para encontrar el proceso que buscas.',
                    'Haz clic para ver el detalle completo, etapas y documentos.',
                    'Tu rol es solo de consulta: puedes ver pero no modificar.',
                ],
            ],
        ];
    }

    // Combinar guías (primero las del rol, luego las comunes)
    $allGuides = array_merge($roleGuides, $commonGuides);
@endphp

{{-- ═══════════════════════════════════════════════════
     AGENTE ESTIVEN – Widget flotante
     ═══════════════════════════════════════════════════ --}}
<div x-data="{
        open: false,
        activeGuide: null,
        guides: {{ Js::from($allGuides) }},
        get currentGuide() {
            return this.activeGuide !== null ? this.guides[this.activeGuide] : null;
        }
     }"
     x-cloak
     class="fixed z-50"
     style="bottom: 1.25rem; right: 1.25rem;">

    {{-- ── Panel desplegable ── --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="mb-3 rounded-2xl overflow-hidden flex flex-col estiven-panel"
         style="width: 380px; max-height: 530px; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 25px 60px -12px rgba(0,0,0,.25), 0 0 0 1px rgba(0,0,0,.03);">

        {{-- Cabecera --}}
        <div class="shrink-0 px-5 py-4 text-white relative"
             style="background: linear-gradient(135deg, #052e16 0%, #166534 50%, #15803d 100%);">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-full flex items-center justify-center shrink-0 estiven-avatar">
                    <svg class="w-7 h-7" viewBox="0 0 100 100" fill="none">
                        <!-- Cara -->
                        <circle cx="50" cy="50" r="40" fill="#FBBF24"/>
                        <!-- Ojos -->
                        <ellipse cx="36" cy="42" rx="5" ry="6" fill="#1e293b"/>
                        <ellipse cx="64" cy="42" rx="5" ry="6" fill="#1e293b"/>
                        <!-- Brillo ojos -->
                        <circle cx="38" cy="40" r="2" fill="white"/>
                        <circle cx="66" cy="40" r="2" fill="white"/>
                        <!-- Sonrisa -->
                        <path d="M 33 58 Q 50 72 67 58" stroke="#1e293b" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                        <!-- Mejillas -->
                        <circle cx="27" cy="55" r="5" fill="#F97316" opacity="0.3"/>
                        <circle cx="73" cy="55" r="5" fill="#F97316" opacity="0.3"/>
                        <!-- Headset (auricular) -->
                        <path d="M 15 45 Q 15 20 50 15 Q 85 20 85 45" stroke="#166534" stroke-width="5" fill="none" stroke-linecap="round"/>
                        <rect x="10" y="40" width="10" height="14" rx="4" fill="#166534"/>
                        <rect x="80" y="40" width="10" height="14" rx="4" fill="#166534"/>
                        <!-- Micrófono -->
                        <line x1="15" y1="54" x2="15" y2="62" stroke="#166534" stroke-width="3" stroke-linecap="round"/>
                        <circle cx="15" cy="64" r="3.5" fill="#166534"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-base leading-tight">Agente Estiven</p>
                    <p class="text-green-300 text-xs mt-0.5 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block"></span>
                        En l&iacute;nea &middot; Listo para ayudarte
                    </p>
                </div>
            </div>
            <button @click="open = false; activeGuide = null"
                    class="absolute top-3.5 right-3.5 p-1.5 rounded-lg text-green-200 hover:text-white hover:bg-white/15 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Saludo --}}
        <div class="shrink-0 px-5 py-3.5" style="background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border-bottom: 1px solid #dcfce7;">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5" style="background: #FBBF24;">
                    <svg class="w-5 h-5" viewBox="0 0 100 100" fill="none">
                        <circle cx="50" cy="50" r="45" fill="#FBBF24"/>
                        <ellipse cx="36" cy="42" rx="4" ry="5" fill="#1e293b"/>
                        <ellipse cx="64" cy="42" rx="4" ry="5" fill="#1e293b"/>
                        <path d="M 35 58 Q 50 70 65 58" stroke="#1e293b" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="bg-white rounded-xl rounded-tl-sm px-3.5 py-2.5 text-sm shadow-sm" style="border: 1px solid #e2e8f0;">
                    <p class="text-gray-700 leading-relaxed">
                        &iexcl;Hola, <strong class="text-green-800">{{ $userName }}</strong>! Soy <strong>Estiven</strong>, tu asistente. &iquest;En qu&eacute; te ayudo hoy?
                    </p>
                    <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Tu rol: <strong class="text-gray-500">{{ \App\Support\RoleLabels::label($roleName) ?: 'Sin rol' }}</strong>
                    </p>
                </div>
            </div>
        </div>

        {{-- Contenido --}}
        <div class="flex-1 overflow-y-auto estiven-scroll">

            {{-- ── Lista de guías ── --}}
            <div x-show="activeGuide === null" class="p-3 space-y-0.5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 px-3 pt-2 pb-2.5">
                    Gu&iacute;as disponibles
                </p>
                <template x-for="(guide, idx) in guides" :key="idx">
                    <button @click="activeGuide = idx"
                            class="estiven-guide-btn w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left text-[13px] transition-all group">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-base shrink-0 transition-transform group-hover:scale-110"
                              style="background: #f0fdf4; border: 1px solid #dcfce7;" x-text="guide.icon"></span>
                        <span class="font-medium text-gray-700 group-hover:text-green-800 leading-snug flex-1" x-text="guide.title"></span>
                        <svg class="w-4 h-4 shrink-0 text-gray-300 group-hover:text-green-600 transition-all group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </template>
            </div>

            {{-- ── Detalle paso a paso ── --}}
            <div x-show="activeGuide !== null" class="p-4">
                <button @click="activeGuide = null"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-green-700 hover:text-green-900 hover:bg-green-50 transition-all mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Volver
                </button>

                <div class="flex items-center gap-2.5 mb-4 pb-3" style="border-bottom: 1px solid #f1f5f9;">
                    <span class="w-9 h-9 rounded-lg flex items-center justify-center text-lg"
                          style="background: linear-gradient(135deg, #f0fdf4, #dcfce7);"
                          x-text="currentGuide?.icon"></span>
                    <h3 class="text-sm font-bold text-gray-800 leading-snug" x-text="currentGuide?.title"></h3>
                </div>

                <div class="space-y-3.5">
                    <template x-for="(step, sIdx) in (currentGuide?.steps || [])" :key="sIdx">
                        <div class="flex gap-3 estiven-step">
                            <div class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white shadow-sm"
                                 style="background: linear-gradient(135deg, #15803d, #14532d); margin-top: 1px;">
                                <span x-text="sIdx + 1"></span>
                            </div>
                            <div class="flex-1 bg-gray-50 rounded-xl px-3.5 py-2.5" style="border: 1px solid #f1f5f9;">
                                <p class="text-[13px] text-gray-600 leading-relaxed" x-html="step"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-5 flex items-start gap-2.5 p-3.5 rounded-xl" style="background: linear-gradient(135deg, #fefce8, #fef9c3); border: 1px solid #fde68a;">
                    <svg class="w-5 h-5 shrink-0 text-amber-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M11.983 1.907a.75.75 0 00-1.292-.657l-8.5 9.5A.75.75 0 002.75 12h6.572l-1.305 6.093a.75.75 0 001.292.657l8.5-9.5A.75.75 0 0017.25 8h-6.572l1.305-6.093z"/>
                    </svg>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        <strong>Consejo:</strong> Si necesitas m&aacute;s ayuda, contacta al administrador del sistema o revisa tus notificaciones.
                    </p>
                </div>
            </div>
        </div>

        {{-- Footer del panel --}}
        <div class="shrink-0 px-4 py-2.5 flex items-center justify-center gap-1.5 text-[10px] text-gray-400"
             style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Agente Estiven &middot; Asistente de Gobernaci&oacute;n de Caldas
        </div>
    </div>

    {{-- ── Botón flotante con nombre ── --}}
    <div class="flex items-end justify-end gap-2">
        {{-- Tooltip / label que aparece cuando está cerrado --}}
        <div x-show="!open"
             x-transition:enter="transition ease-out duration-300 delay-500"
             x-transition:enter-start="opacity-0 translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-0"
             class="hidden sm:flex items-center gap-2 px-4 py-2.5 rounded-2xl rounded-br-sm shadow-lg cursor-pointer select-none estiven-tooltip"
             style="background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 8px 25px rgba(0,0,0,.1);"
             @click="open = true">
            <span class="text-sm font-semibold text-gray-800">Agente Estiven</span>
            <span class="text-xs text-gray-400">| &iquest;Necesitas ayuda?</span>
        </div>

        {{-- Botón circular --}}
        <button @click="open = !open; if(!open) activeGuide = null"
                class="estiven-fab relative flex items-center justify-center transition-all duration-300 focus:outline-none"
                title="Agente Estiven">

            {{-- Estado cerrado: carita --}}
            <div x-show="!open" x-transition class="w-14 h-14 rounded-full flex items-center justify-center shadow-xl estiven-fab-face"
                 style="background: linear-gradient(145deg, #FBBF24 0%, #F59E0B 100%); box-shadow: 0 8px 25px rgba(245,158,11,.4);">
                <svg class="w-9 h-9" viewBox="0 0 100 100" fill="none">
                    <!-- Cara base -->
                    <circle cx="50" cy="50" r="46" fill="#FBBF24"/>
                    <circle cx="50" cy="50" r="46" fill="url(#faceGrad)"/>
                    <!-- Ojos -->
                    <ellipse cx="35" cy="40" rx="5.5" ry="7" fill="#1e293b"/>
                    <ellipse cx="65" cy="40" rx="5.5" ry="7" fill="#1e293b"/>
                    <!-- Brillo ojos -->
                    <circle cx="37.5" cy="37.5" r="2.5" fill="white"/>
                    <circle cx="67.5" cy="37.5" r="2.5" fill="white"/>
                    <!-- Sonrisa grande -->
                    <path d="M 30 58 Q 50 78 70 58" stroke="#92400e" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                    <!-- Mejillas -->
                    <circle cx="23" cy="55" r="6" fill="#F97316" opacity="0.25"/>
                    <circle cx="77" cy="55" r="6" fill="#F97316" opacity="0.25"/>
                    <!-- Headset -->
                    <path d="M 12 42 Q 12 12 50 8 Q 88 12 88 42" stroke="#166534" stroke-width="6" fill="none" stroke-linecap="round"/>
                    <rect x="6" y="37" width="12" height="16" rx="5" fill="#166534"/>
                    <rect x="82" y="37" width="12" height="16" rx="5" fill="#166534"/>
                    <line x1="12" y1="53" x2="12" y2="63" stroke="#166534" stroke-width="3.5" stroke-linecap="round"/>
                    <circle cx="12" cy="65" r="4" fill="#15803d"/>
                    <defs>
                        <radialGradient id="faceGrad" cx="40%" cy="35%">
                            <stop offset="0%" stop-color="#FDE68A" stop-opacity="0.6"/>
                            <stop offset="100%" stop-color="#FBBF24" stop-opacity="0"/>
                        </radialGradient>
                    </defs>
                </svg>
            </div>

            {{-- Estado abierto: X --}}
            <div x-show="open" x-transition class="w-14 h-14 rounded-full flex items-center justify-center shadow-xl"
                 style="background: linear-gradient(135deg, #15803d, #052e16); box-shadow: 0 8px 25px rgba(21,128,61,.4);">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>

            {{-- Indicador de actividad --}}
            <span x-show="!open" class="absolute top-0 right-0 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center estiven-pulse"
                  style="border: 2.5px solid white; box-shadow: 0 2px 4px rgba(0,0,0,.15);">
                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
            </span>
        </button>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }

    .estiven-fab-face {
        transition: transform .3s cubic-bezier(.34,1.56,.64,1);
    }
    .estiven-fab-face:hover {
        transform: scale(1.1) rotate(-5deg);
    }

    .estiven-pulse {
        animation: estiven-pulse-ring 2s infinite;
    }
    @keyframes estiven-pulse-ring {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,.5), 0 2px 4px rgba(0,0,0,.15); }
        50% { box-shadow: 0 0 0 6px rgba(34,197,94,0), 0 2px 4px rgba(0,0,0,.15); }
    }

    .estiven-tooltip {
        animation: estiven-float 3s ease-in-out infinite;
    }
    @keyframes estiven-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }

    .estiven-guide-btn {
        border: 1px solid transparent;
    }
    .estiven-guide-btn:hover {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .estiven-step {
        animation: estiven-step-in .3s ease-out both;
    }
    .estiven-step:nth-child(1) { animation-delay: .05s; }
    .estiven-step:nth-child(2) { animation-delay: .1s; }
    .estiven-step:nth-child(3) { animation-delay: .15s; }
    .estiven-step:nth-child(4) { animation-delay: .2s; }
    .estiven-step:nth-child(5) { animation-delay: .25s; }
    @keyframes estiven-step-in {
        from { opacity: 0; transform: translateX(-8px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .estiven-scroll::-webkit-scrollbar { width: 4px; }
    .estiven-scroll::-webkit-scrollbar-track { background: transparent; }
    .estiven-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 9999px; }
    .estiven-scroll::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    .estiven-scroll { scrollbar-width: thin; scrollbar-color: #d1d5db transparent; }

    .estiven-avatar {
        background: #FBBF24;
        box-shadow: 0 0 0 3px rgba(251,191,36,.25);
    }

    .estiven-panel {
        animation: estiven-panel-in .25s ease-out;
    }
    @keyframes estiven-panel-in {
        from { opacity: 0; transform: translateY(10px) scale(.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>
@endauth
