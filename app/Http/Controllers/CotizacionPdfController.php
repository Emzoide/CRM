<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use Illuminate\Http\Request;
use PDF;

class CotizacionPdfController extends Controller
{
    public function download($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'vehiculo.version', 'vendedor', 'banco'])
            ->findOrFail($id);

        $pdf = PDF::loadView('pdf.cotizacion', [
            'cotizacion' => $cotizacion,
            'cliente' => $cotizacion->cliente,
            'vehiculo' => $cotizacion->vehiculo
        ]);

        return $pdf->download("cotizacion-{$cotizacion->codigo}.pdf");
    }
}
