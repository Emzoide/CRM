<?php

/**
 * Script para ejecutar migraciones desde el navegador
 * Este archivo debe colocarse en la carpeta public y luego acceder desde el navegador
 * IMPORTANTE: Eliminar este archivo después de usarlo por seguridad
 */

// Cargar el entorno de Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Definir la clave secreta - cámbiala por tu propia clave
$claveSecreta = 'migrar_roles_seguro_2024';

// Verificar la clave
if (!isset($_GET['clave']) || $_GET['clave'] !== $claveSecreta) {
    die('Acceso no autorizado');
}

// Definir la acción a realizar
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'migrar';

// Función para ejecutar la migración y capturar la salida
function ejecutarMigracion($tipoMigracion = 'roles') {
    try {
        ob_start();
        
        if ($tipoMigracion === 'roles') {
            // Ejecutar solo la migración específica de roles
            Illuminate\Support\Facades\Artisan::call('migrate', [
                '--path' => 'database/migrations/2024_04_30_000002_create_role_management_tables.php',
                '--force' => true,
            ]);
            $mensaje = 'Migración de gestión de roles ejecutada correctamente';
        } else {
            // Ejecutar todas las migraciones
            Illuminate\Support\Facades\Artisan::call('migrate', [
                '--force' => true,
            ]);
            $mensaje = 'Todas las migraciones pendientes ejecutadas correctamente';
        }
        
        $output = ob_get_clean();
        $resultado = Illuminate\Support\Facades\Artisan::output();
        
        return [
            'exito' => true,
            'mensaje' => $mensaje,
            'detalles' => $resultado
        ];
    } catch (Exception $e) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        return [
            'exito' => false,
            'mensaje' => 'Error al ejecutar la migración',
            'detalles' => $e->getMessage()
        ];
    }
}

// Función para corregir la tabla rol_gestiona_rol
function corregirTablaRoles() {
    try {
        // Verificar si la tabla existe
        if (Illuminate\Support\Facades\Schema::hasTable('rol_gestiona_rol')) {
            // Verificar si la columna rol_id existe
            if (Illuminate\Support\Facades\Schema::hasColumn('rol_gestiona_rol', 'rol_id')) {
                // Eliminar la tabla y recrearla
                Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS rol_gestiona_rol');
                
                // Crear la tabla correctamente
                Illuminate\Support\Facades\Schema::create('rol_gestiona_rol', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('rol_gestor_id');
                    $table->unsignedBigInteger('rol_gestionado_id');
                    $table->timestamps();

                    $table->foreign('rol_gestor_id')->references('id')->on('roles')->onDelete('cascade');
                    $table->foreign('rol_gestionado_id')->references('id')->on('roles')->onDelete('cascade');
                    
                    $table->unique(['rol_gestor_id', 'rol_gestionado_id']);
                });
                
                return [
                    'exito' => true,
                    'mensaje' => 'La tabla rol_gestiona_rol ha sido recreada correctamente',
                    'detalles' => 'La columna rol_id se ha cambiado a rol_gestor_id'
                ];
            } else {
                return [
                    'exito' => true,
                    'mensaje' => 'La tabla rol_gestiona_rol ya tiene la estructura correcta',
                    'detalles' => 'No fue necesario realizar cambios'
                ];
            }
        } else {
            // Crear la tabla desde cero
            Illuminate\Support\Facades\Schema::create('rol_gestiona_rol', function ($table) {
                $table->id();
                $table->unsignedBigInteger('rol_gestor_id');
                $table->unsignedBigInteger('rol_gestionado_id');
                $table->timestamps();

                $table->foreign('rol_gestor_id')->references('id')->on('roles')->onDelete('cascade');
                $table->foreign('rol_gestionado_id')->references('id')->on('roles')->onDelete('cascade');
                
                $table->unique(['rol_gestor_id', 'rol_gestionado_id']);
            });
            
            return [
                'exito' => true,
                'mensaje' => 'La tabla rol_gestiona_rol ha sido creada correctamente',
                'detalles' => 'Se creó la tabla con la estructura adecuada'
            ];
        }
    } catch (Exception $e) {
        return [
            'exito' => false,
            'mensaje' => 'Error al corregir la tabla',
            'detalles' => $e->getMessage()
        ];
    }
}

