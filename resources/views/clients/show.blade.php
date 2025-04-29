{{-- resources/views/clients/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6 space-y-8">

  {{-- Volver --}}
  <div class="mb-6">
    <a href="{{ route('clients.index') }}"
      class="btn btn-outline-primary inline-flex items-center space-x-2" style="text-decoration: none;">
      <i class="fas fa-arrow-left"></i>
      <span>Volver a Clientes</span>
    </a>
  </div>

  {{-- 1. Ficha del Cliente --}}
  <div class="card">
    <div class="card-header flex items-center space-x-4">
      <div class="p-4 bg-blue-100 rounded-full text-blue-600 text-2xl">
        <i class="fas fa-user" style="margin: auto"></i>
      </div>
      <div>
        <h1 class="cliente-nombre">
          {{ $cliente->nombre }}
          @if($cliente->occupation)
          <span class="cliente-ocupacion">({{ $cliente->occupation }})</span>
          @endif
        </h1>
        <div class="mt-1 space-y-1 text-gray-600">
          <div>DNI/RUC: <span class="font-medium">{{ $cliente->dni_ruc }}</span></div>
          <div>Canal: <span class="font-medium">{{ $cliente->canal->nombre ?? '—' }}</span></div>
          @if($cliente->fec_nac)
          <div class="flex items-center space-x-2">
            <span>Fecha de Nacimiento:</span>
            <span class="font-medium">
              {{ \Carbon\Carbon::parse($cliente->fec_nac)->format('d/m/Y') }}
              @if(\Carbon\Carbon::parse($cliente->fec_nac)->format('m-d') == now()->format('m-d'))
              <span class="ml-2 text-pink-500 animate-bounce">
                <i class="fas fa-birthday-cake"></i>
                <span class="text-sm">¡Feliz Cumpleaños!</span>
              </span>
              @endif
            </span>
          </div>
          @endif
          <div class="text-sm text-gray-500">
            Creado: {{ optional($cliente->created_at)->format('d/m/Y H:i') ?? '—' }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- 2. Timeline de Oportunidades y Seguimientos --}}
  <div class="card">
    <div class="card-header justify-between">
      <h2 class="text-xl font-semibold">Oportunidades de Venta</h2>
      <div class="flex space-x-2">
        @php
        $tieneOportunidadActiva = $oportunidades->whereNotIn('etapa_actual', ['won', 'lost'])->isNotEmpty();
        $oportunidadActiva = $oportunidades->whereNotIn('etapa_actual', ['won', 'lost'])->first();
        @endphp

        @if(!$tieneOportunidadActiva)
        <button
          type="button"
          onclick="openNuevaOportunidadModal(true)"
          class="btn btn-success inline-flex items-center space-x-2"
          style="text-decoration: none;">
          <i class="fas fa-plus-circle mr-2"></i>
          <span>Nueva Oportunidad</span>
        </button>
        @endif
      </div>
    </div>

  </div>
  <div class="card-body">
    <div class="relative">
      {{-- Línea vertical del timeline --}}
      <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

      {{-- Lista de oportunidades --}}
      <div class="space-y-6">
        @forelse($oportunidades as $opp)
        <div class="oportunidad {{ $opp->etapa_actual }}">
          {{-- Punto del timeline --}}
          <div class="oportunidad-marker {{ $opp->etapa_actual }}"></div>

          {{-- Cabecera de la oportunidad --}}
          <div class="oportunidad-header {{ $opp->etapa_actual }}">
            <div class="oportunidad-title">
              <span>Oportunidad #{{ $opp->id }} — {{ $opp->created_at->format('d/m/Y') }}</span>
              <span class="oportunidad-badge {{ $opp->etapa_actual }}">
                {{ traducirEstado($opp->etapa_actual) }}
              </span>
              @if($opp->id == ($oportunidadActiva ? $oportunidadActiva->id : null))
              <div class="flex space-x-2">
                <button
                  type="button"
                  onclick="openCierreModal({{ $opp->id }})"
                  class="btn-cierre-oportunidad">
                  <i class="fas fa-flag-checkered"></i>
                  <span>Cerrar Oportunidad</span>
                </button>

                @if($opp->cotizacion_activa)
                <button
                  type="button"
                  onclick="openSeguimientoModal({{ $opp->id }}, false)"
                  class="btn-seguimiento-oportunidad">
                  <i class="fas fa-comment-dots"></i>
                  <span>Nuevo Seguimiento</span>
                </button>
                <button
                  type="button"
                  onclick="openCotizacionActivaModal({{ $opp->id }})"
                  class="btn-cotizacion-activa">
                  <i class="fas fa-file-invoice"></i>
                  <span>Ver Cotización Activa</span>
                </button>
                @else
                <button
                  type="button"
                  onclick="openNuevaCotizacionModal({{ $opp->id }})"
                  class="btn-nueva-cotizacion">
                  <i class="fas fa-plus-circle"></i>
                  <span>Nueva Cotización</span>
                </button>
                @endif
              </div>
              @endif
            </div>
            <div class="oportunidad-toggle">
              <i class="fas fa-chevron-down"></i>
            </div>
          </div>

          {{-- Contenido de la oportunidad --}}
          <div class="oportunidad-content">
            {{-- Información de cierre si está cerrada --}}
            @if(in_array($opp->etapa_actual, ['won','lost']) || !in_array($opp->etapa_actual, ['won','lost']))
            <div class="cierre-info">
              <div class="flex justify-between items-center mb-4">
                <h4 class="cierre-info-title">
                  @if(in_array($opp->etapa_actual, ['won','lost']))
                  Información de Cierre
                  @else
                  Información de Oportunidad
                  @if (!$opp->cotizacion_activa)
                  <p class="text-red-500" style="font-size: 0.875rem; font-weight: 600; font-style: italic;">
                    <i class="fas fa-exclamation-triangle"></i>
                    No hay una cotización activa
                  </p>
                  @endif
                  @endif
                </h4>
                <button
                  type="button"
                  onclick="openHistorialCotizacionesModal({{ $opp->id }})"
                  class="btn-historial-cotizaciones">
                  <i class="fas fa-history"></i>
                  <span>Ver Historial de Cotizaciones</span>
                </button>
              </div>
              @if(in_array($opp->etapa_actual, ['won','lost']))
              <div class="cierre-info-grid">
                <div class="cierre-info-item">
                  <span class="cierre-info-label">Fecha de cierre:</span>
                  <span class="cierre-info-value">{{ optional($opp->fecha_cierre)->format('d/m/Y H:i') ?? 'No disponible' }}</span>
                </div>
                @if($opp->etapa_actual == 'won')
                <div class="cierre-info-item">
                  <span class="cierre-info-label">Monto final:</span>
                  <span class="cierre-info-value">S/ {{ number_format($opp->monto_final, 2) }}</span>
                </div>
                @endif
                <div class="cierre-info-item cierre-info-full">
                  <span class="cierre-info-label">Motivo:</span>
                  <span class="cierre-info-value">{{ $opp->motivo_cierre ?? 'No especificado' }}</span>
                </div>
                <div class="cierre-info-item cierre-info-full">
                  <span class="cierre-info-label">Cerrado por:</span>
                  <span class="cierre-info-value">{{ optional($opp->cerrador)->name ?? 'Sistema' }}</span>
                </div>
              </div>
              @endif
            </div>
            @endif

            {{-- Seguimientos de la oportunidad --}}
            @if($opp->seguimientos->isNotEmpty())
            <div class="seguimientos-container">
              <div class="seguimientos-timeline">
                <div class="seguimientos-line"></div>
                @php
                $prevCotizacionId = null;
                @endphp
                @foreach($opp->seguimientos as $seg)
                @if($prevCotizacionId !== null && $prevCotizacionId !== $seg->cotizacion_id)
                <div class="cotizacion-divider">
                  <span>Se cambió de cotización</span>
                </div>
                @endif
                <div class="seguimiento">
                  <div class="seguimiento-marker"></div>
                  <div class="seguimiento-content">
                    <div class="seguimiento-header">
                      <span class="text-gray-600">{{ $seg->contacto_en->format('d/m/Y H:i') }}</span>
                      <span class="seguimiento-tipo {{ $seg->resultado }}">
                        {{ strtoupper($seg->resultado) }}
                      </span>
                    </div>
                    <p class="seguimiento-comentario">{{ $seg->comentario }}</p>
                    @if($seg->proxima_accion)
                    <p class="seguimiento-accion">
                      Próxima acción: {{ $seg->proxima_accion }}
                    </p>
                    @endif
                  </div>
                </div>
                @php
                $prevCotizacionId = $seg->cotizacion_id;
                @endphp
                @endforeach
              </div>
            </div>
            @else
            <div class="empty-state">
              <p class="empty-state-text">— Sin seguimientos aún —</p>
            </div>
            @endif
          </div>
        </div>
        @empty
        <div class="empty-state">
          <p class="empty-state-text">No hay oportunidades registradas.</p>
          <p class="empty-state-subtext">Crea un seguimiento para iniciar una nueva oportunidad.</p>
        </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

</div>
@include('clients.partials.seguimiento_modal')
@include('clients.partials.cierre_modal')
@include('clients.partials.cotizacion_activa_modal')
@include('clients.partials.historial_cotizaciones_modal')
@include('clients.partials.nueva_cotizacion_modal')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/timeline.css') }}">
<style>
  .btn-seguimiento-oportunidad {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  }

  .btn-seguimiento-oportunidad:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15), 0 3px 5px -1px rgba(0, 0, 0, 0.1);
  }

  .btn-cierre-oportunidad {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  }

  .btn-cierre-oportunidad:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15), 0 3px 5px -1px rgba(0, 0, 0, 0.1);
  }

  .oportunidad-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
  }

  .oportunidad-badge.negotiation {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
  }

  .oportunidad-badge.won {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
  }

  .oportunidad-badge.lost {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
  }

  .oportunidad-badge.prospecting {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
  }

  .oportunidad-badge.qualification {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
  }

  .oportunidad-badge.proposal {
    background: linear-gradient(135deg, #ec4899, #db2777);
    color: white;
  }

  .oportunidad-badge.closing {
    background: linear-gradient(135deg, #06b6d4, #0891b2);
    color: white;
  }

  .btn-cotizacion-activa {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  }

  .btn-cotizacion-activa:hover {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15), 0 3px 5px -1px rgba(0, 0, 0, 0.1);
  }

  .btn-historial-cotizaciones {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #6b7280, #4b5563);
    color: white;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    margin-left: 1rem;
  }

  .btn-historial-cotizaciones:hover {
    background: linear-gradient(135deg, #4b5563, #374151);
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15), 0 3px 5px -1px rgba(0, 0, 0, 0.1);
  }

  .btn-nueva-cotizacion {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  }

  .btn-nueva-cotizacion:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-1px);
    box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15), 0 3px 5px -1px rgba(0, 0, 0, 0.1);
  }

  .cierre-info-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
  }

  .cliente-nombre {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0;
    color: #1f2937;
  }

  .cliente-ocupacion {
    font-size: 1.1rem;
    font-weight: 400;
    color: #6b7280;
    margin-left: 0.5em;
  }
