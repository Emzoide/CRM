<?php
/**
 * Archivo para refrescar la caché y forzar la recarga de vistas
 * Útil cuando se han realizado cambios pero no se ven reflejados en la interfaz
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

// Inicializar el buffer de salida
ob_start();

// Función para mostrar mensajes
function printMessage($message, $type = 'info') {
    $badge = 'bg-info';
    if ($type == 'success') $badge = 'bg-success';
    if ($type == 'error') $badge = 'bg-danger';
    if ($type == 'warning') $badge = 'bg-warning';
    
    echo "<div class='alert alert-{$type} my-2'>{$message}</div>";
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refrescar Vistas y Caché</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4">Refrescar Vistas y Caché</h1>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Proceso de limpieza</h3>
            </div>
            <div class="card-body">
                <?php
                // Si se recibió confirmación del formulario
                if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
                    try {
                        // 1. Limpiar la caché de la aplicación
                        printMessage("Limpiando caché de la aplicación...");
                        Cache::flush();
                        printMessage("Caché de la aplicación limpiada correctamente", 'success');
                        
                        // 2. Limpiar la caché de vistas (eliminar archivos del directorio de vistas compiladas)
                        printMessage("Limpiando caché de vistas...");
                        $viewsPath = storage_path('framework/views');
                        
                        if (is_dir($viewsPath)) {
                            $files = glob($viewsPath . '/*');
                            $count = 0;
                            
                            foreach ($files as $file) {
                                if (is_file($file)) {
                                    @unlink($file);
                                    $count++;
                                }
                            }
                            
                            printMessage("Se eliminaron {$count} archivos de vista compilados", 'success');
                        } else {
                            printMessage("No se encontró el directorio de vistas compiladas", 'warning');
                        }
                        
                        // 3. Verificar datos del cliente
                        printMessage("Verificando datos de clientes...");
                        $clientesConEmail = DB::table('clientes')
                            ->whereNotNull('email')
                            ->orWhereNotNull('phone')
                            ->count();
                        
                        printMessage("Se encontraron {$clientesConEmail} clientes con email o teléfono en la base de datos", 'info');
                        
                        // 4. Mostrar último cliente creado
                        $ultimoCliente = DB::table('clientes')
                            ->orderBy('id', 'desc')
                            ->first();
                        
                        if ($ultimoCliente) {
                            echo "<div class='alert alert-info'>";
                            echo "<h4>Último cliente creado:</h4>";
                            echo "<ul>";
                            echo "<li><strong>ID:</strong> {$ultimoCliente->id}</li>";
                            echo "<li><strong>Nombre:</strong> {$ultimoCliente->nombre}</li>";
                            echo "<li><strong>Email:</strong> " . ($ultimoCliente->email ?: '<em>No definido</em>') . "</li>";
                            echo "<li><strong>Teléfono:</strong> " . ($ultimoCliente->phone ?: '<em>No definido</em>') . "</li>";
                            echo "</ul>";
                            echo "</div>";
                        }
                        
                    } catch (Exception $e) {
                        printMessage("Error: " . $e->getMessage(), 'error');
                    }
                    
                    // Mostrar enlace para volver
                    echo '<div class="mt-4">
                        <a href="' . $_SERVER['PHP_SELF'] . '" class="btn btn-primary">Volver</a>
                        <a href="/clients" class="btn btn-success">Ir a Clientes</a>
                    </div>';
                    
                } else {
                    // Mostrar formulario de confirmación
                ?>
                <div class="alert alert-info">
                    <h4>¡Información!</h4>
                    <p>Este proceso realizará las siguientes acciones:</p>
                    <ol>
                        <li>Limpiar la caché de la aplicación</li>
                        <li>Eliminar los archivos de vistas compiladas</li>
                        <li>Verificar los datos de clientes en la base de datos</li>
                    </ol>
                    <p>Esto puede ayudar a resolver problemas de visualización cuando se han hecho cambios en el código pero no se reflejan en la interfaz.</p>
                </div>
                
                <form method="post" class="mt-4">
                    <input type="hidden" name="confirm" value="yes">
                    <button type="submit" class="btn btn-primary">Iniciar Limpieza</button>
                    <a href="/clients" class="btn btn-secondary">Cancelar</a>
                </form>
                <?php } ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
