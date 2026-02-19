<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Secretaria;
use App\Models\Unidad;

class SecretariasUnidadesSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ESTRUCTURA ORGANIZACIONAL – GOBERNACIÓN DE CALDAS
        |--------------------------------------------------------------------------
        | 15 Secretarías con sus respectivas unidades/grupos/jefaturas.
        |--------------------------------------------------------------------------
        */
        $estructura = [
            'Despacho del Gobernador' => [
                'Despacho del Gobernador',
                'Jefatura de Control Interno',
                'Unidad de Control Disciplinario',
            ],
            'Secretaría de Agricultura y Desarrollo Rural' => [
                'Despacho Secretaría Agricultura',
                'Unidad de Desarrollo Rural',
                'Unidad de Planeación Agropecuaria',
            ],
            'Secretaría de Cultura' => [
                'Despacho Secretaría Cultura',
                'Grupo de Red de Bibliotecas',
                'Unidad de Fomento y Promoción Cultural',
                'Unidad de Patrimonio Cultural',
            ],
            'Secretaría de Deporte, Recreación y Actividad Física' => [
                'Despacho Secretaría Deporte',
                'Unidad de Actividad Física y Tiempo Libre',
                'Unidad de Educación Física y Recreación',
                'Unidad de Fomento del Deporte',
            ],
            'Secretaría de Desarrollo, Empleo e Innovación' => [
                'Despacho Secretaría Desarrollo, Empleo e Innovación',
                'Unidad de Desarrollo Minero',
                'Unidad de Emprendimiento y Desarrollo Empresarial',
                'Unidad de Innovación, Ciencia y Tecnología',
                'Unidad de Turismo',
            ],
            'Secretaría de Gobierno' => [
                'Despacho Secretaría de Gobierno',
                'Unidad de Derechos Humanos',
                'Unidad de Seguridad y Convivencia Ciudadana',
            ],
            'Secretaría de Hacienda' => [
                'Despacho Secretaría de Hacienda',
                'Grupo de Bienes Inmuebles',
                'Grupo de Cobro Coactivo',
                'Grupo de Determinación y Liquidación',
                'Grupo de Pasaportes',
                'Jefatura de Gestión de Ingresos',
                'Jefatura de Gestión Financiera',
                'Unidad de Contabilidad',
                'Unidad de Prestaciones Sociales',
                'Unidad de Presupuesto',
                'Unidad de Rentas',
                'Unidad de Tesorería',
                'Unidad de Tránsito',
            ],
            'Secretaría de Infraestructura' => [
                'Despacho Secretaría de Infraestructura',
                'Unidad de Gestión, Planeación y Desarrollo',
                'Unidad de Ingeniería',
                'Unidad de Proyectos Especiales',
            ],
            'Secretaría de Integración y Desarrollo Social' => [
                'Despacho Secretaría Integración y Desarrollo Social',
                'Unidad de Diseño, Coordinación, Formulación y Seguimiento de Políticas Públicas Sociales',
                'Unidad de Grupos Poblacionales',
            ],
            'Secretaría de Planeación' => [
                'Despacho Secretaría de Planeación',
                'Jefatura de Gestión de la Información',
                'Unidad de Analítica de Datos',
                'Unidad de Desarrollo Territorial',
                'Unidad de Descentralización',
                'Unidad de Regalías e Inversiones Públicas',
                'Unidad de Sistemas',
            ],
            'Secretaría de Vivienda y Territorio' => [
                'Despacho Secretaría de Vivienda y Territorio',
                'Unidad de Agua Potable y Saneamiento Básico',
                'Unidad Técnica de Vivienda',
            ],
            'Secretaría del Medio Ambiente' => [
                'Despacho Secretaría del Medio Ambiente',
                'Jefatura de Gestión del Riesgo',
                'Unidad de Medio Ambiente y Cambio Climático',
            ],
            'Secretaría General' => [
                'Despacho Secretaría General',
                'Grupo de Bienes Muebles',
                'Grupo de Capacitación y Bienestar',
                'Grupo de Gestión Organizacional',
                'Grupo de Seguridad y Salud en el Trabajo',
                'Jefatura de Gestión del Talento Humano',
                'Unidad de Calidad',
                'Unidad de Compras y Suministros',
                'Unidad de Gestión Documental',
            ],
            'Secretaría Jurídica' => [
                'Despacho Secretaría Jurídica',
                'Unidad de Contratación',
                'Unidad de Defensa, Representación Judicial, Asuntos Normativos y Personerías Jurídicas',
            ],
            'Secretaría Privada' => [
                'Grupo de Atención al Ciudadano',
                'Jefatura de Gobierno Abierto',
                'Unidad de Comunicación y Medios',
            ],
        ];

        foreach ($estructura as $nombreSecretaria => $unidades) {
            $secretaria = Secretaria::firstOrCreate(
                ['nombre' => $nombreSecretaria],
                ['activo' => true]
            );

            foreach ($unidades as $nombreUnidad) {
                Unidad::firstOrCreate(
                    [
                        'nombre'        => $nombreUnidad,
                        'secretaria_id' => $secretaria->id,
                    ],
                    ['activo' => true]
                );
            }
        }

        $this->command->info('✅ 15 Secretarías y sus unidades creadas correctamente.');
    }
}
