<?php

namespace App\Http\Controllers;

use App\Models\Oportunidad;
use App\Models\BitacoraEtapasOportunidad;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OportunidadCierreController extends Controller
{
    /**
     * Muestra el formulario para cerrar una oportunidad
     */
    public function show(Oportunidad $oportunidad)
    {
        if (in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
            return back()->withErrors('Esta oportunidad ya está cerrada.');
        }

        // Verificar si tiene al menos un seguimiento
        if (!$oportunidad->seguimientos()->exists()) {
            return back()->withErrors('No se puede cerrar una oportunidad sin seguimientos.');
        }

        // Verificar que exista al menos una cotización activa
        $cotizacionActiva = Cotizacion::where('oportunidad_id', $oportunidad->id)
            ->where('estado', 'active')
            ->first();

        if (!$cotizacionActiva) {
            return back()->withErrors('No hay cotización activa para cerrar esta oportunidad.');
        }

        return view('oportunidades.cierre', compact('oportunidad', 'cotizacionActiva'));
    }

    /**
     * Procesa el cierre de una oportunidad
     */
    public function store(Request $request, Oportunidad $oportunidad)
    {
        if (in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
            return back()->withErrors('Esta oportunidad ya está cerrada.');
        }

        // Verificar si tiene al menos un seguimiento
        if (!$oportunidad->seguimientos()->exists()) {
            return back()->withErrors('No se puede cerrar una oportunidad sin seguimientos.');
        }

        // Verificar que exista al menos una cotización activa
        $cotizacionActiva = Cotizacion::where('oportunidad_id', $oportunidad->id)
            ->where('estado', 'active')
            ->first();

        if (!$cotizacionActiva) {
            return back()->withErrors('No hay cotización activa para cerrar esta oportunidad.');
        }

        $data = $request->validate([
            'resultado' => 'required|in:won,lost',
            'fecha_cierre' => 'required|date',
            'monto_final' => 'required_if:resultado,won|nullable|numeric|min:0',
            'motivo_cierre' => 'required|string',
        ]);

        DB::transaction(function () use ($oportunidad, $data, $cotizacionActiva) {
            // Guardar etapa anterior
            $etapaAnterior = $oportunidad->etapa_actual;

            // Actualizar oportunidad
            $oportunidad->update([
                'etapa_actual' => $data['resultado'],
                'fecha_cierre' => $data['fecha_cierre'],
                'monto_final' => $data['monto_final'],
                'motivo_cierre' => $data['motivo_cierre'],
                'cerrado_por' => auth()->id() ?: 1,
            ]);

            // Registrar en bitácora
            BitacoraEtapasOportunidad::create([
                'oportunidad_id' => $oportunidad->id,
                'from_stage' => $etapaAnterior,
                'to_stage' => $data['resultado'],
                'movido_por' => auth()->id() ?: 1,
            ]);

            // Actualizar estado de la cotización activa
            $nuevoEstado = $data['resultado'] === 'won' ? 'approved' : 'rejected';
            $cotizacionActiva->update([
                'estado' => $nuevoEstado,
                'vence_en' => $data['resultado'] === 'won' ? now()->addDays(30) : null,
            ]);
        });

        return redirect()->route('clients.show', $oportunidad->cliente_id)
            ->with('success', 'Oportunidad cerrada exitosamente.');
    }

    /**
     * Reabre una oportunidad cerrada (solo para usuarios con permisos)
     */
    public function reabrir(Request $request, Oportunidad $oportunidad)
    {
        // Verificar permisos (ajustar según tu sistema)
        if (!auth()->user()->can('reabrir_oportunidad')) {
            return back()->withErrors('No tienes permisos para reabrir oportunidades.');
        }

        // Verificar que esté cerrada
        if (!in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
            return back()->withErrors('Esta oportunidad no está cerrada.');
        }

        $request->validate([
            'motivo_reapertura' => 'required|string',
        ]);

        DB::transaction(function () use ($oportunidad, $request) {
            // Guardar etapa anterior
            $etapaAnterior = $oportunidad->etapa_actual;

            // Actualizar oportunidad
            $oportunidad->update([
                'etapa_actual' => 'negotiation',
                'fecha_cierre' => null,
                'monto_final' => null,
                'motivo_cierre' => null,
                'cerrado_por' => null,
            ]);

            // Registrar en bitácora
            BitacoraEtapasOportunidad::create([
                'oportunidad_id' => $oportunidad->id,
                'from_stage' => $etapaAnterior,
                'to_stage' => 'negotiation',
                'movido_por' => auth()->id() ?: 1,
                'comentarios' => 'REAPERTURA: ' . $request->motivo_reapertura,
            ]);

            // Opcional: Marcar todas las cotizaciones como históricas
            // y crear una nueva cotización activa basada en la última aprobada/rechazada
            $ultimaCotizacion = $oportunidad->cotizaciones()
                ->whereIn('estado', ['approved', 'rejected'])
                ->latest()
                ->first();

            if ($ultimaCotizacion) {
                // Marcar todas como históricas
                Cotizacion::where('oportunidad_id', $oportunidad->id)
                    ->update(['estado' => 'historical']);

                // Clonar la última cotización como activa
                $nuevaCotizacion = $ultimaCotizacion->replicate();
                $nuevaCotizacion->estado = 'active';
                $nuevaCotizacion->codigo = 'COT-' . str_pad(Cotizacion::max('id') + 1, 4, '0', STR_PAD_LEFT);
                $nuevaCotizacion->emitida_en = now();
                $nuevaCotizacion->save();

                // Clonar los detalles
                foreach ($ultimaCotizacion->detalles as $detalle) {
                    $nuevoDetalle = $detalle->replicate();
                    $nuevoDetalle->cotizacion_id = $nuevaCotizacion->id;
                    $nuevoDetalle->save();
                }
            }
        });

        return redirect()->route('clients.show', $oportunidad->cliente_id)
            ->with('success', 'Oportunidad reabierta exitosamente.');
    }
}
