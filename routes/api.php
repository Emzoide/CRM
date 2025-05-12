<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SeguimientoController;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\VersionVehiculo;
use App\Models\Cotizacion;
use App\Models\Oportunidad;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleCotizacion;
use App\Models\Seguimiento;
use App\Models\BitacoraEtapasOportunidad;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\ConsentimientoController;
use App\Http\Controllers\WhatsAppController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas para marcas, modelos y versiones
Route::get('/marcas', function () {
    return response()->json(Marca::orderBy('nombre')->get());
});

Route::get('/modelos/{marca}', function ($marcaId) {
    return response()->json(
        Modelo::where('marca_id', $marcaId)
            ->orderBy('nombre')
            ->get()
    );
});

Route::get('/versiones/{modelo}', function ($modeloId) {
    return response()->json(
        VersionVehiculo::where('modelo_id', $modeloId)
            ->orderBy('nombre')
            ->get()
    );
});

Route::get('/oportunidades/{id}/estado', [SeguimientoController::class, 'verificarEstadoOportunidad']);

Route::get('/oportunidades/{oportunidad}/cotizacion-activa', function (Oportunidad $oportunidad) {
    $cotizacion = $oportunidad->cotizacionActiva()->first();

    if (!$cotizacion) {
        return response()->json([
            'success' => false,
            'message' => 'No se encontró una cotización activa para esta oportunidad'
        ], 404);
    }

    // Cargar todas las relaciones necesarias
    $cotizacion->load([
        'vendedor:id,full_name,login',
        'banco:id,nombre',
        'detalles' => function ($query) {
            $query->with([
                'versionVehiculo.modelo.marca'
            ]);
        }
    ]);

    if ($cotizacion->detalles->isEmpty()) {
        return response()->json([
            'success' => false,
            'error' => 'La cotización no tiene detalles asociados'
        ], 404);
    }

    $detalle = $cotizacion->detalles->first();

    // Cargar la información del cliente
    $cliente = $oportunidad->cliente()->select('id', 'nombre', 'dni_ruc')->first();

    // Construir la información del vehículo
    $vehiculoData = [
        'version' => $detalle->versionVehiculo,
        'modelo' => $detalle->versionVehiculo->modelo,
        'marca' => $detalle->versionVehiculo->modelo->marca,
        'cantidad' => $detalle->cantidad,
        'precio_unit' => $detalle->precio_unit
    ];

    // Añadir la información del vehículo, cliente y banco a la cotización antes de enviarla
    $cotizacionData = $cotizacion->toArray();
    $cotizacionData['vehiculo_detalle'] = $vehiculoData;
    $cotizacionData['cliente'] = $cliente;
    $cotizacionData['banco'] = $cotizacion->banco ? [
        'id' => $cotizacion->banco->id,
        'nombre' => $cotizacion->banco->nombre
    ] : null;

    return response()->json([
        'success' => true,
        'data' => [
            'cotizacion_activa' => $cotizacionData
        ]
    ]);
});

// Endpoint para obtener todas las cotizaciones de una oportunidad
Route::get('/oportunidades/{oportunidad}/cotizaciones', function (Oportunidad $oportunidad) {
    $cotizaciones = $oportunidad->cotizaciones()
        ->orderBy('created_at', 'desc')
        ->get();

    if ($cotizaciones->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No hay cotizaciones registradas para esta oportunidad'
        ], 404);
    }

    // Cargar los detalles de cada cotización
    $cotizaciones->each(function ($cotizacion) {
        $cotizacion->load([
            'detalles' => function ($query) {
                $query->with([
                    'versionVehiculo.modelo.marca'
                ]);
            },
            'rechazador:id,full_name',
            'vendedor:id,full_name',
            'banco:id,nombre'
        ]);

        // Debug log
        \Log::info('Cotización cargada:', [
            'id' => $cotizacion->id,
            'estado' => $cotizacion->estado,
            'rechazada_en' => $cotizacion->rechazada_en,
            'rechazada_por' => $cotizacion->rechazada_por,
            'rechazador' => $cotizacion->rechazador ? $cotizacion->rechazador->toArray() : null
        ]);
    });

    return response()->json([
        'success' => true,
        'data' => $cotizaciones
    ]);
});

Route::get('/cotizaciones/{cotizacion}/vehiculo', function (Cotizacion $cotizacion) {
    // Cargar la cotización con sus relaciones
    $cotizacion->load([
        'detalles' => function ($query) {
            $query->with([
                'versionVehiculo.modelo.marca'
            ]);
        }
    ]);

    if ($cotizacion->detalles->isEmpty()) {
        return response()->json([
            'success' => false,
            'error' => 'La cotización no tiene detalles asociados'
        ], 404);
    }

    // Obtener el primer detalle (asumimos que una cotización tiene un solo vehículo)
    $detalle = $cotizacion->detalles->first();

    return response()->json([
        'success' => true,
        'data' => [
            'cotizacion_id' => $cotizacion->id,
            'vehiculo' => [
                'version' => $detalle->versionVehiculo,
                'modelo' => $detalle->versionVehiculo->modelo,
                'marca' => $detalle->versionVehiculo->modelo->marca,
                'cantidad' => $detalle->cantidad,
                'precio_unit' => $detalle->precio_unit
            ]
        ]
    ]);
});

