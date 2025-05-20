<?php
/**
 * Script para crear la tabla de filtros (versión simplificada)
 */

// Activar mostrado de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para obtener la configuración desde el archivo .env
function getEnvConfig($path) {
    if (!file_exists($path)) {
        die("El archivo .env no existe en: " . $path);
    }
    
    $config = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parsear variables de entorno
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Eliminar comillas si existen
            if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            }
            
            $config[$name] = $value;
        }
    }
    
    return $config;
}

try {
    // Obtener configuración de .env
    $envPath = __DIR__ . '/../.env';
    $config = getEnvConfig($envPath);
    
    // Extraer credenciales de BD
    $host = $config['DB_HOST'] ?? 'localhost';
    $database = $config['DB_DATABASE'] ?? '';
    $username = $config['DB_USERNAME'] ?? '';
    $password = $config['DB_PASSWORD'] ?? '';
    $charset = 'utf8mb4';
    $collation = 'utf8mb4_unicode_ci';
    
    if (empty($database) || empty($username)) {
        die("Configuración de base de datos incompleta en .env");
    }
    
    echo "<h2>Configuración encontrada</h2>";
    echo "<p>Host: $host</p>";
    echo "<p>Base de datos: $database</p>";
    echo "<p>Usuario: $username</p>";
    
    // Establecer la conexión
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset={$charset}", 
        $username, 
        $password, 
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "<h2>Conexión establecida correctamente</h2>";
    
    // Comprobar si la tabla ya existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'filtros_configuracion'");
    if ($stmt->rowCount() > 0) {
        echo "<h3>La tabla filtros_configuracion ya existe</h3>";
    } else {
        // Crear la tabla filtros_configuracion
        $sql = "CREATE TABLE `filtros_configuracion` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `nombre` varchar(100) COLLATE {$collation} NOT NULL,
            `rol_id` bigint(20) UNSIGNED DEFAULT NULL,
            `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
            `es_predeterminado` tinyint(1) NOT NULL DEFAULT '0',
            `configuracion` json DEFAULT NULL,
            `orden` int(11) NOT NULL DEFAULT '0',
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `filtros_rol_id_index` (`rol_id`),
            KEY `filtros_usuario_id_index` (`usuario_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation};";
        
        $pdo->exec($sql);
        echo "<h3>¡Tabla filtros_configuracion creada con éxito!</h3>";
        
        // Buscar el ID del rol 'vendedor' o similar
        echo "<h3>Creando filtros predeterminados...</h3>";
        
        $stmtRol = $pdo->query("SELECT id FROM roles WHERE nombre LIKE '%vendedor%' OR nombre LIKE '%asesor%' LIMIT 1");
        $rolId = $stmtRol->fetch()['id'] ?? null;
        
        if ($rolId) {
            echo "<p>Rol vendedor encontrado: ID $rolId</p>";
        } else {
            echo "<p>No se encontró rol de vendedor. Usando ID 2 por defecto.</p>";
            $rolId = 2;
        }
        
        // Buscar el ID del rol 'admin'
        $stmtAdmin = $pdo->query("SELECT id FROM roles WHERE nombre LIKE '%admin%' OR is_admin = 1 LIMIT 1");
        $adminId = $stmtAdmin->fetch()['id'] ?? null;
        
        if ($adminId) {
            echo "<p>Rol admin encontrado: ID $adminId</p>";
        } else {
            echo "<p>No se encontró rol de admin. Usando ID 1 por defecto.</p>";
            $adminId = 1;
        }
        
        // Crear filtros predeterminados
        // 1. Filtro para vendedores: "Mis clientes"
        $filtroVendedor = [
            'criterios' => [
                ['campo' => 'asignado_a', 'operador' => '=', 'valor' => 'CURRENT_USER']
            ],
            'ordenamiento' => [
                ['campo' => 'ultimo_seguimiento', 'direccion' => 'desc']
            ]
        ];
        
        // 2. Filtro para administradores: "Sin seguimiento reciente"
        $filtroSinSeguimiento = [
            'criterios' => [
                ['campo' => 'ultimo_seguimiento', 'operador' => '>', 'valor' => '5d', 'tipo' => 'tiempo']
            ],
            'ordenamiento' => [
                ['campo' => 'created_at', 'direccion' => 'desc']
            ]
        ];
        
        // 3. Filtro para todos: "Con cotización activa"
        $filtroCotizacionActiva = [
            'criterios' => [
                ['campo' => 'cotizacion_activa', 'operador' => '=', 'valor' => true]
            ],
            'ordenamiento' => [
                ['campo' => 'monto_cotizacion', 'direccion' => 'desc']
            ]
        ];
        
        // Insertar filtros
        $now = date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare("
            INSERT INTO `filtros_configuracion` 
            (`nombre`, `rol_id`, `usuario_id`, `es_predeterminado`, `configuracion`, `orden`, `created_at`, `updated_at`)
            VALUES (?, ?, NULL, ?, ?, ?, ?, ?)
        ");
        
        // Filtro para vendedores
        $stmt->execute([
            'Mis clientes', 
            $rolId, 
            1, // es_predeterminado
            json_encode($filtroVendedor),
            1,
            $now,
            $now
        ]);
        echo "<p>Filtro 'Mis clientes' creado para rol vendedor.</p>";
        
        // Filtro para administradores (sin seguimiento)
        $stmt->execute([
            'Sin seguimiento reciente', 
            $adminId, 
            1, // es_predeterminado
            json_encode($filtroSinSeguimiento),
            1,
            $now,
            $now
        ]);
        echo "<p>Filtro 'Sin seguimiento reciente' creado para rol admin.</p>";
        
        // Filtro para administradores (cotizaciones activas)
        $stmt->execute([
            'Con cotización activa', 
            $adminId, 
            0, // no es_predeterminado
            json_encode($filtroCotizacionActiva),
            2,
            $now,
            $now
        ]);
        echo "<p>Filtro 'Con cotización activa' creado para rol admin.</p>";
        
        echo "<h3>¡Filtros predeterminados creados con éxito!</h3>";
    }
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>Ha ocurrido un error:</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<p>En archivo: " . $e->getFile() . " línea " . $e->getLine() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 20px;
    padding: 20px;
    background: #f5f5f5;
}

h2, h3 {
    color: #333;
}

h2 {
    border-bottom: 2px solid #ddd;
    padding-bottom: 10px;
    margin-top: 30px;
}

pre {
    background: #f8d7da;
    padding: 15px;
    border-radius: 5px;
    overflow: auto;
    color: #721c24;
}

p {
    margin: 10px 0;
}
</style>

<h1>Instalador de Sistema de Filtros</h1>
<p>Este script crea la tabla necesaria para el sistema de filtros dinámicos.</p>
<p>Al terminar, puede <a href="/clients">volver a la página de clientes</a> para comenzar a usar los filtros.</p>
