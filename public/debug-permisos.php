<?php
// Este archivo nos permitirá diagnosticar problemas de permisos sin acceso a la consola

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;

// Verificar que el usuario esté autenticado
if (!Auth::check()) {
    die('Debes iniciar sesión para usar esta herramienta de diagnóstico');
}

$usuario = Auth::user();
echo "<pre>";
echo "---- INFORMACIÓN DEL USUARIO ----\n";
echo "ID: {$usuario->id}\n";
echo "Email: {$usuario->email}\n";
echo "Tienda ID: " . ($usuario->tienda_id ?: 'N/A') . "\n";

// Obtener roles
echo "\n---- ROLES DEL USUARIO ----\n";
$roles = DB::table('usuario_rol')
    ->join('roles', 'usuario_rol.rol_id', '=', 'roles.id')
    ->where('usuario_rol.usuario_id', $usuario->id)
    ->select('roles.id', 'roles.nombre', 'roles.is_admin')
    ->get();

foreach ($roles as $rol) {
    echo "ID: {$rol->id}, Nombre: {$rol->nombre}, ¿Es admin?: " . ($rol->is_admin ? 'SÍ' : 'NO') . "\n";
}

// Verificar permisos específicos
echo "\n---- VERIFICACIÓN DE PERMISOS ----\n";
$permisosAVerificar = [
    'gestionar_usuarios',
    'gestionar_usuarios_tienda',
    'gestionar_usuarios_rol',
    'gestionar_roles'
];

foreach ($permisosAVerificar as $permiso) {
    $tienePermiso = $usuario->tienePermiso($permiso);
    echo "¿Tiene permiso '{$permiso}'?: " . ($tienePermiso ? 'SÍ' : 'NO') . "\n";
}

// Verificar OR de permisos
echo "\n---- VERIFICACIÓN DE PERMISOS COMBINADOS ----\n";
$tieneAlgunPermiso = $usuario->tieneAlgunPermiso(['gestionar_usuarios', 'gestionar_usuarios_tienda', 'gestionar_usuarios_rol']);
echo "¿Tiene alguno de los permisos de usuario?: " . ($tieneAlgunPermiso ? 'SÍ' : 'NO') . "\n";

// Permisos asociados a rutas
echo "\n---- RUTAS DE USUARIOS ----\n";
echo "Ruta admin.usuarios.index: " . ($usuario->tieneAlgunPermiso(['gestionar_usuarios', 'gestionar_usuarios_tienda', 'gestionar_usuarios_rol']) ? 'ACCESIBLE' : 'BLOQUEADA') . "\n";

// Mostrar todos los permisos del usuario
echo "\n---- TODOS LOS PERMISOS DEL USUARIO ----\n";
$permisos = DB::table('usuario_rol')
    ->join('rol_permiso', 'usuario_rol.rol_id', '=', 'rol_permiso.rol_id')
    ->join('permisos', 'rol_permiso.permiso_id', '=', 'permisos.id')
    ->where('usuario_rol.usuario_id', $usuario->id)
    ->select('permisos.id', 'permisos.nombre', 'permisos.descripcion')
    ->get();

foreach ($permisos as $permiso) {
    echo "ID: {$permiso->id}, Nombre: {$permiso->nombre}, Descripción: {$permiso->descripcion}\n";
}

echo "</pre>";
