<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Oportunidad;
use App\Models\Seguimiento;
use App\Models\BitacoraEtapasOportunidad;
use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestionar el seguimiento de oportunidades de venta
 */
class SeguimientoController extends Controller
{
    /**
     * Muestra el listado de seguimientos
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $seguimientos = Seguimiento::with(['oportunidad', 'oportunidad.cliente'])->get();
        return view('seguimientos.index', compact('seguimientos'));
    }

    public function create()
    {
        // TODO: return view('seguimientos.create');
        return redirect('/');
    }

    public function store(Request $request)
    {
        // Log de inicio de la solicitud
        Log::info('Inicio de solicitud de seguimiento', [
            'request_data' => $request->all(),
            'user_id' => auth()->id() ?: 'no_auth'
        ]);

        try {
            /** 1. VALIDACIÓN */
            $rules = [
                'cliente_id'     => 'required|exists:clientes,id',
                'oportunidad_id' => 'nullable|exists:oportunidades,id',
                'contacto_en'    => 'required|date',
                'resultado'      => 'required|in:called,emailed,whatsapp,visited',
                'comentario'     => 'nullable|string',
                'proxima_accion' => 'nullable|string|max:150',
            ];

            // Campos extra solo si NO llega oportunidad
            if (!$request->filled('oportunidad_id')) {
                $rules += [
                    'version_vehiculo_id' => 'required|exists:versiones_vehiculo,id',
                    'tipo_compra'         => 'required|in:contado,credito',
                    'seguro_vehicular'    => 'required|boolean',
                    'precio_unit'         => 'required|numeric|min:0',
                    'banco_id'            => 'nullable|required_if:tipo_compra,credito',
                    'banco_otro'          => 'nullable|required_if:banco_id,otro|string|max:100',
                    'razon_no_seguro'     => 'nullable|required_if:seguro_vehicular,0|string|max:200',
                    'razon_no_plazos'     => 'nullable|string|max:200',
                    'observacion_call_center' => 'nullable|string|max:500',
                ];
            }

            $data = $request->validate($rules);

            // Log después de la validación exitosa
            Log::info('Validación de datos exitosa', [
                'validated_data' => $data
            ]);

