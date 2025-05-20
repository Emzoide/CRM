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

// Deshabilitar la carga automática de relaciones
app(\Illuminate\Database\Eloquent\Factory::class)->withoutRelationships();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solución Final de Permisos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6">Solución Final de Permisos</h1>
        
        <?php
        try {
            // 1. Limpiar todas las cachés del sistema
            echo "<h2 class='text-xl font-semibold mb-4'>Paso 1: Limpiando caché</h2>";
            
            // Limpiar caché de rutas
            \Artisan::call('route:clear');
            echo "<p class='mb-2 text-green-600'>✅ Caché de rutas limpiada</p>";
            
            // Limpiar caché de configuración
            \Artisan::call('config:clear');
            echo "<p class='mb-2 text-green-600'>✅ Caché de configuración limpiada</p>";
            
            // Limpiar caché de vistas
            \Artisan::call('view:clear');
            echo "<p class='mb-2 text-green-600'>✅ Caché de vistas limpiada</p>";
            
            // Limpiar caché de aplicación
            \Artisan::call('cache:clear');
            echo "<p class='mb-2 text-green-600'>✅ Caché de aplicación limpiada</p>";
            
            // Limpiar caché de permisos en la base de datos
            \Illuminate\Support\Facades\Cache::flush();
            echo "<p class='mb-2 text-green-600'>✅ Caché de permisos limpiada</p>";
            
            // 2. Verificar permisos del usuario actual
            $usuario = auth()->user();
            echo "<h2 class='text-xl font-semibold mt-6 mb-4'>Paso 2: Verificando permisos de {$usuario->first_name} {$usuario->last_name}</h2>";
            
            $permisos = [
                'gestionar_usuarios',
                'gestionar_roles',
                'gestionar_tiendas',
                'acceder_chat'
            ];
            
            echo "<table class='min-w-full bg-white border border-gray-300 mb-4'>";
            echo "<thead class='bg-gray-100'><tr><th class='py-2 px-4 border'>Permiso</th><th class='py-2 px-4 border'>Tiene acceso</th></tr></thead>";
            echo "<tbody>";
            
            // Verificar permisos directamente en la BD
            foreach ($permisos as $permiso) {
                $tienePermiso = \Illuminate\Support\Facades\DB::table('usuario_rol')
                    ->join('rol_permiso', 'usuario_rol.rol_id', '=', 'rol_permiso.rol_id')
                    ->join('permisos', 'rol_permiso.permiso_id', '=', 'permisos.id')
                    ->join('roles', 'usuario_rol.rol_id', '=', 'roles.id')
                    ->where('usuario_rol.usuario_id', $usuario->id)
                    ->where(function($query) use ($permiso) {
                        $query->where('permisos.nombre', $permiso)
                              ->orWhere('roles.is_admin', true);
                    })
                    ->exists();
                
                $tieneClass = $tienePermiso ? 'text-green-600' : 'text-red-600';
                $tieneText = $tienePermiso ? '✅ Sí' : '❌ No';
                
                echo "<tr>";
                echo "<td class='py-2 px-4 border'>{$permiso}</td>";
                echo "<td class='py-2 px-4 border {$tieneClass}'>{$tieneText}</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table>";
            
            // 3. Resumen y enlaces
            echo "<h2 class='text-xl font-semibold mt-6 mb-4'>Paso 3: Siguientes pasos</h2>";
            echo "<p class='mb-4'>La caché ha sido limpiada y los permisos verificados. Ahora deberías poder acceder a todas las secciones para las que tienes permisos.</p>";
            
            echo "<div class='flex justify-between mt-6'>";
            echo "<a href='/admin' class='bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'>Ir al panel principal</a>";
            echo "<a href='/admin_panel.php' class='bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded'>Usar panel alternativo</a>";
            echo "</div>";
            
        } catch (\Exception $e) {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>";
            echo "<h3 class='font-bold'>Error:</h3>";
            echo "<p>{$e->getMessage()}</p>";
            echo "<pre class='mt-2 text-sm overflow-auto'>{$e->getTraceAsString()}</pre>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
