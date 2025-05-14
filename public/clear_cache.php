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
        echo "<h2>Limpiando caché del sistema...</h2>";
        
        // Limpiar caché de rutas
        \Artisan::call('route:clear');
        echo "<p>✅ Caché de rutas limpiada</p>";
        
        // Limpiar caché de configuración
        \Artisan::call('config:clear');
        echo "<p>✅ Caché de configuración limpiada</p>";
        
        // Limpiar caché de vistas
        \Artisan::call('view:clear');
        echo "<p>✅ Caché de vistas limpiada</p>";
        
        // Limpiar caché de aplicación
        \Artisan::call('cache:clear');
        echo "<p>✅ Caché de aplicación limpiada</p>";
        
        // Limpiar caché de permisos en la base de datos
        \Illuminate\Support\Facades\Cache::flush();
        echo "<p>✅ Caché de permisos limpiada</p>";
        
        echo "<h3>¡Caché limpiada correctamente!</h3>";
        echo "<p>Ahora deberías poder acceder a todas las funciones del sistema.</p>";
        echo "<p><a href='/admin'>Ir al panel de administración</a></p>";
        
    } catch (\Exception $e) {
        echo "<h3>Error:</h3>";
        echo "<p>{$e->getMessage()}</p>";
        echo "<pre>{$e->getTraceAsString()}</pre>";
    }
} else {
    // Mostrar formulario de confirmación
    echo "<h2>Limpiar caché del sistema</h2>";
    echo "<p>Este script limpiará todas las cachés del sistema para resolver problemas de permisos.</p>";
    echo "<p><strong>Usuario actual:</strong> " . auth()->user()->email . "</p>";
    echo "<p><a href='?run=1' style='padding: 10px 15px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Limpiar caché</a></p>";
}
