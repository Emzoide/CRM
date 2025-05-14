<?php
/**
 * Script para corregir errores de directivas Blade en usuarios.blade.php
 * Este archivo debe eliminarse después de su uso
 */

try {
    // Ruta al archivo
    $rutaArchivo = __DIR__ . '/resources/views/admin/usuarios.blade.php';
    
    // Hacer una copia de seguridad
    if (file_exists($rutaArchivo)) {
        $backup = $rutaArchivo . '.backup.' . date('Y-m-d-H-i-s');
        copy($rutaArchivo, $backup);
        echo "Se ha creado una copia de seguridad en: " . basename($backup) . "<br>";
    }
    
    // Leer el contenido del archivo
    $contenido = file_get_contents($rutaArchivo);
    
    // Verificar y corregir errores comunes

    // 1. Asegurarse de que @section y @endsection están correctamente posicionados
    if (strpos($contenido, '@push(\'scripts\')') > strpos($contenido, '@endsection')) {
        // Mover el push('scripts') antes del endsection
        $contenido = str_replace('@endsection', '', $contenido);
        $contenido .= "\n@endsection"; // Añadir el @endsection al final
    }
    
    // 2. Verificar si faltan @endif para cerrar los @if
    $ifCount = substr_count($contenido, '@if');
    $endifCount = substr_count($contenido, '@endif');
    
    if ($ifCount > $endifCount) {
        $diff = $ifCount - $endifCount;
        echo "Se encontraron $diff directivas @if sin cerrar. Agregando @endif faltantes.<br>";
        
        // Añadir los @endif faltantes al final antes de @endsection
        $endSection = "@endsection";
        $replacement = "";
        
        for ($i = 0; $i < $diff; $i++) {
            $replacement .= "@endif\n";
        }
        
        $replacement .= $endSection;
        $contenido = str_replace($endSection, $replacement, $contenido);
    }
    
    // 3. Arreglar problemas comunes en directivas de control
    // Verificar @foreach sin cerrar
    $foreachCount = substr_count($contenido, '@foreach');
    $endforeachCount = substr_count($contenido, '@endforeach');
    
    if ($foreachCount > $endforeachCount) {
        $diff = $foreachCount - $endforeachCount;
        echo "Se encontraron $diff directivas @foreach sin cerrar. Agregando @endforeach faltantes.<br>";
        
        // Añadir los @endforeach faltantes antes de @endsection
        $endSection = "@endsection";
        $replacement = "";
        
        for ($i = 0; $i < $diff; $i++) {
            $replacement .= "@endforeach\n";
        }
        
        $replacement .= $endSection;
        $contenido = str_replace($endSection, $replacement, $contenido);
    }
    
    // Guardar los cambios
    file_put_contents($rutaArchivo, $contenido);
    
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
    echo "<h3>✅ Corrección Exitosa</h3>";
    echo "<p>El archivo <strong>usuarios.blade.php</strong> ha sido corregido.</p>";
    echo "<p>Se corrigieron:</p>";
    echo "<ul>";
    if ($ifCount > $endifCount) echo "<li>$diff directivas @if faltantes</li>";
    if ($foreachCount > $endforeachCount) echo "<li>$diff directivas @foreach faltantes</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<a href='/admin/usuarios' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Intentar acceder a la página de usuarios</a>";
    
    echo "<p style='margin-top: 20px;'><strong>IMPORTANTE:</strong> Por seguridad, elimina este archivo después de usarlo.</p>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
