<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PAA {{ $anio }} — Gobernación de Caldas</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Arial,sans-serif;font-size:9pt;color:#1a1a1a;background:#fff}
        .page{padding:20px 24px}
        .header{display:flex;align-items:center;gap:14px;border-bottom:2px solid #166534;padding-bottom:12px;margin-bottom:16px}
        .header img{width:48px;height:48px;object-fit:contain}
        .header-text h1{font-size:12pt;font-weight:700;color:#166534}
        .header-text p{font-size:8.5pt;color:#555;margin-top:2px}
        .doc-title{font-size:13pt;font-weight:700;text-align:center;margin:12px 0 4px;color:#1a1a1a}
        .doc-sub{text-align:center;font-size:9pt;color:#555;margin-bottom:16px}
        .kpis{display:flex;gap:10px;margin-bottom:16px}
        .kpi{flex:1;padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;text-align:center}
        .kpi .val{font-size:14pt;font-weight:700;color:#166534}
        .kpi .lbl{font-size:7.5pt;color:#6b7280;margin-top:1px}
        table{width:100%;border-collapse:collapse;font-size:8pt}
        thead tr{background:#166534;color:#fff}
        thead th{padding:6px 8px;text-align:left;font-weight:600;font-size:7.5pt;letter-spacing:.3px}
        tbody tr:nth-child(even){background:#f9fafb}
        tbody tr:hover{background:#f0fdf4}
        tbody td{padding:5px 8px;border-bottom:1px solid #e5e7eb;vertical-align:top}
        .badge{display:inline-block;padding:2px 7px;border-radius:10px;font-size:7pt;font-weight:600}
        .badge-v{background:#dcfce7;color:#15803d}
        .badge-m{background:#fefce8;color:#a16207}
        .badge-e{background:#dbeafe;color:#1d4ed8}
        .badge-c{background:#fee2e2;color:#b91c1c}
        .footer{margin-top:20px;padding-top:10px;border-top:1px solid #e5e7eb;font-size:7.5pt;color:#9ca3af;display:flex;justify-content:space-between}
        .no-print{margin-bottom:14px;display:flex;gap:8px}
        @media print{
            .no-print{display:none!important}
            body{font-size:8pt}
            .page{padding:10px 14px}
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="no-print">
            <button onclick="window.print()" style="padding:7px 18px;background:#166534;color:#fff;border:none;border-radius:7px;font-size:10pt;cursor:pointer;font-weight:600">🖨️ Imprimir / Guardar PDF</button>
            <a href="{{ route('paa.index', ['anio' => $anio]) }}" style="padding:7px 18px;border:1.5px solid #d1d5db;border-radius:7px;font-size:10pt;color:#374151;text-decoration:none">← Regresar</a>
        </div>

        <div class="header">
            <img src="/images/gobernacion.png" alt="Escudo" onerror="this.style.display='none'">
            <div class="header-text">
                <h1>Gobernación de Caldas</h1>
                <p>Plan Anual de Adquisiciones — Vigencia {{ $anio }} | Generado: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="doc-title">PLAN ANUAL DE ADQUISICIONES {{ $anio }}</div>
        <div class="doc-sub">Secretaría de Planeación — Sistema de Contratación Pública</div>

        <div class="kpis">
            <div class="kpi"><div class="val">{{ $resumen['total'] }}</div><div class="lbl">Total necesidades</div></div>
            <div class="kpi"><div class="val">$ {{ number_format($resumen['valor_total'], 0, ',', '.') }}</div><div class="lbl">Valor total estimado (COP)</div></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción / Objeto</th>
                    <th>Dependencia</th>
                    <th>Modalidad</th>
                    <th style="text-align:right">Valor Estimado</th>
                    <th style="text-align:center">Trim.</th>
                    <th style="text-align:center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paas as $p)
                @php
                $badgeClass = ['vigente'=>'badge-v','modificado'=>'badge-m','ejecutado'=>'badge-e','cancelado'=>'badge-c'][$p->estado] ?? '';
                @endphp
                <tr>
                    <td style="font-family:monospace;font-weight:600;white-space:nowrap">{{ $p->codigo_necesidad }}</td>
                    <td style="max-width:240px">{{ $p->descripcion }}</td>
                    <td style="max-width:160px;color:#555">{{ $p->dependencia_solicitante }}</td>
                    <td><strong>{{ $p->modalidad_contratacion }}</strong></td>
                    <td style="text-align:right;font-family:monospace;white-space:nowrap">$ {{ number_format($p->valor_estimado, 0, ',', '.') }}</td>
                    <td style="text-align:center;font-weight:600">T{{ $p->trimestre_estimado }}</td>
                    <td style="text-align:center"><span class="badge {{ $badgeClass }}">{{ ucfirst($p->estado) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:20px">Sin registros para el año {{ $anio }}</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <span>Gobernación de Caldas — Sistema de Seguimiento de Documentos</span>
            <span>PAA {{ $anio }} — {{ $resumen['total'] }} necesidades registradas</span>
        </div>
    </div>
</body>
</html>
