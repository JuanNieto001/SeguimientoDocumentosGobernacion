<?php

namespace App\Services;

use App\Models\Proceso;
use App\Models\Workflow;

class ValidacionContratacionService
{
    /**
     * Rangos de cuantías según normativa colombiana (en SMMLV)
     */
    const CUANTIA_MINIMA = 10; // Mínima cuantía: < 10 SMMLV
    const CUANTIA_MENOR = 100; // Menor cuantía: < 100 SMMLV
    const CUANTIA_MEDIA = 1000; // Media cuantía: < 1000 SMMLV
    
    /**
     * SMMLV vigente para 2026
     * Incremento del 23% según decreto ministerial
     */
    const SMMLV_2026 = 1750905;

    /**
     * Validar si el proceso requiere publicación en SECOP según su cuantía
     */
    public function requierePublicacionSECOP(Proceso $proceso): bool
    {
        $cuantiaEnSMMLV = $this->calcularCuantiaEnSMMLV($proceso->valor_estimado);
        
        // Todos los procesos superiores a 10 SMMLV deben publicarse en SECOP
        return $cuantiaEnSMMLV >= self::CUANTIA_MINIMA;
    }

    /**
     * Validar si el contratista requiere RUP (Registro Único de Proponentes)
     */
    public function requiereRUP(Proceso $proceso): bool
    {
        $cuantiaEnSMMLV = $this->calcularCuantiaEnSMMLV($proceso->valor_estimado);
        
        // RUP obligatorio para procesos superiores a 100 SMMLV (Menor Cuantía hacia arriba)
        return $cuantiaEnSMMLV > self::CUANTIA_MENOR;
    }

    /**
     * Obtener plazo mínimo de publicación según modalidad y cuantía
     */
    public function obtenerPlazoMinimoPublicacion(Proceso $proceso): ?int
    {
        $workflow = $proceso->workflow;
        
        switch ($workflow->nombre) {
            case 'Licitación Pública':
                return 10; // 10 días hábiles mínimo
                
            case 'Selección Abreviada':
                return 5; // 5 días hábiles mínimo
                
            case 'Concurso de Méritos':
                return 10; // 10 días hábiles mínimo
                
            case 'Contratación Directa':
            case 'Mínima Cuantía':
                return 1; // 1 día hábil mínimo
                
            default:
                return null;
        }
    }

    /**
     * Validar si el valor estimado es apropiado para la modalidad seleccionada
     */
    public function validarModalidadPorCuantia(Proceso $proceso): array
    {
        $cuantiaEnSMMLV = $this->calcularCuantiaEnSMMLV($proceso->valor_estimado);
        $workflow = $proceso->workflow;
        $errores = [];

        switch ($workflow->nombre) {
            case 'Mínima Cuantía':
                if ($cuantiaEnSMMLV >= self::CUANTIA_MINIMA) {
                    $errores[] = "Mínima Cuantía solo aplica para valores menores a 10 SMMLV. Valor actual: " . number_format($cuantiaEnSMMLV, 2) . " SMMLV";
                }
                break;

            case 'Contratación Directa':
                // CD permite rangos amplios, validar solo casos específicos
                if ($cuantiaEnSMMLV > self::CUANTIA_MEDIA) {
                    $errores[] = "Contratación Directa requiere justificación legal para valores superiores a 1000 SMMLV";
                }
                break;

            case 'Selección Abreviada':
                if ($cuantiaEnSMMLV < self::CUANTIA_MENOR) {
                    $errores[] = "Selección Abreviada generalmente aplica para valores superiores a 100 SMMLV";
                }
                break;

            case 'Licitación Pública':
                if ($cuantiaEnSMMLV < self::CUANTIA_MEDIA) {
                    $errores[] = "Licitación Pública generalmente aplica para valores superiores a 1000 SMMLV";
                }
                break;
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'cuantia_smmlv' => $cuantiaEnSMMLV,
        ];
    }

    /**
     * Validar requisitos de garantías según cuantía
     */
    public function obtenerGarantiasRequeridas(Proceso $proceso): array
    {
        $cuantiaEnSMMLV = $this->calcularCuantiaEnSMMLV($proceso->valor_estimado);
        $garantias = [];

        if ($cuantiaEnSMMLV < self::CUANTIA_MINIMA) {
            // Mínima cuantía: generalmente no requiere garantías
            $garantias = ['ninguna'];
        } else {
            // Para cuantías superiores
            $garantias = [
                'cumplimiento' => [
                    'porcentaje' => 10,
                    'descripcion' => 'Garantía de cumplimiento del contrato'
                ],
                'calidad' => [
                    'porcentaje' => 10,
                    'vigencia_meses' => 12,
                    'descripcion' => 'Garantía de calidad del servicio/bien'
                ],
                'anticipo' => [
                    'porcentaje' => 100,
                    'descripcion' => 'Garantía de buen manejo del anticipo (si aplica)'
                ],
                'salarios' => [
                    'porcentaje' => 5,
                    'descripcion' => 'Garantía de pago de salarios y prestaciones'
                ],
            ];

            if ($cuantiaEnSMMLV < self::CUANTIA_MENOR) {
                // Menor cuantía: garantías reducidas
                unset($garantias['calidad']);
                unset($garantias['salarios']);
            }
        }

        return $garantias;
    }

