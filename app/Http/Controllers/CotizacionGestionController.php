<?php

namespace App\Http\Controllers;

use App\Models\Oportunidad;
use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Models\BitacoraEtapasOportunidad;
use App\Models\Seguimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CotizacionGestionController extends Controller
{

    public function store(Request $request, Oportunidad $oportunidad)
    {
        // 1. Validar que la oportunidad no esté cerrada
        if (in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
            return back()->withErrors('No se puede crear cotización en una oportunidad cerrada.');
        }

        // 2. Validar datos de cotización y datos de contacto
        $data = $request->validate([
            'version_vehiculo_id' => 'required|exists:versiones_vehiculo,id',
            'precio_unit'         => 'required|numeric|min:0',
            'cantidad'            => 'required|integer|min:1',
            'tipo_compra'         => 'required|in:contado,credito',
            'banco_id'            => 'nullable|required_if:tipo_compra,credito',
            'banco_otro'          => 'nullable|required_if:banco_id,otro|string|max:100',
            'compra_plazos'       => 'nullable|boolean',
            'razon_no_plazos'     => 'nullable|string|max:200',
            'seguro_vehicular'    => 'required|boolean',
            'razon_no_seguro'     => 'nullable|required_if:seguro_vehicular,0|string|max:200',
            'observacion_call_center' => 'nullable|string|max:500',
            // Campos de contacto
            'email'               => 'nullable|email|max:100',
            'phone'               => 'nullable|string|max:50',
            'address'             => 'nullable|string|max:150',
            'occupation'          => 'nullable|string|max:100',
            'canal_id'            => 'nullable|exists:canales_contacto,id',
            'update_client_info'  => 'sometimes|boolean',
        ]);

        // 3. Crear cotización y detalle en transacción
        DB::transaction(function () use ($oportunidad, $data) {
            // 3.1. Marcar previas activas como 'superseded'
            Cotizacion::where('oportunidad_id', $oportunidad->id)
                ->where('estado', 'active')
                ->update(['estado' => 'superseded']);

            // 3.2. Crear nueva cotización
            $cotizacion = Cotizacion::create([
                'oportunidad_id'    => $oportunidad->id,
                'codigo'            => 'COT-' . str_pad(Cotizacion::max('id') + 1, 4, '0', STR_PAD_LEFT),
                'emitida_en'        => now(),
                'vendedor_id'       => auth()->id() ?: 1,
                'total'             => $data['precio_unit'] * $data['cantidad'],
                'estado'            => 'active',
                'tipo_compra'       => $data['tipo_compra'],
                'banco_id'          => $data['banco_id'] === 'otro' ? null : $data['banco_id'],
                'banco_otro'        => $data['banco_id'] === 'otro' ? $data['banco_otro'] : null,
                'compra_plazos'     => $data['compra_plazos'] ?? false,
                'razon_no_plazos'   => $data['razon_no_plazos'] ?? null,
                'seguro_vehicular'  => $data['seguro_vehicular'],
                'razon_no_seguro'   => $data['razon_no_seguro'] ?? null,
                'observacion_call_center' => $data['observacion_call_center'] ?? null,
                // Datos de contacto para esta cotización
                'email'             => $data['email'] ?? null,
                'phone'             => $data['phone'] ?? null,
                'address'           => $data['address'] ?? null,
                'occupation'        => $data['occupation'] ?? null,
            ]);

            // 3.2.1 Actualizar información del cliente si se solicitó
            if (!empty($data['update_client_info'])) {
                $cliente = $oportunidad->cliente;
                if ($cliente) {
                    $cliente->update([
                        'email'      => $data['email'] ?? $cliente->email,
                        'phone'      => $data['phone'] ?? $cliente->phone,
                        'address'    => $data['address'] ?? $cliente->address,
                        'occupation' => $data['occupation'] ?? $cliente->occupation,
                        'canal_id'   => $data['canal_id'] ?? $cliente->canal_id,
                    ]);
                }
            }

            // 3.3. Crear detalle de cotización
            DetalleCotizacion::create([
                'cotizacion_id'       => $cotizacion->id,
                'version_vehiculo_id' => $data['version_vehiculo_id'],
                'cantidad'            => $data['cantidad'],
                'precio_unit'         => $data['precio_unit'],
            ]);

            // 3.4. Actualizar etapa de oportunidad si venía de 'new'
            if ($oportunidad->etapa_actual === 'new') {
                $oportunidad->update(['etapa_actual' => 'quote_sent']);

                BitacoraEtapasOportunidad::create([
                    'oportunidad_id' => $oportunidad->id,
                    'from_stage'     => 'new',
                    'to_stage'       => 'quote_sent',
                    'movido_por'     => auth()->id() ?: 1,
                ]);
            }
        });

        return back()->with('success', 'Cotización creada correctamente.');
    }


    /**
     * Marca una cotización como rechazada por el cliente
     */
    public function rechazar(Request $request, Cotizacion $cotizacion)
    {
        // Validar que la cotización esté activa
        if ($cotizacion->estado !== 'active') {
            return back()->withErrors('Solo se pueden rechazar cotizaciones activas.');
        }

        // Validar que la oportunidad no esté cerrada
        $oportunidad = $cotizacion->oportunidad;
        if (in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
            return back()->withErrors('No se puede modificar cotizaciones de oportunidades cerradas.');
        }

        $data = $request->validate([
            'motivo_rechazo' => 'required|string|max:500',
        ]);

        $cotizacion->update([
            'estado' => 'client-rejected',
            'motivo_rechazo' => $data['motivo_rechazo'],
            'rechazada_en' => now(),
            'rechazada_por' => auth()->id() ?: 1,
        ]);

        return back()->with('success', 'Cotización marcada como rechazada.');
    }
    /**
     * Marca una cotización como rechazada internamente por el equipo de ventas
     */
    public function rechazarInterna(Request $request, Cotizacion $cotizacion)
    {
        // Validar que la cotización esté activa
        if ($cotizacion->estado !== 'active') {
            return back()->withErrors('Solo se pueden rechazar cotizaciones activas.');
        }

        // Validar que la oportunidad no esté cerrada
        $oportunidad = $cotizacion->oportunidad;
        if (in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
            return back()->withErrors('No se puede modificar cotizaciones de oportunidades cerradas.');
        }

        $data = $request->validate([
            'motivo_rechazo' => 'required|string|max:500',
        ]);

        $cotizacion->update([
            'estado' => 'rejected', // Usamos 'rejected' para rechazo interno
            'motivo_rechazo' => $data['motivo_rechazo'],
            'rechazada_en' => now(),
            'rechazada_por' => auth()->id(),
        ]);

        // Opcionalmente, podríamos actualizar la etapa de la oportunidad
        // si el rechazo interno implica un cambio en el flujo de ventas

        return back()->with('success', 'Cotización rechazada internamente.');
    }


    /**
     * Muestra el historial de cotizaciones de una oportunidad
     */
    public function historial(Oportunidad $oportunidad)
    {
        $cotizaciones = $cotizacion->cotizaciones()
            ->with(['detalles.versionVehiculo'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('cotizaciones.historial', compact('oportunidad', 'cotizaciones'));
    }
}
