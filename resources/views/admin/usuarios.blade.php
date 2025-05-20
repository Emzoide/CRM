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
                    
                    @if(!$permisos['tieneAccesoCompleto'])
                    <div class="alert alert-info mb-3" style="background-color: #f8f9fa; border-left: 4px solid #17a2b8; padding: 10px 15px; border-radius: 0 4px 4px 0;">
                        <i class="fas fa-info-circle" style="color: #17a2b8; margin-right: 5px;"></i> 
                        <strong>Acceso Limitado:</strong> 
                        @if($permisos['puedeGestionarTienda'])
                            Puedes gestionar usuarios de tu tienda actual.
                        @endif
                        @if($permisos['puedeGestionarRol'])
                            @if($permisos['puedeGestionarTienda'])
                                También puedes asignar tu rol actual a otros usuarios.
                            @else
                                Puedes asignar tu rol actual a otros usuarios.
                            @endif
                        @endif
                    </div>
                    @endif

                    <!-- Botón para crear nuevo usuario (visible solo si tiene permisos) -->
                    <div class="admin-section-header">
                        <h3 class="admin-section-title">Lista de Usuarios</h3>
                        
                        <button type="button" class="admin-btn admin-btn-primary" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">
                            <i class="fas fa-plus admin-icon"></i> Nuevo Usuario
                        </button>
                        
                        @if(!$permisos['tieneAccesoCompleto'])
                        <div class="admin-badge admin-badge-info ms-2" data-bs-toggle="tooltip" data-bs-placement="top" 
                             title="Tu rol te permite gestionar usuarios con restricciones">
                            <i class="fas fa-info-circle"></i> Acceso limitado
                        </div>
                        @endif
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
                                        $sucursalNombre = null;
                                        if ($usuario->tienda && $usuario->tienda->sucursal) {
                                            $sucursalNombre = $usuario->tienda->sucursal->nombre;
                                        }
                                        
                                        // 2) Tienda: mostrar el nombre de la tienda
                                        $tiendaNombre = $usuario->tienda ? $usuario->tienda->nombre : null;
                                        @endphp
                                        
                                        @if($tiendaNombre)
                                        @if($sucursalNombre)
                                        {{ $sucursalNombre }} - {{ $tiendaNombre }}
                                        @else
                                        {{ $tiendaNombre }}
                                        @endif
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
                                                <i class="fas fa-circle"></i> En línea
                                            </span>
                                            @else
                                            @php
                                            if ($diff < 1) {
                                                echo 'Hace un instante' ;
                                                } elseif ($diff < 60) {
                                                echo "Hace $diff minuto" . ($diff != 1 ? 's' : '');
                                                } elseif ($diff < 1440) {
                                                $hours = floor($diff / 60);
                                                echo "Hace $hours hora" . ($hours != 1 ? 's' : '');
                                                } else {
                                                echo $usuario->last_login->format('d/m/Y H:i');
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
                                            <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST" class="admin-inline-form">
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
                                
                                <!-- Modal para editar usuario -->
                                <div class="modal fade" id="editarUsuarioModal{{ $usuario->id }}" tabindex="-1" aria-labelledby="editarUsuarioModalLabel{{ $usuario->id }}" aria-hidden="true">
                                    <div class="modal-dialog admin-modal-dialog">
                                        <div class="modal-content admin-modal-content">
                                            <div class="modal-header admin-modal-header">
                                                <h5 class="modal-title admin-modal-title" id="editarUsuarioModalLabel{{ $usuario->id }}">Editar Usuario</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body admin-modal-body">
                                                    <div class="admin-form-group">
                                                    <label for="first_name{{ $usuario->id }}" class="admin-custom-label admin-required-field">Nombres</label>
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
                                                        <label for="rol_id{{ $usuario->id }}" class="admin-custom-label admin-required-field">Rol</label>
                                                        <select class="admin-custom-select" id="rol_id{{ $usuario->id }}" name="rol_id" required
                                                               @if(!$permisos['tieneAccesoCompleto'] && $permisos['puedeGestionarRol'] && count($roles) == 1)
                                                               disabled
                                                               @endif>
                                                            <option value="">Seleccione un rol</option>
                                                            @foreach($roles as $rol)
                                                            @php
                                                                $rolUsuario = $usuario->getRolAttribute();
                                                                $rolId = $rolUsuario ? $rolUsuario->id : null;
                                                            @endphp
                                                            <option value="{{ $rol->id }}" {{ $rolId == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('rol_id')
                                                        <div class="admin-error-message">{{ $message }}</div>
                                                        @enderror
                                                        
                                                        @if(!$permisos['tieneAccesoCompleto'] && $permisos['puedeGestionarRol'] && count($roles) == 1)
                                                        <!-- Si solo puede asignar un rol, agregamos un campo oculto -->
                                                        <input type="hidden" name="rol_id" value="{{ $roles[0]->id }}">
                                                        <small class="text-muted mt-1 d-block">
                                                            <i class="fas fa-info-circle"></i> Solo puedes asignar tu rol actual: <strong>{{ $roles[0]->nombre }}</strong>
                                                        </small>
                                                        @elseif(!$permisos['tieneAccesoCompleto'])
                                                        <small class="text-muted mt-1 d-block">
                                                            <i class="fas fa-info-circle"></i> Solo puedes asignar los roles mostrados
                                                        </small>
                                                        @endif
                                                    </div>
                                                    <div class="admin-form-group">
                                                        <label for="tienda_id{{ $usuario->id }}" class="admin-custom-label">Tienda</label>
                                                        <select class="admin-custom-select" id="tienda_id{{ $usuario->id }}" name="tienda_id"
                                                               @if(!$permisos['tieneAccesoCompleto'] && $permisos['puedeGestionarTienda'] && count($tiendas) == 1)
                                                               disabled
                                                               @endif>
                                                            <option value="">Seleccione una tienda</option>
                                                            @foreach($tiendas as $tienda)
                                                            <option value="{{ $tienda->id }}" 
                                                                   {{ $usuario->tienda_id == $tienda->id ? 'selected' : '' }}>
                                                                @if($tienda->sucursal)
                                                                {{ $tienda->sucursal->nombre }} - {{ $tienda->nombre }}
                                                                @else
                                                                {{ $tienda->nombre }}
                                                                @endif
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        @error('tienda_id')
                                                        <div class="admin-error-message">{{ $message }}</div>
                                                        @enderror
                                                        
                                                        @if(!$permisos['tieneAccesoCompleto'] && $permisos['puedeGestionarTienda'] && count($tiendas) == 1)
                                                        <!-- Si solo puede gestionar su tienda, mostramos un mensaje y campo oculto -->
                                                        <input type="hidden" name="tienda_id" value="{{ $tiendas[0]->id }}">
                                                        <small class="text-muted mt-1 d-block">
                                                            <i class="fas fa-info-circle"></i> Solo puedes asignar usuarios a tu tienda actual: <strong>{{ $tiendas[0]->nombre }}</strong>
                                                        </small>
                                                        @endif
                                                    </div>
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
                                                <div class="modal-footer admin-modal-footer">
                                                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="admin-btn admin-btn-primary">Guardar cambios</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear nuevo usuario -->
<div class="modal fade" id="crearUsuarioModal" tabindex="-1" aria-labelledby="crearUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog admin-modal-dialog">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title admin-modal-title" id="crearUsuarioModalLabel">Crear Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.usuarios.store') }}" method="POST">
                @csrf
                <div class="modal-body admin-modal-body">
                    <div class="admin-form-group">
                        <label for="loginCreate" class="admin-custom-label admin-required-field">Login</label>
                        <input type="text" class="admin-custom-input" id="loginCreate" name="login" value="{{ old('login') }}" required>
                        @error('login')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="passwordCreate" class="admin-custom-label admin-required-field">Contraseña</label>
                        <input type="password" class="admin-custom-input" id="passwordCreate" name="password" required>
                        @error('password')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="password_confirmationCreate" class="admin-custom-label admin-required-field">Confirmar Contraseña</label>
                        <input type="password" class="admin-custom-input" id="password_confirmationCreate" name="password_confirmation" required>
                    </div>
                    <div class="admin-form-group">
                        <label for="first_nameCreate" class="admin-custom-label admin-required-field">Nombres</label>
                        <input type="text" class="admin-custom-input" id="first_nameCreate" name="first_name" value="{{ old('first_name') }}" required>
                        @error('first_name')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="last_nameCreate" class="admin-custom-label admin-required-field">Apellidos</label>
                        <input type="text" class="admin-custom-input" id="last_nameCreate" name="last_name" value="{{ old('last_name') }}" required>
                        @error('last_name')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="emailCreate" class="admin-custom-label admin-required-field">Email</label>
                        <input type="email" class="admin-custom-input" id="emailCreate" name="email" value="{{ old('email') }}" required>
                        @error('email')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="admin-form-group">
                        <label for="rol_idCreate" class="admin-custom-label admin-required-field">Rol</label>
                        <select class="admin-custom-select" id="rol_idCreate" name="rol_id" required
                               @if(!$permisos['tieneAccesoCompleto'] && $permisos['puedeGestionarRol'] && count($roles) == 1)
                               disabled
                               @endif>
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" @if(old('rol_id') == $rol->id) selected @endif>{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        @error('rol_id')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                        
                        @if(!$permisos['tieneAccesoCompleto'] && $permisos['puedeGestionarRol'] && count($roles) == 1)
                        <!-- Si solo puede asignar un rol, agregamos un campo oculto -->
                        <input type="hidden" name="rol_id" value="{{ $roles[0]->id }}">
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle"></i> Solo puedes asignar tu rol actual: <strong>{{ $roles[0]->nombre }}</strong>
                        </small>
                        @elseif(!$permisos['tieneAccesoCompleto'])
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle"></i> Solo puedes asignar los roles mostrados
                        </small>
                        @endif
                    </div>
                    <div class="admin-form-group">
                        <label for="tienda_idCreate" class="admin-custom-label">Tienda</label>
                        <select class="admin-custom-select" id="tienda_idCreate" name="tienda_id" 
                               @if(!$permisos['tieneAccesoCompleto'] && $permisos['puedeGestionarTienda'] && count($tiendas) == 1)
                               disabled
                               @endif>
                            <option value="">Seleccione una tienda</option>
                            @foreach($tiendas as $tienda)
                            <option value="{{ $tienda->id }}" 
                                   @if(!$permisos['tieneAccesoCompleto'] && $permisos['puedeGestionarTienda'] && 
                                      count($tiendas) == 1 && $tiendas[0]->id == $tienda->id)
                                   selected
                                   @endif>
                                @if($tienda->sucursal)
                                {{ $tienda->sucursal->nombre }} - {{ $tienda->nombre }}
                                @else
                                {{ $tienda->nombre }}
                                @endif
                            </option>
                            @endforeach
                        </select>
                        @error('tienda_id')
                        <div class="admin-error-message">{{ $message }}</div>
                        @enderror
                        
                        @if(!$permisos['tieneAccesoCompleto'] && $permisos['puedeGestionarTienda'] && count($tiendas) == 1)
                        <!-- Si solo puede gestionar su tienda, mostramos un mensaje y campo oculto -->
                        <input type="hidden" name="tienda_id" value="{{ $tiendas[0]->id }}">
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle"></i> Solo puedes asignar usuarios a tu tienda actual: <strong>{{ $tiendas[0]->nombre }}</strong>
                        </small>
                        @endif
                    </div>
                </div>
                <div class="modal-footer admin-modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // No inicializar manualmente los modales
        // Bootstrap 5 maneja automáticamente los modales mediante los atributos data-bs-*
        
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
