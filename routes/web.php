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
use Illuminate\Support\Facades\Auth;

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

    if (Auth::attempt($credentials, $request->has('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    return back()->withErrors([
        'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
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
        return view('dashboard');
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

    // Menú de administración
    Route::get('/admin', function () {
        return view('admin-menus');
    })->name('admin.menus');

    // Perfil de usuario
    Route::get('/profile', function () {
        return view('profile.show');
    })->name('profile.show');
});

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
