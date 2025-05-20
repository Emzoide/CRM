<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OportunidadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Cliente;
use App\Models\CanalContacto;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\OportunidadCierreController;
use App\Http\Controllers\CotizacionGestionController;
use App\Http\Controllers\BitacoraEtapasOportunidadController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\SucursalController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\SucursalController as AdminSucursalController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Chat\PanelController;
use App\Http\Controllers\CotizacionPdfController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\VehiculoController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Rutas de autenticación (sin protección)
Route::get('/login', function () {
    return view('login.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt(array_merge($credentials, ['activo' => true]), $request->has('remember'))) {
        $request->session()->regenerate();
        // Actualizar last_login
        $usuario = Auth::user();
        $usuario->last_login = now();
        $usuario->save();
        return redirect()->intended('/');
    }

    return back()->withErrors([
        'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros o el usuario está inactivo.',
    ])->withInput($request->only('email'));
})->name('login.submit');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Todas las demás rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    // Dashboard route
    Route::get('/', function () {
        $totalClientes = \App\Models\Cliente::count();
        $clientesMesActual = \App\Models\Cliente::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $clientesMesPasado = \App\Models\Cliente::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $cambio = $clientesMesPasado > 0 ? (($clientesMesActual - $clientesMesPasado) / $clientesMesPasado) * 100 : 0;
        $cambioTexto = number_format(abs($cambio), 1) . '%';
        $cambioTipo = $cambio >= 0 ? 'up' : 'down';

        // Obtener los 3 clientes más recientes
        $clientesRecientes = \App\Models\Cliente::latest()
            ->take(3)
            ->get()
            ->map(function ($cliente) {
                return [
                    'name' => $cliente->nombre,
                    'email' => $cliente->email,
                    'timeAgo' => $cliente->created_at->diffForHumans()
                ];
            });

        // Obtener las 3 cotizaciones más recientes
        $cotizacionesRecientes = \App\Models\Cotizacion::with(['oportunidad.cliente', 'vendedor'])
            ->latest('emitida_en')
            ->take(3)
            ->get()
            ->map(function ($cotizacion) {
                return [
                    'code' => $cotizacion->codigo,
                    'clientName' => $cotizacion->oportunidad->cliente->nombre,
                    'clientEmail' => $cotizacion->oportunidad->cliente->email,
                    'date' => $cotizacion->emitida_en->format('d/m/Y'),
                    'total' => number_format($cotizacion->total, 2),
                    'status' => $cotizacion->estado
                ];
            });

        return view('dashboard', compact('totalClientes', 'cambioTexto', 'cambioTipo', 'clientesRecientes', 'cotizacionesRecientes'));
    })->name('home');

    Route::get('/seguimiento', function () {
        $canales = DB::table('canales_contacto')->get();
        return view('seguimiento.index-seguimiento', compact('canales'));
    })->name('seguimiento.index');

    // Clientes
    Route::resource('clients', ClienteController::class)
        ->parameters(['clients' => 'cliente']);

    // Oportunidades
    Route::resource('oportunidades', OportunidadController::class)
        ->except(['index', 'create', 'show', 'edit']);

    // Seguimientos
    Route::resource('seguimientos', SeguimientoController::class)
        ->only(['store']);

    // Cierre de oportunidades
    Route::get('/oportunidades/{oportunidad}/cierre', [OportunidadCierreController::class, 'show'])
        ->name('oportunidades.cierre.show');

    Route::post('/oportunidades/{oportunidad}/cierre', [OportunidadCierreController::class, 'store'])
        ->name('oportunidades.cierre.store');

    // Cotizaciones
    Route::post('/cotizaciones/{cotizacion}/rechazar-interna', [CotizacionGestionController::class, 'rechazarInterna'])
        ->name('cotizaciones.rechazar-interna');

    Route::post('/cotizaciones/{cotizacion}/rechazar', [CotizacionGestionController::class, 'rechazar'])
        ->name('cotizaciones.rechazar');

    Route::get('/oportunidades/{oportunidad}/cotizaciones/historial', [CotizacionGestionController::class, 'historial'])
        ->name('oportunidades.cotizaciones.historial');

    Route::get('/oportunidades/{oportunidad}/cotizacion-activa', [OportunidadController::class, 'getCotizacionActiva'])
        ->name('oportunidades.cotizacion-activa');

    Route::post('/oportunidades/{oportunidad}/cotizaciones', [CotizacionGestionController::class, 'store'])
        ->name('oportunidades.cotizaciones.store');

    // Bitácora de etapas
    Route::resource('bitacora_etapas', BitacoraEtapasOportunidadController::class)
        ->only(['index', 'show']);

    // Otras rutas
    Route::get('/inventory', function () {
        return view('inventory.index');
    })->name('inventory.index');

    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');

    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');

    // Menú de administración - Accesible para todos los usuarios autenticados
    Route::get('/admin', function () {
        return view('admin-menus');
    })->name('admin.menus');

    // Perfil de usuario
    Route::get('/profile', function () {
        return view('profile.show');
    })->name('profile.show');

    // Tiendas y Sucursales
    Route::resource('tiendas', TiendaController::class);
    Route::resource('sucursales', SucursalController::class);
    
    // Filtros de configuración
    Route::resource('filtros', FiltroConfiguracionController::class);

    // Ruta para probar el chatbot de posventa
    Route::get('/chatbot-test', function () {
        return view('chatbot-test');
    });


    Route::post('/filtros/{id}/predeterminado', [FiltroConfiguracionController::class, 'setPredeterminado'])->name('filtros.predeterminado');

    Route::get('/admin/sucursales', [AdminSucursalController::class, 'index'])->name('admin.sucursales');


    // Ruta para actualizar el último acceso
    Route::post('/user/heartbeat', function () {
        $user = auth()->user();
        if ($user) {
            $user->last_login = now();
            $user->save();
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error'], 401);
    })->name('user.heartbeat');

    // Ruta de chat - Accesible para todos los usuarios autenticados
    Route::get('chat', [PanelController::class, 'index']);
});

