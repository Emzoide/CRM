<?php
// Carga el entorno de Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    // Limpia toda la caché
    \Artisan::call('cache:clear');
    \Artisan::call('config:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');

    // Limpia la caché de Laravel
    \Illuminate\Support\Facades\Cache::flush();

    // Limpia la sesión actual (opcional)
    session()->flush();

    echo json_encode([
        'success' => true,
        'message' => 'Caché limpiada correctamente. La aplicación debería funcionar ahora.',
        'next_step' => 'Vuelve a la página principal y prueba nuevamente.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al limpiar la caché: ' . $e->getMessage()
    ]);
}
