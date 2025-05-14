<?php
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Listado de Rutas</h1>";
echo "<pre>";

$routes = app('router')->getRoutes();
foreach ($routes as $route) {
    echo $route->uri() . ' => ' . $route->getActionName() . "<br>";
}

echo "</pre>";
