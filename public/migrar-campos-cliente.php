<?php
/**
 * Script para migrar campos de email y phone a la tabla clientes
 * Como no tenemos acceso a las migraciones de Laravel a través de la consola,
 * este script realiza las operaciones directamente en la base de datos
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Aumentar tiempo de ejecución para evitar timeouts
set_time_limit(300);

// Inicializar el buffer de salida
ob_start();

// Función para mostrar mensajes
function printMessage($message, $type = 'info') {
    $badge = 'bg-info';
    if ($type == 'success') $badge = 'bg-success';
    if ($type == 'error') $badge = 'bg-danger';
    if ($type == 'warning') $badge = 'bg-warning';
    
    echo "<div class='alert alert-{$type} my-2'>{$message}</div>";
    // No usamos ob_flush ya que puede causar errores si el buffer no está inicializado
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
    <title>Migración de Campos a Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4">Migración de Campos Email y Phone a Clientes</h1>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Proceso de migración</h3>
            </div>
            <div class="card-body">
                <?php
                // Si se recibió confirmación del formulario
                if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
                    try {
                        // PASO 1: Verificar si ya existen las columnas
                        $columnaEmailExiste = Schema::hasColumn('clientes', 'email');
                        $columnaPhoneExiste = Schema::hasColumn('clientes', 'phone');
                        
                        // Verificar si ambas columnas existen
                        if ($columnaEmailExiste && $columnaPhoneExiste) {
                            printMessage("Las columnas 'email' y 'phone' ya existen en la tabla 'clientes', continuando con la migración de datos.", 'info');
                        } else {
                            // Mostrar mensaje de error si falta alguna columna
                            if (!$columnaEmailExiste) {
                                printMessage("ATENCIÓN: La columna 'email' no existe en la tabla 'clientes'", 'error');
                            }
                            
                            if (!$columnaPhoneExiste) {
                                printMessage("ATENCIÓN: La columna 'phone' no existe en la tabla 'clientes'", 'error');
                            }
                            
                            printMessage("Es necesario crear primero las columnas faltantes. Por favor, ejecuta el siguiente SQL en phpMyAdmin:", 'warning');
                            
                            $sqlColumnas = "ALTER TABLE clientes ";
                            if (!$columnaEmailExiste) {
                                $sqlColumnas .= "ADD COLUMN email VARCHAR(255) NULL, ";
                            }
                            if (!$columnaPhoneExiste) {
                                $sqlColumnas .= "ADD COLUMN phone VARCHAR(100) NULL ";
                            }
                            $sqlColumnas = rtrim($sqlColumnas, ', ');
                            
                            echo "<div class='alert alert-info'><pre>{$sqlColumnas};</pre></div>";
                            echo "<p class='mt-4'>Ejecuta este SQL y luego vuelve a cargar esta página para continuar con la migración.</p>";
                            echo "<a href='" . $_SERVER['PHP_SELF'] . "' class='btn btn-primary'>Recargar después de ejecutar SQL</a>";
                            exit;
                        }
                        
                        // Obtener el modo de migración (sobreescribir o no)
                        $modo = isset($_POST['modo']) ? $_POST['modo'] : 'no_sobreescribir';
                        
                        // PASO 3: Migrar datos desde cotizaciones
                        printMessage("Migrando datos desde la tabla 'cotizaciones' (Modo: " . ($modo == 'sobreescribir' ? 'Sobreescribir existentes' : 'Preservar existentes') . ")...");
                        
                        // Obtener todos los clientes con sus cotizaciones más recientes
                        $clientes = DB::table('clientes')->get();
                        $actualizados = 0;
                        $sinCotizacion = 0;
                        $yaConDatos = 0;
                        $sinDatos = 0;
                        
                        printMessage("Procesando " . count($clientes) . " clientes...");
                        
                        // Array para almacenar detalles
                        $detalles = [];
                        
                        foreach ($clientes as $cliente) {
                            // Buscar la cotización activa más reciente para este cliente
                            $cotizacion = DB::table('cotizaciones')
                                ->join('oportunidades', 'cotizaciones.oportunidad_id', '=', 'oportunidades.id')
                                ->where('oportunidades.cliente_id', '=', $cliente->id)
                                ->orderBy('cotizaciones.emitida_en', 'desc')
                                ->select('cotizaciones.*')
                                ->first();
                            
                            if (!$cotizacion) {
                                // No se encontró cotización para este cliente
                                $sinCotizacion++;
                                continue;
                            }
                            
                            // Verificar si los datos del cliente y la cotización
                            $clienteTieneEmail = !empty($cliente->email);
                            $clienteTienePhone = !empty($cliente->phone);
                            $cotizacionTieneEmail = !empty($cotizacion->email);
                            $cotizacionTienePhone = !empty($cotizacion->phone);
                            
                            if (!$cotizacionTieneEmail && !$cotizacionTienePhone) {
                                $sinDatos++;
                                continue;
                            }
                            
                            // Actualizar cliente según el modo
                            $updates = [];
                            
                            if ($modo == 'sobreescribir') {
                                // Sobreescribir todos los datos si la cotización tiene información
                                if ($cotizacionTieneEmail) {
                                    $updates['email'] = $cotizacion->email;
                                }
                                
                                if ($cotizacionTienePhone) {
                                    $updates['phone'] = $cotizacion->phone;
                                }
                            } else {
                                // Solo actualizar campos vacíos del cliente
                                if (!$clienteTieneEmail && $cotizacionTieneEmail) {
                                    $updates['email'] = $cotizacion->email;
                                } elseif ($clienteTieneEmail) {
                                    $yaConDatos++;
                                }
                                
                                if (!$clienteTienePhone && $cotizacionTienePhone) {
                                    $updates['phone'] = $cotizacion->phone;
                                } elseif ($clienteTienePhone) {
                                    $yaConDatos++;
                                }
                            }
                            
                            if (!empty($updates)) {
                                DB::table('clientes')
                                    ->where('id', $cliente->id)
                                    ->update($updates);
                                
                                $actualizados++;
                                
                                // Guardar detalle de la actualización
                                $detalles[] = [
                                    'id' => $cliente->id,
                                    'nombre' => $cliente->nombre,
                                    'email_antiguo' => $cliente->email,
                                    'email_nuevo' => $updates['email'] ?? $cliente->email,
                                    'phone_antiguo' => $cliente->phone,
                                    'phone_nuevo' => $updates['phone'] ?? $cliente->phone
                                ];
                            }
                        }
                        
                        // Mostrar resumen
                        printMessage("Resumen de la migración:", 'success');
                        printMessage("- Clientes actualizados: {$actualizados}", $actualizados > 0 ? 'success' : 'info');
                        printMessage("- Clientes ya con datos: {$yaConDatos}", 'info');
                        printMessage("- Clientes sin cotizaciones: {$sinCotizacion}", $sinCotizacion > 0 ? 'warning' : 'info');
                        printMessage("- Cotizaciones sin datos: {$sinDatos}", $sinDatos > 0 ? 'warning' : 'info');
                        
                        // Mostrar detalles de actualizaciones si hay alguna
                        if (count($detalles) > 0) {
                            echo '<div class="accordion mt-4" id="clientesActualizados">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingDetalles">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDetalles" aria-expanded="false" aria-controls="collapseDetalles">
                                                Ver detalles de los ' . count($detalles) . ' clientes actualizados
                                            </button>
                                        </h2>
                                        <div id="collapseDetalles" class="accordion-collapse collapse" aria-labelledby="headingDetalles" data-bs-parent="#clientesActualizados">
                                            <div class="accordion-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Nombre</th>
                                                                <th>Email Anterior</th>
                                                                <th>Email Nuevo</th>
                                                                <th>Teléfono Anterior</th>
                                                                <th>Teléfono Nuevo</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>';
                                                        
                            foreach ($detalles as $d) {
                                echo '<tr>
                                        <td>' . $d['id'] . '</td>
                                        <td>' . $d['nombre'] . '</td>
                                        <td>' . ($d['email_antiguo'] ?: '<em>Vacío</em>') . '</td>
                                        <td>' . ($d['email_nuevo'] ?: '<em>Vacío</em>') . '</td>
                                        <td>' . ($d['phone_antiguo'] ?: '<em>Vacío</em>') . '</td>
                                        <td>' . ($d['phone_nuevo'] ?: '<em>Vacío</em>') . '</td>
                                      </tr>';
                            }
                                                        
                            echo '                </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
                        }
                        
                        // PASO 4: Actualizar el modelo Cliente.php
                        $modeloCliente = realpath(__DIR__ . '/../app/Models/Cliente.php');
                        
                        if (file_exists($modeloCliente)) {
                            $contenidoActual = file_get_contents($modeloCliente);
                            
                            // Verificar si ya están los campos en fillable
                            if (strpos($contenidoActual, "'email'") === false || strpos($contenidoActual, "'phone'") === false) {
                                // Patrón para buscar la línea de fillable
                                $pattern = "/protected\s+\\\$fillable\s*=\s*\[(.*?)\];/s";
                                
                                if (preg_match($pattern, $contenidoActual, $matches)) {
                                    $fillable = $matches[1];
                                    
                                    // Eliminar los getters antiguos si existen
                                    $contenidoActualizado = preg_replace(
                                        "/public function getEmailAttribute\(\).*?}\n/s",
                                        "",
                                        $contenidoActual
                                    );
                                    
                                    $contenidoActualizado = preg_replace(
                                        "/public function getPhoneAttribute\(\).*?}\n/s",
                                        "",
                                        $contenidoActualizado ?? $contenidoActual
                                    );
                                    
                                    // Agregar los campos a fillable
                                    $nuevoFillable = str_replace(
                                        "]",
                                        ", 'email', 'phone']",
                                        $matches[0]
                                    );
                                    
                                    $contenidoActualizado = str_replace(
                                        $matches[0],
                                        $nuevoFillable,
                                        $contenidoActualizado ?? $contenidoActual
                                    );
                                    
                                    // Guardar los cambios
                                    if ($contenidoActualizado !== $contenidoActual) {
                                        file_put_contents($modeloCliente, $contenidoActualizado);
                                        printMessage("Modelo Cliente.php actualizado correctamente", 'success');
                                    } else {
                                        printMessage("No fue necesario actualizar el modelo Cliente.php", 'info');
                                    }
                                } else {
                                    printMessage("No se pudo encontrar el array fillable en Cliente.php", 'error');
                                }
                            } else {
                                printMessage("El modelo Cliente.php ya contiene los campos en fillable", 'info');
                            }
                        } else {
                            printMessage("No se encontró el archivo del modelo Cliente.php", 'error');
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
                <div class="alert alert-warning">
                    <h4>¡Atención!</h4>
                    <p>Este script realizará los siguientes cambios:</p>
                    <ol>
                        <li>Agregar los campos 'email' y 'phone' a la tabla 'clientes' si no existen</li>
                        <li>Migrar los datos desde la cotización activa más reciente de cada cliente</li>
                        <li>Actualizar el modelo Cliente.php para incluir estos campos</li>
                    </ol>
                    <p><strong>Recomendación:</strong> Realiza una copia de seguridad de la base de datos antes de continuar.</p>
                </div>
                
                <form method="post" class="mt-4">
                    <input type="hidden" name="confirm" value="yes">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="backupCheck" required>
                        <label class="form-check-label" for="backupCheck">
                            He realizado una copia de seguridad de la base de datos
                        </label>
                    </div>
                    <button type="submit" class="btn btn-danger">Iniciar Migración</button>
                    <a href="/clients" class="btn btn-secondary">Cancelar</a>
                </form>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