    /**
     * Validar requisitos habilitantes según modalidad
     */
    public function obtenerRequisitosHabilitantes(Proceso $proceso): array
    {
        $cuantiaEnSMMLV = $this->calcularCuantiaEnSMMLV($proceso->valor_estimado);
        $workflow = $proceso->workflow;
        $requisitos = [
            'juridicos' => [],
            'financieros' => [],
            'tecnicos' => [],
            'organizacionales' => [],
        ];

        // Requisitos jurídicos
        $requisitos['juridicos'][] = 'Certificado de existencia y representación legal (menor a 30 días)';
        
        if ($this->requiereRUP($proceso)) {
            $requisitos['juridicos'][] = 'Registro Único de Proponentes (RUP) vigente';
        }

        // Requisitos financieros
        if ($cuantiaEnSMMLV >= self::CUANTIA_MENOR) {
            $requisitos['financieros'][] = 'Estados financieros del último año';
            $requisitos['financieros'][] = 'Indicadores financieros mínimos';
        }

        // Requisitos técnicos
        if ($workflow->nombre !== 'Mínima Cuantía') {
            $requisitos['tecnicos'][] = 'Experiencia específica en el objeto contractual';
            $requisitos['tecnicos'][] = 'Equipo de trabajo y recursos técnicos';
        }

        // Requisitos organizacionales
        if ($cuantiaEnSMMLV >= self::CUANTIA_MEDIA) {
            $requisitos['organizacionales'][] = 'Sistema de Gestión de Calidad certificado';
            $requisitos['organizacionales'][] = 'Plan de riesgos';
        }

        return $requisitos;
    }

    /**
     * Calcular cuantía en SMMLV
     */
    private function calcularCuantiaEnSMMLV(float $valor): float
    {
        return $valor / self::SMMLV_2026;
    }

    /**
     * Validar plazos legales del proceso
     */
    public function validarPlazosLegales(Proceso $proceso): array
    {
        $validaciones = [];
        
        // Validar plazo de publicación
        $plazoMinimo = $this->obtenerPlazoMinimoPublicacion($proceso);
        if ($plazoMinimo) {
            $validaciones['plazo_publicacion'] = [
                'dias_minimos' => $plazoMinimo,
                'descripcion' => "El proceso debe estar publicado al menos {$plazoMinimo} días hábiles"
            ];
        }

        // Validar plazo de estudios previos
        $validaciones['estudios_previos'] = [
            'requerido' => true,
            'descripcion' => 'Los estudios previos deben estar completos antes de iniciar el proceso'
        ];

        // Validar plazo de CDP y disponibilidad presupuestal
        $validaciones['disponibilidad_presupuestal'] = [
            'requerido' => true,
            'momento' => 'antes_compromiso',
            'descripcion' => 'CDP debe estar disponible antes de comprometer el gasto'
        ];

        return $validaciones;
    }

    /**
     * Obtener recomendaciones según el proceso
     */
    public function obtenerRecomendaciones(Proceso $proceso): array
    {
        $cuantiaEnSMMLV = $this->calcularCuantiaEnSMMLV($proceso->valor_estimado);
        $recomendaciones = [];

        if (!$this->requierePublicacionSECOP($proceso)) {
            $recomendaciones[] = [
                'tipo' => 'info',
                'mensaje' => 'Este proceso no requiere publicación en SECOP por su cuantía, pero se recomienda para transparencia'
            ];
        }

        if ($cuantiaEnSMMLV > 50 && $cuantiaEnSMMLV < self::CUANTIA_MENOR) {
            $recomendaciones[] = [
                'tipo' => 'warning',
                'mensaje' => 'Considerar realizar comparación de al menos 3 cotizaciones'
            ];
        }

        $validacionModalidad = $this->validarModalidadPorCuantia($proceso);
        if (!$validacionModalidad['valido']) {
            foreach ($validacionModalidad['errores'] as $error) {
                $recomendaciones[] = [
                    'tipo' => 'error',
                    'mensaje' => $error
                ];
            }
        }

        return $recomendaciones;
    }
}
