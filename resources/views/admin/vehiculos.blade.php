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
                    <h2>Gestión de Vehículos</h2>
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
                            <button class="nav-link active" id="marcas-tab" data-bs-toggle="tab" data-bs-target="#marcas" type="button" role="tab">
                                <i class="fas fa-car admin-icon"></i> Marcas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="modelos-tab" data-bs-toggle="tab" data-bs-target="#modelos" type="button" role="tab">
                                <i class="fas fa-car-side admin-icon"></i> Modelos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="versiones-tab" data-bs-toggle="tab" data-bs-target="#versiones" type="button" role="tab">
                                <i class="fas fa-car-battery admin-icon"></i> Versiones
                            </button>
                        </li>
                    </ul>

                    <!-- Contenido de las pestañas -->
                    <div class="tab-content" id="myTabContent">
                        <!-- Pestaña de Marcas -->
                        <div class="tab-pane fade show active" id="marcas" role="tabpanel">
                            <div class="admin-section-header">
                                <h3 class="admin-section-title">Lista de Marcas</h3>
                                <button type="button" class="admin-btn admin-btn-primary" data-bs-toggle="modal" data-bs-target="#crearMarcaModal">
                                    <i class="fas fa-plus admin-icon"></i> Nueva Marca
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
                                        @foreach($marcas as $marca)
                                        <tr>
                                            <td>{{ $marca->nombre }}</td>
                                            <td>
                                                <div class="admin-action-buttons">
                                                    <button type="button" class="admin-btn admin-btn-warning admin-btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editarMarcaModal{{ $marca->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.vehiculos.marca.destroy', $marca) }}" method="POST" class="admin-inline-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta marca?')">
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

                        <!-- Pestaña de Modelos -->
                        <div class="tab-pane fade" id="modelos" role="tabpanel">
                            <div class="admin-section-header">
                                <h3 class="admin-section-title">Lista de Modelos</h3>
                                <button type="button" class="admin-btn admin-btn-primary" data-bs-toggle="modal" data-bs-target="#crearModeloModal">
                                    <i class="fas fa-plus admin-icon"></i> Nuevo Modelo
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table admin-table">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Marca</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modelos as $modelo)
                                        <tr>
                                            <td>{{ $modelo->nombre }}</td>
                                            <td>
                                                <span class="admin-badge admin-badge-primary">
                                                    {{ $modelo->marca->nombre }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="admin-action-buttons">
                                                    <button type="button" class="admin-btn admin-btn-warning admin-btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editarModeloModal{{ $modelo->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.vehiculos.modelo.destroy', $modelo) }}" method="POST" class="admin-inline-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este modelo?')">
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

                        <!-- Pestaña de Versiones -->
                        <div class="tab-pane fade" id="versiones" role="tabpanel">
                            <div class="admin-section-header">
                                <h3 class="admin-section-title">Lista de Versiones</h3>
                                <button type="button" class="admin-btn admin-btn-primary" data-bs-toggle="modal" data-bs-target="#crearVersionModal">
                                    <i class="fas fa-plus admin-icon"></i> Nueva Versión
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table admin-table">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Modelo</th>
                                            <th>Marca</th>
                                            <th>Año</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($versiones as $version)
                                        <tr>
                                            <td>{{ $version->nombre }}</td>
                                            <td>{{ $version->modelo->nombre }}</td>
                                            <td>
                                                <span class="admin-badge admin-badge-primary">
                                                    {{ $version->modelo->marca->nombre }}
                                                </span>
                                            </td>
                                            <td>{{ $version->anio ?? 'N/A' }}</td>
                                            <td>
                                                <div class="admin-action-buttons">
                                                    <button type="button" class="admin-btn admin-btn-warning admin-btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editarVersionModal{{ $version->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.vehiculos.version.destroy', $version) }}" method="POST" class="admin-inline-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta versión?')">
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

<!-- Modal Crear Marca -->
<div class="modal fade admin-modal" id="crearMarcaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Marca</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.vehiculos.marca.store') }}" method="POST">
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

<!-- Modal Crear Modelo -->
<div class="modal fade admin-modal" id="crearModeloModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Modelo</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.vehiculos.modelo.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="marca_id" class="admin-custom-label admin-required-field">Marca</label>
                        <select class="admin-custom-select" id="marca_id" name="marca_id" required>
                            <option value="">Seleccione una marca</option>
                            @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                            @endforeach
                        </select>
                        @error('marca_id')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
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

<!-- Modal Crear Versión -->
<div class="modal fade admin-modal" id="crearVersionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Versión</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.vehiculos.version.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="modelo_id" class="admin-custom-label admin-required-field">Modelo</label>
                        <select class="admin-custom-select" id="modelo_id" name="modelo_id" required>
                            <option value="">Seleccione un modelo</option>
                            @foreach($modelos as $modelo)
                            <option value="{{ $modelo->id }}">{{ $modelo->marca->nombre }} - {{ $modelo->nombre }}</option>
                            @endforeach
                        </select>
                        @error('modelo_id')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="nombre" class="admin-custom-label admin-required-field">Nombre</label>
                        <input type="text" class="admin-custom-input" id="nombre" name="nombre" required>
                        @error('nombre')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="anio" class="admin-custom-label">Año</label>
                        <input type="number" class="admin-custom-input" id="anio" name="anio" min="1886" max="{{ (date('Y') + 1) }}">
                        @error('anio')
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

<!-- Modales de Edición de Marcas -->
@foreach($marcas as $marca)
<div class="modal fade admin-modal" id="editarMarcaModal{{ $marca->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Marca</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.vehiculos.marca.update', $marca) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="nombre" class="admin-custom-label admin-required-field">Nombre</label>
                        <input type="text" class="admin-custom-input" id="nombre" name="nombre" value="{{ $marca->nombre }}" required>
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

<!-- Modales de Edición de Modelos -->
@foreach($modelos as $modelo)
<div class="modal fade admin-modal" id="editarModeloModal{{ $modelo->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Modelo</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.vehiculos.modelo.update', $modelo) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="marca_id" class="admin-custom-label admin-required-field">Marca</label>
                        <select class="admin-custom-select" id="marca_id" name="marca_id" required>
                            @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}" {{ $modelo->marca_id == $marca->id ? 'selected' : '' }}>
                                {{ $marca->nombre }}
                            </option>
                            @endforeach
                        </select>
                        @error('marca_id')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="nombre" class="admin-custom-label admin-required-field">Nombre</label>
                        <input type="text" class="admin-custom-input" id="nombre" name="nombre" value="{{ $modelo->nombre }}" required>
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

