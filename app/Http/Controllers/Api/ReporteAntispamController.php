<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consentimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReporteAntispamController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Iniciando búsqueda de consentimientos', [
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'estado' => $request->estado
            ]);

            $query = Consentimiento::query();

            // Solo aplicar filtros si se proporcionan
            if ($request->has('fecha_inicio') && $request->fecha_inicio) {
                $query->whereDate('fecha_aceptacion', '>=', $request->fecha_inicio);
            }

            if ($request->has('fecha_fin') && $request->fecha_fin) {
                $query->whereDate('fecha_aceptacion', '<=', $request->fecha_fin);
            }

            // Solo aplicar filtro de estado si se selecciona uno específico
            if ($request->has('estado') && $request->estado !== '') {
                if ($request->estado == '1') {
                    // Aceptado: ambos deben ser true
                    $query->where('acepta_politicas', 1)
                        ->where('acepta_comunicaciones', 1);
                } else if ($request->estado == '0') {
                    // Rechazado: al menos uno debe ser false
                    $query->where(function ($q) {
                        $q->where('acepta_politicas', 0)
                            ->orWhere('acepta_comunicaciones', 0);
                    });
                }
            }

            // Ordenar por fecha de aceptación descendente
            $query->orderBy('fecha_aceptacion', 'desc');

            // Paginar resultados
            $perPage = $request->input('per_page', 10);
            $consentimientos = $query->paginate($perPage);

            Log::info('Resultados encontrados', [
                'total' => $consentimientos->total(),
                'current_page' => $consentimientos->currentPage(),
                'per_page' => $consentimientos->perPage(),
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            return response()->json([
                'success' => true,
                'data' => $consentimientos->items(),
                'current_page' => $consentimientos->currentPage(),
                'last_page' => $consentimientos->lastPage(),
                'total' => $consentimientos->total(),
                'per_page' => $consentimientos->perPage()
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener consentimientos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los consentimientos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
