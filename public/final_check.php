<?php

// Cargar el entorno de Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Funciones para verificar
function limpiarCache() {
    try {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
        
        \Illuminate\Support\Facades\Cache::flush();
        
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function verificarAuth() {
    try {
        return auth()->check() ? 'Autenticado como: ' . auth()->user()->email : 'No autenticado';
    } catch (Exception $e) {
        return 'Error en autenticación: ' . $e->getMessage();
    }
}

function verificarRoles() {
    try {
        if (!auth()->check()) {
            return 'No se puede verificar roles - usuario no autenticado';
        }
        
        $usuario = auth()->user();
        $roles = \Illuminate\Support\Facades\DB::table('usuario_rol')
            ->join('roles', 'usuario_rol.rol_id', '=', 'roles.id')
            ->where('usuario_rol.usuario_id', $usuario->id)
            ->pluck('roles.nombre')
            ->toArray();
        
        return [
            'roles' => $roles,
            'es_admin' => in_array('ADMINISTRADOR', $roles)
        ];
    } catch (Exception $e) {
        return 'Error al verificar roles: ' . $e->getMessage();
    }
}

function verificarPermisos() {
    try {
        if (!auth()->check()) {
            return 'No se puede verificar permisos - usuario no autenticado';
        }
        
        $usuario = auth()->user();
        $permisos = \Illuminate\Support\Facades\DB::table('usuario_rol')
            ->join('rol_permiso', 'usuario_rol.rol_id', '=', 'rol_permiso.rol_id')
            ->join('permisos', 'rol_permiso.permiso_id', '=', 'permisos.id')
            ->where('usuario_rol.usuario_id', $usuario->id)
            ->pluck('permisos.nombre')
            ->toArray();
        
        return $permisos;
    } catch (Exception $e) {
        return 'Error al verificar permisos: ' . $e->getMessage();
    }
}

// Ejecutar verificaciones
$resultados = [
    'limpieza_cache' => limpiarCache(),
    'autenticacion' => verificarAuth()
];

// Solo agregar estas verificaciones si el usuario está autenticado
if (auth()->check()) {
    $resultados['roles'] = verificarRoles();
    $resultados['permisos'] = verificarPermisos();
}

header('Content-Type: application/json');
echo json_encode($resultados, JSON_PRETTY_PRINT);
