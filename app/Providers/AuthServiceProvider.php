<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Definir un gate general para cualquier permiso
        Gate::before(function ($user, $ability) {
            // Si el usuario usa el método tienePermiso y tiene el permiso, autorizarlo
            if (method_exists($user, 'tienePermiso') && $user->tienePermiso($ability)) {
                return true;
            }
            
            // Si el usuario tiene método hasRole y es admin, autorizarlo
            if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return true;
            }
            
            // En cualquier otro caso, seguir con la verificación normal
            return null;
        });
        
        // Registrar gates específicos para cada permiso usado en las rutas
        $permisos = [
            'gestionar_roles',
            'gestionar_vehiculos',
            'gestionar_tiendas',
            'gestionar_sucursales',
            'gestionar_reportes',
            'ver_reportes',
            'gestionar_chat',
            'gestionar_clientes',
            'acceder_chat'
        ];
        
        foreach ($permisos as $permiso) {
            Gate::define($permiso, function ($user) use ($permiso) {
                return $user->tienePermiso($permiso);
            });
        }
        
        // Gate especial para la gestión de usuarios (combina los tres permisos)
        Gate::define('gestionar_usuarios|gestionar_usuarios_tienda|gestionar_usuarios_rol', function ($user) {
            return $user->tieneAlgunPermiso([
                'gestionar_usuarios',
                'gestionar_usuarios_tienda',
                'gestionar_usuarios_rol'
            ]);
        });
        
        // Definir también los gates individuales para estos permisos
        Gate::define('gestionar_usuarios', function ($user) {
            return $user->tienePermiso('gestionar_usuarios');
        });
        
        Gate::define('gestionar_usuarios_tienda', function ($user) {
            return $user->tienePermiso('gestionar_usuarios_tienda');
        });
        
        Gate::define('gestionar_usuarios_rol', function ($user) {
            return $user->tienePermiso('gestionar_usuarios_rol');
        });
    }
}
