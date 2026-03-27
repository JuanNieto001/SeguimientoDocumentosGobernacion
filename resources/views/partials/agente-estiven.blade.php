{{--
    Marsetiv bot – Asistente flotante de ayuda contextual.
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
            'title' => 'Restablecer mi contraseña',
            'steps' => [
                'En la pantalla de inicio de sesión, intenta ingresar con tu correo.',
                'Si la contraseña es incorrecta, aparecerá el botón <strong>"¿Olvidaste tu contraseña?"</strong>.',
                'Haz clic en ese botón. Tu correo registrado se rellenará automáticamente.',
                'Escribe un <strong>correo de destino</strong> donde puedas recibir el enlace (puede ser personal).',
                'Haz clic en <strong>"Enviar enlace"</strong>. Revisa tu bandeja de entrada (o spam).',
                'Abre el correo, haz clic en el botón <strong>"Restablecer contraseña"</strong> y escribe tu nueva contraseña.',
                'El enlace expira en <strong>60 minutos</strong>. Si no lo recibes, vuelve a intentarlo.',
            ],
        ],
        [
            'icon'  => '👁️',
            'title' => 'Previsualizar documentos',
            'steps' => [
                'Ve al detalle de un proceso y ubica la sección de <strong>documentos</strong>.',
                'Haz clic en el ícono de <strong>vista previa (ojo)</strong> junto al documento que deseas ver.',
                'Se abrirá un panel donde podrás ver PDFs e imágenes directamente sin descargar.',
                'En el panel lateral verás las <strong>Versiones</strong> del documento. Haz clic en una versión para verla.',
                'En la pestaña <strong>Acciones</strong> puedes reemplazar el documento o ver su información (tipo, peso, versión).',
                'Para descargar, usa el botón <strong>"Descargar"</strong> en la parte superior del panel.',
            ],
        ],
        [
            'icon'  => '🔄',
            'title' => 'Reemplazar un documento',
            'steps' => [
                'Abre la <strong>vista previa</strong> del documento que deseas reemplazar.',
                'Ve a la pestaña <strong>"Acciones"</strong> en el panel lateral.',
                'Haz clic en la zona de <strong>"Arrastra el archivo aquí o haz clic para seleccionar"</strong>.',
                'Selecciona el nuevo archivo desde tu computador. Verás el nombre y tamaño del archivo seleccionado.',
                'Si te equivocaste, presiona <strong>"Quitar"</strong> para limpiar la selección.',
                'Haz clic en <strong>"Reemplazar"</strong>. El sistema creará una nueva versión y conservará las anteriores.',
                'Si el documento está <strong>bloqueado</strong> (ya fue recibido por otra área), solo un administrador puede reemplazarlo e indicar un motivo.',
            ],
        ],
        [
            'icon'  => '📌',
            'title' => 'Ver versiones de un documento',
            'steps' => [
                'Abre la <strong>vista previa</strong> del documento.',
                'En el panel lateral derecho, ve a la pestaña <strong>"Versiones"</strong>.',
                'Verás una lista con todas las versiones: número de versión, quién la subió, fecha y estado.',
                'Haz clic en cualquier versión para cargar su previsualización.',
                'La versión actual se resalta en <strong>azul</strong>. Las aprobadas tienen etiqueta verde, rechazadas roja, pendientes amarilla.',
                'Si una versión fue un <strong>reemplazo administrativo</strong>, aparecerá etiquetada junto con el motivo.',
            ],
        ],
        [
            'icon'  => '📧',
            'title' => 'Cómo funcionan las alertas por correo',
            'steps' => [
                'El sistema envía correos automáticos cuando cambia el estado de un proceso que te involucra.',
                'Recibirás alertas cuando un proceso <strong>llega a tu área</strong>, es <strong>devuelto</strong> o necesita acción.',
                'Los correos se envían al email de tu cuenta registrada en el sistema.',
                'Dentro del correo verás un botón que te lleva directamente al proceso en el sistema.',
                'Puedes complementar las alertas por correo con las <strong>notificaciones internas</strong> (ícono de campana 🔔).',
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

    // GOBERNADOR
    elseif ($roleName === 'gobernador') {
        $roleGuides = [
            [
                'icon'  => '📊',
                'title' => 'Ver panorama general de procesos',
                'steps' => [
                    'En el <strong>Panel principal</strong> verás un resumen de todos los procesos activos.',
                    'Usa <strong>"Ver procesos"</strong> para explorar el listado completo con filtros.',
                    'Haz clic en un proceso para ver su seguimiento detallado por etapa.',
                ],
            ],
            [
                'icon'  => '🌐',
                'title' => 'Consultar contratos en SECOP II',
                'steps' => [
                    'Ve a <strong>"Consulta SECOP II"</strong> en el menú lateral.',
                    'Verás las estadísticas generales de contratación de la Gobernación.',
                    'Usa la barra de búsqueda para encontrar contratos específicos.',
                    'Haz clic en un contrato para ver su detalle completo.',
                ],
            ],
            [
                'icon'  => '📈',
                'title' => 'Revisar reportes y estadísticas',
                'steps' => [
                    'Ve a <strong>"Reportes"</strong> en el menú lateral.',
                    'Consulta el estado general, reportes por dependencia y actividad por actor.',
                    'Usa los filtros de fecha y secretaría para acotar la información.',
                ],
            ],
        ];
    }

    // Combinar guías (primero las del rol, luego las comunes)
    $allGuides = array_merge($roleGuides, $commonGuides);

    // ── Guías dinámicas desde la BD (creadas por admin) ──
    // Si existen guías en BD, reemplazan las hardcoded para evitar duplicados
    $dbGuides = \App\Models\EstivenGuide::with('steps')
        ->where('activo', true)
        ->whereIn('role', [$roleName, '_common'])
        ->orderBy('role')
        ->orderBy('orden')
        ->get();

    if ($dbGuides->isNotEmpty()) {
        $allGuides = $dbGuides->map(fn($g) => $g->toEstivenArray())->toArray();
    }
@endphp

{{-- ═══════════════════════════════════════════════════
    MARSETIV BOT – Widget flotante
     ═══════════════════════════════════════════════════ --}}
<div x-data="agenteEstiven()"
     x-cloak
    x-ref="widgetRoot"
    class="fixed z-50"
    x-bind:style="'left:' + posX + 'px; top:' + posY + 'px;'">

    {{-- ── Panel desplegable ── --}}
    <div x-show="open"
            x-ref="panel"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="absolute rounded-2xl overflow-hidden flex flex-col estiven-panel"
            x-bind:style="panelStyle">

        {{-- Cabecera --}}
           <div class="shrink-0 px-5 py-4 text-white relative"
               @pointerdown="startDrag($event)"
               style="background: linear-gradient(135deg, #052e16 0%, #166534 50%, #15803d 100%); cursor: move; touch-action: none;">
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
                    <p class="font-bold text-base leading-tight">Marsetiv bot</p>
                    <p class="text-green-300 text-xs mt-0.5 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block"></span>
                        En l&iacute;nea &middot; Listo para ayudarte
                    </p>
                </div>
            </div>
            <button @click="open = false; activeGuide = null; vista = 'guias'"
                    @pointerdown.stop
                    data-no-drag
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
                        &iexcl;Hola, <strong class="text-green-800">{{ $userName }}</strong>! Soy <strong>Marsetiv bot</strong>, tu asistente. &iquest;En qu&eacute; te ayudo hoy?
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
            <div x-show="activeGuide === null && vista === 'guias'" class="p-3 space-y-0.5">
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

                {{-- Separador + Botón solicitar ayuda --}}
                <div class="pt-3 mt-2" style="border-top: 1px solid #f1f5f9;">
                    <button @click="vista = 'ayuda'"
                            class="w-full flex items-center gap-3 px-3 py-3 rounded-xl text-left text-[13px] transition-all group"
                            style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #bfdbfe;">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-base shrink-0"
                              style="background: #2563eb; color: #fff;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        <span class="font-semibold leading-snug flex-1" style="color: #1e40af;">¿Necesitas más ayuda? Escríbenos</span>
                        <svg class="w-4 h-4 shrink-0 text-blue-400 group-hover:text-blue-600 transition-all group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- ── Detalle paso a paso ── --}}
            <div x-show="activeGuide !== null && vista === 'guias'" class="p-4">
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
                        <strong>Consejo:</strong> Si necesitas m&aacute;s ayuda, usa el bot&oacute;n <strong>"&iquest;Necesitas m&aacute;s ayuda?"</strong> en la lista de gu&iacute;as para enviar un correo al equipo de soporte.
                    </p>
                </div>
            </div>

            {{-- ── Formulario de solicitud de ayuda por correo ── --}}
            <div x-show="vista === 'ayuda'" class="p-4">
                <button @click="vista = 'guias'; helpError = ''; helpEnviado = false"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-700 hover:text-blue-900 hover:bg-blue-50 transition-all mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Volver a gu&iacute;as
                </button>

                <div class="flex items-center gap-2.5 mb-4 pb-3" style="border-bottom: 1px solid #f1f5f9;">
                    <span class="w-9 h-9 rounded-lg flex items-center justify-center"
                          style="background: linear-gradient(135deg, #eff6ff, #dbeafe);">
                        <svg class="w-5 h-5" style="color:#2563eb" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <h3 class="text-sm font-bold leading-snug" style="color:#1e293b">Solicitar ayuda por correo</h3>
                </div>

                {{-- Mensaje de éxito --}}
                <div x-show="helpEnviado" x-transition class="mb-4 flex items-start gap-2.5 p-3.5 rounded-xl text-sm"
                     style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>&iexcl;Correo enviado! El equipo de soporte te responder&aacute; pronto.</span>
                </div>

                {{-- Mensaje de error --}}
                <div x-show="helpError" x-transition class="mb-4 flex items-start gap-2.5 p-3.5 rounded-xl text-sm"
                     style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span x-text="helpError"></span>
                </div>

                <div x-show="!helpEnviado" class="space-y-3">
                    <p class="text-xs leading-relaxed" style="color:#64748b">
                        Describe tu problema o duda y el equipo de soporte te responder&aacute; al correo registrado (<strong>{{ $user->email }}</strong>).
                    </p>

                    <div>
                        <label class="text-xs font-medium block mb-1" style="color:#374151">Asunto</label>
                        <input x-model="helpAsunto" type="text" maxlength="150"
                               placeholder="Ej: No puedo subir un documento en la etapa 3"
                               class="w-full text-sm rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               style="border-color:#e2e8f0">
                    </div>

                    <div>
                        <label class="text-xs font-medium block mb-1" style="color:#374151">Describe tu problema</label>
                        <textarea x-model="helpMensaje" rows="4" maxlength="1500"
                                  placeholder="Cuéntanos con detalle qué necesitas: en qué pantalla estás, qué intentas hacer y qué error ves..."
                                  class="w-full text-sm rounded-lg border px-3 py-2 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  style="border-color:#e2e8f0"></textarea>
                        <p class="text-right text-[10px] mt-0.5" style="color:#94a3b8"><span x-text="helpMensaje.length"></span>/1500</p>
                    </div>

                    <button @click="enviarAyuda()" :disabled="helpEnviando"
                            class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition flex items-center justify-center gap-2"
                            style="background:#2563eb">
                        <template x-if="!helpEnviando">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                Enviar solicitud de ayuda
                            </span>
                        </template>
                        <template x-if="helpEnviando">
                            <span class="flex items-center gap-2">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                Enviando...
                            </span>
                        </template>
                    </button>
                </div>

                <div class="mt-4 flex items-start gap-2.5 p-3 rounded-xl" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" style="color:#94a3b8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-[11px] leading-relaxed" style="color:#94a3b8">
                        Tu nombre, correo y rol se incluir&aacute;n autom&aacute;ticamente en el mensaje para que el equipo pueda ayudarte m&aacute;s r&aacute;pido.
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
            Marsetiv bot &middot; Asistente de Gobernaci&oacute;n de Caldas
        </div>
    </div>

    {{-- ── Botón flotante (sin tooltip para evitar saltos) ── --}}
        <button @click="toggleOpenFromFab()"
                x-ref="fab"
                @pointerdown="startDrag($event)"
                class="estiven-fab relative flex items-center justify-center transition-all duration-300 focus:outline-none"
                title="Marsetiv bot">

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

<style>
    [x-cloak] { display: none !important; }

    .estiven-fab-face {
        transition: transform .3s cubic-bezier(.34,1.56,.64,1);
    }
    .estiven-fab-face:hover {
        transform: scale(1.03);
    }

    .estiven-pulse {
        animation: estiven-pulse-ring 2s infinite;
    }
    @keyframes estiven-pulse-ring {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,.5), 0 2px 4px rgba(0,0,0,.15); }
        50% { box-shadow: 0 0 0 6px rgba(34,197,94,0), 0 2px 4px rgba(0,0,0,.15); }
    }

    .estiven-tooltip {
        animation: none;
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

<script>
function agenteEstiven() {
    return {
        open: false,
        activeGuide: null,
        vista: 'guias',
        posX: 0,
        posY: 0,
        panelLeft: -324,
        panelTop: -540,
        dragging: false,
        dragMoved: false,
        dragOffsetX: 0,
        dragOffsetY: 0,
        dragStartX: 0,
        dragStartY: 0,
        moveHandler: null,
        upHandler: null,
        resizeObserver: null,
        resizeHandler: null,
        guides: @json($allGuides),
        helpAsunto: '',
        helpMensaje: '',
        helpEnviando: false,
        helpEnviado: false,
        helpError: '',
        storageKey: 'marsetiv_widget_position_v1',
        get panelStyle() {
            return `width: 380px; max-height: 530px; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 25px 60px -12px rgba(0,0,0,.25), 0 0 0 1px rgba(0,0,0,.03); left: ${this.panelLeft}px; top: ${this.panelTop}px;`;
        },

        init() {
            this.$nextTick(() => {
                this.restorePosition();
                this.resizeHandler = () => {
                    this.clampToViewport();
                    this.updatePanelPlacement();
                };
                window.addEventListener('resize', this.resizeHandler);
                this.$watch('open', (isOpen) => {
                    if (isOpen) {
                        this.$nextTick(() => {
                            this.observePanelSize();
                            this.updatePanelPlacement();
                        });
                    } else {
                        this.disconnectPanelObserver();
                    }
                });
            });
        },

        observePanelSize() {
            const panel = this.$refs.panel;
            if (!panel || typeof ResizeObserver === 'undefined') return;

            this.disconnectPanelObserver();
            this.resizeObserver = new ResizeObserver(() => {
                this.updatePanelPlacement();
            });
            this.resizeObserver.observe(panel);
        },

        disconnectPanelObserver() {
            if (this.resizeObserver) {
                this.resizeObserver.disconnect();
                this.resizeObserver = null;
            }
        },

        restorePosition() {
            try {
                const raw = localStorage.getItem(this.storageKey);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (typeof parsed?.x === 'number' && typeof parsed?.y === 'number') {
                        this.posX = parsed.x;
                        this.posY = parsed.y;
                        this.$nextTick(() => this.clampToViewport());
                        return;
                    }
                }
            } catch (_) {
                // Ignorar errores de lectura/parsing y usar posicion inicial.
            }

            this.setInitialPosition();
        },

        persistPosition() {
            try {
                localStorage.setItem(this.storageKey, JSON.stringify({ x: this.posX, y: this.posY }));
            } catch (_) {
                // Ignorar errores de almacenamiento.
            }
        },

        setInitialPosition() {
            const margin = 20;
            const fab = this.$refs.fab;
            const fabW = fab?.offsetWidth || 56;
            const fabH = fab?.offsetHeight || 56;
            this.posX = Math.max(margin, window.innerWidth - fabW - margin);
            this.posY = Math.max(margin, window.innerHeight - fabH - margin);
            this.updatePanelPlacement();
        },

        clampToViewport() {
            const margin = 12;
            const fab = this.$refs.fab;
            const fabW = fab?.offsetWidth || 56;
            const fabH = fab?.offsetHeight || 56;
            const maxX = Math.max(margin, window.innerWidth - fabW - margin);
            const maxY = Math.max(margin, window.innerHeight - fabH - margin);
            this.posX = Math.min(Math.max(margin, this.posX), maxX);
            this.posY = Math.min(Math.max(margin, this.posY), maxY);
        },

        updatePanelPlacement() {
            const margin = 12;
            const gap = 10;
            const panelWidth = 380;
            const panelHeight = Math.min(this.$refs.panel?.offsetHeight || 530, 530);
            const fabW = this.$refs.fab?.offsetWidth || 56;
            const fabH = this.$refs.fab?.offsetHeight || 56;

            let absoluteLeft = this.posX - (panelWidth - fabW);
            absoluteLeft = Math.min(
                Math.max(margin, absoluteLeft),
                Math.max(margin, window.innerWidth - margin - panelWidth)
            );

            const belowTop = this.posY + fabH + gap;
            const aboveTop = this.posY - panelHeight - gap;
            const canOpenBelow = belowTop + panelHeight <= window.innerHeight - margin;
            const canOpenAbove = aboveTop >= margin;

            let absoluteTop;
            if (!canOpenBelow && canOpenAbove) {
                absoluteTop = aboveTop;
            } else {
                absoluteTop = canOpenBelow
                    ? belowTop
                    : Math.max(margin, window.innerHeight - margin - panelHeight);
            }

            this.panelLeft = absoluteLeft - this.posX;
            this.panelTop = absoluteTop - this.posY;
        },

        startDrag(event) {
            if (event.target.closest('input, textarea, select, a, [data-no-drag]')) return;
            if (event.button !== undefined && event.button !== 0) return;

            const point = { x: event.clientX, y: event.clientY };
            this.dragging = true;
            this.dragMoved = false;
            this.dragStartX = point.x;
            this.dragStartY = point.y;
            this.dragOffsetX = point.x - this.posX;
            this.dragOffsetY = point.y - this.posY;

            this.moveHandler = (e) => this.onDragMove(e);
            this.upHandler = () => this.stopDrag();

            window.addEventListener('pointermove', this.moveHandler);
            window.addEventListener('pointerup', this.upHandler, { once: true });
            event.preventDefault();
        },

        onDragMove(event) {
            if (!this.dragging) return;

            const nextX = event.clientX - this.dragOffsetX;
            const nextY = event.clientY - this.dragOffsetY;

            if (Math.abs(event.clientX - this.dragStartX) > 4 || Math.abs(event.clientY - this.dragStartY) > 4) {
                this.dragMoved = true;
            }

            this.posX = nextX;
            this.posY = nextY;
            this.clampToViewport();
            if (this.open) this.updatePanelPlacement();
        },

        stopDrag() {
            this.dragging = false;
            if (this.moveHandler) {
                window.removeEventListener('pointermove', this.moveHandler);
                this.moveHandler = null;
            }
            if (this.upHandler) {
                window.removeEventListener('pointerup', this.upHandler);
                this.upHandler = null;
            }
            this.clampToViewport();
            if (this.open) this.updatePanelPlacement();
            this.persistPosition();
        },

        openPanel() {
            if (this.dragMoved) {
                this.dragMoved = false;
                return;
            }
            this.open = true;
            this.$nextTick(() => this.updatePanelPlacement());
        },

        toggleOpenFromFab() {
            if (this.dragMoved) {
                this.dragMoved = false;
                return;
            }
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.updatePanelPlacement());
            }
            if (!this.open) {
                this.activeGuide = null;
                this.vista = 'guias';
                this.disconnectPanelObserver();
            }
        },

        get currentGuide() {
            return this.activeGuide !== null ? this.guides[this.activeGuide] : null;
        },

        async enviarAyuda() {
            this.helpError = '';
            if (!this.helpAsunto.trim() || !this.helpMensaje.trim()) {
                this.helpError = 'Completa ambos campos antes de enviar.';
                return;
            }
            this.helpEnviando = true;
            try {
                var csrfToken = document.querySelector('meta[name="csrf-token"]');
                var res = await fetch('{{ route("estiven.solicitar-ayuda") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ asunto: this.helpAsunto, mensaje: this.helpMensaje })
                });
                var data = await res.json();
                if (data.success) {
                    this.helpEnviado = true;
                    this.helpAsunto = '';
                    this.helpMensaje = '';
                    var self = this;
                    setTimeout(function() { self.helpEnviado = false; self.vista = 'guias'; }, 4000);
                } else {
                    this.helpError = data.message || 'No se pudo enviar. Intenta de nuevo.';
                }
            } catch (e) {
                this.helpError = 'Error de conexión. Intenta de nuevo.';
            } finally {
                this.helpEnviando = false;
            }
        }
    };
}
</script>
@endauth