// Obtener la estructura actual de la tabla
function obtenerEstructuraTabla() {
    try {
        if (Illuminate\Support\Facades\Schema::hasTable('rol_gestiona_rol')) {
            $columnas = Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM rol_gestiona_rol');
            return [
                'exito' => true,
                'mensaje' => 'La tabla rol_gestiona_rol existe',
                'columnas' => $columnas
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'La tabla rol_gestiona_rol no existe'
            ];
        }
    } catch (Exception $e) {
        return [
            'exito' => false,
            'mensaje' => 'Error al obtener la estructura de la tabla',
            'detalles' => $e->getMessage()
        ];
    }
}

// Ejecutar la migración o la corrección según corresponda
$resultado = null;
$estructuraTabla = null;

if ($accion === 'corregir') {
    $estructuraTabla = obtenerEstructuraTabla();
    if (isset($_POST['ejecutar'])) {
        $resultado = corregirTablaRoles();
        $estructuraTabla = obtenerEstructuraTabla(); // Actualizar la estructura después de la corrección
    }
} else {
    // Ejecutar la migración
    $tipoMigracion = isset($_POST['tipo_migracion']) ? $_POST['tipo_migracion'] : 'roles';
    if (isset($_POST['ejecutar'])) {
        $resultado = ejecutarMigracion($tipoMigracion);
    }
}

// Verificar si se puede escribir en la base de datos
function verificarConexionDB() {
    try {
        $conexion = Illuminate\Support\Facades\DB::connection()->getPdo();
        return [
            'exito' => true,
            'mensaje' => 'Conexión a la base de datos establecida',
            'detalles' => 'Conectado a: ' . Illuminate\Support\Facades\DB::connection()->getDatabaseName()
        ];
    } catch (Exception $e) {
        return [
            'exito' => false,
            'mensaje' => 'Error al conectar con la base de datos',
            'detalles' => $e->getMessage()
        ];
    }
}

$estadoDB = verificarConexionDB();

// Obtener la lista de migraciones
function obtenerMigraciones() {
    try {
        $migraciones = Illuminate\Support\Facades\DB::table('migrations')->get();
        return [
            'exito' => true,
            'migraciones' => $migraciones
        ];
    } catch (Exception $e) {
        return [
            'exito' => false,
            'mensaje' => 'Error al obtener las migraciones',
            'detalles' => $e->getMessage()
        ];
    }
}

