@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/admin/panels.css') }}" rel="stylesheet">
<style>
    .admin-switch-label.bg-danger {
        background-color: #dc3545 !important;
    }

    .admin-switch-label.bg-danger .admin-switch-inner {
        transform: translateX(100%);
    }

    .admin-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .admin-btn:disabled:hover {
        transform: none;
        box-shadow: none;
    }
    
    .access-info {
        background-color: #f8f9fa;
        border-left: 4px solid #17a2b8;
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 0 4px 4px 0;
    }
    
    .access-info i {
        color: #17a2b8;
        margin-right: 5px;
    }
</style>
@endpush

@section('content')
<div class="admin-container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Gestión de Usuarios</h2>
                </div>

                <div class="admin-card-body">
                    <div class="access-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Acceso Limitado:</strong> 
                        @if(auth()->user()->tienePermiso('gestionar_usuarios_tienda'))
                            Puedes gestionar usuarios de tu tienda actual.
                        @endif
                        @if(auth()->user()->tienePermiso('gestionar_usuarios_rol'))
                            Puedes asignar tu rol actual a otros usuarios.
                        @endif
                    </div>
                
                    @if (session('success'))
                    <div class="admin-alert admin-alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="admin-alert admin-alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <!-- Botón para crear nuevo usuario -->
                    <div class="admin-section-header">
                        <h3 class="admin-section-title">Lista de Usuarios</h3>
                        <button type="button" class="admin-btn admin-btn-primary" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">
                            <i class="fas fa-plus admin-icon"></i> Nuevo Usuario
                        </button>
                    </div>

                    <!-- Tabla de usuarios -->
                    <div class="table-responsive">
                        <table class="table admin-table">
                            <thead>
                                <tr>
                                    <th>Login</th>
                                    <th>Nombre Completo</th>
                                    <th>Rol</th>
                                    <th>Tienda</th>
                                    <th>Estado</th>
                                    <th>Último Acceso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usuarios as $usuario)
                                <tr class="{{ !$usuario->activo ? 'bg-danger bg-opacity-10' : '' }}">
                                    <td>{{ $usuario->login }}</td>
                                    <td>{{ $usuario->full_name }}</td>
                                    <td>
                                        @foreach($usuario->roles as $rol)
                                        <span class="badge bg-primary">{{ $rol->nombre }}</span>
                                        @endforeach
                                    </td>
                                    {{-- Columna Tienda --}}
                                    <td>
                                        @php
                                        // 1) Sucursal: la que venga desde la tienda o la relación directa
                                        $sucursalNombre = $usuario->tienda->sucursal->nombre
                                        ?? $usuario->sucursal->nombre
                                        ?? null;

                                        // 2) Tienda: solo si existe relación
                                        $tiendaNombre = $usuario->tienda->nombre ?? null;
                                        @endphp

                                        @if($sucursalNombre || $tiendaNombre)
                                        {{ $sucursalNombre }}{{ $tiendaNombre ? ' - '.$tiendaNombre : '' }}
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($usuario->activo)
                                        <span class="admin-badge admin-badge-success">Activo</span>
                                        @else
                                        <span class="admin-badge admin-badge-danger" style="background-color: #dc3545 !important; color: #fff !important;">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($usuario->last_login)
                                        @php
                                        $now = now();
                                        $diff = $now->diffInMinutes($usuario->last_login);
                                        $isOnline = $diff < 5; // Consideramos en línea si su último acceso fue hace menos de 5 minutos
                                            @endphp
                                            @if($isOnline)
                                            <span class="admin-badge admin-badge-success">
                                            <i class="fas fa-circle" style="font-size: 8px; margin-right: 5px;"></i> En línea
                                            </span>
                                            @else
                                            @php
                                            if ($diff < 1) {
                                                echo 'Hace un instante' ;
                                                } elseif ($diff < 60) {
                                                echo $diff==1 ? 'Hace 1 minuto' : "Hace {$diff} minutos" ;
                                                } elseif ($diff < 1440) { // 24 horas
                                                $hours=floor($diff / 60);
                                                echo $hours==1 ? 'Hace 1 hora' : "Hace {$hours} horas" ;
                                                } else {
                                                echo $usuario->last_login->format('d/m/Y \a \l\a\s H:i');
                                                }
                                                @endphp
                                                @endif
                                                @else
                                                <span class="text-muted">No disponible</span>
                                                @endif
                                    </td>
                                    <td>
                                        <div class="admin-action-buttons">
                                            <button type="button" class="admin-btn admin-btn-warning admin-btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editarUsuarioModal{{ $usuario->id }}"
                                                @if($usuario->id === auth()->id())
                                                disabled
                                                title="No puedes editarte a ti mismo desde aquí"
                                                @endif>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.usuarios.limitado.destroy', $usuario) }}" method="POST" class="admin-inline-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm"
                                                    onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')"
                                                    @if($usuario->id === auth()->id())
                                                    disabled
                                                    title="No puedes eliminarte a ti mismo"
                                                    @endif>
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

