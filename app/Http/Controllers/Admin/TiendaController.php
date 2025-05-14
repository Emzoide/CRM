<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tienda;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;

class TiendaController extends Controller
{
    public function index()
    {
        $tiendas = Tienda::with('sucursal')->orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre')->get();
        return View::make('admin.tiendas', compact('tiendas', 'sucursales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string',
            'sucursal_id' => 'required|exists:sucursales,id'
        ]);

        Tienda::create($request->all());

        return Redirect::route('admin.tiendas.index')
            ->with('success', 'Tienda creada exitosamente');
    }

    public function update(Request $request, Tienda $tienda)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string',
            'sucursal_id' => 'required|exists:sucursales,id'
        ]);

        $tienda->update($request->all());

        return Redirect::route('admin.tiendas.index')
            ->with('success', 'Tienda actualizada exitosamente');
    }

    public function destroy(Tienda $tienda)
    {
        $tienda->delete();

        return Redirect::route('admin.tiendas.index')
            ->with('success', 'Tienda eliminada exitosamente');
    }
}
