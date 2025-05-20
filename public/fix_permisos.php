<?php

// Cargar el entorno de Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Verificar si el usuario está autenticado
if (!auth()->check()) {
    die('Debes iniciar sesión primero');
}

// Verificar si es una solicitud de ejecución
if (isset($_GET['run'])) {
    try {
        // Obtener el usuario actual
        $usuario = auth()->user();
        echo "<h2>Usuario: {$usuario->email}</h2>";
        
        // Obtener el rol de administrador o crearlo si no existe
        $rolAdmin = \App\Models\Rol::firstOrCreate(
            ['nombre' => 'ADMINISTRADOR'],
            [
                'descripcion' => 'Administrador del sistema',
                'is_admin' => true
            ]
        );
        
        echo "<p>Rol de administrador: {$rolAdmin->nombre}</p>";
        
        // Asignar rol de administrador al usuario actual usando la tabla pivote
        // Primero eliminamos cualquier rol existente
        \DB::table('usuario_rol')->where('usuario_id', $usuario->id)->delete();
        
        // Luego asignamos el rol de administrador
        \DB::table('usuario_rol')->insert([
            'usuario_id' => $usuario->id,
            'rol_id' => $rolAdmin->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "<p>Rol asignado al usuario</p>";
        
        // Crear todos los permisos necesarios
        $permisos = [
            'gestionar_usuarios',
            'gestionar_roles',
            'gestionar_tiendas',
            'configuracion_sistema',
            'gestionar_usuarios_tienda',
            'gestionar_usuarios_rol',
            'gestionar_vehiculos',
            'gestionar_cotizaciones',
            'gestionar_clientes',
            'ver_reportes',
            'gestionar_marcas',
            'gestionar_modelos',
            'gestionar_versiones',
            'acceder_chat'
        ];
        
        echo "<p>Creando permisos...</p>";
        $permisosCreados = [];
        
        foreach ($permisos as $nombrePermiso) {
            $permiso = \App\Models\Permiso::firstOrCreate(
                ['nombre' => $nombrePermiso],
                [
                    'descripcion' => 'Permiso: ' . $nombrePermiso,
                    'grupo' => 'Sistema'
                ]
            );
            $permisosCreados[] = $permiso->id;
        }
        
        echo "<p>Permisos creados: " . count($permisosCreados) . "</p>";
        
        // Asignar todos los permisos al rol de administrador
        $rolAdmin->permisos()->sync($permisosCreados);
        
        echo "<p>Permisos asignados al rol de administrador</p>";
        
        // Limpiar caché
        \Illuminate\Support\Facades\Cache::flush();
        
        echo "<h3>¡Permisos corregidos correctamente!</h3>";
        echo "<p>Ahora deberías tener acceso a todas las funciones del sistema.</p>";
        echo "<p><a href='/admin'>Ir al panel de administración</a></p>";
        
    } catch (\Exception $e) {
        echo "<h3>Error:</h3>";
        echo "<p>{$e->getMessage()}</p>";
        echo "<pre>{$e->getTraceAsString()}</pre>";
    }
} else {
    // Mostrar formulario de confirmación
    echo "<h2>Reparar permisos de administrador</h2>";
    echo "<p>Este script asignará todos los permisos necesarios a tu usuario actual.</p>";
    echo "<p><strong>Usuario actual:</strong> " . auth()->user()->email . "</p>";
    echo "<p><a href='?run=1' style='padding: 10px 15px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Reparar permisos</a></p>";
}
