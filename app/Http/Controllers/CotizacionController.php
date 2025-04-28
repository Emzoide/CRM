<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    public function index()
    {
        $cotizaciones = Cotizacion::all();
        // TODO: return view('cotizaciones.index', compact('cotizaciones'));
        return redirect('/');
    }

    public function create()
    {
        // TODO: return view('cotizaciones.create');
        return redirect('/');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'oportunidad_id'         => 'required|exists:oportunidades,id',
            'codigo'                 => 'required|string|max:30|unique:cotizaciones,codigo,' . $cotizacion->id ?? '',
            'emitida_en'             => 'required|date',
            'vence_en'               => 'nullable|date|after_or_equal:emitida_en',
            'vendedor_id'            => 'required|exists:usuarios,id',
            'total'                  => 'required|numeric|min:0',
            'estado'                 => 'required|in:active,superseded,client-rejected,approved,rejected,historical',

            // **nuevos campos**
            'tipo_compra'            => 'required|in:contado,credito',
            'banco_id'               => 'nullable',
            'banco_otro'             => 'nullable|required_if:banco_id,otro|string|max:60',
            'compra_plazos'          => 'boolean',
            'razon_no_plazos'        => 'nullable|string|max:150',
            'seguro_vehicular'       => 'boolean',
            'razon_no_seguro'        => 'nullable|string|max:150',
            'observacion_call_center' => 'nullable|string',
            'decision_final'         => 'nullable|in:en_proceso,won,lost',
            'razon_decision'         => 'nullable|string|max:255',
        ]);

        Cotizacion::create($data);
        return redirect('/');
    }

    public function show(Cotizacion $cotizacion)
    {
        // TODO: return view('cotizaciones.show', compact('cotizacion'));
        return redirect('/');
    }

    public function edit(Cotizacion $cotizacion)
    {
        // TODO: return view('cotizaciones.edit', compact('cotizacion'));
        return redirect('/');
    }

    public function update(Request $request, Cotizacion $cotizacion)
    {
        $data = $request->validate([
            'oportunidad_id'         => 'required|exists:oportunidades,id',
            'codigo'                 => 'required|string|max:30|unique:cotizaciones,codigo,' . $cotizacion->id ?? '',
            'emitida_en'             => 'required|date',
            'vence_en'               => 'nullable|date|after_or_equal:emitida_en',
            'vendedor_id'            => 'required|exists:usuarios,id',
            'total'                  => 'required|numeric|min:0',
            'estado'                 => 'required|in:active,superseded,client-rejected,approved,rejected,historical',

            // **nuevos campos**
            'tipo_compra'            => 'required|in:contado,credito',
            'banco_id'               => 'nullable',
            'banco_otro'             => 'nullable|required_if:banco_id,otro|string|max:60',
            'compra_plazos'          => 'boolean',
            'razon_no_plazos'        => 'nullable|string|max:150',
            'seguro_vehicular'       => 'boolean',
            'razon_no_seguro'        => 'nullable|string|max:150',
            'observacion_call_center' => 'nullable|string',
            'decision_final'         => 'nullable|in:en_proceso,won,lost',
            'razon_decision'         => 'nullable|string|max:255',
        ]);
        $cotizacion->update($data);
        return redirect('/');
    }

    public function destroy(Cotizacion $cotizacion)
    {
        $cotizacion->delete();
        return redirect('/');
    }
}
