<?php
// Script para arreglar errores de IDs duplicados en los modales
// Ejecutar este script desde el navegador para limpiar la caché de Blade

// Configuración
$viewsPath = __DIR__ . '/resources/views/admin/usuarios.blade.php';
$cachePath = __DIR__ . '/storage/framework/views';

// 1. Limpiar la caché de vistas
$files = glob($cachePath . '/*.php');
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

// 2. Leer el archivo de vista
$content = file_get_contents($viewsPath);

// Buscar y reemplazar IDs potencialmente duplicados
$newContent = $content;

// Corregir IDs en el formulario de creación
$newContent = str_replace(
    'id="roles{{ $usuario->id }}" name="roles[]"',
    'id="rolesCreate" name="roles[]"',
    $newContent
);

$newContent = str_replace(
    'for="roles{{ $usuario->id }}"',
    'for="rolesCreate"',
    $newContent
);

$newContent = str_replace(
    'id="tienda_id{{ $usuario->id }}" name="tienda_id"',
    'id="tienda_idCreate" name="tienda_id"',
    $newContent
);

$newContent = str_replace(
    'for="tienda_id{{ $usuario->id }}"',
    'for="tienda_idCreate"',
    $newContent
);

// 3. Escribir el archivo corregido
file_put_contents($viewsPath, $newContent);

echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;'>";
echo "<h2 style='color: #0066cc;'>Corrección de IDs Duplicados</h2>";

if ($content === $newContent) {
    echo "<p style='padding: 10px; background-color: #fff3cd; border-left: 5px solid #ffc107;'>No se encontraron problemas de IDs duplicados para corregir.</p>";
} else {
    echo "<p style='padding: 10px; background-color: #d4edda; border-left: 5px solid #28a745;'>Se han corregido posibles IDs duplicados en la vista.</p>";
}

echo "<h3>Pasos a seguir:</h3>";
echo "<ol>";
echo "<li>Se ha limpiado la caché de vistas.</li>";
echo "<li>Se han actualizado los IDs en el archivo usuarios.blade.php.</li>";
echo "<li><strong>Importante:</strong> Regresa a la página de usuarios y recarga. Los modales deberían funcionar correctamente ahora.</li>";
echo "</ol>";

echo "<p><strong>Nota:</strong> Si sigues viendo errores en la consola, por favor revisa la consola y toma nota de exactamente qué IDs están causando problemas.</p>";
echo "</div>";
?>
