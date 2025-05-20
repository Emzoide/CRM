<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermisosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $permisos = [
            // Permisos de administración
            [
                'nombre' => 'gestionar_usuarios', 
                'descripcion' => 'Permite gestionar TODOS los usuarios del sistema',
                'grupo' => 'Administración'
            ],
            [
                'nombre' => 'gestionar_roles', 
                'descripcion' => 'Permite gestionar los roles y permisos del sistema',
                'grupo' => 'Administración'
            ],
            [
                'nombre' => 'gestionar_tiendas', 
                'descripcion' => 'Permite gestionar las tiendas y sucursales',
                'grupo' => 'Configuración'
            ],
            [
                'nombre' => 'configuracion_sistema', 
                'descripcion' => 'Permite acceder a la configuración del sistema',
                'grupo' => 'Configuración'
            ],
            
            // Permisos de gestión de usuarios
            [
                'nombre' => 'gestionar_usuarios_tienda', 
                'descripcion' => 'Permite gestionar usuarios de la misma tienda',
                'grupo' => 'Gestión de Usuarios'
            ],
            [
                'nombre' => 'gestionar_usuarios_rol', 
                'descripcion' => 'Permite gestionar usuarios del mismo rol',
                'grupo' => 'Gestión de Usuarios'
            ],
            
            // Permisos de operaciones
            [
                'nombre' => 'gestionar_vehiculos', 
                'descripcion' => 'Permite gestionar los vehículos',
                'grupo' => 'Operaciones'
            ],
            [
                'nombre' => 'gestionar_cotizaciones', 
                'descripcion' => 'Permite gestionar las cotizaciones',
                'grupo' => 'Operaciones'
            ],
            [
                'nombre' => 'gestionar_clientes', 
                'descripcion' => 'Permite gestionar los clientes',
                'grupo' => 'Operaciones'
            ],
            [
                'nombre' => 'ver_reportes', 
                'descripcion' => 'Permite ver los reportes del sistema',
                'grupo' => 'Reportes'
            ],
            
            // Permisos específicos para vehículos
            [
                'nombre' => 'gestionar_marcas', 
                'descripcion' => 'Permite gestionar las marcas de vehículos',
                'grupo' => 'Vehículos'
            ],
            [
                'nombre' => 'gestionar_modelos', 
                'descripcion' => 'Permite gestionar los modelos de vehículos',
                'grupo' => 'Vehículos'
            ],
            [
                'nombre' => 'gestionar_versiones', 
                'descripcion' => 'Permite gestionar las versiones de vehículos',
                'grupo' => 'Vehículos'
            ],
            
            // Permisos de comunicación
            [
                'nombre' => 'acceder_chat', 
                'descripcion' => 'Permite acceder al sistema de chat',
                'grupo' => 'Comunicación'
            ],
        ];

        foreach ($permisos as $permiso) {
            Permiso::firstOrCreate(
                ['nombre' => $permiso['nombre']],
                $permiso
            );
        }
    }
}