$listaMigraciones = obtenerMigraciones();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herramienta de Gestión de Roles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .alert {
            margin-bottom: 20px;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .badge-pill {
            border-radius: 10px;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1 class="mb-4 text-center">Herramienta de Gestión de Roles</h1>
                
                <!-- Menú de navegación -->
                <ul class="nav nav-pills mb-4 justify-content-center">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $accion === 'migrar' ? 'active' : ''; ?>" href="?clave=<?php echo urlencode($claveSecreta); ?>&accion=migrar">Ejecutar Migraciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $accion === 'corregir' ? 'active' : ''; ?>" href="?clave=<?php echo urlencode($claveSecreta); ?>&accion=corregir">Corregir Tabla de Roles</a>
                    </li>
                </ul>
                
                <div class="alert alert-warning">
                    <strong>¡Advertencia!</strong> Esta herramienta debe ser utilizada solo por administradores. 
                    Elimina este archivo después de usarlo por razones de seguridad.
                </div>

                <!-- Estatus de conexión a la DB -->
                <div class="card mb-4">
                    <div class="card-header">
                        Estado de la Base de Datos
                    </div>
                    <div class="card-body">
                        <?php if ($estadoDB['exito']): ?>
                            <div class="alert alert-success">
                                <strong>Conexión exitosa:</strong> <?php echo htmlspecialchars($estadoDB['detalles']); ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <strong>Error de conexión:</strong> <?php echo htmlspecialchars($estadoDB['detalles']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($accion === 'migrar'): ?>
                <!-- Formulario para ejecutar la migración -->
                <div class="card">
                    <div class="card-header">
                        Ejecutar Migración
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <p class="form-label fw-bold">Selecciona qué migración deseas ejecutar:</p>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="tipo_migracion" id="roles" value="roles" checked>
                                    <label class="form-check-label" for="roles">
                                        <strong>Solo la migración de gestión de roles</strong> (2024_04_30_000002_create_role_management_tables.php)
                                        <p class="text-muted small">Esta opción crea las tablas necesarias para la gestión avanzada de roles y tiendas.</p>
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="tipo_migracion" id="todas" value="todas">
                                    <label class="form-check-label" for="todas">
                                        <strong>Todas las migraciones pendientes</strong>
                                        <p class="text-muted small">Cuidado: Esto ejecutará todas las migraciones que no se hayan aplicado aún.</p>
                                    </label>
                                </div>
                            </div>
                            <button type="submit" name="ejecutar" class="btn btn-primary">Ejecutar Migración</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($accion === 'corregir'): ?>
                <!-- Estructura actual de la tabla -->
                <div class="card mb-4">
                    <div class="card-header">
                        Estructura Actual de la Tabla
                    </div>
                    <div class="card-body">
                        <?php if (isset($estructuraTabla['exito']) && $estructuraTabla['exito']): ?>
                            <div class="alert alert-info">
                                <strong><?php echo htmlspecialchars($estructuraTabla['mensaje']); ?></strong>
                            </div>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Campo</th>
                                        <th>Tipo</th>
                                        <th>Nulo</th>
                                        <th>Clave</th>
                                        <th>Predeterminado</th>
                                        <th>Extra</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estructuraTabla['columnas'] as $columna): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($columna->Field); ?></td>
                                        <td><?php echo htmlspecialchars($columna->Type); ?></td>
                                        <td><?php echo htmlspecialchars($columna->Null); ?></td>
                                        <td><?php echo htmlspecialchars($columna->Key); ?></td>
                                        <td><?php echo htmlspecialchars($columna->Default ?? 'NULL'); ?></td>
                                        <td><?php echo htmlspecialchars($columna->Extra); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php 
                            $tieneColumnaRolId = false;
                            foreach ($estructuraTabla['columnas'] as $columna) {
                                if ($columna->Field === 'rol_id') {
                                    $tieneColumnaRolId = true;
                                    break;
                                }
                            }
                            if ($tieneColumnaRolId): ?>
                                <div class="alert alert-danger">
                                    <strong>Problema detectado:</strong> La tabla tiene una columna <code>rol_id</code> en lugar de <code>rol_gestor_id</code> que es lo que espera el modelo.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success">
                                    <strong>¡Estructura correcta!</strong> La tabla tiene la estructura adecuada, no es necesario realizar cambios.
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <strong><?php echo htmlspecialchars($estructuraTabla['mensaje']); ?></strong>
                                <p>Es necesario crear la tabla.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Formulario para ejecutar la corrección -->
                <div class="card">
                    <div class="card-header">
                        Corregir Tabla rol_gestiona_rol
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <p class="form-label">Esta acción corregirá la estructura de la tabla <code>rol_gestiona_rol</code> para que use <code>rol_gestor_id</code> en lugar de <code>rol_id</code>.</p>
                                <p class="fw-bold text-danger">¡Atención! Si la tabla ya existe, se eliminará y recreará. Cualquier dato existente se perderá.</p>
                            </div>
                            <button type="submit" name="ejecutar" class="btn btn-danger">Corregir Tabla</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Resultado de la operación si existe -->
                <?php if ($resultado !== null): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        Resultado de la <?php echo $accion === 'migrar' ? 'Migración' : 'Corrección'; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($resultado['exito']): ?>
                            <div class="alert alert-success">
                                <strong><?php echo htmlspecialchars($resultado['mensaje']); ?></strong>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <strong><?php echo htmlspecialchars($resultado['mensaje']); ?></strong>
                            </div>
                        <?php endif; ?>
                        
                        <h5>Detalles:</h5>
                        <pre><?php echo htmlspecialchars($resultado['detalles']); ?></pre>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Lista de migraciones ya ejecutadas -->
                <?php if (isset($listaMigraciones['exito']) && $listaMigraciones['exito']): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        Migraciones Ejecutadas
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Migración</th>
                                    <th>Lote</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listaMigraciones['migraciones'] as $migracion): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($migracion->migration); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($migracion->batch); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
