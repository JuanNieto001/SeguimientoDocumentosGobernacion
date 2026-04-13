<?php
/**
 * Archivo: backend/database/seeders/EstivenGuidesSeeder.php
 * Proposito: Cargar guias base de Marsetiv en inicializacion.
 */

namespace Database\Seeders;

use App\Models\EstivenGuide;
use Illuminate\Database\Seeder;

class EstivenGuidesSeeder extends Seeder
{
    public function run(): void
    {
        // No sobrescribir contenido si ya fue gestionado por admin.
        if (EstivenGuide::query()->exists()) {
            return;
        }

        $guides = [
            [
                'role' => '_common',
                'icon' => '📋',
                'title' => 'Ver mis tareas pendientes',
                'orden' => 10,
                'steps' => [
                    'Ingresa con tu usuario y contraseña.',
                    'En el menu lateral entra a Mi Area o Mis Solicitudes.',
                    'Abre un proceso para ver detalle y acciones disponibles.',
                ],
            ],
            [
                'role' => '_common',
                'icon' => '🔔',
                'title' => 'Revisar notificaciones',
                'orden' => 20,
                'steps' => [
                    'Haz clic en la campana de la parte superior.',
                    'Abre la alerta para ir al proceso asociado.',
                    'Marca como leida o elimina segun necesidad.',
                ],
            ],
            [
                'role' => '_common',
                'icon' => '👁️',
                'title' => 'Previsualizar documentos',
                'orden' => 30,
                'steps' => [
                    'Entra al detalle del proceso.',
                    'Usa el icono de vista previa en el documento.',
                    'Consulta versiones y descarga desde el panel.',
                ],
            ],
            [
                'role' => 'admin',
                'icon' => '🆕',
                'title' => 'Crear un proceso',
                'orden' => 10,
                'steps' => [
                    'Entra a Procesos y selecciona Nueva solicitud.',
                    'Completa tipo de proceso, secretaria, unidad y datos base.',
                    'Guarda para iniciar el flujo automaticamente.',
                ],
            ],
            [
                'role' => 'admin',
                'icon' => '👥',
                'title' => 'Gestionar usuarios y roles',
                'orden' => 20,
                'steps' => [
                    'Abre Administracion y luego Usuarios.',
                    'Crea o edita usuario, rol, secretaria y unidad.',
                    'Guarda cambios y valida acceso del perfil.',
                ],
            ],
            [
                'role' => 'admin_general',
                'icon' => '⚙️',
                'title' => 'Configurar guias de Marsetiv',
                'orden' => 10,
                'steps' => [
                    'Ve a Administracion y abre Guias de Marsetiv.',
                    'Crea o edita guias por rol o comun para todos.',
                    'Define pasos, orden e imagenes de apoyo.',
                ],
            ],
            [
                'role' => 'unidad_solicitante',
                'icon' => '📎',
                'title' => 'Cargar documentos de la etapa',
                'orden' => 10,
                'steps' => [
                    'Abre Mi bandeja y selecciona el proceso.',
                    'Ubica el documento requerido y pulsa Subir archivo.',
                    'Carga archivo y valida estado cargado.',
                ],
            ],
            [
                'role' => 'planeacion',
                'icon' => '📅',
                'title' => 'Gestionar PAA y validacion',
                'orden' => 10,
                'steps' => [
                    'Abre Plan Anual (PAA) desde el menu.',
                    'Verifica inclusion y soportes del proceso.',
                    'Aprueba o devuelve con observaciones.',
                ],
            ],
            [
                'role' => 'hacienda',
                'icon' => '💰',
                'title' => 'Emitir CDP y viabilidad',
                'orden' => 10,
                'steps' => [
                    'Revisa proceso en bandeja de Hacienda.',
                    'Carga soporte presupuestal y CDP/RP segun corresponda.',
                    'Aprueba para continuar flujo.',
                ],
            ],
            [
                'role' => 'juridica',
                'icon' => '⚖️',
                'title' => 'Validar soporte juridico',
                'orden' => 10,
                'steps' => [
                    'Revisa documentos del contratista y soporte legal.',
                    'Emite concepto Ajustado a Derecho.',
                    'Aprueba o devuelve con observaciones precisas.',
                ],
            ],
            [
                'role' => 'secop',
                'icon' => '🌐',
                'title' => 'Publicar en SECOP II',
                'orden' => 10,
                'steps' => [
                    'Verifica que etapas previas esten completas.',
                    'Publica en SECOP II y registra codigo.',
                    'Carga acta de inicio y cierra la gestion.',
                ],
            ],
            [
                'role' => 'abogado_unidad',
                'icon' => '📦',
                'title' => 'Gestionar repositorio SIA Observa',
                'orden' => 10,
                'steps' => [
                    'Accede al proceso autorizado por tu unidad.',
                    'Revisa accesos y documentos finales disponibles.',
                    'Sube o descarga soportes segun permiso asignado.',
                ],
            ],
        ];

        foreach ($guides as $guideData) {
            $guide = EstivenGuide::create([
                'role' => $guideData['role'],
                'icon' => $guideData['icon'],
                'title' => $guideData['title'],
                'orden' => $guideData['orden'],
                'activo' => true,
            ]);

            foreach ($guideData['steps'] as $index => $content) {
                $guide->steps()->create([
                    'step_number' => $index + 1,
                    'content' => $content,
                ]);
            }
        }
    }
}
