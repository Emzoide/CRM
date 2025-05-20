<?php

// Cargar el entorno de Laravel básico sin relaciones
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Verificar si el usuario está autenticado
if (!auth()->check()) {
    die('Debes iniciar sesión primero');
}

$usuario = auth()->user();

// Verificar si es admin directamente en la base de datos
$isAdmin = DB::table('usuario_rol')
    ->join('roles', 'usuario_rol.rol_id', '=', 'roles.id')
    ->where('usuario_rol.usuario_id', $usuario->id)
    ->where('roles.nombre', 'ADMINISTRADOR')
    ->exists();

if (!$isAdmin) {
    die('Esta herramienta solo está disponible para administradores');
}

// Función para aplicar bypass a las rutas
if (isset($_GET['apply'])) {
    try {
        // Modificar Kernel.php para bypass
        $kernelPath = __DIR__ . '/../app/Http/Kernel.php';
        $kernelContent = file_get_contents($kernelPath);
        
        // Reemplazar middleware can
        $kernelContent = preg_replace(
            "/'can' => \\\\.*::class,/", 
            "'can' => \\App\\Http\\Middleware\\BypassAuth::class, // Bypass temporal",
            $kernelContent
        );
        
        // Reemplazar otros middlewares de permisos
        $kernelContent = preg_replace(
            "/'permiso' => \\\\.*::class,/", 
            "'permiso' => \\App\\Http\\Middleware\\BypassAuth::class, // Bypass temporal",
            $kernelContent
        );
        
        $kernelContent = preg_replace(
            "/'puede.gestionar.usuario' => \\\\.*::class,/", 
            "'puede.gestionar.usuario' => \\App\\Http\\Middleware\\BypassAuth::class, // Bypass temporal",
            $kernelContent
        );
        
        file_put_contents($kernelPath, $kernelContent);
        
        // Crear archivo BypassAuth si no existe
        $bypassPath = __DIR__ . '/../app/Http/Middleware/BypassAuth.php';
        if (!file_exists($bypassPath)) {
            $bypassContent = <<<'EOD'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BypassAuth
{
    /**
     * Middleware temporal para bypasear todas las verificaciones de autenticación
     */
    public function handle(Request $request, Closure $next)
    {
        // Simplemente permitir todas las solicitudes
        return $next($request);
    }
}
EOD;
            file_put_contents($bypassPath, $bypassContent);
        }
        
        // Limpiar caché
        @shell_exec('cd .. && php artisan cache:clear');
        @shell_exec('cd .. && php artisan config:clear');
        @shell_exec('cd .. && php artisan route:clear');
        @shell_exec('cd .. && php artisan view:clear');
        
        $message = "Bypass aplicado correctamente. Ahora puedes acceder a todas las secciones sin verificación de permisos.";
        $success = true;
    } catch (Exception $e) {
        $message = "Error al aplicar bypass: " . $e->getMessage();
        $success = false;
    }
}

// Función para restaurar configuración original
if (isset($_GET['restore'])) {
    try {
        // Restaurar Kernel.php
        $kernelPath = __DIR__ . '/../app/Http/Kernel.php';
        $kernelContent = file_get_contents($kernelPath);
        
        // Restaurar middleware can
        $kernelContent = preg_replace(
            "/'can' => \\\\.*::class, \/\/ Bypass temporal/", 
            "'can' => \\Illuminate\\Auth\\Middleware\\Authorize::class,",
            $kernelContent
        );
        
        // Restaurar otros middlewares de permisos
        $kernelContent = preg_replace(
            "/'permiso' => \\\\.*::class, \/\/ Bypass temporal/", 
            "'permiso' => \\App\\Http\\Middleware\\VerificarPermiso::class,",
            $kernelContent
        );
        
        $kernelContent = preg_replace(
            "/'puede.gestionar.usuario' => \\\\.*::class, \/\/ Bypass temporal/", 
            "'puede.gestionar.usuario' => \\App\\Http\\Middleware\\PuedeGestionarUsuario::class,",
            $kernelContent
        );
        
        file_put_contents($kernelPath, $kernelContent);
        
        // Limpiar caché
        @shell_exec('cd .. && php artisan cache:clear');
        @shell_exec('cd .. && php artisan config:clear');
        @shell_exec('cd .. && php artisan route:clear');
        @shell_exec('cd .. && php artisan view:clear');
        
        $message = "Configuración original restaurada.";
        $success = true;
    } catch (Exception $e) {
        $message = "Error al restaurar: " . $e->getMessage();
        $success = false;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bypass de Seguridad</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Herramienta de Bypass de Seguridad</h1>
                <p class="text-gray-600">Esta herramienta permite desactivar temporalmente el sistema de permisos</p>
            </div>
            
            <?php if (isset($message)): ?>
            <div class="mb-6 p-4 rounded-md <?php echo $success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="?apply=1" class="block bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded text-center">
                    Activar Bypass de Permisos
                </a>
                <a href="?restore=1" class="block bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded text-center">
                    Restaurar Configuración Original
                </a>
            </div>
            
            <div class="mt-6">
                <p class="text-gray-800 font-semibold mb-2">Información del usuario actual:</p>
                <ul class="list-disc pl-5 text-gray-600">
                    <li>Usuario: <?php echo $usuario->email; ?></li>
                    <li>Es administrador: <?php echo $isAdmin ? 'Sí' : 'No'; ?></li>
                </ul>
            </div>
            
            <div class="mt-6">
                <p class="text-gray-800 font-semibold mb-2">Siguientes pasos:</p>
                <ol class="list-decimal pl-5 text-gray-600">
                    <li>Haz clic en "Activar Bypass de Permisos"</li>
                    <li>Accede al <a href="/admin" class="text-blue-600 hover:underline">panel de administración</a></li>
                    <li>Cuando termines, regresa a esta página y haz clic en "Restaurar Configuración Original"</li>
                </ol>
            </div>
            
            <div class="mt-6 text-center">
                <a href="/admin" class="inline-block bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Ir al Panel de Administración
                </a>
            </div>
        </div>
    </div>
</body>
</html>