// Rutas para administración
Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Eliminamos la definición duplicada y aplicamos middleware directamente al resource
    Route::resource('usuarios', UsuarioController::class)
        ->middleware('can:gestionar_usuarios|gestionar_usuarios_tienda|gestionar_usuarios_rol')
        ->names('admin.usuarios');
    
    // Rutas de administración de roles
    Route::resource('roles', \App\Http\Controllers\Admin\RolController::class)
        ->names('admin.roles')
        ->middleware('can:gestionar_roles');
    
    // Rutas para reportes
    Route::get('reportes', [ReporteController::class, 'index'])
        ->name('admin.reportes.index')
        ->middleware('can:ver_reportes');
        
    Route::get('reportes/antispam', [ReporteController::class, 'antispam'])
        ->name('admin.reportes.antispam')
        ->middleware('can:ver_reportes');
    
    // Rutas para gestión de vehículos
    Route::get('vehiculos', [VehiculoController::class, 'index'])
        ->name('admin.vehiculos')
        ->middleware('can:gestionar_vehiculos');
        
    Route::prefix('vehiculos')->middleware('can:gestionar_vehiculos')->group(function () {
        // Rutas para marcas
        Route::get('marcas', [VehiculoController::class, 'indexMarcas'])->name('admin.vehiculos.marcas');
        Route::post('marcas', [VehiculoController::class, 'storeMarca'])->name('admin.vehiculos.marcas.store');
        Route::put('marcas/{marca}', [VehiculoController::class, 'updateMarca'])->name('admin.vehiculos.marcas.update');
        Route::delete('marcas/{marca}', [VehiculoController::class, 'destroyMarca'])->name('admin.vehiculos.marcas.destroy');
        
        // Rutas para modelos
        Route::get('modelos', [VehiculoController::class, 'indexModelos'])->name('admin.vehiculos.modelos');
        Route::post('modelos', [VehiculoController::class, 'storeModelo'])->name('admin.vehiculos.modelos.store');
        Route::put('modelos/{modelo}', [VehiculoController::class, 'updateModelo'])->name('admin.vehiculos.modelos.update');
        Route::delete('modelos/{modelo}', [VehiculoController::class, 'destroyModelo'])->name('admin.vehiculos.modelos.destroy');
        
        // Rutas para versiones
        Route::get('versiones', [VehiculoController::class, 'indexVersiones'])->name('admin.vehiculos.versiones');
        Route::post('versiones', [VehiculoController::class, 'storeVersion'])->name('admin.vehiculos.versiones.store');
        Route::put('versiones/{version}', [VehiculoController::class, 'updateVersion'])->name('admin.vehiculos.versiones.update');
        Route::delete('versiones/{version}', [VehiculoController::class, 'destroyVersion'])->name('admin.vehiculos.versiones.destroy');
    });
    
    // Rutas para gestión de tiendas
    Route::resource('tiendas', TiendaController::class)
        ->names('admin.tiendas')
        ->middleware('can:gestionar_tiendas');
    
    // Rutas para gestión de sucursales
    Route::resource('sucursales', SucursalController::class)
        ->names('admin.sucursales')
        ->middleware('can:gestionar_tiendas');
});

// Registrar el middleware personalizado
auth()->shouldUse('web');

Route::get('/cotizaciones/{id}/pdf', [CotizacionPdfController::class, 'download'])->name('cotizaciones.pdf');

// Ruta de depuración (protegida)
Route::middleware(['auth'])->get('/debug/routes', function () {
    $collection = collect(Route::getRoutes())->map(function ($route) {
        return [
            'method'     => implode('|', $route->methods()),
            'uri'        => $route->uri(),
            'name'       => $route->getName(),
            'action'     => $route->getActionName(),
            'middleware' => $route->gatherMiddleware(),
        ];
    })->sortBy('uri')->values();

    return response()->json($collection);
});
