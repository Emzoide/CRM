<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use App\Models\ProductoFinanciero;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\VersionVehiculo;
use Illuminate\Http\Request;


class ModeloController extends Controller
{
    public function index()
    {
        //return view('modelos.index', compact('modelos'));
        return redirect('/') // debería retornar view('modelos.index', compact('modelos'))
            ->with('info', 'Vista temporal reemplazada');
    }

    public function create()
    {
        //return view('modelos.create');
        return redirect('/') // debería retornar view('modelos.create')
            ->with('info', 'Vista temporal reemplazada');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'nombre' => 'required|string|max:40|unique:modelos,nombre',
        ]);

        Modelo::create($data);

        return redirect('/') // debería redirigir a route('modelos.index')
            ->with('success', 'Modelo creado exitosamente.');
    }

    public function show(Modelo $modelo)
    {
        //return view('modelos.show', compact('modelo'));
        return redirect('/') // debería retornar view('modelos.show', compact('modelo'))
            ->with('info', 'Vista temporal reemplazada');
    }

    public function edit(Modelo $modelo)
    {
        //return view('modelos.edit', compact('modelo'));
        return redirect('/') // debería retornar view('modelos.edit', compact('modelo'))
            ->with('info', 'Vista temporal reemplazada');
    }

    public function update(Request $request, Modelo $modelo)
    {
        $data = $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'nombre' => 'required|string|max:40|unique:modelos,nombre,' . $modelo->id,
        ]);

        $modelo->update($data);

        return redirect('/') // debería redirigir a route('modelos.index')
            ->with('success', 'Modelo actualizado exitosamente.');
    }

    public function destroy(Modelo $modelo)
    {
        $modelo->delete();

        return redirect('/') // debería redirigir a route('modelos.index')
            ->with('success', 'Modelo eliminado exitosamente.');
    }
}
