@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/admin/panels.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="admin-container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Gestión de Tiendas y Sucursales</h2>
                </div>

                <div class="admin-card-body">
                    @if (session('success'))
                    <div class="admin-alert admin-alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    <!-- Pestañas -->
                    <ul class="nav nav-tabs admin-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tiendas-tab" data-bs-toggle="tab" data-bs-target="#tiendas" type="button" role="tab">
                                <i class="fas fa-store admin-icon"></i> Tiendas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sucursales-tab" data-bs-toggle="tab" data-bs-target="#sucursales" type="button" role="tab">
                                <i class="fas fa-building admin-icon"></i> Sucursales
                            </button>
                        </li>
                    </ul>

                    <!-- Contenido de las pestañas -->
                    <div class="tab-content" id="myTabContent">
                        <!-- Pestaña de Tiendas -->
                        <div class="tab-pane fade show active" id="tiendas" role="tabpanel">
                            <div class="admin-section-header">
                                <h3 class="admin-section-title">Lista de Tiendas</h3>
                                <button type="button" class="admin-btn admin-btn-primary" data-bs-toggle="modal" data-bs-target="#crearTiendaModal">
                                    <i class="fas fa-plus admin-icon"></i> Nueva Tienda
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table admin-table">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Dirección</th>
                                            <th>Sucursal</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tiendas as $tienda)
                                        <tr>
                                            <td>{{ $tienda->nombre }}</td>
                                            <td>{{ $tienda->direccion }}</td>
                                            <td>
                                                <span class="admin-badge admin-badge-primary">
                                                    {{ $tienda->sucursal->nombre }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="admin-action-buttons">
                                                    <button type="button" class="admin-btn admin-btn-warning admin-btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editarTiendaModal{{ $tienda->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.tiendas.destroy', $tienda) }}" method="POST" class="admin-inline-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta tienda?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pestaña de Sucursales -->
                        <div class="tab-pane fade" id="sucursales" role="tabpanel">
                            <div class="admin-section-header">
                                <h3 class="admin-section-title">Lista de Sucursales</h3>
                                <button type="button" class="admin-btn admin-btn-primary" data-bs-toggle="modal" data-bs-target="#crearSucursalModal">
                                    <i class="fas fa-plus admin-icon"></i> Nueva Sucursal
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table admin-table">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sucursales as $sucursal)
                                        <tr>
                                            <td>{{ $sucursal->nombre }}</td>
                                            <td>
                                                <div class="admin-action-buttons">
                                                    <button type="button" class="admin-btn admin-btn-warning admin-btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editarSucursalModal{{ $sucursal->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.sucursales.destroy', $sucursal) }}" method="POST" class="admin-inline-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta sucursal?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Tienda -->
<div class="modal fade admin-modal" id="crearTiendaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Tienda</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.tiendas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="nombre" class="admin-custom-label admin-required-field">Nombre</label>
                        <input type="text" class="admin-custom-input" id="nombre" name="nombre" required>
                        @error('nombre')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="direccion" class="admin-custom-label admin-required-field">Dirección</label>
                        <input type="text" class="admin-custom-input" id="direccion" name="direccion" required>
                        @error('direccion')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="sucursal_id" class="admin-custom-label admin-required-field">Sucursal</label>
                        <select class="admin-custom-select" id="sucursal_id" name="sucursal_id" required>
                            <option value="">Seleccione una sucursal</option>
                            @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sucursal_id')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Crear Sucursal -->
<div class="modal fade admin-modal" id="crearSucursalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Sucursal</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sucursales.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="nombre" class="admin-custom-label admin-required-field">Nombre</label>
                        <input type="text" class="admin-custom-input" id="nombre" name="nombre" required>
                        @error('nombre')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modales de Edición de Tiendas -->
@foreach($tiendas as $tienda)
<div class="modal fade admin-modal" id="editarTiendaModal{{ $tienda->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Tienda</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.tiendas.update', $tienda) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="nombre" class="admin-custom-label admin-required-field">Nombre</label>
                        <input type="text" class="admin-custom-input" id="nombre" name="nombre" value="{{ $tienda->nombre }}" required>
                        @error('nombre')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="direccion" class="admin-custom-label admin-required-field">Dirección</label>
                        <input type="text" class="admin-custom-input" id="direccion" name="direccion" value="{{ $tienda->direccion }}" required>
                        @error('direccion')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="sucursal_id" class="admin-custom-label admin-required-field">Sucursal</label>
                        <select class="admin-custom-select" id="sucursal_id" name="sucursal_id" required>
                            @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" {{ $tienda->sucursal_id == $sucursal->id ? 'selected' : '' }}>
                                {{ $sucursal->nombre }}
                            </option>
                            @endforeach
                        </select>
                        @error('sucursal_id')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Modales de Edición de Sucursales -->
@foreach($sucursales as $sucursal)
<div class="modal fade admin-modal" id="editarSucursalModal{{ $sucursal->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Sucursal</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sucursales.update', $sucursal) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="nombre" class="admin-custom-label admin-required-field">Nombre</label>
                        <input type="text" class="admin-custom-input" id="nombre" name="nombre" value="{{ $sucursal->nombre }}" required>
                        @error('nombre')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar las pestañas usando Bootstrap 5
        var triggerTabList = [].slice.call(document.querySelectorAll('#myTab button'))
        triggerTabList.forEach(function (tabEl) {
            // Crear nueva instancia de Tab para cada elemento
            new bootstrap.Tab(tabEl)
        })

        // Activar la primera pestaña por defecto
        var firstTab = document.querySelector('#tiendas-tab')
        if (firstTab) {
            var bsTab = new bootstrap.Tab(firstTab)
            bsTab.show()
        }

        // Inicializar los modales
        var modalElements = document.querySelectorAll('.modal')
        modalElements.forEach(function (modalEl) {
            new bootstrap.Modal(modalEl)
        })

        // Animaciones para las tablas
        var tableRows = document.querySelectorAll('.admin-table tbody tr')
        tableRows.forEach(function(row, index) {
            row.style.animationDelay = (index * 0.1) + 's'
            row.classList.add('fade-in')
        })
    })
</script>
@endpush