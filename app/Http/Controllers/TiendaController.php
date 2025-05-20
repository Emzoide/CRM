<?php

namespace App\Http\Controllers;

use App\Models\Tienda;
use App\Models\Sucursal;
use Illuminate\Support\Facades\View;

class TiendaController extends Controller
{
    /**
     * Muestra la vista unificada de tiendas y sucursales
     */
    public function index()
    {
        $tiendas = Tienda::with('sucursal')->orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre')->get();
        return View::make('admin.tiendas', compact('tiendas', 'sucursales'));
    }

    /**
     * Captura cualquier otra acción
     */
    public function __call($method, $parameters)
    {
        return $this->index();
    }
}
