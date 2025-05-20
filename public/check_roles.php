<?php

// Cargar el entorno de Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Verificar la estructura de la tabla roles
try {
    $columnas = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM roles");

    // Obtener ejemplo de registros
    $roles = \Illuminate\Support\Facades\DB::table('roles')->get();

    echo json_encode([
        'columnas' => $columnas,
        'roles' => $roles
    ], JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
