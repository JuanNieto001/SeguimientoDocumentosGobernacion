<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Inclusión PAA — {{ $paa->codigo_necesidad }}</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Times New Roman',serif;background:#fff;color:#1a1a1a;font-size:12pt;line-height:1.6}
        .page{max-width:760px;margin:0 auto;padding:40px 50px}
        .logo-header{display:flex;align-items:center;gap:20px;border-bottom:3px solid #166534;padding-bottom:16px;margin-bottom:24px}
        .logo-header img{width:64px;height:64px;object-fit:contain}
        .org-info h1{font-size:14pt;font-weight:700;color:#166534;margin-bottom:2px}
        .org-info p{font-size:10pt;color:#555}
        .cert-title{text-align:center;margin:28px 0 10px;font-size:16pt;font-weight:700;letter-spacing:1px;color:#1a1a1a;text-transform:uppercase}
        .cert-subtitle{text-align:center;font-size:10pt;color:#555;margin-bottom:28px}
        .cert-num{text-align:center;font-size:10pt;margin-bottom:32px}
        .cert-num span{display:inline-block;padding:6px 20px;border:1.5px solid #166534;border-radius:4px;font-family:monospace;font-size:11pt;font-weight:700;color:#166534;letter-spacing:2px}
        .body-text{text-align:justify;font-size:11.5pt;line-height:1.8;margin-bottom:20px}
        .datos-table{width:100%;border-collapse:collapse;margin:20px 0 28px}
        .datos-table tr td:first-child{font-weight:600;width:40%;color:#374151;padding:8px 10px;border-bottom:1px solid #e5e7eb;vertical-align:top}
        .datos-table tr td:last-child{padding:8px 10px;border-bottom:1px solid #e5e7eb;color:#1a1a1a}
        .seal-area{display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-top:60px}
        .seal-box{text-align:center}
        .seal-line{border-top:1.5px solid #1a1a1a;padding-top:8px;margin-top:50px;font-size:9.5pt;color:#374151}
        .footer-note{margin-top:36px;padding:12px 16px;background:#f0fdf4;border-left:3px solid #166534;font-size:9pt;color:#374151;line-height:1.5}
        @media print{
            body{background:#fff}
            .no-print{display:none!important}
            .page{padding:20px 30px}
        }
    </style>
</head>
<body>
    <div class="page">

        {{-- Botones (solo pantalla) --}}
        <div class="no-print" style="margin-bottom:20px;display:flex;gap:10px">
            <button onclick="window.print()" style="padding:8px 20px;background:#166534;color:#fff;border:none;border-radius:8px;font-size:11pt;cursor:pointer;font-weight:600">
                🖨️ Imprimir / Guardar PDF
            </button>
            <a href="{{ route('paa.show', $paa->id) }}" style="padding:8px 20px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:11pt;color:#374151;text-decoration:none">
                ← Regresar
            </a>
        </div>

        {{-- Encabezado --}}
        <div class="logo-header">
            <img src="/images/gobernacion.png" alt="Escudo de Caldas" onerror="this.style.display='none'">
            <div class="org-info">
                <h1>Gobernación de Caldas</h1>
                <p>Secretaría de Planeación — Sistema de Contratación Pública</p>
                <p>Manizales, Caldas, Colombia</p>
            </div>
        </div>

        {{-- Título --}}
        <div class="cert-title">Certificado de Inclusión en el<br>Plan Anual de Adquisiciones</div>
        <div class="cert-subtitle">Vigencia {{ $paa->anio }}</div>

        <div class="cert-num">
            <span>CERT-PAA-{{ $paa->anio }}-{{ str_pad($paa->id, 4, '0', STR_PAD_LEFT) }}</span>
        </div>

        {{-- Cuerpo --}}
        <p class="body-text">
            El/La suscrito(a) <strong>Secretario(a) de Planeación</strong> de la Gobernación de Caldas, en ejercicio de sus funciones legales y reglamentarias, y de conformidad con lo establecido en la Ley 80 de 1993, la Ley 1150 de 2007, el Decreto 1082 de 2015 y demás normas concordantes,
        </p>
        <p class="body-text">
            <strong>CERTIFICA</strong> que la necesidad identificada con el código <strong>{{ $paa->codigo_necesidad }}</strong> se encuentra debidamente incluida en el <strong>Plan Anual de Adquisiciones (PAA) vigencia {{ $paa->anio }}</strong> de la Gobernación de Caldas, con las siguientes características:
        </p>

        {{-- Tabla de datos --}}
        <table class="datos-table">
            <tr>
                <td>Código de necesidad</td>
                <td><strong>{{ $paa->codigo_necesidad }}</strong></td>
            </tr>
            <tr>
                <td>Objeto del contrato</td>
                <td>{{ $paa->descripcion }}</td>
            </tr>
            <tr>
                <td>Dependencia solicitante</td>
                <td>{{ $paa->dependencia_solicitante }}</td>
            </tr>
            <tr>
                <td>Modalidad de contratación</td>
                <td>{{ $paa->modalidad_contratacion }} — {{ $modalidades[$paa->modalidad_contratacion] ?? '' }}</td>
            </tr>
            <tr>
                <td>Valor estimado</td>
                <td><strong>$ {{ number_format($paa->valor_estimado, 2, ',', '.') }} COP</strong></td>
            </tr>
            <tr>
                <td>Trimestre estimado de inicio</td>
                <td>{{ ['1'=>'I Trimestre (Ene–Mar)','2'=>'II Trimestre (Abr–Jun)','3'=>'III Trimestre (Jul–Sep)','4'=>'IV Trimestre (Oct–Dic)'][$paa->trimestre_estimado] ?? 'T'.$paa->trimestre_estimado }}</td>
            </tr>
            <tr>
                <td>Estado en el PAA</td>
                <td>{{ ucfirst($paa->estado) }}</td>
            </tr>
            <tr>
                <td>Fecha de expedición</td>
                <td>{{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</td>
            </tr>
        </table>

        <p class="body-text">
            El presente certificado se expide en la ciudad de Manizales a los <strong>{{ now()->day }}</strong> días del mes de <strong>{{ now()->locale('es')->isoFormat('MMMM') }}</strong> del año <strong>{{ now()->year }}</strong>, para los fines previstos en la ley.
        </p>

        {{-- Firmas --}}
        <div class="seal-area">
            <div class="seal-box">
                <div class="seal-line">
                    <strong>Secretaría de Planeación</strong><br>
                    Gobernación de Caldas<br>
                    <span style="font-size:8.5pt;color:#6b7280">Firma y sello</span>
                </div>
            </div>
            <div class="seal-box">
                <div class="seal-line">
                    <strong>Ordenador del Gasto</strong><br>
                    Gobernación de Caldas<br>
                    <span style="font-size:8.5pt;color:#6b7280">Firma y sello</span>
                </div>
            </div>
        </div>

        {{-- Nota --}}
        <div class="footer-note">
            <strong>NOTA:</strong> Este certificado fue generado digitalmente por el Sistema de Seguimiento de Documentos de la Gobernación de Caldas el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}. Código de verificación: <em>{{ strtoupper(substr(md5($paa->id . $paa->codigo_necesidad . $paa->anio), 0, 16)) }}</em>. Para verificar la autenticidad de este documento, contáctese con la Secretaría de Planeación.
        </div>

    </div>
</body>
</html>
