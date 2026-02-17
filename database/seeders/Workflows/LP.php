<?php

return [
    'codigo' => 'LP',
    'nombre' => 'Licitación Pública',
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
                'PAA',
                'Acta aprobación PAA',
            ],
        ],
        [
            'orden' => 2,
            'nombre' => 'Validación inclusión en PAA',
            'area_role' => 'planeacion',
            'items' => [
                'Certificado inclusión en PAA',
                'Acta modificación PAA (si aplica)',
            ],
        ],
        [
            'orden' => 3,
            'nombre' => 'Autorización formal de inicio',
            'area_role' => 'planeacion',
            'items' => [
                'Acto de autorización de inicio',
            ],
        ],
        [
            'orden' => 4,
            'nombre' => 'Estudio de mercado',
            'area_role' => 'unidad_solicitante',
            'items' => [
                '3 cotizaciones (mínimo)',
                'Precios históricos SECOP (si aplica)',
                'Análisis del sector',
                'Informe estudio de mercado',
            ],
        ],
        [
            'orden' => 5,
            'nombre' => 'CDP y documentos presupuestales',
            'area_role' => 'hacienda',
            'items' => [
                'CDP',
                'PAA',
                'Certificado compatibilidad',
            ],
        ],
        [
            'orden' => 6,
            'nombre' => 'Viabilidad económica e indicadores',
            'area_role' => 'hacienda',
            'items' => [
                'Viabilidad económica',
                'Indicadores financieros',
            ],
        ],
        [
            'orden' => 7,
            'nombre' => 'Estudios previos',
            'area_role' => 'unidad_solicitante',
            'items' => [
                'Estudios previos completos',
                'Análisis de riesgo',
                'Garantías',
                'Supervisor',
            ],
        ],
        [
            'orden' => 8,
            'nombre' => 'Revisión jurídica',
            'area_role' => 'juridica',
            'items' => [
                'Concepto jurídico / Ajustado a Derecho',
            ],
        ],
        [
            'orden' => 9,
            'nombre' => 'Prepliegos',
            'area_role' => 'secop',
            'items' => [
                'Proyecto de pliego publicado (prepliegos)',
            ],
        ],
        [
            'orden' => 10,
            'nombre' => 'Período de observaciones',
            'area_role' => 'secop',
            'items' => [
                'Observaciones recibidas',
                'Respuestas publicadas',
            ],
        ],
        [
            'orden' => 11,
            'nombre' => 'Pliegos definitivos',
            'area_role' => 'secop',
            'items' => [
                'Pliego definitivo publicado',
            ],
        ],
        [
            'orden' => 12,
            'nombre' => 'Audiencia de riesgos',
            'area_role' => 'secop',
            'items' => [
                'Acta audiencia de riesgos',
                'Ajuste matriz de riesgos (si aplica)',
            ],
        ],
        [
            'orden' => 13,
            'nombre' => 'Apertura oficial del proceso',
            'area_role' => 'secop',
            'items' => [
                'Apertura publicada',
            ],
        ],
        [
            'orden' => 14,
            'nombre' => 'Recepción de propuestas',
            'area_role' => 'secop',
            'items' => [
                'Propuestas recibidas',
            ],
        ],
        [
            'orden' => 15,
            'nombre' => 'Evaluación por comité evaluador',
            'area_role' => 'secop',
            'items' => [
                'Informe evaluación preliminar',
            ],
        ],
        [
            'orden' => 16,
            'nombre' => 'Publicación informe de evaluación',
            'area_role' => 'secop',
            'items' => [
                'Informe publicado',
                'Respuestas a observaciones',
            ],
        ],
        [
            'orden' => 17,
            'nombre' => 'Audiencia pública de adjudicación',
            'area_role' => 'planeacion',
            'items' => [
                'Resolución de adjudicación en audiencia',
                'Acta de audiencia',
            ],
        ],
        [
            'orden' => 18,
            'nombre' => 'Firma del contrato',
            'area_role' => 'juridica',
            'items' => [
                'Contrato firmado',
                'Cargue en SECOP',
            ],
        ],
        [
            'orden' => 19,
            'nombre' => 'Registro Presupuestal (RP)',
            'area_role' => 'hacienda',
            'items' => [
                'RP',
            ],
        ],
        [
            'orden' => 20,
            'nombre' => 'Pólizas',
            'area_role' => 'juridica',
            'items' => [
                'Pólizas aprobadas',
            ],
        ],
        [
            'orden' => 21,
            'nombre' => 'Acta de inicio',
            'area_role' => 'secop',
            'items' => [
                'Acta de inicio firmada',
            ],
        ],
        [
            'orden' => 22,
            'nombre' => 'Ejecución y cierre',
            'area_role' => 'secop',
            'items' => [
                'Informes',
                'Pagos',
                'Acta terminación',
                'Cierre SECOP',
            ],
        ],
    ],
];
