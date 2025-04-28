<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;

class SucursalController extends Controller
{
    /**
     * Muestra la vista unificada de tiendas y sucursales
     */
    public function index()
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        $tiendas = Tienda::with('sucursal')->orderBy('nombre')->get();
        return View::make('admin.tiendas', compact('sucursales', 'tiendas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        Sucursal::create($request->only('nombre'));

        return Redirect::route('admin.sucursales')
            ->with('success', 'Sucursal creada exitosamente');
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        $sucursal->update($request->only('nombre'));

        return Redirect::route('admin.sucursales')
            ->with('success', 'Sucursal actualizada exitosamente');
    }

    public function destroy(Sucursal $sucursal)
    {
        $sucursal->delete();

        return Redirect::route('admin.sucursales')
            ->with('success', 'Sucursal eliminada exitosamente');
    }
}