            DB::transaction(function () use ($data) {
                /* 2. Oportunidad */
                $oportunidad = null;

                if (!empty($data['oportunidad_id'])) {
                    $oportunidad = Oportunidad::find($data['oportunidad_id']);
                    Log::info('Oportunidad encontrada', [
                        'oportunidad_id' => $oportunidad ? $oportunidad->id : null,
                        'etapa_actual' => $oportunidad ? $oportunidad->etapa_actual : null
                    ]);
                }

                // Verificar si la oportunidad está cerrada o no existe
                if (!$oportunidad || in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
                    Log::info('Creando nueva oportunidad', [
                        'cliente_id' => $data['cliente_id']
                    ]);

                    // ➜ CREA nueva
                    $oportunidad = Oportunidad::create([
                        'cliente_id'   => $data['cliente_id'],
                        'etapa_actual' => 'new',
                        'probabilidad' => 0,
                    ]);

                    Log::info('Nueva oportunidad creada', [
                        'oportunidad_id' => $oportunidad->id
                    ]);

                    // ➜ Bitácora inicial
                    BitacoraEtapasOportunidad::create([
                        'oportunidad_id' => $oportunidad->id,
                        'from_stage'     => 'new',
                        'to_stage'       => 'new',
                        'movido_por'     => auth()->id() ?: 1,
                    ]);

                    // ➜ Cotización activa
                    $cotizacion = Cotizacion::create([
                        'oportunidad_id' => $oportunidad->id,
                        'codigo'         => 'COT-' . str_pad(Cotizacion::max('id') + 1, 4, '0', STR_PAD_LEFT),
                        'emitida_en'     => now(),
                        'vence_en'       => now()->addDays(30), // 30 días de validez por defecto
                        'vendedor_id'    => auth()->id() ?: 1,
                        'total'          => $data['precio_unit'],
                        'estado'         => 'active',
                        'motivo_rechazo' => null,
                        'rechazada_en'   => null,
                        'rechazada_por'  => null,
                        'tipo_compra'    => $data['tipo_compra'],
                        'banco_id'       => $data['banco_id'] === 'otro' ? null : $data['banco_id'],
                        'banco_otro'     => $data['banco_id'] === 'otro' ? $data['banco_otro'] : null,
                        'compra_plazos'  => $data['compra_plazos'] ?? false,
                        'razon_no_plazos' => $data['razon_no_plazos'] ?? null,
                        'seguro_vehicular' => $data['seguro_vehicular'],
                        'razon_no_seguro' => $data['razon_no_seguro'] ?? null,
                        'observacion_call_center' => $data['observacion_call_center'] ?? null,
                    ]);

                    Log::info('Nueva cotización creada', [
                        'cotizacion_id' => $cotizacion->id,
                        'codigo' => $cotizacion->codigo,
                        'banco_id' => $data['banco_id'],
                        'banco_otro' => $data['banco_id'] === 'otro' ? $data['banco_otro'] : null
                    ]);

                    DetalleCotizacion::create([
                        'cotizacion_id'       => $cotizacion->id,
                        'version_vehiculo_id' => $data['version_vehiculo_id'],
                        'cantidad'            => 1,
                        'precio_unit'         => $data['precio_unit'],
                    ]);

                    Log::info('Detalle de cotización creado', [
                        'version_vehiculo_id' => $data['version_vehiculo_id'],
                        'precio_unit' => $data['precio_unit']
                    ]);
                } else {
                    $cotizacion = $oportunidad->ultimaCotizacion;
                    Log::info('Usando cotización existente', [
                        'cotizacion_id' => $cotizacion ? $cotizacion->id : null
                    ]);
                }

                /* 3. Seguimiento */
                $seguimiento = Seguimiento::create([
                    'oportunidad_id' => $oportunidad->id,
                    'cotizacion_id'  => optional($cotizacion)->id,
                    'usuario_id'     => auth()->id() ?: 1,
                    'contacto_en'    => $data['contacto_en'],
                    'resultado'      => $data['resultado'],
                    'comentario'     => $data['comentario'] ?? null,
                    'proxima_accion' => $data['proxima_accion'] ?? null,
                ]);

                Log::info('Seguimiento creado', [
                    'seguimiento_id' => $seguimiento->id,
                    'oportunidad_id' => $oportunidad->id,
                    'resultado' => $data['resultado']
                ]);

                /* 4. Cambiar etapa new → negotiation (primer contacto) */
                if ($oportunidad->etapa_actual === 'new') {
                    $oportunidad->update(['etapa_actual' => 'negotiation']);

                    BitacoraEtapasOportunidad::create([
                        'oportunidad_id' => $oportunidad->id,
                        'from_stage'    => 'new',
                        'to_stage'      => 'negotiation',
                        'movido_por'    => auth()->id() ?? 1,
                    ]);

                    Log::info('Etapa de oportunidad actualizada', [
                        'oportunidad_id' => $oportunidad->id,
                        'from_stage' => 'new',
                        'to_stage' => 'negotiation'
                    ]);
                }
            });

            // Log de éxito al finalizar la transacción
            Log::info('Seguimiento guardado exitosamente', [
                'cliente_id' => $data['cliente_id'],
                'oportunidad_id' => $data['oportunidad_id'] ?? 'nueva'
            ]);

            return back()->with('success', 'Seguimiento guardado');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log de error de validación
            Log::error('Error de validación en seguimiento', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            throw $e;
        } catch (\Exception $e) {
            // Log de error general
            Log::error('Error al guardar seguimiento', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return back()->with('error', 'Error al guardar el seguimiento: ' . $e->getMessage());
        }
    }

    public function show(Seguimiento $seguimiento)
    {
        // TODO: return view('seguimientos.show', compact('seguimiento'));
        return redirect('/');
    }

    public function edit(Seguimiento $seguimiento)
    {
        // TODO: return view('seguimientos.edit', compact('seguimiento'));
        return redirect('/');
    }

    public function update(Request $request, Seguimiento $seguimiento)
    {
        $data = $request->validate([
            'contacto_en'   => 'required|date',
            'resultado'     => 'required|in:called,emailed,whatsapp,visited',
            'comentario'    => 'nullable|string',
            'proxima_accion' => 'nullable|date',
        ]);
        $seguimiento->update($data);
        return redirect('/');
    }

    public function destroy(Seguimiento $seguimiento)
    {
        $seguimiento->delete();
        return redirect('/');
    }

    /**
     * Verifica el estado de una oportunidad
     */
    public function verificarEstadoOportunidad($id)
    {
        $oportunidad = Oportunidad::findOrFail($id);

        return response()->json([
            'estado' => $oportunidad->etapa_actual,
            'esta_cerrada' => in_array($oportunidad->etapa_actual, ['won', 'lost'])
        ]);
    }
}
