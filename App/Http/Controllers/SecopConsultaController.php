<?php

namespace App\Http\Controllers;

use App\Services\SecopDatosAbiertoService;
use Illuminate\Http\Request;

class SecopConsultaController extends Controller
{
    public function __construct(private SecopDatosAbiertoService $secop) {}

    /**
     * Vista principal: buscador y listado de contratos SECOP II.
     */
    public function index(Request $request)
    {
        $contratos = [];
        $estadisticas = null;
        $busqueda = $request->input('busqueda');
        $filtros = $request->only(['estado', 'tipo_contrato', 'modalidad', 'contratista', 'objeto', 'fecha_desde', 'fecha_hasta']);

        if ($busqueda) {
            $contratos = $this->secop->buscarPorReferencia($busqueda);
        } elseif (array_filter($filtros)) {
            $contratos = $this->secop->buscarPorEntidad($filtros);
        } else {
            // Carga inicial: últimos contratos de la Gobernación + estadísticas
            $contratos = $this->secop->buscarPorEntidad(['limit' => 25]);
            $estadisticas = $this->secop->obtenerEstadisticas();
        }

        return view('secop.consulta', compact('contratos', 'estadisticas', 'busqueda', 'filtros'));
    }

    /**
     * Detalle de un contrato específico.
     */
    public function detalle(string $idContrato)
    {
        $contrato = $this->secop->obtenerContrato($idContrato);

        if (!$contrato) {
            return back()->with('error', 'No se encontró el contrato en SECOP II.');
        }

        return view('secop.consulta-detalle', compact('contrato'));
    }

    /**
     * Refrescar datos de un contrato (invalidar cache).
     */
    public function refrescar(string $idContrato)
    {
        $contrato = $this->secop->refrescar($idContrato);

        if (!$contrato) {
            return back()->with('error', 'No se pudo actualizar la información del contrato.');
        }

        return back()->with('success', 'Información actualizada desde SECOP II.');
    }
}