<!-- Modales de Edición de Versiones -->
@foreach($versiones as $version)
<div class="modal fade admin-modal" id="editarVersionModal{{ $version->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Versión</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.vehiculos.version.update', $version) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="modelo_id" class="admin-custom-label admin-required-field">Modelo</label>
                        <select class="admin-custom-select" id="modelo_id" name="modelo_id" required>
                            @foreach($modelos as $modelo)
                            <option value="{{ $modelo->id }}" {{ $version->modelo_id == $modelo->id ? 'selected' : '' }}>
                                {{ $modelo->marca->nombre }} - {{ $modelo->nombre }}
                            </option>
                            @endforeach
                        </select>
                        @error('modelo_id')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="nombre" class="admin-custom-label admin-required-field">Nombre</label>
                        <input type="text" class="admin-custom-input" id="nombre" name="nombre" value="{{ $version->nombre }}" required>
                        @error('nombre')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="anio" class="admin-custom-label">Año</label>
                        <input type="number" class="admin-custom-input" id="anio" name="anio" value="{{ $version->anio }}" min="1886" max="{{ (date('Y') + 1) }}">
                        @error('anio')
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
        // Inicializar las pestañas de Bootstrap
        var triggerTabList = [].slice.call(document.querySelectorAll('#myTab button'))
        triggerTabList.forEach(function(triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl)
            triggerEl.addEventListener('click', function(event) {
                event.preventDefault()
                tabTrigger.show()
            })
        })

        // Inicializar los modales
        var modals = document.querySelectorAll('.admin-modal')
        modals.forEach(function(modal) {
            new bootstrap.Modal(modal, {
                backdrop: 'static',
                keyboard: false
            })
        })

        // Manejar el cierre de modales
        var closeButtons = document.querySelectorAll('.admin-modal-close')
        closeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var modal = bootstrap.Modal.getInstance(this.closest('.admin-modal'))
                modal.hide()
            })
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