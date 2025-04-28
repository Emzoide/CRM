<?php

namespace App\Http\Controllers;

use App\Models\DetalleCotizacion;
use Illuminate\Http\Request;

class DetalleCotizacionController extends Controller
{
    public function index()
    {
        $detalles = DetalleCotizacion::all();
        // TODO: return view('detalle_cotizacion.index', compact('detalles'));
        return redirect('/');
    }

    public function create()
    {
        // TODO: return view('detalle_cotizacion.create');
        return redirect('/');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cotizacion_id'       => 'required|exists:cotizaciones,id',
            'version_vehiculo_id' => 'required|exists:versiones_vehiculo,id',
            'cantidad'            => 'nullable|integer|min:1',
            'precio_unit'         => 'required|numeric|min:0',
        ]);
        DetalleCotizacion::create($data);
        return redirect('/');
    }

    public function show(DetalleCotizacion $detalle)
    {
        // TODO: return view('detalle_cotizacion.show', compact('detalle'));
        return redirect('/');
    }

    public function edit(DetalleCotizacion $detalle)
    {
        // TODO: return view('detalle_cotizacion.edit', compact('detalle'));
        return redirect('/');
    }

    public function update(Request $request, DetalleCotizacion $detalle)
    {
        $data = $request->validate([
            'cantidad'    => 'nullable|integer|min:1',
            'precio_unit' => 'required|numeric|min:0',
        ]);
        $detalle->update($data);
        return redirect('/');
    }

    public function destroy(DetalleCotizacion $detalle)
    {
        $detalle->delete();
        return redirect('/');
    }
}
