<?php

// Verificar qué clase está manejando cada una de las rutas problemáticas

$controllers = [
    'admin/tiendas' => '??',
    'admin/sucursales' => '??',
    'tiendas' => '??',
    'sucursales' => '??'
];

$routeNames = [];

function getRouteInfo() {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Http\Kernel');
    $kernel->handle(Illuminate\Http\Request::capture());
    
    $routes = app('router')->getRoutes();
    $info = [];

    foreach ($routes as $route) {
        $uri = $route->uri();
        $name = $route->getName() ?? 'unnamed';
        $action = $route->getActionName();
        
        $info[$uri] = [
            'name' => $name,
            'action' => $action,
            'methods' => implode('|', $route->methods())
        ];
    }
    
    return $info;
}

try {
    $routeInfo = getRouteInfo();
    
    echo "<h1>Diagnóstico de Rutas</h1>";
    echo "<pre>";
    
    // Mostrar información sobre las rutas que nos interesan
    foreach ($controllers as $uri => $placeholder) {
        if (isset($routeInfo[$uri])) {
            echo "URI: {$uri}\n";
            echo "  Controlador: {$routeInfo[$uri]['action']}\n";
            echo "  Nombre de ruta: {$routeInfo[$uri]['name']}\n";
            echo "  Métodos: {$routeInfo[$uri]['methods']}\n";
            echo "\n";
            
            $controllers[$uri] = $routeInfo[$uri]['action'];
            $routeNames[$routeInfo[$uri]['name']] = $uri;
        } else {
            echo "URI: {$uri}\n";
            echo "  No encontrada en las rutas registradas\n\n";
        }
    }
    
    // Buscar rutas con nombres relacionados
    echo "\n<h2>Rutas con nombres relacionados a tiendas o sucursales</h2>\n";
    foreach ($routeInfo as $uri => $info) {
        if (strpos($info['name'], 'tienda') !== false || strpos($info['name'], 'sucursal') !== false) {
            echo "Nombre: {$info['name']}\n";
            echo "  URI: {$uri}\n";
            echo "  Controlador: {$info['action']}\n";
            echo "  Métodos: {$info['methods']}\n\n";
        }
    }
    
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
