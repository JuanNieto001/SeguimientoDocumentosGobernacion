<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecopDatosAbiertoService
{
    private string $apiUrl;
    private string $keyId;
    private string $keySecret;
    private string $entidadNit;

    public function __construct()
    {
        $this->apiUrl = config('services.secop.api_url');
        $this->keyId = config('services.secop.key_id');
        $this->keySecret = config('services.secop.key_secret');
        $this->entidadNit = config('services.secop.entidad_nit');
    }

    /**
     * Buscar contratos en SECOP II por número de proceso o contrato.
     */
    public function buscarPorReferencia(string $referencia): array
    {
        $cacheKey = 'secop_ref_' . md5($referencia);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($referencia) {
            return $this->query([
                '$where' => "referencia_del_contrato LIKE '%{$this->escapeSoql($referencia)}%' OR id_contrato LIKE '%{$this->escapeSoql($referencia)}%' OR proceso_de_compra LIKE '%{$this->escapeSoql($referencia)}%'",
                '$limit' => 20,
                '$order' => 'ultima_actualizacion DESC',
            ]);
        });
    }

    /**
     * Buscar contratos de la Gobernación de Caldas.
     */
    public function buscarPorEntidad(array $filtros = []): array
    {
        $where = ["nit_entidad = '{$this->entidadNit}'"];

        if (!empty($filtros['estado'])) {
            $where[] = "estado_contrato = '{$this->escapeSoql($filtros['estado'])}'";
        }

        if (!empty($filtros['tipo_contrato'])) {
            $where[] = "tipo_de_contrato = '{$this->escapeSoql($filtros['tipo_contrato'])}'";
        }

        if (!empty($filtros['modalidad'])) {
            $where[] = "modalidad_de_contratacion LIKE '%{$this->escapeSoql($filtros['modalidad'])}%'";
        }

        if (!empty($filtros['contratista'])) {
            $where[] = "proveedor_adjudicado LIKE '%{$this->escapeSoql($filtros['contratista'])}%'";
        }

        if (!empty($filtros['objeto'])) {
            $where[] = "objeto_del_contrato LIKE '%{$this->escapeSoql($filtros['objeto'])}%'";
        }

        if (!empty($filtros['fecha_desde'])) {
            $where[] = "fecha_de_firma >= '{$filtros['fecha_desde']}T00:00:00.000'";
        }

        if (!empty($filtros['fecha_hasta'])) {
            $where[] = "fecha_de_firma <= '{$filtros['fecha_hasta']}T23:59:59.000'";
        }

        $params = [
            '$where' => implode(' AND ', $where),
            '$limit' => min($filtros['limit'] ?? 25, 100),
            '$offset' => $filtros['offset'] ?? 0,
            '$order' => 'ultima_actualizacion DESC',
        ];

        $cacheKey = 'secop_entidad_' . md5(json_encode($params));

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($params) {
            return $this->query($params);
        });
    }

    /**
     * Obtener detalle de un contrato por su ID de SECOP.
     */
    public function obtenerContrato(string $idContrato): ?array
    {
        $cacheKey = 'secop_contrato_' . md5($idContrato);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($idContrato) {
            $resultados = $this->query([
                '$where' => "id_contrato = '{$this->escapeSoql($idContrato)}'",
                '$limit' => 1,
            ]);

            return $resultados[0] ?? null;
        });
    }

    /**
     * Obtener estadísticas resumidas de la entidad.
     */
    public function obtenerEstadisticas(): array
    {
        return Cache::remember('secop_stats_' . $this->entidadNit, now()->addMinutes(30), function () {
            $contratos = $this->query([
                '$where' => "nit_entidad = '{$this->entidadNit}'",
                '$select' => 'estado_contrato, count(*) as total, sum(valor_del_contrato) as valor_total',
                '$group' => 'estado_contrato',
                '$order' => 'total DESC',
            ]);

            $stats = [
                'total' => 0,
                'valor_total' => 0,
                'por_estado' => [],
            ];

            foreach ($contratos as $row) {
                $count = (int) ($row['total'] ?? 0);
                $valor = (float) ($row['valor_total'] ?? 0);
                $stats['total'] += $count;
                $stats['valor_total'] += $valor;
                $stats['por_estado'][$row['estado_contrato'] ?? 'Sin estado'] = [
                    'cantidad' => $count,
                    'valor' => $valor,
                ];
            }

            return $stats;
        });
    }

    /**
     * Refrescar datos de un contrato (invalida cache).
     */
    public function refrescar(string $idContrato): ?array
    {
        Cache::forget('secop_contrato_' . md5($idContrato));
        return $this->obtenerContrato($idContrato);
    }

    /**
     * Ejecutar consulta contra la API de Socrata.
     */
    private function query(array $params): array
    {
        try {
            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->withBasicAuth($this->keyId, $this->keySecret)
                ->get($this->apiUrl, $params);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning('SECOP API respondió con error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'params' => $params,
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error consultando SECOP API', [
                'message' => $e->getMessage(),
                'params' => $params,
            ]);

            return [];
        }
    }

    /**
     * Escapar valores para SoQL (Socrata Query Language).
     */
    private function escapeSoql(string $value): string
    {
        return str_replace("'", "''", $value);
    }
}
