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

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Permisos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6">Diagnóstico de Permisos y Roles</h1>
        
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Datos del Usuario</h2>
            <?php
            $usuario = auth()->user();
            echo "<p class='mb-2'><strong>ID:</strong> {$usuario->id}</p>";
            echo "<p class='mb-2'><strong>Email:</strong> {$usuario->email}</p>";
            echo "<p class='mb-2'><strong>Nombre:</strong> {$usuario->first_name} {$usuario->last_name}</p>";
            
            // Verificar rol
            echo "<h3 class='text-lg font-semibold mt-4 mb-2'>Roles:</h3>";
            if (method_exists($usuario, 'roles')) {
                $roles = $usuario->roles()->get();
                if ($roles->count() > 0) {
                    echo "<ul class='list-disc pl-6 mb-4'>";
                    foreach ($roles as $rol) {
                        echo "<li><strong>{$rol->nombre}</strong> - {$rol->descripcion}</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p class='text-red-600 mb-4'>⚠️ El usuario no tiene roles asignados en la tabla pivote</p>";
                }
            } else {
                echo "<p class='text-red-600 mb-4'>⚠️ El método 'roles' no existe en el modelo Usuario</p>";
            }
            
            // Verificar si la columna rol_id existe en la tabla usuarios
            try {
                $rolId = \DB::table('usuarios')->where('id', $usuario->id)->value('rol_id');
                if ($rolId) {
                    $rol = \App\Models\Rol::find($rolId);
                    if ($rol) {
                        echo "<p class='mb-2 text-green-600'>✅ Rol directo asignado en la columna rol_id: <strong>{$rol->nombre}</strong></p>";
                    } else {
                        echo "<p class='text-red-600 mb-4'>⚠️ La columna rol_id contiene un ID inválido: {$rolId}</p>";
                    }
                } else {
                    echo "<p class='text-yellow-600 mb-4'>⚠️ La columna rol_id existe pero está vacía para este usuario</p>";
                }
            } catch (\Exception $e) {
                echo "<p class='text-red-600 mb-4'>⚠️ La columna rol_id no existe en la tabla usuarios: {$e->getMessage()}</p>";
            }
            ?>
        </div>
        
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Verificación de Permisos</h2>
            <?php
            // Lista de todos los permisos disponibles
            $todosPermisos = \App\Models\Permiso::all();
            echo "<p class='mb-2'><strong>Total de permisos en el sistema:</strong> {$todosPermisos->count()}</p>";
            
            // Comprobar permisos clave
            $permisosClaves = [
                'gestionar_usuarios',
                'gestionar_usuarios_tienda',
                'gestionar_usuarios_rol',
                'gestionar_roles',
                'gestionar_tiendas',
                'acceder_chat'
            ];
            
            echo "<h3 class='text-lg font-semibold mt-4 mb-2'>Permisos clave:</h3>";
            echo "<table class='min-w-full bg-white border border-gray-300 mb-4'>";
            echo "<thead class='bg-gray-100'><tr><th class='py-2 px-4 border'>Permiso</th><th class='py-2 px-4 border'>Existe</th><th class='py-2 px-4 border'>Usuario tiene permiso</th></tr></thead>";
            echo "<tbody>";
            
            foreach ($permisosClaves as $nombrePermiso) {
                $existePermiso = $todosPermisos->where('nombre', $nombrePermiso)->count() > 0;
                
                // Verificar si el usuario tiene el permiso
                $tienePermiso = false;
                if ($existePermiso) {
                    if (method_exists($usuario, 'tienePermiso')) {
                        try {
                            $tienePermiso = $usuario->tienePermiso($nombrePermiso);
                        } catch (\Exception $e) {
                            $tienePermiso = "Error: " . $e->getMessage();
                        }
                    } else {
                        $tienePermiso = "Método no existe";
                    }
                }
                
                $existeClass = $existePermiso ? 'text-green-600' : 'text-red-600';
                $tieneClass = $tienePermiso === true ? 'text-green-600' : 'text-red-600';
                
                echo "<tr>";
                echo "<td class='py-2 px-4 border'>{$nombrePermiso}</td>";
                echo "<td class='py-2 px-4 border {$existeClass}'>" . ($existePermiso ? '✅ Sí' : '❌ No') . "</td>";
                echo "<td class='py-2 px-4 border {$tieneClass}'>" . ($tienePermiso === true ? '✅ Sí' : ($tienePermiso === false ? '❌ No' : $tienePermiso)) . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table>";
            
            // Comprobar directamente en la base de datos
            echo "<h3 class='text-lg font-semibold mt-4 mb-2'>Permisos en base de datos (sin caché):</h3>";
            
            if (isset($roles) && $roles->count() > 0) {
                $permisosUsuario = [];
                foreach ($roles as $rol) {
                    $permisosRol = \DB::table('rol_permiso')
                        ->join('permisos', 'rol_permiso.permiso_id', '=', 'permisos.id')
                        ->where('rol_permiso.rol_id', $rol->id)
                        ->select('permisos.nombre', 'permisos.descripcion')
                        ->get();
                    
                    foreach ($permisosRol as $permiso) {
                        $permisosUsuario[$permiso->nombre] = $permiso->descripcion;
                    }
                }
                
                if (count($permisosUsuario) > 0) {
                    echo "<ul class='list-disc pl-6 mb-4'>";
                    foreach ($permisosUsuario as $nombre => $descripcion) {
                        echo "<li><strong>{$nombre}</strong> - {$descripcion}</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p class='text-red-600 mb-4'>⚠️ No se encontraron permisos asignados a los roles del usuario</p>";
                }
            } else if (isset($rol) && $rol) {
                $permisosRol = \DB::table('rol_permiso')
                    ->join('permisos', 'rol_permiso.permiso_id', '=', 'permisos.id')
                    ->where('rol_permiso.rol_id', $rol->id)
                    ->select('permisos.nombre', 'permisos.descripcion')
                    ->get();
                
                if ($permisosRol->count() > 0) {
                    echo "<ul class='list-disc pl-6 mb-4'>";
                    foreach ($permisosRol as $permiso) {
                        echo "<li><strong>{$permiso->nombre}</strong> - {$permiso->descripcion}</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p class='text-red-600 mb-4'>⚠️ No se encontraron permisos asignados al rol directo del usuario</p>";
                }
            } else {
                echo "<p class='text-red-600 mb-4'>⚠️ No se pueden verificar permisos sin roles asignados</p>";
            }
        ?>
        </div>
        
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Estructura de la base de datos</h2>
            <?php
            try {
                // Verificar estructura de tabla usuarios
                $columnasUsuarios = \DB::select("DESCRIBE usuarios");
                echo "<h3 class='text-lg font-semibold mt-4 mb-2'>Tabla 'usuarios':</h3>";
                echo "<table class='min-w-full bg-white border border-gray-300 mb-4'>";
                echo "<thead class='bg-gray-100'><tr><th class='py-2 px-4 border'>Columna</th><th class='py-2 px-4 border'>Tipo</th><th class='py-2 px-4 border'>Nulo</th><th class='py-2 px-4 border'>Clave</th></tr></thead>";
                echo "<tbody>";
                
                foreach ($columnasUsuarios as $columna) {
                    echo "<tr>";
                    echo "<td class='py-2 px-4 border'>{$columna->Field}</td>";
                    echo "<td class='py-2 px-4 border'>{$columna->Type}</td>";
                    echo "<td class='py-2 px-4 border'>{$columna->Null}</td>";
                    echo "<td class='py-2 px-4 border'>{$columna->Key}</td>";
                    echo "</tr>";
                }
                
                echo "</tbody></table>";
                
                // Verificar existencia de tablas clave
                $tablas = [
                    'roles',
                    'permisos',
                    'rol_permiso',
                    'usuario_rol'
                ];
                
                echo "<h3 class='text-lg font-semibold mt-4 mb-2'>Tablas clave:</h3>";
                echo "<ul class='list-disc pl-6 mb-4'>";
                
                foreach ($tablas as $tabla) {
                    $existeTabla = \DB::select("SHOW TABLES LIKE '{$tabla}'");
                    $existeClass = !empty($existeTabla) ? 'text-green-600' : 'text-red-600';
                    echo "<li class='{$existeClass}'><strong>{$tabla}</strong> - " . (!empty($existeTabla) ? '✅ Existe' : '❌ No existe') . "</li>";
                }
                
                echo "</ul>";
                
            } catch (\Exception $e) {
                echo "<p class='text-red-600 mb-4'>Error al verificar estructura de la base de datos: {$e->getMessage()}</p>";
            }
            ?>
        </div>
        
        <div class="flex justify-between mt-8">
            <a href="<?php echo url('admin_panel.php'); ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Ir al Panel de Administración (Bypass)
            </a>
            <a href="<?php echo url('admin'); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Volver al Menú Normal
            </a>
        </div>
    </div>
</body>
</html>
