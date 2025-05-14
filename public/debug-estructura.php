<?php

// Archivo para visualizar la estructura de tablas sin acceso a consola
require_once __DIR__ . '/../vendor/autoload.php';

// Carga el entorno de Laravel sin ejecutar la aplicación completa
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Aumentar tiempo de ejecución para evitar timeouts
set_time_limit(120);

// Tablas a analizar
$tablas = [
    'clientes',
    'cotizaciones',
    'oportunidades',
    'seguimientos'
];

// Función para obtener información detallada de columna
function getColumnType($tabla, $columna) {
    return DB::select("SHOW COLUMNS FROM {$tabla} WHERE Field = '{$columna}'")[0] ?? null;
}

// Función para obtener foreign keys
function getForeignKeys($tabla) {
    return DB::select("
        SELECT
            COLUMN_NAME as column_name, 
            REFERENCED_TABLE_NAME as foreign_table,
            REFERENCED_COLUMN_NAME as foreign_column
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_SCHEMA = DATABASE() AND
            TABLE_NAME = '{$tabla}' AND
            REFERENCED_TABLE_NAME IS NOT NULL
    ");
}

// Configuración de página
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estructura de Base de Datos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .table { margin-bottom: 30px; }
        .table th { position: sticky; top: 0; background: #f8f9fa; }
        .foreign-key { color: #0d6efd; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Estructura de Base de Datos</h1>
        
        <div class="alert alert-info">
            <h4>Instrucciones</h4>
            <p>Esta página muestra la estructura de las tablas principales del sistema. Utilice esta información para planificar la migración de los campos email y phone a la tabla clientes.</p>
        </div>
        
        <?php foreach ($tablas as $tabla): ?>
            <?php if (Schema::hasTable($tabla)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><?= $tabla ?></h3>
                    </div>
                    <div class="card-body">
                        <h4>Columnas</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Nulo</th>
                                        <th>Llave</th>
                                        <th>Default</th>
                                        <th>Extra</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $columnas = DB::select("SHOW COLUMNS FROM {$tabla}");
                                    foreach ($columnas as $col): 
                                    ?>
                                    <tr>
                                        <td><?= $col->Field ?></td>
                                        <td><?= $col->Type ?></td>
                                        <td><?= $col->Null ?></td>
                                        <td><?= $col->Key ?></td>
                                        <td><?= $col->Default ?? 'NULL' ?></td>
                                        <td><?= $col->Extra ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php 
                        $foreignKeys = getForeignKeys($tabla);
                        if (count($foreignKeys) > 0): 
                        ?>
                        <h4 class="mt-4">Relaciones</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Columna</th>
                                        <th>Referencia a</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($foreignKeys as $fk): ?>
                                    <tr>
                                        <td><?= $fk->column_name ?></td>
                                        <td><?= $fk->foreign_table ?>.<?= $fk->foreign_column ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    La tabla '<?= $tabla ?>' no existe en la base de datos.
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">Plan de Migración</h3>
            </div>
            <div class="card-body">
                <ol>
                    <li>Crear una migración para añadir los campos 'email' y 'phone' a la tabla clientes.</li>
                    <li>Migrar los datos desde la última cotización activa de cada cliente a la tabla clientes.</li>
                    <li>Actualizar los modelos para reflejar los cambios.</li>
                    <li>Actualizar cualquier formulario o vista que utilice estos campos.</li>
                </ol>
                
                <h5 class="mt-4">Código para la migración:</h5>
                <pre class="bg-light p-3 rounded"><code>
// Migración para añadir campos
php artisan make:migration add_contact_fields_to_clientes_table --table=clientes
                
// En la migración:
public function up()
{
    Schema::table('clientes', function (Blueprint $table) {
        $table->string('email')->nullable()->after('fec_nac');
        $table->string('phone')->nullable()->after('email');
    });
    
    // Migrar datos desde cotizaciones
    DB::statement('
        UPDATE clientes c
        JOIN (
            SELECT 
                o.cliente_id,
                cot.email,
                cot.phone
            FROM oportunidades o
            JOIN cotizaciones cot ON o.id = cot.oportunidad_id
            WHERE cot.estado = "active"
            GROUP BY o.cliente_id
            ORDER BY cot.emitida_en DESC
        ) data ON c.id = data.cliente_id
        SET 
            c.email = data.email,
            c.phone = data.phone
    ');
}

public function down()
{
    Schema::table('clientes', function (Blueprint $table) {
        $table->dropColumn(['email', 'phone']);
    });
}
</code></pre>
            </div>
        </div>
    </div>
</body>
</html>
