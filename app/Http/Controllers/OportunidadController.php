<?php

namespace App\Http\Controllers;

use App\Models\Oportunidad;
use App\Models\BitacoraEtapasOportunidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class OportunidadController extends Controller
{
    public function index()
    {
        $oportunidades = Oportunidad::all();
        // TODO: return view('oportunidades.index', compact('oportunidades'));
        return redirect('/');
    }

    public function create()
    {
        // TODO: return view('oportunidades.create');
        return redirect('/');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'      => 'required|exists:clientes,id',
            'canal_fuente_id' => 'nullable|exists:canales_contacto,id',
            'etapa_actual'    => 'required|in:new,quote_sent,negotiation',
            'probabilidad'    => 'nullable|integer|min:0|max:100',
        ]);

        // ———————————————— CHECK: ¿ya existe activa? ————————————————
        $tieneActiva = \App\Models\Oportunidad::where('cliente_id', $data['cliente_id'])
            ->whereNotIn('etapa_actual', ['won', 'lost'])
            ->exists();

        if ($tieneActiva) {
            return back()
                ->withInput()
                ->withErrors(['cliente_id' => 'Este cliente ya tiene una oportunidad activa.']);
        }
        // ——————————————————————————————————————————————————————————————

        \App\Models\Oportunidad::create($data);

        return redirect()->route('clients.show', $data['cliente_id'])
            ->with('success', 'Oportunidad creada.');
    }


    public function show(Oportunidad $oportunidad)
    {
        // TODO: return view('oportunidades.show', compact('oportunidad'));
        return redirect('/');
    }

    public function edit(Oportunidad $oportunidad)
    {
        // TODO: return view('oportunidades.edit', compact('oportunidad'));
        return redirect('/');
    }

    public function update(Request $request, Oportunidad $oportunidad)
    {
        if (in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
            return back()->withErrors('No se puede modificar una oportunidad cerrada.');
        }
        $data = $request->validate([
            'cliente_id'      => 'required|exists:clientes,id',
            'canal_fuente_id' => 'nullable|exists:canales_contacto,id',
            'etapa_actual'    => 'required|in:new,quote_sent,negotiation',
            'probabilidad'    => 'nullable|integer|min:0|max:100',
        ]);
        $oportunidad->update($data);
        return redirect('/');
    }

    public function destroy(Oportunidad $oportunidad)
    {
        $oportunidad->delete();
        return redirect('/');
    }

    /**
     * Muestra el formulario para cerrar una oportunidad
     */
    public function showCierre(Oportunidad $oportunidad)
    {
        if (in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
            return back()->withErrors('Esta oportunidad ya está cerrada.');
        }

        return view('oportunidades.cierre', compact('oportunidad'));
    }

    /**
     * Procesa el cierre de una oportunidad
     */
    public function procesarCierre(Request $request, Oportunidad $oportunidad)
    {
        if (in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
            return back()->withErrors('Esta oportunidad ya está cerrada.');
        }

        $data = $request->validate([
            'resultado' => 'required|in:won,lost',
            'fecha_cierre' => 'required|date',
            'monto_final' => 'required_if:resultado,won|nullable|numeric|min:0',
            'motivo_cierre' => 'required|string',
        ]);

        DB::transaction(function () use ($oportunidad, $data) {
            // Guardar etapa anterior
            $etapaAnterior = $oportunidad->etapa_actual;

            // Actualizar oportunidad
            $oportunidad->update([
                'etapa_actual' => $data['resultado'],
                'fecha_cierre' => $data['fecha_cierre'],
                'monto_final' => $data['monto_final'],
                'motivo_cierre' => $data['motivo_cierre'],
                'cerrado_por' => auth()->id(),
            ]);

            // Registrar en bitácora
            BitacoraEtapasOportunidad::create([
                'oportunidad_id' => $oportunidad->id,
                'from_stage' => $etapaAnterior,
                'to_stage' => $data['resultado'],
                'movido_por' => auth()->id(),
            ]);
        });

        return redirect()->route('oportunidades.show', $oportunidad)
            ->with('success', 'Oportunidad cerrada exitosamente.');
    }

    /**
     * Obtiene la cotización activa de una oportunidad
     */
    public function getCotizacionActiva(Oportunidad $oportunidad)
    {
        if (in_array($oportunidad->etapa_actual, ['won', 'lost'])) {
            return response()->json([
                'success' => false,
                'message' => 'Esta oportunidad ya está cerrada'
            ], 400);
        }

        try {
            $cotizacion = $oportunidad->load([
                'cliente:id,nombre',
                'cotizaciones' => function ($query) {
                    $query->where('estado', 'active')
                        ->with(['detalles.versionVehiculo.modelo.marca']);
                }
            ]);

            // Obtener la primera cotización activa
            $cotizacionActiva = $cotizacion->cotizaciones->first();

            return response()->json([
                'success' => true,
                'data' => array_merge($cotizacion->toArray(), [
                    'cotizacion_activa' => $cotizacionActiva
                ])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cargar cotización activa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar la cotización'
            ], 500);
        }
    }
}
