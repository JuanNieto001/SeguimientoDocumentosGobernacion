<?php

return [
    'codigo' => 'CD_PN',
    'nombre' => 'Contratación Directa - Persona Natural',
    'activo' => true,
    'etapas' => [
        [
            'orden' => 0,
            'nombre' => 'Identificación de la necesidad',
            'area_role' => 'unidad_solicitante',
            // ✅ Unidad NO usa checklist por ahora (solo carga archivos)
            'items' => [],
        ],
        [
            'orden' => 1,
            'nombre' => 'Planeación anual - PAA',
            'area_role' => 'planeacion',
            'items' => [
                'Plan Anual de Adquisiciones (PAA)',
                'Acta de aprobación del PAA',
            ],
        ],
        [
            'orden' => 2,
            'nombre' => 'Validación inclusión en PAA',
            'area_role' => 'planeacion',
            'items' => [
                'Certificado de inclusión en PAA',
                'Acta de modificación del PAA (si aplica)',
            ],
        ],
        [
            'orden' => 3,
            'nombre' => 'Autorización formal de inicio',
            'area_role' => 'planeacion',
            'items' => [
                'Acto de autorización de inicio de contratación',
            ],
        ],
        [
            'orden' => 4,
            'nombre' => 'Preparación inicial de Estudios Previos',
            'area_role' => 'unidad_solicitante',
            'items' => [
                'Estudio de Mercado (cuando aplique)',
                'Análisis del Sector',
                'Estudios Previos (borrador/proyectados)',
            ],
        ],
        [
            'orden' => 5,
            'nombre' => 'Solicitud de documentos presupuestales iniciales',
            'area_role' => 'planeacion',
            'items' => [
                'CDP',
                'PAA',
                'Certificado de compatibilidad del gasto',
            ],
        ],
        [
            'orden' => 6,
            'nombre' => 'Solicitud de viabilidad económica',
            'area_role' => 'hacienda',
            'items' => [
                'Viabilidad económica',
            ],
        ],
        [
            'orden' => 7,
            'nombre' => 'Verificación del contratista',
            'area_role' => 'juridica',
            'items' => [
                'Hoja de vida y soportes (SIGEP)',
                'Certificados de experiencia',
                'Antecedentes Procuraduría (SIRI)',
                'Antecedentes Policía',
                'Antecedentes Contraloría',
                'Verificación de inhabilidades e incompatibilidades',
                'Aportes a seguridad social al día',
                'Checklist precontractual completo',
            ],
        ],
        [
            'orden' => 8,
            'nombre' => 'Proyección del contrato',
            'area_role' => 'unidad_solicitante',
            'items' => [
                'Minuta del contrato (borrador)',
                'Solicitud de contratación',
                'Designación de supervisor',
            ],
        ],
        [
            'orden' => 9,
            'nombre' => 'Revisión y aprobación Secretaría de Planeación',
            'area_role' => 'planeacion',
            'items' => [
                'Estudios previos firmados/aprobados',
                'Minuta revisada y aprobada',
                'Solicitud de contratación firmada',
                'Designación de supervisor firmada',
            ],
        ],
        [
            'orden' => 10,
            'nombre' => 'Ajustado a Derecho (Secretaría Jurídica)',
            'area_role' => 'juridica',
            'items' => [
                'Cotizaciones y correos',
                'Análisis del Sector',
                'CDP',
                'PAA',
                'Certificado de compatibilidad',
                'Viabilidad económica',
                'Estudios previos',
                'Minuta del contrato',
                'Solicitud y designación de supervisor',
                'Ajustado a Derecho',
            ],
        ],
        [
            'orden' => 11,
            'nombre' => 'Estructuración en SECOP',
            'area_role' => 'secop',
            'items' => [
                'Proceso estructurado en SECOP',
                'Carga de documentos en SECOP',
                'Contrato electrónico generado/descargado',
            ],
        ],
        [
            'orden' => 12,
            'nombre' => 'Firma del contrato',
            'area_role' => 'secop',
            'items' => [
                'Contrato firmado',
                'Contrato cargado en SECOP',
            ],
        ],
        [
            'orden' => 13,
            'nombre' => 'Registro Presupuestal (RP)',
            'area_role' => 'hacienda',
            'items' => [
                'Solicitud de RP',
                'RP emitido',
            ],
        ],
        [
            'orden' => 14,
            'nombre' => 'Radicación expediente físico',
            'area_role' => 'juridica',
            'items' => [
                'Expediente físico organizado',
                'Comprobante de radicación',
            ],
        ],
        [
            'orden' => 15,
            'nombre' => 'Pólizas',
            'area_role' => 'juridica',
            'items' => [
                'Pólizas cargadas en SECOP',
                'Pólizas aprobadas',
            ],
        ],
        [
            'orden' => 16,
            'nombre' => 'Acta de inicio',
            'area_role' => 'secop',
            'items' => [
                'Acta de inicio firmada',
                'Registro en sistema',
            ],
        ],
        [
            'orden' => 17,
            'nombre' => 'Ejecución y cierre',
            'area_role' => 'secop',
            'items' => [
                'Informes de supervisión',
                'Pagos tramitados',
                'Acta de terminación (si aplica)',
                'Liquidación (si aplica)',
                'Cierre en SECOP',
            ],
        ],
    ],
];
