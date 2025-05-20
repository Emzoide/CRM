<?php

// Script para copiar el archivo corregido al archivo original
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Definir rutas de archivos
$archivoCorregido = __DIR__ . '/../resources/views/admin/usuarios-corregido.blade.php';
$archivoOriginal = __DIR__ . '/../resources/views/admin/usuarios.blade.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Reparar Vista de Usuarios</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .container { max-width: 800px; margin: 0 auto; }
        .btn { 
            display: inline-block; 
            background: #4CAF50; 
            color: white; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 4px; 
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Reparación de la Vista de Usuarios</h1>";

if (!file_exists($archivoCorregido)) {
    echo "<p class='error'>Error: No se encontró el archivo corregido. Por favor, sube el archivo usuarios-corregido.blade.php al directorio resources/views/admin/.</p>";
} else {
    // Hacer una copia de seguridad del archivo original
    $timeStamp = date('YmdHis');
    $backupFile = __DIR__ . "/../resources/views/admin/usuarios.blade.{$timeStamp}.bak.php";
    
    if (file_exists($archivoOriginal)) {
        if (copy($archivoOriginal, $backupFile)) {
            echo "<p class='success'>✅ Se ha creado una copia de seguridad del archivo original en: usuarios.blade.{$timeStamp}.bak.php</p>";
        } else {
            echo "<p class='error'>❌ No se pudo crear una copia de seguridad del archivo original.</p>";
        }
    }
    
    // Copiar el archivo corregido al original
    if (copy($archivoCorregido, $archivoOriginal)) {
        echo "<p class='success'>✅ ¡El archivo de vista ha sido reparado exitosamente!</p>";
        
        // Limpiar caché de vistas
        try {
            $kernel->call('view:clear');
            echo "<p class='success'>✅ La caché de vistas ha sido limpiada correctamente.</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error al limpiar la caché de vistas: " . $e->getMessage() . "</p>";
        }
        
        echo "<p class='info'>La vista de administración de usuarios debería funcionar correctamente ahora.</p>";
        echo "<a href='/admin/usuarios' class='btn'>Ir a Gestión de Usuarios</a>";
    } else {
        echo "<p class='error'>❌ No se pudo reemplazar el archivo original. Verifica los permisos del sistema de archivos.</p>";
        echo "<p class='info'>Puedes intentar reemplazar manualmente el archivo:</p>";
        echo "<ol>
            <li>Descarga el archivo <a href='/resources/views/admin/usuarios-corregido.blade.php' download>usuarios-corregido.blade.php</a></li>
            <li>Sube este archivo a tu servidor en la ruta: resources/views/admin/</li>
            <li>Renómbralo a usuarios.blade.php (reemplazando el archivo existente)</li>
        </ol>";
    }
}

echo "    </div>
</body>
</html>";