</style>
@endpush
@push('scripts')
<script src="{{ asset('js/timeline.js') }}"></script>
<script>
  // Scripts para el modal de seguimiento
  function openSeguimientoModal(oportunidadId, sinOportunidadActiva) {
    const modal = document.getElementById('seguimientoModal');
    if (!modal) {
      console.error("No se encontró el modal de seguimiento");
      return;
    }

    // Establecer el ID de la oportunidad en el formulario
    document.querySelector('#seguimientoForm input[name="oportunidad_id"]').value = oportunidadId;

    // Mostrar/ocultar campos de cotización según sinOportunidadActiva
    const camposCotizacion = document.getElementById('camposCotizacion');
    if (camposCotizacion) {
      camposCotizacion.style.display = sinOportunidadActiva ? 'block' : 'none';
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function closeSeguimientoModal() {
    const modal = document.getElementById('seguimientoModal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
    const form = document.getElementById('seguimientoForm');
    if (form) form.reset();
  }

  // Scripts para el modal de cierre
  function openCierreModal(oportunidadId) {
    const modal = document.getElementById('cierreModal');
    if (!modal) {
      console.error("No se encontró el modal de cierre");
      return;
    }
    document.querySelector('#cierreForm input[name="oportunidad_id"]').value = oportunidadId;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function closeCierreModal() {
    const modal = document.getElementById('cierreModal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
    const form = document.getElementById('cierreForm');
    if (form) form.reset();
  }

  // Para nueva oportunidad
  function openNuevaOportunidadModal(sinOportunidadActiva) {
    console.log('openNuevaOportunidadModal');
    const modal = document.getElementById('seguimientoModal');
    if (!modal) {
      console.error("No se encontró el modal de seguimiento");
      return;
    }

    // Limpiar el ID de oportunidad para nueva oportunidad
    document.querySelector('#seguimientoForm input[name="oportunidad_id"]').value = '';

    // Mostrar campos de cotización para nueva oportunidad
    const camposCotizacion = document.getElementById('camposCotizacion');
    if (camposCotizacion) {
      camposCotizacion.style.display = 'block';
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  // Cerrar modales al hacer clic fuera o presionar ESC
  window.addEventListener('click', e => {
    if (e.target.id === 'seguimientoModal') closeSeguimientoModal();
    if (e.target.id === 'cierreModal') closeCierreModal();
    if (e.target.id === 'cotizacionActivaModal') closeCotizacionActivaModal();
  });

  window.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      closeSeguimientoModal();
      closeCierreModal();
      closeCotizacionActivaModal();
    }
  });

  document.addEventListener('DOMContentLoaded', () => {
    // Inicialización de componentes si es necesario
  });

  @php

  function traducirEstado($estado) {
    $estados = [
      'negotiation' => 'En Negociación',
      'won' => 'Ganada',
      'lost' => 'Perdida',
      'prospecting' => 'Prospección',
      'qualification' => 'Calificación',
      'proposal' => 'Propuesta',
      'closing' => 'Cierre'
    ];
    return $estados[$estado] ?? ucfirst(str_replace('_', ' ', $estado));
  }
  @endphp

  function openCotizacionActivaModal(oportunidadId) {
    console.log('aqui se esta abriendo el modal de show.blade.php');
    const modal = document.getElementById('cotizacionActivaModal');
    if (!modal) {
      console.error("No se encontró el modal de cotización activa");
      return;
    }

    // Limpiar el contenido anterior
    const cotizacionContent = document.getElementById('cotizacionContent');
    if (cotizacionContent) {
      cotizacionContent.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Cargando cotización...</div>';
    }

    // Mostrar el modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Cargar los datos de la cotización activa
    fetch(`/api/oportunidades/${oportunidadId}/cotizacion-activa`)
      .then(response => {
        if (!response.ok) {
          throw new Error('No se pudo cargar la cotización');
        }
        return response.json();
      })
      .then(data => {
        if (data.success && data.data.cotizacion_activa) {
          const cotizacion = data.data.cotizacion_activa;
          const cliente = data.data.cotizacion_activa.cliente;
          const vehiculo = data.data.cotizacion_activa.vehiculo_detalle;
          const banco = data.data.cotizacion_activa.banco;

          cotizacionActualId = cotizacion.id; // Guardar el ID de la cotización actual
          console.log('cotizacionActualId: ', cotizacionActualId);
          // Actualizar el contenido del modal
          if (cotizacionContent) {
            cotizacionContent.innerHTML = `
              <div class="cotizacion-info">
                <div class="cotizacion-info-header">
                  <h3 class="cotizacion-info-title">Cotización ${cotizacion.codigo}</h3>
                  <div class="cotizacion-info-date">Emitida: ${new Date(cotizacion.emitida_en).toLocaleDateString()}</div>
                </div>
                <div class="cotizacion-info-section">
                  <h4 class="cotizacion-info-subtitle">Información del Cliente</h4>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Nombre</div>
                    <div class="cotizacion-info-value">${cliente.nombre}</div>
                  </div>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">DNI/RUC</div>
                    <div class="cotizacion-info-value">${cliente.dni_ruc}</div>
                  </div>
                </div>
                <div class="cotizacion-info-section">
                  <h4 class="cotizacion-info-subtitle">Información del Vehículo</h4>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Marca</div>
                    <div class="cotizacion-info-value">${vehiculo.marca.nombre}</div>
                  </div>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Modelo</div>
                    <div class="cotizacion-info-value">${vehiculo.modelo.nombre}</div>
                  </div>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Versión</div>
                    <div class="cotizacion-info-value">${vehiculo.version.nombre}</div>
                  </div>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Cantidad</div>
                    <div class="cotizacion-info-value">${vehiculo.cantidad}</div>
                  </div>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Precio Unitario</div>
                    <div class="cotizacion-info-value">S/ ${Number(vehiculo.precio_unit).toFixed(2)}</div>
                  </div>
                </div>
                <div class="cotizacion-info-section">
                  <h4 class="cotizacion-info-subtitle">Detalles de la Cotización</h4>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Vendedor</div>
                    <div class="cotizacion-info-value">${cotizacion.vendedor.full_name}</div>
                  </div>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Monto</div>
                    <div class="cotizacion-info-value">S/ ${Number(cotizacion.total).toFixed(2)}</div>
                  </div>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Tipo de Compra</div>
                    <div class="cotizacion-info-value">${cotizacion.tipo_compra === 'credito' ? 'Crédito' : 'Contado'}</div>
                  </div>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Banco</div>
                    <div class="cotizacion-info-value">${banco ? banco.nombre : 'No aplica'}</div>
                  </div>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Compra a Plazos</div>
                    <div class="cotizacion-info-value">${cotizacion.compra_plazos ? 'Sí' : 'No'} ${cotizacion.razon_no_plazos ? `(${cotizacion.razon_no_plazos})` : ''}</div>
                  </div>
                  <div class="cotizacion-info-item">
                    <div class="cotizacion-info-label">Seguro Vehicular</div>
                    <div class="cotizacion-info-value">${cotizacion.seguro_vehicular ? 'Sí' : 'No'} ${cotizacion.razon_no_seguro ? `(${cotizacion.razon_no_seguro})` : ''}</div>
                  </div>
                </div>
              </div>
            `;
          }
        } else {
          if (cotizacionContent) {
            cotizacionContent.innerHTML = '<div class="text-center p-4 text-red-500">No se encontró una cotización activa para esta oportunidad.</div>';
          }
        }
      })
      .catch(error => {
        console.error('Error al cargar la cotización:', error);
        if (cotizacionContent) {
          cotizacionContent.innerHTML = '<div class="text-center p-4 text-red-500">Error al cargar la cotización. Por favor, intente nuevamente.</div>';
        }
      });
  }

  // Función para cerrar el modal de cotización activa
  function closeCotizacionActivaModal() {
    const modal = document.getElementById('cotizacionActivaModal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }
</script>
@endpush