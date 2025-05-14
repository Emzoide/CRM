<?php
// Cargar el entorno de Laravel
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Bypass</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .admin-menu {
            --card-bg: #ffffff;
            --card-hover-bg: #f9fafb;
            --card-border: #e5e7eb;
            --card-hover-border: #3b82f6;
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --card-hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --icon-color: #3b82f6;
            --icon-hover-color: #2563eb;
            --text-color: #1f2937;
        }

        .admin-menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .admin-menu-card {
            background: var(--card-bg);
            border-radius: 0.75rem;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.2s ease-in-out;
            aspect-ratio: 1 / 1;
        }

        .admin-menu-card:hover,
        .admin-menu-card:focus {
            background: var(--card-hover-bg);
            border-color: var(--card-hover-border);
            box-shadow: var(--card-hover-shadow);
            transform: translateY(-4px);
        }

        .admin-menu-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3.5rem;
            height: 3.5rem;
            margin-bottom: 1rem;
            border-radius: 50%;
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--icon-color);
            font-size: 1.5rem;
            transition: all 0.2s ease;
        }

        .admin-menu-card:hover .admin-menu-icon {
            background-color: rgba(59, 130, 246, 0.2);
            color: var(--icon-hover-color);
            transform: scale(1.1);
        }

        .admin-menu-title {
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            margin: 0;
        }

        .status-box {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
        }

        @media (max-width: 640px) {
            .admin-menu-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-6 admin-menu">
        <div class="flex justify-between items-center mb-8 px-4">
            <h1 class="text-2xl md:text-3xl font-semibold text-gray-800">Panel de Administración (Modo Bypass)</h1>
        </div>

        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 mx-4" role="alert">
            <p class="font-bold">Modo de acceso directo</p>
            <p>Este panel muestra todas las opciones sin verificar permisos. Usuario actual: <?php echo $usuario->email; ?></p>
        </div>

        <div class="admin-menu-grid">
            <!-- Sucursales -->
            <a href="<?php echo url('admin/sucursales'); ?>" class="admin-menu-card">
                <div class="admin-menu-icon">
                    <i class="fas fa-store"></i>
                </div>
                <h2 class="admin-menu-title">Sucursales</h2>
            </a>

            <!-- Usuarios -->
            <a href="<?php echo url('admin/usuarios'); ?>" class="admin-menu-card">
                <div class="admin-menu-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h2 class="admin-menu-title">Usuarios</h2>
            </a>

            <!-- Roles -->
            <a href="<?php echo url('admin/roles'); ?>" class="admin-menu-card">
                <div class="admin-menu-icon">
                    <i class="fas fa-user-tag"></i>
                </div>
                <h2 class="admin-menu-title">Roles</h2>
            </a>

            <!-- Tiendas -->
            <a href="<?php echo url('admin/tiendas'); ?>" class="admin-menu-card">
                <div class="admin-menu-icon">
                    <i class="fas fa-store-alt"></i>
                </div>
                <h2 class="admin-menu-title">Tiendas</h2>
            </a>

            <!-- Reportes -->
            <a href="<?php echo url('admin/reportes'); ?>" class="admin-menu-card">
                <div class="admin-menu-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h2 class="admin-menu-title">Reportes</h2>
            </a>

            <!-- Vehículos -->
            <a href="<?php echo url('admin/vehiculos'); ?>" class="admin-menu-card">
                <div class="admin-menu-icon">
                    <i class="fas fa-car"></i>
                </div>
                <h2 class="admin-menu-title">Vehículos</h2>
            </a>

            <!-- Chat -->
            <a href="<?php echo url('chat'); ?>" class="admin-menu-card">
                <div class="admin-menu-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h2 class="admin-menu-title">Chat</h2>
            </a>

            <!-- Volver al menú normal -->
            <a href="<?php echo url('admin'); ?>" class="admin-menu-card">
                <div class="admin-menu-icon">
                    <i class="fas fa-undo"></i>
                </div>
                <h2 class="admin-menu-title">Menú Normal</h2>
            </a>
        </div>
    </div>
</body>
</html>
