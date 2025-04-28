<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use App\Models\ProductoFinanciero;
use App\Models\Marca;
use Illuminate\Http\Request;


class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return view('marcas.index', compact('marcas'));
        return redirect('/') // debería retornar view('marcas.index', compact('marcas'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //return view('marcas.create');
        return redirect('/') // debería retornar view('marcas.create')
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:40|unique:marcas,nombre',
        ]);

        Marca::create($data);

        return redirect('/') // debería redirigir a route('marcas.index')
            ->with('success', 'Marca creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Marca $marca)
    {
        //return view('marcas.show', compact('marca'));
        return redirect('/') // debería retornar view('marcas.show', compact('marca'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marca $marca)
    {
        //return view('marcas.edit', compact('marca'));
        return redirect('/') // debería retornar view('marcas.edit', compact('marca'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Marca $marca)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:40|unique:marcas,nombre,' . $marca->id,
        ]);

        $marca->update($data);

        return redirect('/') // debería redirigir a route('marcas.index')
            ->with('success', 'Marca actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Marca $marca)
    {
        $marca->delete();

        return redirect('/') // debería redirigir a route('marcas.index')
            ->with('success', 'Marca eliminada exitosamente.');
    }
}
