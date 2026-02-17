<?php

return [
    'codigo' => 'MC',
    'nombre' => 'Mínima Cuantía',
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
            'nombre' => 'CDP',
            'area_role' => 'hacienda',
            'items' => [
                'CDP',
            ],
        ],
        [
            'orden' => 6,
            'nombre' => 'Estudios previos',
            'area_role' => 'unidad_solicitante',
            'items' => [
                'Estudios previos completos',
                'Análisis de riesgo',
                'Garantías (si aplica)',
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
            'nombre' => 'Proyección invitación pública',
            'area_role' => 'unidad_solicitante',
            'items' => [
                'Invitación pública',
            ],
        ],
        [
            'orden' => 9,
            'nombre' => 'Publicación en SECOP',
            'area_role' => 'secop',
            'items' => [
                'Invitación publicada en SECOP',
            ],
        ],
        [
            'orden' => 10,
            'nombre' => 'Recepción de ofertas',
            'area_role' => 'secop',
            'items' => [
                'Ofertas recibidas',
            ],
        ],
        [
            'orden' => 11,
            'nombre' => 'Evaluación de ofertas',
            'area_role' => 'secop',
            'items' => [
                'Verificación jurídica habilitante',
                'Comparación de precios',
                'Informe de evaluación',
            ],
        ],
        [
            'orden' => 12,
            'nombre' => 'Acta de selección',
            'area_role' => 'secop',
            'items' => [
                'Acta de selección',
            ],
        ],
        [
            'orden' => 13,
            'nombre' => 'Aceptación de la oferta',
            'area_role' => 'planeacion',
            'items' => [
                'Comunicación de aceptación',
            ],
        ],
        [
            'orden' => 14,
            'nombre' => 'Contrato formal (si aplica)',
            'area_role' => 'juridica',
            'items' => [
                'Contrato firmado (si aplica)',
            ],
        ],
        [
            'orden' => 15,
            'nombre' => 'Registro Presupuestal (RP)',
            'area_role' => 'hacienda',
            'items' => [
                'RP',
            ],
        ],
        [
            'orden' => 16,
            'nombre' => 'Pólizas (si aplica)',
            'area_role' => 'juridica',
            'items' => [
                'Pólizas aprobadas (si aplica)',
            ],
        ],
        [
            'orden' => 17,
            'nombre' => 'Acta de inicio',
            'area_role' => 'secop',
            'items' => [
                'Acta de inicio firmada',
            ],
        ],
        [
            'orden' => 18,
            'nombre' => 'Ejecución y cierre',
            'area_role' => 'secop',
            'items' => [
                'Informes',
                'Pagos',
                'Acta terminación',
                'Cierre en SECOP',
            ],
        ],
    ],
];
