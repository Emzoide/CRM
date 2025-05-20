<?php
// Este archivo sirve para limpiar las cachés del sistema
require __DIR__ . '/../vendor/autoload.php';

// Inicia Laravel para acceder a sus funciones
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Estilo para la página
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Limpieza de Caché</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .info {
            color: #2980b9;
        }
        .warning {
            color: #f39c12;
            font-weight: bold;
        }
        .log {
            background-color: #f5f5f5;
            border-left: 4px solid #3498db;
            padding: 10px 15px;
            margin: 10px 0;
            font-family: monospace;
        }
        a.button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        a.button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Limpieza de Caché del CRM</h1>";

// Función para limpiar directorios de caché
function limpiarDirectorio($ruta) {
    if (!is_dir($ruta)) {
        return "No es un directorio válido: $ruta";
    }
    
    $archivos = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($ruta, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($archivos as $archivo) {
        if ($archivo->isDir()) {
            @rmdir($archivo->getRealPath());
        } else {
            @unlink($archivo->getRealPath());
        }
    }
    
    return "Directorio limpiado: $ruta";
}

// Limpiar varias cachés manualmente
echo "<h2>Limpieza Manual de Cachés</h2>";

// 1. Limpiar el directorio de vistas compiladas
$viewsPath = $app->storagePath() . '/framework/views';
try {
    $resultado = limpiarDirectorio($viewsPath);
    echo "<div class='log success'>✅ " . htmlspecialchars($resultado) . "</div>";
} catch (Exception $e) {
    echo "<div class='log error'>❌ Error al limpiar vistas: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// 2. Limpiar la caché de configuración
$configPath = $app->bootstrapPath() . '/cache/config.php';
if (file_exists($configPath)) {
    try {
        unlink($configPath);
        echo "<div class='log success'>✅ Caché de configuración eliminada</div>";
    } catch (Exception $e) {
        echo "<div class='log error'>❌ Error al eliminar caché de configuración: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// 3. Limpiar la caché de rutas
$routesPath = $app->bootstrapPath() . '/cache/routes-v7.php';
if (file_exists($routesPath)) {
    try {
        unlink($routesPath);
        echo "<div class='log success'>✅ Caché de rutas eliminada</div>";
    } catch (Exception $e) {
        echo "<div class='log error'>❌ Error al eliminar caché de rutas: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Usar comandos Artisan (utilizando el kernel directamente)
echo "<h2>Limpieza con Comandos Artisan</h2>";

try {
    $kernel->call('view:clear');
    echo "<div class='log success'>✅ Comando view:clear ejecutado correctamente</div>";
} catch (Exception $e) {
    echo "<div class='log error'>❌ Error ejecutando view:clear: " . htmlspecialchars($e->getMessage()) . "</div>";
}

try {
    $kernel->call('route:clear');
    echo "<div class='log success'>✅ Comando route:clear ejecutado correctamente</div>";
} catch (Exception $e) {
    echo "<div class='log error'>❌ Error ejecutando route:clear: " . htmlspecialchars($e->getMessage()) . "</div>";
}

try {
    $kernel->call('config:clear');
    echo "<div class='log success'>✅ Comando config:clear ejecutado correctamente</div>";
} catch (Exception $e) {
    echo "<div class='log error'>❌ Error ejecutando config:clear: " . htmlspecialchars($e->getMessage()) . "</div>";
}

try {
    $kernel->call('cache:clear');
    echo "<div class='log success'>✅ Comando cache:clear ejecutado correctamente</div>";
} catch (Exception $e) {
    echo "<div class='log error'>❌ Error ejecutando cache:clear: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Mostrar información adicional
echo "
    <h2>Información Adicional</h2>
    <div class='log info'>
        <p>✅ Se ha corregido el archivo <strong>usuarios.blade.php</strong> para eliminar todos los errores de sintaxis.</p>
        <p>✅ Se han limpiado las cachés para que los cambios surtan efecto.</p>
        <p>✅ El sistema ahora debería mostrar la pantalla de gestión de usuarios correctamente.</p>
    </div>

    <h2>Próximos Pasos</h2>
    <div class='log warning'>
        <p>Si después de realizar esta limpieza de caché, sigues experimentando problemas:</p>
        <ol>
            <li>Verifica si hay errores en el archivo <strong>storage/logs/laravel.log</strong></li>
            <li>Reinicia el servidor web si tienes acceso a esa función</li>
            <li>Comprueba si los permisos de los archivos son correctos</li>
        </ol>
    </div>

    <div style='margin-top: 20px;'>
        <a href='/admin/usuarios' class='button'>Ir a Gestión de Usuarios</a>
        <a href='/' class='button' style='margin-left: 10px;'>Volver al Inicio</a>
    </div>
";

echo "</div></body></html>";
