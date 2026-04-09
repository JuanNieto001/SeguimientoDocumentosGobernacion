<?php

namespace App\Services;

class ArchivosPorAreaService
{
    /**
     * Obtener tipos de archivos requeridos por área
     */
    public function obtenerTiposArchivosPorArea(string $area): array
    {
        return match($area) {
            'unidad_solicitante' => [
                'borrador_estudios_previos' => [
                    'nombre' => 'Borrador de Estudios Previos',
                    'requerido' => true,
                    'mime_types' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                    'max_size' => 10240, // 10MB
                ],
                'formato_necesidades' => [
                    'nombre' => 'Formato de Necesidades',
                    'requerido' => true,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 5120, // 5MB
                ],
                'cotizaciones' => [
                    'nombre' => 'Cotizaciones de Referencia',
                    'requerido' => false,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 5120,
                    'multiple' => true,
                ],
            ],
            
            'planeacion' => [
                'estudios_previos_revisados' => [
                    'nombre' => 'Estudios Previos Revisados',
                    'requerido' => true,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 10240,
                ],
                'inclusion_paa' => [
                    'nombre' => 'Certificado de Inclusión en PAA',
                    'requerido' => true,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 2048,
                ],
                'observaciones_planeacion' => [
                    'nombre' => 'Observaciones de Planeación',
                    'requerido' => false,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 5120,
                ],
            ],
            
            'hacienda' => [
                'cdp' => [
                    'nombre' => 'Certificado de Disponibilidad Presupuestal (CDP)',
                    'requerido' => true,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 2048,
                ],
                'rp' => [
                    'nombre' => 'Registro Presupuestal (RP)',
                    'requerido' => false,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 2048,
                ],
                'analisis_financiero' => [
                    'nombre' => 'Análisis Financiero',
                    'requerido' => false,
                    'mime_types' => ['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                    'max_size' => 5120,
                ],
            ],
            
            'juridica' => [
                'ajustado_derecho' => [
                    'nombre' => 'Ajustado a Derecho',
                    'requerido' => true,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 5120,
                ],
                'verificacion_contratista' => [
                    'nombre' => 'Verificación de Antecedentes del Contratista',
                    'requerido' => true,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 3072,
                ],
                'polizas' => [
                    'nombre' => 'Pólizas y Garantías',
                    'requerido' => true,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 5120,
                    'multiple' => true,
                ],
                'concepto_juridico' => [
                    'nombre' => 'Concepto Jurídico',
                    'requerido' => false,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 5120,
                ],
            ],
            
            'secop' => [
                'publicacion_secop' => [
                    'nombre' => 'Comprobante de Publicación en SECOP',
                    'requerido' => true,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 5120,
                ],
                'contrato' => [
                    'nombre' => 'Contrato',
                    'requerido' => true,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 10240,
                ],
                'acta_inicio' => [
                    'nombre' => 'Acta de Inicio',
                    'requerido' => true,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 5120,
                ],
                'registro_contrato_secop' => [
                    'nombre' => 'Registro de Contrato en SECOP',
                    'requerido' => false,
                    'mime_types' => ['application/pdf'],
                    'max_size' => 3072,
                ],
            ],
            
            default => [],
        };
    }

    /**
     * Validar si un archivo cumple los requisitos
     */
    public function validarArchivo(string $area, string $tipoArchivo, $archivo): array
    {
        $tiposPermitidos = $this->obtenerTiposArchivosPorArea($area);
        
        if (!isset($tiposPermitidos[$tipoArchivo])) {
            return [
                'valido' => false,
                'error' => 'Tipo de archivo no permitido para esta área'
            ];
        }

        $requisitos = $tiposPermitidos[$tipoArchivo];
        
        // Validar mime type
        if (!in_array($archivo->getMimeType(), $requisitos['mime_types'])) {
            return [
                'valido' => false,
                'error' => 'Formato de archivo no permitido. Formatos aceptados: ' . implode(', ', $requisitos['mime_types'])
            ];
        }

        // Validar tamaño (en KB)
        $tamañoKB = $archivo->getSize() / 1024;
        if ($tamañoKB > $requisitos['max_size']) {
            return [
                'valido' => false,
                'error' => sprintf('El archivo excede el tamaño máximo permitido de %.2f MB', $requisitos['max_size'] / 1024)
            ];
        }

        return ['valido' => true];
    }

    /**
     * Verificar si todos los archivos requeridos están presentes
     */
    public function verificarArchivosRequeridos(string $area, array $archivosPresentes): array
    {
        $tiposPermitidos = $this->obtenerTiposArchivosPorArea($area);
        $faltantes = [];
        
        foreach ($tiposPermitidos as $tipo => $config) {
            if ($config['requerido'] && !in_array($tipo, $archivosPresentes)) {
                $faltantes[] = $config['nombre'];
            }
        }

        return [
            'completo' => empty($faltantes),
            'faltantes' => $faltantes,
            'porcentaje' => $this->calcularPorcentajeCompletitud($area, $archivosPresentes),
        ];
    }

    /**
     * Calcular porcentaje de completitud de archivos
     */
    private function calcularPorcentajeCompletitud(string $area, array $archivosPresentes): float
    {
        $tiposPermitidos = $this->obtenerTiposArchivosPorArea($area);
        $requeridos = array_filter($tiposPermitidos, fn($config) => $config['requerido']);
        
        if (empty($requeridos)) {
            return 100.0;
        }

        $presentesRequeridos = array_intersect(array_keys($requeridos), $archivosPresentes);
        
        return (count($presentesRequeridos) / count($requeridos)) * 100;
    }

    /**
     * Obtener lista de archivos pendientes
     */
    public function obtenerArchivosPendientes(string $area, array $archivosPresentes): array
    {
        $tiposPermitidos = $this->obtenerTiposArchivosPorArea($area);
        $pendientes = [];
        
        foreach ($tiposPermitidos as $tipo => $config) {
            if (!in_array($tipo, $archivosPresentes)) {
                $pendientes[] = [
                    'tipo' => $tipo,
                    'nombre' => $config['nombre'],
                    'requerido' => $config['requerido'],
                ];
            }
        }

        return $pendientes;
    }
}
