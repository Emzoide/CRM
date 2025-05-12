<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cotización {{ $cotizacion->codigo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            margin: 0;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 40%;
            padding: 5px;
            font-weight: bold;
            color: #666;
        }

        .info-value {
            display: table-cell;
            width: 60%;
            padding: 5px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Cotización {{ $cotizacion->codigo }}</h1>
        <p>Emitida el {{ $cotizacion->emitida_en->format('d/m/Y H:i') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Información del Cliente</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombre:</div>
                <div class="info-value">{{ $cliente->nombre }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">DNI/RUC:</div>
                <div class="info-value">{{ $cliente->dni_ruc }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Información del Vehículo</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Marca:</div>
                <div class="info-value">{{ $vehiculo->marca->nombre }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Modelo:</div>
                <div class="info-value">{{ $vehiculo->modelo->nombre }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Versión:</div>
                <div class="info-value">{{ $vehiculo->version->nombre }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Cantidad:</div>
                <div class="info-value">{{ $vehiculo->cantidad }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Precio Unitario:</div>
                <div class="info-value">S/ {{ number_format($vehiculo->precio_unit, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Detalles de la Cotización</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Vendedor:</div>
                <div class="info-value">{{ $cotizacion->vendedor->full_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Monto Total:</div>
                <div class="info-value">S/ {{ number_format($cotizacion->total, 2) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tipo de Compra:</div>
                <div class="info-value">{{ $cotizacion->tipo_compra === 'credito' ? 'Crédito' : 'Contado' }}</div>
            </div>
            @if($cotizacion->banco)
            <div class="info-row">
                <div class="info-label">Banco:</div>
                <div class="info-value">{{ $cotizacion->banco->nombre }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Compra a Plazos:</div>
                <div class="info-value">{{ $cotizacion->compra_plazos ? 'Sí' : 'No' }} {{ $cotizacion->razon_no_plazos ? "({$cotizacion->razon_no_plazos})" : '' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Seguro Vehicular:</div>
                <div class="info-value">{{ $cotizacion->seguro_vehicular ? 'Sí' : 'No' }} {{ $cotizacion->razon_no_seguro ? "({$cotizacion->razon_no_seguro})" : '' }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Este documento es generado automáticamente por el sistema CRM de Interamericana Norte.</p>
        <p>Fecha de emisión: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>

</html>