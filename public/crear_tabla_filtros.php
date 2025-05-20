<?php
/**
 * Script para crear la tabla de filtros (ejecutar desde el navegador)
 */

// Incluir el autoloader de Composer
require __DIR__ . '/vendor/autoload.php';

// Cargar variables de entorno desde .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configurar la conexión a la base de datos
$host = env('DB_HOST', 'localhost');
$database = env('DB_DATABASE', 'forge');
$username = env('DB_USERNAME', 'forge');
$password = env('DB_PASSWORD', '');
$charset = 'utf8mb4';
$collation = 'utf8mb4_unicode_ci';

try {
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
        
        // Crear filtros predeterminados
        $filtroVendedor = [
            'criterios' => [
                ['campo' => 'asignado_a', 'operador' => '=', 'valor' => 'CURRENT_USER']
            ],
            'ordenamiento' => [
                ['campo' => 'ultimo_seguimiento', 'direccion' => 'desc']
            ]
        ];
        
        $filtroSinSeguimiento = [
            'criterios' => [
                ['campo' => 'ultimo_seguimiento', 'operador' => '>', 'valor' => '5d', 'tipo' => 'tiempo']
            ],
            'ordenamiento' => [
                ['campo' => 'created_at', 'direccion' => 'desc']
            ]
        ];
        
        $filtroCotizacionActiva = [
            'criterios' => [
                ['campo' => 'cotizacion_activa', 'operador' => '=', 'valor' => true]
            ],
            'ordenamiento' => [
                ['campo' => 'monto_cotizacion', 'direccion' => 'desc']
            ]
        ];
        
        // Buscar el ID del rol 'vendedor' o similar
        $stmtRol = $pdo->query("SELECT id FROM roles WHERE nombre LIKE '%vendedor%' OR nombre LIKE '%asesor%' LIMIT 1");
        $rolId = $stmtRol->fetch()['id'] ?? 2;
        
        // Buscar el ID del rol 'admin'
        $stmtAdmin = $pdo->query("SELECT id FROM roles WHERE nombre LIKE '%admin%' OR is_admin = 1 LIMIT 1");
        $adminId = $stmtAdmin->fetch()['id'] ?? 1;
        
        // Insertar filtros predeterminados
        $stmt = $pdo->prepare("
            INSERT INTO `filtros_configuracion` 
            (`nombre`, `rol_id`, `usuario_id`, `es_predeterminado`, `configuracion`, `orden`, `created_at`, `updated_at`)
            VALUES (?, ?, NULL, ?, ?, ?, NOW(), NOW())
        ");
        
        // Filtro para vendedores
        $stmt->execute([
            'Mis clientes', 
            $rolId, 
            true, 
            json_encode($filtroVendedor),
            1
        ]);
        
        // Filtro para administradores (clientes sin seguimiento)
        $stmt->execute([
            'Sin seguimiento reciente', 
            $adminId, 
            true, 
            json_encode($filtroSinSeguimiento),
            1
        ]);
        
        // Filtro para administradores (cotizaciones activas)
        $stmt->execute([
            'Con cotización activa', 
            $adminId, 
            false, 
            json_encode($filtroCotizacionActiva),
            2
        ]);
        
        echo "<h3>¡Tabla filtros_configuracion creada con éxito!</h3>";
        echo "<p>Se han creado filtros predeterminados para roles de vendedor y administrador.</p>";
    }
} catch (PDOException $e) {
    echo "<h3>Error al crear la tabla:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

/**
 * Helper para obtener variables de entorno
 */
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}
?>
