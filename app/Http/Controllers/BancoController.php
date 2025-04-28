<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use App\Models\ProductoFinanciero;
use App\Models\Marca;
use Illuminate\Http\Request;

class BancoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return view('bancos.index', compact('bancos'));
        return redirect('/') // debería retornar view('bancos.index', compact('bancos'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //return view('bancos.create');
        return redirect('/') // debería retornar view('bancos.create')
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:60|unique:bancos,nombre',
        ]);

        Banco::create($data);

        return redirect('/') // debería redirigir a route('bancos.index')
            ->with('success', 'Banco creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Banco $banco)
    {
        //return view('bancos.show', compact('banco'));
        return redirect('/') // debería retornar view('bancos.show', compact('banco'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banco $banco)
    {
        //return view('bancos.edit', compact('banco'));
        return redirect('/') // debería retornar view('bancos.edit', compact('banco'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banco $banco)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:60|unique:bancos,nombre,' . $banco->id,
        ]);

        $banco->update($data);

        return redirect('/') // debería redirigir a route('bancos.index')
            ->with('success', 'Banco actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banco $banco)
    {
        $banco->delete();

        return redirect('/') // debería redirigir a route('bancos.index')
            ->with('success', 'Banco eliminado exitosamente.');
    }
}