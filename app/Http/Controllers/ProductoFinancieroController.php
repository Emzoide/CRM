<?php

namespace App\Http\Controllers;

use App\Models\ProductoFinanciero;
use Illuminate\Http\Request;

class ProductoFinancieroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return view('productos_financieros.index', compact('productos'));
        return redirect('/') // debería retornar view('productos_financieros.index', compact('productos'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //return view('productos_financieros.create');
        return redirect('/') // debería retornar view('productos_financieros.create')
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'banco_id' => 'required|exists:bancos,id',
            'nombre'   => 'required|string|max:60|unique:productos_financieros,nombre',
            'tipo'     => 'required|in:credit,leasing,fondos_colectivos,other',
        ]);

        ProductoFinanciero::create($data);

        return redirect('/') // debería redirigir a route('productos_financieros.index')
            ->with('success', 'Producto financiero creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductoFinanciero $producto)
    {
        //return view('productos_financieros.show', compact('producto'));
        return redirect('/') // debería retornar view('productos_financieros.show', compact('producto'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductoFinanciero $producto)
    {
        //return view('productos_financieros.edit', compact('producto'));
        return redirect('/') // debería retornar view('productos_financieros.edit', compact('producto'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductoFinanciero $producto)
    {
        $data = $request->validate([
            'banco_id' => 'required|exists:bancos,id',
            'nombre'   => 'required|string|max:60|unique:productos_financieros,nombre,' . $producto->id,
            'tipo'     => 'required|in:credit,leasing,fondos_colectivos,other',
        ]);

        $producto->update($data);

        return redirect('/') // debería redirigir a route('productos_financieros.index')
            ->with('success', 'Producto financiero actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductoFinanciero $producto)
    {
        $producto->delete();

        return redirect('/') // debería redirigir a route('productos_financieros.index')
            ->with('success', 'Producto financiero eliminado exitosamente.');
    }
}