<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo ?? 'Reporte' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }
        .header {
            margin-bottom: 12px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
        }
        .meta {
            margin-top: 6px;
            font-size: 10px;
            color: #374151;
        }
        .meta span {
            margin-right: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
            text-align: left;
            font-weight: bold;
        }
        .footer {
            margin-top: 10px;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo ?? 'Reporte' }}</h1>
        <div class="meta">
            <span><strong>Generado:</strong> {{ optional($generadoEn ?? now())->format('d/m/Y H:i:s') }}</span>
            @if(!empty($contexto ?? []))
                @foreach($contexto as $k => $v)
                    <span><strong>{{ $k }}:</strong> {{ $v }}</span>
                @endforeach
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($columnas as $col)
                    <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($filas as $fila)
                <tr>
                    @foreach($fila as $celda)
                        <td>{{ $celda }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columnas) }}">Sin datos para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Sistema de Seguimiento Contractual - Gobernación de Caldas
    </div>
</body>
</html>
