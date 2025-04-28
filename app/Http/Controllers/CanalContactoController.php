<?php

namespace App\Http\Controllers;

use App\Models\CanalContacto;
use Illuminate\Http\Request;

class CanalContactoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return view('canales_contacto.index', compact('canales'));
        return redirect('/') // debería retornar view('canales_contacto.index', compact('canales'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //return view('canales_contacto.create');
        return redirect('/') // debería retornar view('canales_contacto.create')
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:canales_contacto,nombre',
        ]);

        CanalContacto::create($data);

        return redirect('/') // debería redirigir a route('canales_contacto.index')
            ->with('success', 'Canal de contacto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CanalContacto $canalContacto)
    {
        //return view('canales_contacto.show', compact('canalContacto'));
        return redirect('/') // debería retornar view('canales_contacto.show', compact('canalContacto'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CanalContacto $canalContacto)
    {
        //return view('canales_contacto.edit', compact('canalContacto'));
        return redirect('/') // debería retornar view('canales_contacto.edit', compact('canalContacto'))
            ->with('info', 'Vista temporal reemplazada');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CanalContacto $canalContacto)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:canales_contacto,nombre,' . $canalContacto->id,
        ]);

        $canalContacto->update($data);

        return redirect('/') // debería redirigir a route('canales_contacto.index')
            ->with('success', 'Canal de contacto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CanalContacto $canalContacto)
    {
        $canalContacto->delete();

        return redirect('/') // debería redirigir a route('canales_contacto.index')
            ->with('success', 'Canal de contacto eliminado exitosamente.');
    }
}
