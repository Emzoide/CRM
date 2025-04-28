<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;


class UsuarioController extends Controller
{
    public function index()
    {
        //return view('usuarios.index', compact('usuarios'));
        return redirect('/') // debería retornar view('usuarios.index', compact('usuarios'))
            ->with('info', 'Vista temporal reemplazada');
    }

    public function create()
    {
        //return view('usuarios.create');
        return redirect('/') // debería retornar view('usuarios.create')
            ->with('info', 'Vista temporal reemplazada');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string|max:30|unique:usuarios,login',
            'full_name' => 'required|string|max:100',
            'password' => 'required|string|min:6',
            'rol' => 'required|in:admin,seller,supervisor',
            'tienda' => 'nullable|string|max:10',
        ]);
        $data['password_hash'] = bcrypt($data['password']);
        unset($data['password']);

        Usuario::create($data);

        return redirect('/') // debería redirigir a route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show(Usuario $usuario)
    {
        //return view('usuarios.show', compact('usuario'));
        return redirect('/') // debería retornar view('usuarios.show', compact('usuario'))
            ->with('info', 'Vista temporal reemplazada');
    }

    public function edit(Usuario $usuario)
    {
        //return view('usuarios.edit', compact('usuario'));
        return redirect('/') // debería retornar view('usuarios.edit', compact('usuario'))
            ->with('info', 'Vista temporal reemplazada');
    }

    public function update(Request $request, Usuario $usuario)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:100',
            'rol' => 'required|in:admin,seller,supervisor',
            'tienda' => 'nullable|string|max:10',
            'activo' => 'boolean',
        ]);

        $usuario->update($data);

        return redirect('/') // debería redirigir a route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(Usuario $usuario)
    {
        $usuario->delete();

        return redirect('/') // debería redirigir a route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}
