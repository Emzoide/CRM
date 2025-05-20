<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\CanalContacto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\VersionVehiculo;
use App\Models\Banco;
use App\Models\Marca;
use App\Models\FiltroConfiguracion;
use App\Models\Rol;
use App\Models\Tienda;
use App\Services\FiltroClienteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;


class ClienteController extends Controller
{
    /**
     * Muestra el listado de clientes y los datos para los modales de crear/editar.
     */
    public function index(Request $request)
    {
        try {
            $usuario = Auth::user();
            $query = Cliente::query();
            
            // Obtener el filtro seleccionado o el predeterminado
            $filtroId = $request->input('filtro_id');
            $filtro = null;
            
            if ($filtroId) {
                // Buscar el filtro solicitado (verificando permisos)
                $filtro = FiltroConfiguracion::disponiblesParaUsuario($usuario)
                    ->where('id', $filtroId)
                    ->first();
            }
            
            // Si no hay filtro específico o no se encontró, usar el predeterminado
            if (!$filtro) {
                $filtro = FiltroConfiguracion::obtenerPredeterminadoParaUsuario($usuario);
            }
            
            // Aplicar el filtro si existe
            if ($filtro) {
                $query = $filtro->aplicarAConsulta($query);
            }
            
            // Procesar filtros personalizados de la solicitud actual
            $filtroPersonalizado = $request->input('filtro_personalizado');
            if ($filtroPersonalizado) {
                $configuracion = json_decode($filtroPersonalizado, true);
                if ($configuracion) {
                    $query = app(FiltroClienteService::class)->aplicarFiltros($query, $configuracion);
                }
            }
            
            // Obtener clientes paginados
            $clientes = $query->paginate(10);
            
            // Para poblar el select del modal "Crear"
            $canales = CanalContacto::orderBy('nombre')->get();
            
            // Obtener filtros disponibles para el usuario
            $filtrosDisponibles = FiltroConfiguracion::disponiblesParaUsuario($usuario)
                ->orderBy('rol_id')
                ->orderBy('orden')
                ->get();
                
            // Obtener roles para filtros
            $roles = Rol::all();
            
            // Obtener tiendas para filtros
            $tiendas = Tienda::all();
            
            return view('clients.index', compact('clientes', 'canales', 'filtrosDisponibles', 'filtro', 'roles', 'tiendas'));
            
        } catch (\Exception $e) {
            Log::error('Error en ClienteController@index: ' . $e->getMessage());
            return back()->withErrors('Ha ocurrido un error al cargar los clientes.');
        }
    }

    /**
     * Almacena un cliente nuevo (desde el modal de index).
     */
    public function store(Request $request)
    {
        // Validación para todos los campos del cliente
        $data = $request->validate([
            'dni_ruc'    => 'required|string|max:15|unique:clientes,dni_ruc,' . ($cliente->id ?? 'NULL'),
            'nombre'     => 'required|string|max:100',
            'fec_nac'    => 'nullable|date',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:100',
        ]);

        // Guardamos todos los datos del cliente
        $cliente = Cliente::create($data);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    /**
     * Muestra el detalle de un cliente (página separada).
     */
    public function show(Cliente $cliente)
    {
        try {
            $oportunidades = $cliente->oportunidades()
                ->with(['seguimientos' => function ($query) {
                    $query->orderBy('contacto_en', 'desc');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            // Obtener la oportunidad activa (si existe)
            $activa = $oportunidades->first(function ($oportunidad) {
                return !in_array($oportunidad->etapa_actual, ['won', 'lost']);
            });

            // Obtener todas las versiones de vehículos para el formulario de primer contacto
            $versiones = VersionVehiculo::with(['modelo.marca'])->orderBy('nombre')->get();
            $bancos = Banco::orderBy('nombre')->get();

            return view('clients.show', compact('cliente', 'oportunidades', 'activa', 'versiones', 'bancos'));
        } catch (\Exception $e) {
            Log::error('Error en ClienteController@show: ' . $e->getMessage());
            return back()->withErrors('Ha ocurrido un error al cargar los datos del cliente.');
        }
    }

    /**
     * Actualiza un cliente existente (desde el modal de index).
     */
    public function update(Request $request, Cliente $cliente)
    {
        // Validación para todos los campos del cliente
        $data = $request->validate([
            'dni_ruc'    => 'required|string|max:15|unique:clientes,dni_ruc,' . $cliente->id,
            'nombre'     => 'required|string|max:100',
            'fec_nac'    => 'nullable|date',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:100',
        ]);

        // Actualizamos todos los campos del cliente
        $cliente->update($data);
        
        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Elimina un cliente (desde el modal de index).
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }
    public function edit(Cliente $cliente)
    {
        $canales = CanalContacto::orderBy('nombre')->get();
        // Esta vista será solo el contenido del modal: encabezado + form
        return view('clients.partials.edit_modal', compact('cliente', 'canales'));
    }
}
