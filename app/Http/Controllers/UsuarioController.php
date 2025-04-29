<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Tienda;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;

class UsuarioController extends Controller
{
    public function index()
    {
        // Cargar usuarios con sus relaciones
        $usuarios = Usuario::with(['tienda.sucursal', 'sucursal'])->get();

        // Depuración para verificar las relaciones
        foreach ($usuarios as $usuario) {
            \Log::info("Usuario ID: {$usuario->id}, Login: {$usuario->login}");
            \Log::info("  - Tienda ID: " . ($usuario->tienda_id ?? 'null'));
            \Log::info("  - Sucursal ID: " . ($usuario->sucursal_id ?? 'null'));

            // Verificar si la tienda existe y es un objeto
            if ($usuario->tienda && is_object($usuario->tienda)) {
                \Log::info("  - Tienda: " . ($usuario->tienda->nombre ?? 'Sin nombre'));
                \Log::info("  - Tienda Sucursal ID: " . ($usuario->tienda->sucursal_id ?? 'null'));

                // Verificar si la propiedad sucursal existe
                if (isset($usuario->tienda->sucursal) && $usuario->tienda->sucursal) {
                    \Log::info("    - Sucursal: " . ($usuario->tienda->sucursal->nombre ?? 'Sin nombre'));
                } else {
                    \Log::info("    - No tiene sucursal asociada");
                }
            } else {
                \Log::info("  - No tiene tienda asociada o la tienda no es un objeto válido");
            }

            // Verificar la sucursal directa del usuario
            if ($usuario->sucursal && is_object($usuario->sucursal)) {
                \Log::info("  - Sucursal directa: " . ($usuario->sucursal->nombre ?? 'Sin nombre'));
            }
        }

        // Cargar tiendas con sus sucursales
        $tiendas = Tienda::with('sucursal')->orderBy('nombre')->get();

        // Depuración de tiendas
        foreach ($tiendas as $tienda) {
            \Log::info("Tienda ID: {$tienda->id}");
            \Log::info("  - Nombre: " . ($tienda->nombre ?? 'Sin nombre'));
            \Log::info("  - Sucursal ID: " . ($tienda->sucursal_id ?? 'null'));
            if ($tienda->sucursal && is_object($tienda->sucursal)) {
                \Log::info("  - Sucursal: " . ($tienda->sucursal->nombre ?? 'Sin nombre'));
            } else {
                \Log::info("  - No tiene sucursal asociada o la sucursal no es un objeto válido");
            }
        }

        return View::make('admin.usuarios', compact('usuarios', 'tiendas'));
    }

    public function create()
    {
        return Redirect::route('admin.usuarios.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string|max:30|unique:usuarios,login',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'rol' => 'required|in:admin,seller,supervisor',
            'tienda_id' => 'nullable|exists:tiendas,id',
        ]);

        $data['password_hash'] = Hash::make($data['password']);
        unset($data['password']);
        $data['activo'] = true;
        $data['full_name'] = trim("{$data['first_name']} {$data['last_name']}");

        // Si se seleccionó una tienda, obtener su sucursal_id
        if (!empty($data['tienda_id'])) {
            $tienda = Tienda::find($data['tienda_id']);
            if ($tienda) {
                $data['sucursal_id'] = $tienda->sucursal_id;
            }
        }

        \Log::info("Creando usuario con datos:", $data);

        try {
            $usuario = Usuario::create($data);
            \Log::info("Usuario creado:", [
                'id' => $usuario->id,
                'tienda_id' => $usuario->tienda_id,
                'sucursal_id' => $usuario->sucursal_id,
                'email' => $usuario->email,
                'full_name' => $usuario->full_name
            ]);
        } catch (\Exception $e) {
            \Log::error("Error al crear usuario: " . $e->getMessage());
            \Log::error("Datos enviados: ", $data);
            throw $e;
        }

        return Redirect::route('admin.usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show(Usuario $usuario)
    {
        return Redirect::route('admin.usuarios.index');
    }

    public function edit(Usuario $usuario)
    {
        return Redirect::route('admin.usuarios.index');
    }

    public function update(Request $request, Usuario $usuario)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:usuarios,email,' . $usuario->id,
            'rol' => 'required|in:admin,seller,supervisor',
            'tienda_id' => 'nullable|exists:tiendas,id',
            'activo' => 'boolean',
        ]);

        $data['full_name'] = trim("{$data['first_name']} {$data['last_name']}");

        \Log::info("Actualizando usuario {$usuario->id} con datos:", $data);

        // Asegurarse de que tienda_id sea null si está vacío
        if (empty($data['tienda_id'])) {
            $data['tienda_id'] = null;
            $data['sucursal_id'] = null;
        } else {
            // Si se seleccionó una tienda, obtener su sucursal_id
            $tienda = Tienda::find($data['tienda_id']);
            if ($tienda) {
                $data['sucursal_id'] = $tienda->sucursal_id;
            }
        }

        $usuario->update($data);

        // Recargar el usuario con sus relaciones para verificar
        $usuario->refresh();

        \Log::info("Usuario actualizado:", [
            'id' => $usuario->id,
            'tienda_id' => $usuario->tienda_id,
            'sucursal_id' => $usuario->sucursal_id,
            'tienda' => ($usuario->tienda && is_object($usuario->tienda)) ? [
                'id' => $usuario->tienda->id ?? null,
                'nombre' => $usuario->tienda->nombre ?? 'Sin nombre',
                'sucursal_id' => $usuario->tienda->sucursal_id ?? null
            ] : null
        ]);

        return Redirect::route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(Usuario $usuario)
    {
        $usuario->delete();

        return Redirect::route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}
