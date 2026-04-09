<?php

namespace App\Enums;

enum ProcessType: string
{
    case CONTRATACION_DIRECTA_PERSONA_NATURAL = 'cd_pn';
    case CONTRATACION_DIRECTA_PERSONA_JURIDICA = 'cd_pj';
    case LICITACION_PUBLICA = 'lp';
    case SELECCION_ABREVIADA = 'sa';
    case CONCURSO_MERITOS = 'cm';
    case MINIMA_CUANTIA = 'mc';

    public function getLabel(): string
    {
        return match($this) {
            self::CONTRATACION_DIRECTA_PERSONA_NATURAL => 'Contratación Directa - Persona Natural',
            self::CONTRATACION_DIRECTA_PERSONA_JURIDICA => 'Contratación Directa - Persona Jurídica',
            self::LICITACION_PUBLICA => 'Licitación Pública',
            self::SELECCION_ABREVIADA => 'Selección Abreviada',
            self::CONCURSO_MERITOS => 'Concurso de Méritos',
            self::MINIMA_CUANTIA => 'Mínima Cuantía',
        };
    }

    public function getPrefix(): string
    {
        return match($this) {
            self::CONTRATACION_DIRECTA_PERSONA_NATURAL => 'CD-PN',
            self::CONTRATACION_DIRECTA_PERSONA_JURIDICA => 'CD-PJ',
            self::LICITACION_PUBLICA => 'LP',
            self::SELECCION_ABREVIADA => 'SA',
            self::CONCURSO_MERITOS => 'CM',
            self::MINIMA_CUANTIA => 'MC',
        };
    }
}
