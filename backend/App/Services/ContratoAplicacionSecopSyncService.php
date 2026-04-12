<?php
/**
 * Archivo: backend/App/Services/ContratoAplicacionSecopSyncService.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

namespace App\Services;

use App\Models\ContratoAplicacion;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ContratoAplicacionSecopSyncService
{
    public function __construct(private SecopDatosAbiertoService $secop)
    {
    }

    public function syncCollection(iterable $contratos): array
    {
        $summary = [
            'updated' => 0,
            'not_found' => 0,
            'skipped' => 0,
            'errors' => 0,
            'items' => [],
        ];

        foreach ($contratos as $contrato) {
            if (!$contrato instanceof ContratoAplicacion) {
                $summary['skipped']++;
                continue;
            }

            try {
                $result = $this->syncContrato($contrato);
                $summary['items'][] = $result;
                $status = (string) ($result['status'] ?? 'skipped');
                if (array_key_exists($status, $summary)) {
                    $summary[$status]++;
                } else {
                    $summary['skipped']++;
                }
            } catch (\Throwable $e) {
                $summary['errors']++;
                $summary['items'][] = [
                    'status' => 'error',
                    'secop_id' => (string) ($contrato->secop_proceso_id ?? ''),
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $summary;
    }

    public function syncContrato(ContratoAplicacion $contrato): array
    {
        $secopId = strtoupper(trim((string) $contrato->secop_proceso_id));

        if ($secopId === '') {
            return [
                'status' => 'skipped',
                'reason' => 'missing_secop_id',
            ];
        }

        $payload = $this->secop->obtenerContrato($secopId);

        if (!$payload) {
            $matches = $this->secop->buscarPorReferencia($secopId);
            $payload = $matches[0] ?? null;
        }

        if (!$payload || !is_array($payload)) {
            return [
                'status' => 'not_found',
                'secop_id' => $secopId,
            ];
        }

        $this->hydrateFromSecop($contrato, $payload, $secopId);
        $contrato->save();

        return [
            'status' => 'updated',
            'secop_id' => $secopId,
            'estado_secop' => $this->firstNonEmptyString($payload, ['estado_contrato']),
        ];
    }

    public function upsertFromSecopReferences(array $items): array
    {
        $prepared = [];

        foreach ($items as $item) {
            $secopId = strtoupper(trim((string) ($item['secop_id'] ?? '')));
            $aplicacion = trim((string) ($item['aplicacion'] ?? ''));
            $activo = array_key_exists('activo', $item)
                ? ($item['activo'] === null ? null : (bool) $item['activo'])
                : null;

            if ($secopId === '') {
                continue;
            }

            $prepared[$secopId] = [
                'secop_id' => $secopId,
                'aplicacion' => $aplicacion,
                'activo' => $activo,
            ];
        }

        $summary = [
            'created' => 0,
            'existing' => 0,
            'sync' => [
                'updated' => 0,
                'not_found' => 0,
                'skipped' => 0,
                'errors' => 0,
                'items' => [],
            ],
        ];

        foreach ($prepared as $item) {
            $secopId = $item['secop_id'];
            $aplicacion = $item['aplicacion'];
            $activo = $item['activo'];

            $contrato = ContratoAplicacion::query()
                ->where('secop_proceso_id', $secopId)
                ->orderByDesc('id')
                ->first();

            if (!$contrato) {
                $contrato = new ContratoAplicacion();
                $contrato->secop_proceso_id = $secopId;
                $contrato->aplicacion = $aplicacion !== '' ? $aplicacion : $secopId;
                $contrato->estado = 'vigente';
                $contrato->activo = $activo ?? true;
                $contrato->save();
                $summary['created']++;
            } else {
                $hasChanges = false;

                if ($aplicacion !== '' && $aplicacion !== $contrato->aplicacion) {
                    $contrato->aplicacion = $aplicacion;
                    $hasChanges = true;
                }

                if ($activo !== null && $activo !== (bool) $contrato->activo) {
                    $contrato->activo = $activo;
                    $hasChanges = true;
                }

                if ($hasChanges) {
                    $contrato->save();
                }

                $summary['existing']++;
            }

            $result = $this->syncContrato($contrato);
            $summary['sync']['items'][] = $result;

            $status = (string) ($result['status'] ?? 'skipped');
            if (array_key_exists($status, $summary['sync'])) {
                $summary['sync'][$status]++;
            } else {
                $summary['sync']['skipped']++;
            }
        }

        return $summary;
    }

    private function hydrateFromSecop(ContratoAplicacion $contrato, array $payload, string $secopId): void
    {
        $estadoSecop = $this->firstNonEmptyString($payload, ['estado_contrato']) ?? '';
        $nombreAplicacion = $this->sugerirNombreAplicacion($payload, $secopId);

        $fechaInicio = $this->firstDateString($payload, [
            'fecha_de_inicio_del_contrato',
            'fecha_de_inicio_de_contrato',
            'fecha_de_inicio_contrato',
            'contract_start_date',
        ]);

        $fechaFin = $this->firstDateString($payload, [
            'fecha_de_fin_del_contrato',
            'fecha_de_terminacion_del_contrato',
            'fecha_de_fin_de_contrato',
            'contract_end_date',
        ]);

        if ($this->shouldOverwriteAplicacion((string) $contrato->aplicacion, $secopId)) {
            $contrato->aplicacion = $nombreAplicacion;
        }

        $contrato->secop_proceso_id = $secopId;
        $contrato->numero_contrato = $this->firstNonEmptyString($payload, [
            'referencia_del_contrato',
            'id_contrato',
            'proceso_de_compra',
        ]) ?? $contrato->numero_contrato;

        $contrato->proveedor = $this->firstNonEmptyString($payload, ['proveedor_adjudicado']) ?? $contrato->proveedor;
        $contrato->objeto = $this->firstNonEmptyString($payload, ['objeto_del_contrato', 'descripcion_del_proceso']) ?? $contrato->objeto;
        $contrato->fecha_inicio = $fechaInicio ?? $contrato->fecha_inicio;
        $contrato->fecha_fin = $fechaFin ?? $contrato->fecha_fin;

        $valor = $this->firstNumericValue($payload, ['valor_del_contrato', 'valor_total_contrato']);
        if ($valor !== null) {
            $contrato->valor_total = $valor;
        }

        $secopUrl = $this->firstNonEmptyString($payload, ['urlproceso.url', 'urlproceso']);
        if ($secopUrl !== null) {
            $contrato->secop_url = $secopUrl;
        }

        $estadoInterno = $this->resolveEstadoInterno($estadoSecop, $fechaFin);
        $contrato->estado = $estadoInterno;

        // Respetar inactivacion manual: si el contrato ya fue marcado inactivo,
        // la sincronizacion no lo reactiva automaticamente.
        $activoAutomatico = $this->resolveActivo($estadoSecop, $fechaFin);
        $contrato->activo = (bool) $contrato->activo ? $activoAutomatico : false;

        $metadata = (array) ($contrato->secop_metadata ?? []);
        $metadata['secop_estado'] = $estadoSecop;
        $metadata['ultima_actualizacion'] = $this->firstNonEmptyString($payload, ['ultima_actualizacion']);
        $metadata['fecha_firma'] = $this->firstNonEmptyString($payload, ['fecha_de_firma']);
        $metadata['proceso_de_compra'] = $this->firstNonEmptyString($payload, ['proceso_de_compra']);
        $metadata['sincronizado_at'] = now()->toIso8601String();

        $contrato->secop_metadata = $metadata;
    }

    public function sugerirNombreAplicacion(array $payload, string $fallback = ''): string
    {
        $fromText = $this->extractNameFromDescriptiveText([
            (string) ($payload['objeto_del_contrato'] ?? ''),
            (string) ($payload['descripcion_del_proceso'] ?? ''),
        ]);

        if ($fromText !== null) {
            return $fromText;
        }

        $fromProvider = $this->normalizeProviderName((string) ($payload['proveedor_adjudicado'] ?? ''));
        if ($fromProvider !== null) {
            return $fromProvider;
        }

        $reference = trim((string) ($payload['referencia_del_contrato'] ?? ''));
        if ($reference !== '' && !$this->isSecopCode($reference)) {
            return $this->formatReadableName($reference);
        }

        $fallback = trim((string) $fallback);
        if ($fallback !== '') {
            return strtoupper($fallback);
        }

        return 'Aplicativo sin nombre';
    }

    private function shouldOverwriteAplicacion(string $current, string $secopId): bool
    {
        $current = trim($current);
        if ($current === '') {
            return true;
        }

        if (strcasecmp($current, $secopId) === 0) {
            return true;
        }

        return $this->isSecopCode($current);
    }

    private function extractNameFromDescriptiveText(array $texts): ?string
    {
        foreach ($texts as $text) {
            $source = trim((string) $text);
            if ($source === '') {
                continue;
            }

            if (preg_match('/\(([A-Za-z0-9ÁÉÍÓÚÑáéíóúñ ._-]{2,50})\)/u', $source, $match)) {
                $candidate = $this->sanitizeCandidateName($match[1]);
                if ($candidate !== null) {
                    return $candidate;
                }
            }

            if (preg_match('/\bSGI[-\s]*([A-Za-z0-9ÁÉÍÓÚÑáéíóúñ]{2,40})\b/u', $source, $match)) {
                $candidate = $this->sanitizeCandidateName($match[1]);
                if ($candidate !== null) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function normalizeProviderName(string $provider): ?string
    {
        $provider = trim($provider);
        if ($provider === '') {
            return null;
        }

        $provider = preg_replace('/\b(S\.?\s*A\.?\s*S\.?|SAS|S\.?\s*A\.?|LTDA|LIMITADA|E\.?U\.?)\b/iu', '', $provider) ?? $provider;
        $provider = trim((string) preg_replace('/\s+/', ' ', $provider));

        if ($provider === '') {
            return null;
        }

        if (preg_match('/\b(information|management|solutions|systems|consulting)\b/i', $provider)) {
            $parts = preg_split('/\s+/', $provider) ?: [];
            if (count($parts) > 0) {
                $provider = (string) $parts[0];
            }
        }

        return $this->sanitizeCandidateName($provider);
    }

    private function sanitizeCandidateName(string $value): ?string
    {
        $value = trim($value);
        $value = trim($value, " \t\n\r\0\x0B.,;:-_()[]{}\"'");

        if ($value === '' || $this->isSecopCode($value)) {
            return null;
        }

        if (mb_strlen($value) < 2) {
            return null;
        }

        return $this->formatReadableName($value);
    }

    private function formatReadableName(string $value): string
    {
        $value = trim((string) preg_replace('/\s+/', ' ', $value));
        return Str::title(Str::lower($value));
    }

    private function isSecopCode(string $value): bool
    {
        return (bool) preg_match('/^CO1\.[A-Z0-9]+\.[0-9]+$/i', trim($value));
    }

    private function resolveEstadoInterno(string $estadoSecop, ?string $fechaFin): string
    {
        $normalized = Str::of($estadoSecop)->ascii()->lower()->trim()->value();

        if (Str::contains($normalized, ['suspend'])) {
            return 'suspendido';
        }

        if (Str::contains($normalized, ['en ejecucion', 'ejecucion', 'activo', 'vigente'])) {
            return $this->estadoPorFechaFin($fechaFin);
        }

        if (Str::contains($normalized, ['cerrado', 'terminado', 'liquidado', 'finalizado', 'cancelado'])) {
            return 'vencido';
        }

        if (Str::contains($normalized, ['por vencer', 'vencimiento'])) {
            return 'por_vencer';
        }

        return $this->estadoPorFechaFin($fechaFin);
    }

    private function resolveActivo(string $estadoSecop, ?string $fechaFin): bool
    {
        $normalized = Str::of($estadoSecop)->ascii()->lower()->trim()->value();

        if (Str::contains($normalized, ['cerrado', 'terminado', 'liquidado', 'finalizado', 'cancelado'])) {
            return false;
        }

        if ($fechaFin !== null) {
            try {
                return Carbon::parse($fechaFin)->endOfDay()->greaterThanOrEqualTo(now()->startOfDay());
            } catch (\Throwable $e) {
                return true;
            }
        }

        return true;
    }

    private function estadoPorFechaFin(?string $fechaFin): string
    {
        if ($fechaFin === null) {
            return 'vigente';
        }

        try {
            $end = Carbon::parse($fechaFin)->endOfDay();
            $today = now()->startOfDay();

            if ($end->lt($today)) {
                return 'vencido';
            }

            if ($end->lte($today->copy()->addDays(90))) {
                return 'por_vencer';
            }
        } catch (\Throwable $e) {
            return 'vigente';
        }

        return 'vigente';
    }

    private function firstNonEmptyString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($payload, $key);

            if (is_string($value)) {
                $trimmed = trim($value);
                if ($trimmed !== '') {
                    return $trimmed;
                }
            }

            if (is_numeric($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    private function firstDateString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($payload, $key);
            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            try {
                return Carbon::parse($value)->toDateString();
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    private function firstNumericValue(array $payload, array $keys): ?float
    {
        foreach ($keys as $key) {
            $value = Arr::get($payload, $key);
            if ($value === null || $value === '') {
                continue;
            }

            if (is_numeric($value)) {
                return (float) $value;
            }

            if (!is_string($value)) {
                continue;
            }

            $clean = preg_replace('/[^0-9,.-]/', '', $value);
            if ($clean === null || $clean === '') {
                continue;
            }

            if (substr_count($clean, '.') > 1 && substr_count($clean, ',') === 0) {
                $clean = str_replace('.', '', $clean);
            }

            if (substr_count($clean, ',') === 1 && substr_count($clean, '.') === 0) {
                $clean = str_replace(',', '.', $clean);
            } else {
                $clean = str_replace(',', '', $clean);
            }

            if (is_numeric($clean)) {
                return (float) $clean;
            }
        }

        return null;
    }
}
