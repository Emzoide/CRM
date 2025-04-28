<?php
namespace App\Http\Controllers;

use App\Models\BitacoraEtapasOportunidad;
use Illuminate\Http\Request;

class BitacoraEtapasOportunidadController extends Controller
{
    public function index()
    {
        $logs = BitacoraEtapasOportunidad::all();
        // TODO: return view('bitacora_etapas.index', compact('logs'));
        return redirect('/');
    }

    public function create()
    {
        // TODO: return view('bitacora_etapas.create');
        return redirect('/');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'oportunidad_id' => 'required|exists:oportunidades,id',
            'from_stage'     => 'nullable|in:new,quote_sent,negotiation,won,lost',
            'to_stage'       => 'required|in:new,quote_sent,negotiation,won,lost',
            'movido_por'     => 'nullable|exists:usuarios,id',
        ]);
        BitacoraEtapasOportunidad::create($data);
        return redirect('/');
    }

    public function show(BitacoraEtapasOportunidad $bitacora)
    {
        // TODO: return view('bitacora_etapas.show', compact('bitacora'));
        return redirect('/');
    }

    public function edit(BitacoraEtapasOportunidad $bitacora)
    {
        // TODO: return view('bitacora_etapas.edit', compact('bitacora'));
        return redirect('/');
    }

    public function update(Request $request, BitacoraEtapasOportunidad $bitacora)
    {
        $data = $request->validate([
            'from_stage' => 'nullable|in:new,quote_sent,negotiation,won,lost',
            'to_stage'   => 'required|in:new,quote_sent,negotiation,won,lost',
        ]);
        $bitacora->update($data);
        return redirect('/');
    }

    public function destroy(BitacoraEtapasOportunidad $bitacora)
    {
        $bitacora->delete();
        return redirect('/');
    }
}