// Endpoint para rechazar una cotización
Route::post('/cotizaciones/{cotizacion}/rechazar', function (Cotizacion $cotizacion, Request $request) {
    // Validar que la cotización no esté ya rechazada
    if ($cotizacion->estado === 'rejected' || $cotizacion->estado === 'client-rejected') {
        return response()->json([
            'success' => false,
            'message' => 'Esta cotización ya ha sido rechazada'
        ], 400);
    }

    // Validar que se proporcione un motivo de rechazo
    $request->validate([
        'motivo_rechazo' => 'required|string|min:3'
    ]);

    // Actualizar la cotización con los datos de rechazo
    $cotizacion->update([
        'estado' => 'rejected',
        'rechazada_por' => auth()->id(),
        'rechazada_en' => now(),
        'motivo_rechazo' => $request->motivo_rechazo
    ]);

    // Log para depuración
    \Log::info('Cotización rechazada:', [
        'id' => $cotizacion->id,
        'estado' => $cotizacion->estado,
        'rechazada_por' => $cotizacion->rechazada_por,
        'rechazada_en' => $cotizacion->rechazada_en,
        'motivo_rechazo' => $cotizacion->motivo_rechazo
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Cotización rechazada correctamente',
        'data' => $cotizacion
    ]);
});

// Endpoint para crear una nueva cotización en una oportunidad existente
Route::post('/oportunidades/{oportunidad}/cotizaciones', function (Oportunidad $oportunidad, Request $request) {
    // Validar que la oportunidad no esté cerrada
    if (in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
        return response()->json([
            'success' => false,
            'message' => 'No se puede crear cotización en una oportunidad cerrada'
        ], 400);
    }

    try {
        DB::transaction(function () use ($oportunidad, $request) {
            // 1. Marcar todas las cotizaciones previas como 'superseded'
            Cotizacion::where('oportunidad_id', $oportunidad->id)
                ->where('estado', 'active')
                ->update(['estado' => 'superseded']);

            // 2. Crear nueva cotización
            $cotizacion = Cotizacion::create([
                'oportunidad_id' => $oportunidad->id,
                'codigo' => 'COT-' . str_pad(Cotizacion::max('id') + 1, 4, '0', STR_PAD_LEFT),
                'emitida_en' => now(),
                'vendedor_id' => auth()->id() ?: 1,
                'total' => $request->precio_unit * $request->cantidad,
                'estado' => 'active',
                'tipo_compra' => $request->tipo_compra,
                'banco_id' => $request->banco_id ?? null,
                'banco_otro' => $request->banco_otro ?? null,
                'compra_plazos' => $request->compra_plazos ?? false,
                'razon_no_plazos' => $request->razon_no_plazos ?? null,
                'seguro_vehicular' => $request->seguro_vehicular,
                'razon_no_seguro' => $request->razon_no_seguro ?? null,
                'observacion_call_center' => $request->observacion_call_center ?? null,
            ]);

            // 3. Crear detalle de cotización
            DetalleCotizacion::create([
                'cotizacion_id' => $cotizacion->id,
                'version_vehiculo_id' => $request->version_vehiculo_id,
                'cantidad' => $request->cantidad,
                'precio_unit' => $request->precio_unit,
            ]);

            // 4. Crear seguimiento
            Seguimiento::create([
                'oportunidad_id' => $oportunidad->id,
                'contacto_en' => $request->contacto_en,
                'resultado' => $request->resultado,
                'comentario' => $request->comentario,
                'proxima_accion' => $request->proxima_accion,
                'usuario_id' => auth()->id() ?: 1,
            ]);

            // 5. Actualizar etapa de oportunidad si es necesario
            if ($oportunidad->etapa_actual === 'new') {
                $oportunidad->update(['etapa_actual' => 'quote_sent']);

                BitacoraEtapasOportunidad::create([
                    'oportunidad_id' => $oportunidad->id,
                    'from_stage' => 'new',
                    'to_stage' => 'quote_sent',
                    'movido_por' => auth()->id() ?: 1,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Nueva cotización y seguimiento creados correctamente'
        ]);
    } catch (\Exception $e) {
        \Log::error('Error al crear cotización: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al crear la cotización: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/whatsapp/templates', [\App\Http\Controllers\Chat\MessageController::class, 'getTemplates']);

// Rutas para el webhook de WhatsApp
Route::get('/webhook/chat', [WebhookController::class, 'verify']);
Route::post('/webhook/chat', [WebhookController::class, 'receive']);

Route::post('/webhook/send-template', [\App\Http\Controllers\Chat\WebhookController::class, 'sendTemplateWebhook']);

Route::post('/whatsapp/token', [MessageController::class, 'setToken']);


//Ruta para recibir peticiones de consentimiento
Route::post('/consentimiento', [ConsentimientoController::class, 'store']);
Route::post('/consentimientos', [ConsentimientoController::class, 'store']);
Route::get('/consentimientos/dni/{dni}', [ConsentimientoController::class, 'show']);

// Rutas de reportes API
Route::prefix('reportes')->group(function () {
    Route::get('/antispam', [App\Http\Controllers\Api\ReporteAntispamController::class, 'index']);
});

// Rutas para WhatsApp Webhook
Route::post('/whatsapp/webhook/template', [App\Http\Controllers\Api\WhatsAppWebhookController::class, 'sendTemplate']);

Route::post('/whatsapp/send-contact', [WhatsAppController::class, 'sendContactTemplate']);
Route::post('/whatsapp/send-reactivation', [WhatsAppController::class, 'sendReactivationTemplate']);
