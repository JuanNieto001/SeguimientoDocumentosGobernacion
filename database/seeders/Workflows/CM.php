<?php

return [
    'codigo' => 'CM',
    'nombre' => 'Concurso de Méritos',
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
            'nombre' => 'Estudios previos',
            'area_role' => 'unidad_solicitante',
            'items' => [
                'Estudios previos completos',
                'Criterios de calidad técnica',
                'Análisis de riesgo',
                'Garantías',
                'Supervisor',
            ],
        ],
        [
            'orden' => 7,
            'nombre' => 'Revisión jurídica',
            'area_role' => 'juridica',
            'items' => [
                'Concepto jurídico / Ajustado a Derecho',
            ],
        ],
        [
            'orden' => 8,
            'nombre' => 'Prepliegos',
            'area_role' => 'secop',
            'items' => [
                'Proyecto de pliego publicado (prepliegos)',
            ],
        ],
        [
            'orden' => 9,
            'nombre' => 'Período de observaciones',
            'area_role' => 'secop',
            'items' => [
                'Observaciones recibidas',
                'Respuestas publicadas',
            ],
        ],
        [
            'orden' => 10,
            'nombre' => 'Pliegos definitivos',
            'area_role' => 'secop',
            'items' => [
                'Pliego definitivo publicado',
            ],
        ],
        [
            'orden' => 11,
            'nombre' => 'Evaluación técnica',
            'area_role' => 'secop',
            'items' => [
                'Informe evaluación técnica con puntajes',
            ],
        ],
        [
            'orden' => 12,
            'nombre' => 'Lista corta',
            'area_role' => 'secop',
            'items' => [
                'Acta conformación lista corta',
                'Publicación en SECOP',
            ],
        ],
        [
            'orden' => 13,
            'nombre' => 'Negociación económica',
            'area_role' => 'secop',
            'items' => [
                'Propuesta económica',
                'Acta de negociación',
            ],
        ],
        [
            'orden' => 14,
            'nombre' => 'Adjudicación',
            'area_role' => 'planeacion',
            'items' => [
                'Acto administrativo de adjudicación',
            ],
        ],
        [
            'orden' => 15,
            'nombre' => 'Firma del contrato',
            'area_role' => 'juridica',
            'items' => [
                'Contrato firmado',
                'Cargue en SECOP',
            ],
        ],
        [
            'orden' => 16,
            'nombre' => 'Registro Presupuestal (RP)',
            'area_role' => 'hacienda',
            'items' => [
                'RP',
            ],
        ],
        [
            'orden' => 17,
            'nombre' => 'Pólizas',
            'area_role' => 'juridica',
            'items' => [
                'Pólizas aprobadas',
            ],
        ],
        [
            'orden' => 18,
            'nombre' => 'Acta de inicio',
            'area_role' => 'secop',
            'items' => [
                'Acta de inicio firmada',
            ],
        ],
        [
            'orden' => 19,
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
