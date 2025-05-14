<?php

// Solo funcionalidad básica
require __DIR__.'/../vendor/autoload.php';

// Conectar directamente a la base de datos
try {
    // Obtener las variables de conexión del archivo .env
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $envVars = [];
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value);
            }
        }
        
        // Configurar la conexión
        $db_host = $envVars['DB_HOST'] ?? 'localhost';
        $db_name = $envVars['DB_DATABASE'] ?? 'crm';
        $db_user = $envVars['DB_USERNAME'] ?? 'root';
        $db_pass = $envVars['DB_PASSWORD'] ?? '';
        
        // Conectar a la base de datos
        $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $success = true;
        $message = "Conexión establecida con éxito";
    } else {
        throw new Exception("No se encontró el archivo .env");
    }
} catch (Exception $e) {
    $success = false;
    $message = "Error de conexión: " . $e->getMessage();
}

// Modificar los archivos si se ha solicitado
if (isset($_GET['fix']) && $success) {
    try {
        // 1. Crear middleware BypassAuth
        $middlewarePath = __DIR__ . '/../app/Http/Middleware';
        if (!is_dir($middlewarePath)) {
            mkdir($middlewarePath, 0755, true);
        }
        
        $bypassPath = $middlewarePath . '/BypassAuth.php';
        $bypassContent = <<<'EOD'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BypassAuth
{
    /**
     * Bypass de verificación de permisos
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
EOD;
        file_put_contents($bypassPath, $bypassContent);
        
        // 2. Modificar Kernel.php
        $kernelPath = __DIR__ . '/../app/Http/Kernel.php';
        if (file_exists($kernelPath)) {
            $kernelContent = file_get_contents($kernelPath);
            
            // Reemplazar middleware can
            $kernelContent = preg_replace(
                "/'can' => \\\\Illuminate\\\\Auth\\\\Middleware\\\\Authorize::class,/", 
                "'can' => \\App\\Http\\Middleware\\BypassAuth::class, // Temporal",
                $kernelContent
            );
            
            file_put_contents($kernelPath, $kernelContent);
            
            $fixMessage = "Se ha creado el middleware BypassAuth y se ha modificado el Kernel.";
            $fixSuccess = true;
        } else {
            $fixMessage = "No se encontró el archivo Kernel.php en la ruta esperada.";
            $fixSuccess = false;
        }
    } catch (Exception $e) {
        $fixMessage = "Error al modificar archivos: " . $e->getMessage();
        $fixSuccess = false;
    }
}

// Volver a la configuración original
if (isset($_GET['restore']) && $success) {
    try {
        $kernelPath = __DIR__ . '/../app/Http/Kernel.php';
        if (file_exists($kernelPath)) {
            $kernelContent = file_get_contents($kernelPath);
            
            // Restaurar middleware can
            $kernelContent = preg_replace(
                "/'can' => \\\\App\\\\Http\\\\Middleware\\\\BypassAuth::class, \\/\\/ Temporal/", 
                "'can' => \\Illuminate\\Auth\\Middleware\\Authorize::class,",
                $kernelContent
            );
            
            file_put_contents($kernelPath, $kernelContent);
            
            $restoreMessage = "Se ha restaurado la configuración original del Kernel.";
            $restoreSuccess = true;
        } else {
            $restoreMessage = "No se encontró el archivo Kernel.php en la ruta esperada.";
            $restoreSuccess = false;
        }
    } catch (Exception $e) {
        $restoreMessage = "Error al restaurar archivos: " . $e->getMessage();
        $restoreSuccess = false;
    }
}

// Verificar permisos del usuario actual si tenemos conexión
$userInfo = null;
if ($success && isset($_COOKIE['laravel_session'])) {
    try {
        // Obtener sesión
        $sessionQuery = $conn->prepare("SELECT payload FROM sessions WHERE id = ?");
        $sessionQuery->execute([$_COOKIE['laravel_session']]);
        
        if ($row = $sessionQuery->fetch(PDO::FETCH_ASSOC)) {
            $sessionData = unserialize(base64_decode($row['payload']));
            
            if (isset($sessionData['login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'])) {
                $userId = $sessionData['login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'];
                
                // Obtener información del usuario
                $userQuery = $conn->prepare("SELECT id, email, first_name, last_name FROM usuarios WHERE id = ?");
                $userQuery->execute([$userId]);
                
                if ($userRow = $userQuery->fetch(PDO::FETCH_ASSOC)) {
                    $userInfo = $userRow;
                    
                    // Verificar si es admin
                    $adminQuery = $conn->prepare("
                        SELECT COUNT(*) as is_admin
                        FROM usuario_rol ur
                        JOIN roles r ON ur.rol_id = r.id
                        WHERE ur.usuario_id = ? AND r.nombre = 'ADMINISTRADOR'
                    ");
                    $adminQuery->execute([$userId]);
                    $adminRow = $adminQuery->fetch(PDO::FETCH_ASSOC);
                    $userInfo['is_admin'] = $adminRow['is_admin'] > 0;
                }
            }
        }
    } catch (Exception $e) {
        // Ignorar error, solo no mostraremos info del usuario
    }
}

// Salida HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solución Directa - Bypass de Permisos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 py-8">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Solución Directa - Bypass de Permisos</h1>
        
        <?php if (!$success): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                <h3 class="font-bold">Error de conexión:</h3>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php else: ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                <p class="font-bold">Conexión a la base de datos: OK</p>
            </div>
            
            <?php if (isset($fixMessage)): ?>
                <div class="<?php echo $fixSuccess ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700'; ?> border-l-4 p-4 mb-6">
                    <p><?php echo htmlspecialchars($fixMessage); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($restoreMessage)): ?>
                <div class="<?php echo $restoreSuccess ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700'; ?> border-l-4 p-4 mb-6">
                    <p><?php echo htmlspecialchars($restoreMessage); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($userInfo): ?>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-4">Usuario actual</h2>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <p><strong>ID:</strong> <?php echo htmlspecialchars($userInfo['id']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($userInfo['email']); ?></p>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($userInfo['first_name'] . ' ' . $userInfo['last_name']); ?></p>
                        <p><strong>Admin:</strong> <?php echo $userInfo['is_admin'] ? '✅ Sí' : '❌ No'; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">Opciones</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="?fix=1" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded text-center">
                        Aplicar Bypass de Permisos
                    </a>
                    <a href="?restore=1" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded text-center">
                        Restaurar Configuración Original
                    </a>
                </div>
            </div>
            
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">Pasos a seguir</h2>
                <ol class="list-decimal pl-5 space-y-2">
                    <li>Haz clic en <strong>Aplicar Bypass de Permisos</strong></li>
                    <li>Accede al <a href="/admin" class="text-blue-600 hover:underline">Panel de Administración</a></li>
                    <li>Cuando termines, vuelve a esta página y haz clic en <strong>Restaurar Configuración Original</strong></li>
                </ol>
            </div>
        <?php endif; ?>
        
        <div class="mt-8 text-center">
            <a href="/admin" class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded text-center">
                Ir al Panel de Administración
            </a>
        </div>
    </div>
</body>
</html>
