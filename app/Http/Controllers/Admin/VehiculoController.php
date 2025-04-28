<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\VersionVehiculo;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    /**
     * Muestra la vista de administración de vehículos
     */
    public function index()
    {
        $marcas = Marca::all();
        $modelos = Modelo::with('marca')->get();
        $versiones = VersionVehiculo::with('modelo.marca')->get();

        return view('admin.vehiculos', compact('marcas', 'modelos', 'versiones'));
    }

    /**
     * Almacena una nueva marca
     */
    public function storeMarca(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:40|unique:marcas,nombre',
        ]);

        Marca::create($data);

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Marca creada exitosamente.');
    }

    /**
     * Actualiza una marca existente
     */
    public function updateMarca(Request $request, Marca $marca)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:40|unique:marcas,nombre,' . $marca->id,
        ]);

        $marca->update($data);

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Marca actualizada exitosamente.');
    }

    /**
     * Elimina una marca
     */
    public function destroyMarca(Marca $marca)
    {
        $marca->delete();

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Marca eliminada exitosamente.');
    }

    /**
     * Almacena un nuevo modelo
     */
    public function storeModelo(Request $request)
    {
        $data = $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'nombre' => 'required|string|max:40|unique:modelos,nombre',
        ]);

        Modelo::create($data);

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Modelo creado exitosamente.');
    }

    /**
     * Actualiza un modelo existente
     */
    public function updateModelo(Request $request, Modelo $modelo)
    {
        $data = $request->validate([
            'marca_id' => 'required|exists:marcas,id',
            'nombre' => 'required|string|max:40|unique:modelos,nombre,' . $modelo->id,
        ]);

        $modelo->update($data);

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Modelo actualizado exitosamente.');
    }

    /**
     * Elimina un modelo
     */
    public function destroyModelo(Modelo $modelo)
    {
        $modelo->delete();

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Modelo eliminado exitosamente.');
    }

    /**
     * Almacena una nueva versión
     */
    public function storeVersion(Request $request)
    {
        $data = $request->validate([
            'modelo_id' => 'required|exists:modelos,id',
            'nombre' => 'required|string|max:60',
            'anio' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        VersionVehiculo::create($data);

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Versión creada exitosamente.');
    }

    /**
     * Actualiza una versión existente
     */
    public function updateVersion(Request $request, VersionVehiculo $version)
    {
        $data = $request->validate([
            'modelo_id' => 'required|exists:modelos,id',
            'nombre' => 'required|string|max:60',
            'anio' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $version->update($data);

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Versión actualizada exitosamente.');
    }

    /**
     * Elimina una versión
     */
    public function destroyVersion(VersionVehiculo $version)
    {
        $version->delete();

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Versión eliminada exitosamente.');
    }
}
