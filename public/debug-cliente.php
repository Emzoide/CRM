<?php
/**
 * Script para depurar y mostrar datos de clientes
 * Útil para ver qué está pasando con los campos email y phone
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Inicializar el buffer de salida
ob_start();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depuración de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4">Depuración de Clientes</h1>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Datos de Clientes</h3>
            </div>
            <div class="card-body">
                <?php
                // Consulta a la tabla de clientes
                $clientes = DB::table('clientes')->get();
                
                if ($clientes->isEmpty()) {
                    echo "<div class='alert alert-warning'>No se encontraron clientes en la base de datos.</div>";
                } else {
                    echo "<h4>Total de clientes: " . count($clientes) . "</h4>";
                    
                    // Mostrar una tabla con todos los clientes y sus atributos
                    echo "<div class='table-responsive mt-4'>";
                    echo "<table class='table table-striped'>";
                    echo "<thead><tr>
                        <th>ID</th>
                        <th>DNI/RUC</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Columnas</th>
                    </tr></thead>";
                    echo "<tbody>";
                    
                    foreach ($clientes as $cliente) {
                        echo "<tr>";
                        echo "<td>{$cliente->id}</td>";
                        echo "<td>{$cliente->dni_ruc}</td>";
                        echo "<td>{$cliente->nombre}</td>";
                        echo "<td>" . (isset($cliente->email) ? $cliente->email : '<span class="text-danger">No definido o NULL</span>') . "</td>";
                        echo "<td>" . (isset($cliente->phone) ? $cliente->phone : '<span class="text-danger">No definido o NULL</span>') . "</td>";
                        
                        // Mostrar todas las columnas disponibles
                        echo "<td><small>";
                        foreach ((array)$cliente as $campo => $valor) {
                            $campoLimpio = preg_replace('/.*\x00(.*)/', '$1', $campo);
                            echo "<strong>{$campoLimpio}</strong>: " . (is_null($valor) ? "NULL" : (strlen($valor) > 50 ? substr($valor, 0, 50) . "..." : $valor)) . "<br>";
                        }
                        echo "</small></td>";
                        
                        echo "</tr>";
                    }
                    
                    echo "</tbody></table>";
                    echo "</div>";
                }
                
                // Ahora vamos a verificar la estructura de la tabla
                echo "<h4 class='mt-5'>Estructura de la tabla clientes</h4>";
                
                $columnas = DB::select("SHOW COLUMNS FROM clientes");
                
                echo "<div class='table-responsive mt-3'>";
                echo "<table class='table table-striped table-sm'>";
                echo "<thead><tr>
                    <th>Campo</th>
                    <th>Tipo</th>
                    <th>Nulo</th>
                    <th>Llave</th>
                    <th>Default</th>
                </tr></thead>";
                echo "<tbody>";
                
                foreach ($columnas as $col) {
                    echo "<tr>";
                    echo "<td>{$col->Field}</td>";
                    echo "<td>{$col->Type}</td>";
                    echo "<td>{$col->Null}</td>";
                    echo "<td>{$col->Key}</td>";
                    echo "<td>" . (is_null($col->Default) ? "NULL" : $col->Default) . "</td>";
                    echo "</tr>";
                }
                
                echo "</tbody></table>";
                echo "</div>";
                
                // Verificar si las columnas email y phone existen
                $tieneEmail = false;
                $tienePhone = false;
                
                foreach ($columnas as $col) {
                    if ($col->Field === 'email') $tieneEmail = true;
                    if ($col->Field === 'phone') $tienePhone = true;
                }
                
                echo "<div class='alert " . ($tieneEmail && $tienePhone ? "alert-success" : "alert-danger") . " mt-4'>";
                echo "<strong>Verificación de campos:</strong><br>";
                echo "- Campo 'email': " . ($tieneEmail ? "✅ Existe" : "❌ No existe") . "<br>";
                echo "- Campo 'phone': " . ($tienePhone ? "✅ Existe" : "❌ No existe");
                echo "</div>";
                
                // Agregar solución automática
                if (!$tieneEmail || !$tienePhone) {
                    echo "<div class='alert alert-warning mt-4'>";
                    echo "<h5>Solución:</h5>";
                    echo "<p>Se requiere agregar las columnas faltantes a la tabla de clientes. Ejecuta el siguiente SQL en phpMyAdmin:</p>";
                    
                    $sql = "ALTER TABLE clientes ";
                    if (!$tieneEmail) $sql .= "ADD COLUMN email VARCHAR(255) NULL, ";
                    if (!$tienePhone) $sql .= "ADD COLUMN phone VARCHAR(100) NULL ";
                    $sql = rtrim($sql, ", ");
                    
                    echo "<pre>{$sql};</pre>";
                    echo "</div>";
                } else {
                    // Sugerir solución para actualizar la vista
                    echo "<div class='alert alert-info mt-4'>";
                    echo "<h5>Verificación de la vista:</h5>";
                    echo "<p>Las columnas existen en la base de datos, pero podrían no estar mostrándose correctamente en la vista 'index.blade.php'. Verifica los siguientes aspectos:</p>";
                    echo "<ol>";
                    echo "<li>Asegúrate de que los clientes se estén cargando correctamente en el controlador.</li>";
                    echo "<li>Verifica que los campos estén incluidos en la propiedad \$fillable del modelo Cliente.</li>";
                    echo "<li>Comprueba que no haya getters personalizados que puedan estar sobreescribiendo los valores.</li>";
                    echo "</ol>";
                    
                    // Sugerir código para forzar la actualización
                    echo "<h5>Solución rápida:</h5>";
                    echo "<p>Puedes intentar actualizar manualmente los valores que están en la base de datos:</p>";
                    echo "<pre>UPDATE clientes SET email = email, phone = phone WHERE id > 0;</pre>";
                    echo "</div>";
                }
                ?>
                
                <div class="mt-4">
                    <a href="/clients" class="btn btn-primary">Volver a Clientes</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
