<?php

namespace App\Http\Controllers;

use App\Models\VersionVehiculo;
use Illuminate\Http\Request;

class VersionVehiculoController extends Controller
{
    public function index()
    {
        //return view('versiones.index', compact('versiones'));
        return redirect('/') // debería retornar view('versiones.index', compact('versiones'))
            ->with('info', 'Vista temporal reemplazada');
    }

    public function create()
    {
        //return view('versiones.create');
        return redirect('/') // debería retornar view('versiones.create')
            ->with('info', 'Vista temporal reemplazada');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'modelo_id' => 'required|exists:modelos,id',
            'nombre' => 'required|string|max:60',
            'anio' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        VersionVehiculo::create($data);

        return redirect('/') // debería redirigir a route('versiones.index')
            ->with('success', 'Versión creada exitosamente.');
    }

    public function show(VersionVehiculo $version)
    {
        //return view('versiones.show', compact('version'));
        return redirect('/') // debería retornar view('versiones.show', compact('version'))
            ->with('info', 'Vista temporal reemplazada');
    }

    public function edit(VersionVehiculo $version)
    {
        //return view('versiones.edit', compact('version'));
        return redirect('/') // debería retornar view('versiones.edit', compact('version'))
            ->with('info', 'Vista temporal reemplazada');
    }

    public function update(Request $request, VersionVehiculo $version)
    {
        $data = $request->validate([
            'modelo_id' => 'required|exists:modelos,id',
            'nombre' => 'required|string|max:60',
            'anio' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $version->update($data);

        return redirect('/') // debería redirigir a route('versiones.index')
            ->with('success', 'Versión actualizada exitosamente.');
    }

    public function destroy(VersionVehiculo $version)
    {
        $version->delete();

        return redirect('/') // debería redirigir a route('versiones.index')
            ->with('success', 'Versión eliminada exitosamente.');
    }
}