<!-- Modal Crear Usuario -->
<div class="modal fade admin-modal" id="crearUsuarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Usuario</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.usuarios.limitado.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> Estás creando un usuario con acceso limitado según tus permisos.
                    </div>
                
                    <div class="admin-form-group">
                        <label for="login" class="admin-custom-label admin-required-field">Login</label>
                        <input type="text" class="admin-custom-input" id="login" name="login" required>
                        @error('login')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="first_name" class="admin-custom-label admin-required-field">Nombre</label>
                        <input type="text" class="admin-custom-input" id="first_name" name="first_name" required>
                        @error('first_name')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="last_name" class="admin-custom-label admin-required-field">Apellidos</label>
                        <input type="text" class="admin-custom-input" id="last_name" name="last_name" required>
                        @error('last_name')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="email" class="admin-custom-label admin-required-field">Email</label>
                        <input type="email" class="admin-custom-input" id="email" name="email" required>
                        @error('email')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="password" class="admin-custom-label admin-required-field">Contraseña</label>
                        <input type="password" class="admin-custom-input" id="password" name="password" required>
                        @error('password')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="admin-form-group">
                        <label for="roles" class="admin-custom-label admin-required-field">Roles</label>
                        <select class="admin-custom-select" id="roles" name="roles[]" multiple required>
                            @foreach($roles as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        @error('roles')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                        
                        @if(auth()->user()->tienePermiso('gestionar_usuarios_rol'))
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle"></i> Solo puedes asignar tu rol actual
                        </small>
                        @endif
                    </div>
                    
                    @if(auth()->user()->tienePermiso('gestionar_usuarios_tienda'))
                    <div class="admin-form-group">
                        <label class="admin-custom-label">Tienda</label>
                        <input type="text" class="admin-custom-input" value="{{ auth()->user()->tienda->nombre ?? 'No asignada' }}" disabled>
                        <input type="hidden" name="tienda_id" value="{{ auth()->user()->tienda_id }}">
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle"></i> El usuario será asignado a tu tienda actual
                        </small>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modales de Edición de Usuarios -->
@foreach($usuarios as $usuario)
<div class="modal fade admin-modal" id="editarUsuarioModal{{ $usuario->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuario</h5>
                <button type="button" class="btn-close admin-modal-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.usuarios.limitado.update', $usuario) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> Estás editando este usuario con acceso limitado según tus permisos.
                    </div>
                    
                    <div class="admin-form-group">
                        <label for="login" class="admin-custom-label">Login</label>
                        <input type="text" class="admin-custom-input" id="login" value="{{ $usuario->login }}" disabled>
                        <small class="admin-text-muted">El login no puede ser modificado</small>
                    </div>
                    <div class="admin-form-group">
                        <label for="first_name{{ $usuario->id }}" class="admin-custom-label admin-required-field">Nombre</label>
                        <input type="text" class="admin-custom-input" id="first_name{{ $usuario->id }}" name="first_name" value="{{ $usuario->first_name }}" required>
                        @error('first_name')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="last_name{{ $usuario->id }}" class="admin-custom-label admin-required-field">Apellidos</label>
                        <input type="text" class="admin-custom-input" id="last_name{{ $usuario->id }}" name="last_name" value="{{ $usuario->last_name }}" required>
                        @error('last_name')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="email{{ $usuario->id }}" class="admin-custom-label admin-required-field">Email</label>
                        <input type="email" class="admin-custom-input" id="email{{ $usuario->id }}" name="email" value="{{ $usuario->email }}" required>
                        @error('email')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="admin-form-group">
                        <label for="roles{{ $usuario->id }}" class="admin-custom-label admin-required-field">Roles</label>
                        <select class="admin-custom-select" id="roles{{ $usuario->id }}" name="roles[]" multiple required>
                            @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" {{ in_array($rol->id, $usuario->roles->pluck('id')->toArray()) ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        @error('roles')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                        
                        @if(auth()->user()->tienePermiso('gestionar_usuarios_rol'))
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle"></i> Solo puedes asignar tu rol actual
                        </small>
                        @endif
                    </div>
                    
                    @if(auth()->user()->tienePermiso('gestionar_usuarios_tienda'))
                    <div class="admin-form-group">
                        <label class="admin-custom-label">Tienda</label>
                        <input type="text" class="admin-custom-input" value="{{ auth()->user()->tienda->nombre ?? 'No asignada' }}" disabled>
                        <input type="hidden" name="tienda_id" value="{{ auth()->user()->tienda_id }}">
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle"></i> El usuario será asignado a tu tienda actual
                        </small>
                    </div>
                    @endif
                    
                    <div class="admin-form-group">
                        <div class="admin-switch">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" id="activo{{ $usuario->id }}" name="activo" value="1" {{ $usuario->activo ? 'checked' : '' }}>
                            <label for="activo{{ $usuario->id }}" class="admin-switch-label {{ !$usuario->activo ? 'bg-danger' : '' }}">
                                <span class="admin-switch-inner"></span>
                                <span class="admin-switch-state">{{ $usuario->activo ? 'Usuario Activo' : 'Usuario Inactivo' }}</span>
                            </label>
                        </div>
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
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
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

        // Manejar el cambio de estado del switch de activación
        document.querySelectorAll('input[type="checkbox"][name="activo"]').forEach(function(checkbox) {
            const label = checkbox.nextElementSibling;

            checkbox.addEventListener('change', function() {
                const stateText = label.querySelector('.admin-switch-state');
                stateText.textContent = this.checked ? 'Usuario Activo' : 'Usuario Inactivo';

                if (this.checked) {
                    label.classList.remove('bg-danger');
                } else {
                    label.classList.add('bg-danger');
                }
            });
        });

        // Función para enviar el heartbeat
        function sendHeartbeat() {
            fetch('{{ route("user.heartbeat") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            }).catch(error => console.error('Error en heartbeat:', error));
        }

        // Enviar heartbeat cada 2 minutos
        setInterval(sendHeartbeat, 120000);
        // Enviar el primer heartbeat inmediatamente
        sendHeartbeat();
    });
</script>
@endpush
