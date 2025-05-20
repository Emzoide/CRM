@extends('layouts.app')

@section('content')
<!-- Asegurarnos que jQuery esté cargado -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="container-fluid"
    data-permiso-gestion-usuarios="{{ $permisos->where('nombre', 'gestionar_usuarios')->first()->id }}"
    data-permiso-gestion-usuarios-tienda="{{ $permisos->where('nombre', 'gestionar_usuarios_tienda')->first()->id }}"
    data-permiso-gestion-usuarios-rol="{{ $permisos->where('nombre', 'gestionar_usuarios_rol')->first()->id }}">
    <div class="row"> 
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestión de Roles</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" id="btnNuevoRol">
                            <i class="fas fa-plus"></i> Nuevo Rol
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Permisos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $rol)
                                <tr>
                                    <td>{{ $rol->nombre }}</td>
                                    <td>{{ $rol->descripcion }}</td>
                                    <td>
                                        @foreach($rol->permisos as $permiso)
                                        <span class="badge bg-info">{{ $permiso->nombre }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info btn-editar" data-id="{{ $rol->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="{{ $rol->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Modal Crear/Editar Rol -->
<div class="modal fade" id="modalRol" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Crear Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRol">
                <div class="modal-body">
                    <!-- Contenedor para mensajes de error -->
                    <div id="form-errors" class="alert alert-danger" style="display:none;"></div>
                    
                    <input type="hidden" id="rol_id" name="rol_id">
                    <input type="hidden" id="roles_seleccionados_input" name="roles_seleccionados">
                    <input type="hidden" id="tiendas_rel" name="tiendas_rel">
                    <input type="hidden" id="roles_rel" name="roles_rel">
                    <input type="hidden" id="tiendas_seleccionadas_input" name="tiendas_seleccionadas">
                    
                    <!-- Tabs de navegación -->
                    <ul class="nav nav-tabs" id="rolTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">Datos Generales</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="permisos-tab" data-bs-toggle="tab" data-bs-target="#permisos" type="button" role="tab">Permisos</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link disabled" id="roles-gestion-tab" data-bs-toggle="tab" data-bs-target="#roles-gestion" type="button" role="tab">Roles a Gestionar</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link disabled" id="tiendas-gestion-tab" data-bs-toggle="tab" data-bs-target="#tiendas-gestion" type="button" role="tab">Tiendas a Gestionar</button>
                        </li>
                    </ul>
                    
                    <!-- Contenido de los tabs -->
                    <div class="tab-content mt-3" id="rolTabsContent">
                        <!-- Tab de datos generales -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="es_admin" name="es_admin">
                                <label class="form-check-label" for="es_admin">Es Administrador</label>
                                <small class="form-text text-muted">Los roles administradores tienen todos los permisos</small>
                            </div>

                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Rol</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                            </div>
                        </div>
                        
                        <!-- Tab de permisos -->
                        <div class="tab-pane fade" id="permisos" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Permisos</label>
                                <div class="row">
                            @foreach($permisos->groupBy('grupo') as $grupo => $grupoPermisos)
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ ucfirst($grupo) }}</h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach($grupoPermisos as $permiso)
                                        <div class="form-check">
                                            <input class="form-check-input permiso-check" type="checkbox"
                                                name="permisos[]" value="{{ $permiso->id }}"
                                                id="permiso{{ $permiso->id }}"
                                                data-permiso="{{ $permiso->nombre }}">
                                            <label class="form-check-label" for="permiso{{ $permiso->id }}">
                                                {{ $permiso->nombre }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab de roles a gestionar -->
                        <div class="tab-pane fade" id="roles-gestion" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Roles que este rol puede gestionar</label>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> <strong>IMPORTANTE:</strong> Selecciona los roles de usuarios que podrán ser gestionados por este rol.
                                    Por ejemplo, un "Jefe de Marca KIA" podría gestionar usuarios con roles "Asesores KIA" y "Jefes de Tienda KIA".
                                    Este permiso define qué tipos de usuarios puede ver y modificar.
                                </div>
                                <select class="form-control select2-roles" id="roles_gestionables" multiple>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Tab de tiendas a gestionar -->
                        <div class="tab-pane fade" id="tiendas-gestion" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Tiendas que este rol puede gestionar</label>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> <strong>IMPORTANTE:</strong> Selecciona las tiendas cuyos <strong>usuarios</strong> podrán ser gestionados por este rol.
                                    No se gestionan las tiendas directamente, sino los <strong>usuarios que pertenecen a estas tiendas</strong>.
                                    Si no seleccionas ninguna, solo podrán gestionar usuarios de su propia tienda.
                                </div>
                                <select class="form-control select2-tiendas" id="tiendas_gestionables" multiple>
                                    @foreach($tiendas as $tienda)
                                        <option value="{{ $tienda->id }}">{{ $tienda->nombre }} @if($tienda->sucursal) ({{ $tienda->sucursal->nombre }}) @endif</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Selección de Roles -->
<div class="modal fade" id="modalRoles" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Roles a Gestionar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <select class="form-select" id="puede_gestionar_roles" name="puede_gestionar_roles[]" multiple>
                        @foreach($roles as $rol)
                        <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="guardarSeleccionRoles()">Guardar Selección</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Selección de Tiendas -->
<div class="modal fade" id="modalTiendas" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Tiendas a Gestionar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <select class="form-select" id="puede_gestionar_tiendas" name="puede_gestionar_tiendas[]" multiple>
                        @foreach($tiendas as $tienda)
                        <option value="{{ $tienda->id }}">{{ $tienda->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="guardarSeleccionTiendas()">Guardar Selección</button>
            </div>
        </div>
    </div>
</div>

@php
$permisoGestionUsuarios = $permisos->where('nombre', 'gestionar_usuarios')->first();
@endphp

<script>
    // Variables para almacenar las selecciones
    let rolesSeleccionados = [];
    let tiendasSeleccionadas = [];

    // Obtener los IDs de los permisos
    const permisoGestionUsuariosId = document.querySelector('.container-fluid').dataset.permisoGestionUsuarios;
    const permisoGestionUsuariosTiendaId = document.querySelector('.container-fluid').dataset.permisoGestionUsuariosTienda;
    const permisoGestionUsuariosRolId = document.querySelector('.container-fluid').dataset.permisoGestionUsuariosRol;

    // Definir funciones globalmente
    function abrirModalCrear() {
        // Limpiar el formulario
        document.getElementById('formRol').reset();
        
        // Limpiar los campos ocultos
        document.getElementById('rol_id').value = '';
        document.getElementById('roles_seleccionados_input').value = '';
        document.getElementById('tiendas_rel').value = '';
        document.getElementById('roles_rel').value = '';
        document.getElementById('tiendas_seleccionadas_input').value = '';
        
        // Resetear los select2
        $('#roles_gestionables').val(null).trigger('change');
        $('#tiendas_gestionables').val(null).trigger('change');
        
        // Resetear checkboxes de permisos
        document.querySelectorAll('input[name="permisos[]"]').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Cambiar el título y mostrar el modal
        document.getElementById('modalTitulo').textContent = 'Crear Nuevo Rol';
        
        // Actualizar la acción del formulario para crear
        document.getElementById('formRol').setAttribute('action', '/admin/roles');
        document.getElementById('formRol').setAttribute('method', 'POST');
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('modalRol'));
        modal.show();
        
        // Activar la primera pestaña (datos generales)
        document.getElementById('general-tab').click();
    }
    
    function abrirModalEditar(id) {
        // Limpiar el formulario primero
        document.getElementById('formRol').reset();
        
        // Cambiar el título del modal
        document.getElementById('modalTitulo').textContent = 'Editar Rol';

        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('modalRol'));
        modal.show();
        
        // Activar la primera pestaña
        document.getElementById('general-tab').click();

        // Obtener los datos del rol
        fetch(`/admin/roles/${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos del rol:', data);
                
                // Llenar el formulario
                // Limpiar cualquier mensaje de error anterior
                const errorContainer = document.getElementById('form-errors');
                if (errorContainer) {
                    errorContainer.style.display = 'none';
                }
                
                // Limpiar clases de validación
                document.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
                
                document.getElementById('rol_id').value = data.id;
                document.getElementById('nombre').value = data.nombre;
                document.getElementById('descripcion').value = data.descripcion;
                document.getElementById('es_admin').checked = data.is_admin;

                // Para los permisos (múltiples checkboxes)
                document.querySelectorAll('input[name="permisos[]"]').forEach(checkbox => {
                    checkbox.checked = data.permisos.includes(parseInt(checkbox.value));
                });
                
                // Ejecutar el actualizador de pestañas después de cargar los permisos
                // para que se desbloqueen las pestañas correspondientes
                setTimeout(() => {
                    actualizarPestanasGestion();
                }, 100); // Pequeño retraso para asegurar que los checkboxes ya estén marcados
                
                // Para los roles gestionables
                if (data.roles_rel && data.roles_rel.length > 0) {
                    $('#roles_gestionables').val(data.roles_rel);
                    $('#roles_gestionables').trigger('change');
                    $('#roles_rel').val(JSON.stringify(data.roles_rel));
                } else {
                    $('#roles_gestionables').val(null).trigger('change');
                    $('#roles_rel').val('[]');
                }
                
                // Para las tiendas gestionables
                if (data.tiendas_rel && data.tiendas_rel.length > 0) {
                    $('#tiendas_gestionables').val(data.tiendas_rel);
                    $('#tiendas_gestionables').trigger('change');
                    $('#tiendas_rel').val(JSON.stringify(data.tiendas_rel));
                } else {
                    $('#tiendas_gestionables').val(null).trigger('change');
                    $('#tiendas_rel').val('[]');
                }

                // Actualizar la acción del formulario
                document.getElementById('formRol').setAttribute('action', `/admin/roles/${id}`);
                document.getElementById('formRol').setAttribute('method', 'PUT');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los datos del rol: ' + error.message);
                modal.hide();
            });
    }
    
    function confirmarEliminar(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este rol?')) {
            // Mostrar indicador de carga
            const loadingHtml = '<div class="position-fixed top-50 start-50 translate-middle bg-white p-3 rounded shadow"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Eliminando rol...</p></div>';
            const loadingDiv = document.createElement('div');
            loadingDiv.innerHTML = loadingHtml;
            document.body.appendChild(loadingDiv.firstChild);

            fetch(`/admin/roles/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    // Eliminar indicador de carga
                    const loadingElement = document.querySelector('.position-fixed.top-50.start-50');
                    if (loadingElement) {
                        loadingElement.remove();
                    }

                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }

                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        // Mostrar mensaje de éxito
                        alert('Rol eliminado exitosamente');
                        window.location.reload();
                    } else if (data && data.error) {
                        alert('Error: ' + data.error);
                    } else {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    // Eliminar indicador de carga en caso de error
                    const loadingElement = document.querySelector('.position-fixed.top-50.start-50');
                    if (loadingElement) {
                        loadingElement.remove();
                    }

                    console.error('Error:', error);
                    alert('Error al eliminar el rol. Por favor, intente nuevamente.');
                });
        }
    }
    
    // Exponer las funciones globalmente para acceso desde HTML
    window.abrirModalCrear = abrirModalCrear;
    window.abrirModalEditar = abrirModalEditar;
    window.confirmarEliminar = confirmarEliminar;
    
    // Inicializar Select2 cuando el documento esté listo
    $(document).ready(function() {
        // Configuración para Select2 con z-index alto para evitar problemas con el modal
        $('.select2-multiple').select2({
            placeholder: "Selecciona los permisos",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalRol')
        });
        
        $('.select2-roles').select2({
            placeholder: "Selecciona los roles a gestionar",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalRol')
        });
        
        $('.select2-tiendas').select2({
            placeholder: "Selecciona las tiendas a gestionar",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalRol')
        });
        
        // Aplicar estilos al modal para asegurar que los Select2 se muestren correctamente
        $('<style>' +
            '.select2-container--open { z-index: 9999; }' +
            '.modal-backdrop { z-index: 1050 !important; }' +
            '.modal { z-index: 1055 !important; }' +
            '.select2-dropdown { z-index: 2000 !important; }' +
        '</style>').appendTo('head');
    });

    // Definir las funciones para los modales
    function abrirModalRoles() {
        $('#puede_gestionar_roles').val(rolesSeleccionados).trigger('change');
        const modal = new bootstrap.Modal(document.getElementById('modalRoles'));
        modal.show();
    }

    function abrirModalTiendas() {
        $('#puede_gestionar_tiendas').val(tiendasSeleccionadas).trigger('change');
        const modal = new bootstrap.Modal(document.getElementById('modalTiendas'));
        modal.show();
    }

    function guardarSeleccionRoles() {
        // Obtener los valores seleccionados
        rolesSeleccionados = $('#puede_gestionar_roles').val() || [];
        console.log('Roles seleccionados:', rolesSeleccionados);
        
        // Actualizar el campo oculto inmediatamente
        document.getElementById('roles_seleccionados_input').value = JSON.stringify(rolesSeleccionados);
        
        // Actualizar los badges visuales
        mostrarBadgesRoles();
        
        // Cerrar el modal
        const modalEl = document.getElementById('modalRoles');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    }
    
    // Función para mostrar badges de roles seleccionados y sincronizar campo oculto
    function mostrarBadgesRoles() {
        const containerRoles = document.getElementById('badgesRolesSeleccionados');
        const noRoles = document.getElementById('noRolesSeleccionados');
        const inputHidden = document.getElementById('roles_seleccionados_input');
        if (!containerRoles || !noRoles || !inputHidden) return;
        let html = '';
        if (rolesSeleccionados.length > 0) {
            $('#puede_gestionar_roles option:selected').each(function() {
                html += `<span class='badge bg-primary me-1 mb-1'>${$(this).text()}</span>`;
            });
            noRoles.style.display = 'none';
        } else {
            noRoles.style.display = 'block';
        }
        containerRoles.innerHTML = html;
        inputHidden.value = JSON.stringify(rolesSeleccionados);
    }

    // Función para mostrar badges de tiendas seleccionadas y sincronizar campo oculto
    function mostrarBadgesTiendas() {
        const containerTiendas = document.getElementById('badgesTiendasSeleccionadas');
        const noTiendas = document.getElementById('noTiendasSeleccionadas');
        const inputHidden = document.getElementById('tiendas_seleccionadas_input');
        if (!containerTiendas || !noTiendas || !inputHidden) return;
        let html = '';
        if (tiendasSeleccionadas.length > 0) {
            $('#puede_gestionar_tiendas option:selected').each(function() {
                html += `<span class='badge bg-success me-1 mb-1'>${$(this).text()}</span>`;
            });
            noTiendas.style.display = 'none';
        } else {
            noTiendas.style.display = 'block';
        }
        containerTiendas.innerHTML = html;
        inputHidden.value = JSON.stringify(tiendasSeleccionadas);
    }

    function guardarSeleccionTiendas() {
        // Obtener los valores seleccionados
        tiendasSeleccionadas = $('#puede_gestionar_tiendas').val() || [];
        console.log('Tiendas seleccionadas:', tiendasSeleccionadas);
        
        // Actualizar el campo oculto inmediatamente
        document.getElementById('tiendas_seleccionadas_input').value = JSON.stringify(tiendasSeleccionadas);
        
        // Actualizar los badges visuales
        mostrarBadgesTiendas();
        
        // Cerrar el modal
        const modalEl = document.getElementById('modalTiendas');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    }



    // Configurar los eventos de botones usando jQuery
    $('#btnNuevoRol').on('click', function() {
        abrirModalCrear();
    });
    
    // Configurar botones de editar y eliminar usando delegación de eventos
    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        abrirModalEditar(id);
    });
    
    $(document).on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');
        confirmarEliminar(id);
    });

    window.confirmarEliminar = function(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este rol?')) {
            // Mostrar indicador de carga
            const loadingHtml = '<div class="position-fixed top-50 start-50 translate-middle bg-white p-3 rounded shadow"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Eliminando rol...</p></div>';
            const loadingDiv = document.createElement('div');
            loadingDiv.innerHTML = loadingHtml;
            document.body.appendChild(loadingDiv.firstChild);

            fetch(`/admin/roles/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(async response => {
                    // Si la respuesta es exitosa (2xx) pero no es JSON, probablemente sea una redirección
                    if (response.ok) {
                        const contentType = response.headers.get('content-type');
                        const isJson = contentType && contentType.includes('application/json');
                        
                        // Si no es JSON, es probablemente una redirección exitosa
                        if (!isJson) {
                            window.location.reload();
                            return; // Detener ejecución aquí
                        }
                        
                        // Si es JSON, intentar parsearlo
                        try {
                            const data = await response.json();
                            if (data.success) {
                                window.location.reload();
                                return;
                            } else {
                                throw new Error(data.message || 'Error al eliminar el rol');
                            }
                        } catch (parseError) {
                            console.error('Error parsing JSON:', parseError);
                            // Si hay error de parseo pero la respuesta fue exitosa, recargar la página
                            window.location.reload();
                            return;
                        }
                    }
                    
                    // Si la respuesta no es exitosa (no 2xx)
                    try {
                        const contentType = response.headers.get('content-type');
                        const isJson = contentType && contentType.includes('application/json');
                        const data = isJson ? await response.json() : null;
                        throw new Error(data?.message || 'Error en el servidor');
                    } catch (error) {
                        if (error.name === 'SyntaxError') {
                            throw new Error('Error en el servidor. Por favor, verifica la consola para más detalles.');
                        }
                        throw error;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el rol: ' + error.message);
                    const loadingElement = document.querySelector('.position-fixed');
                    if (loadingElement) loadingElement.remove();
                });
        }
    }

    // Manejar los checkboxes de permisos
    // Deshabilitar las pestañas de gestión inicialmente
    function actualizarPestanasGestion() {
        const permisosGestionTienda = document.querySelector('input[data-permiso="gestionar_usuarios_tienda"]');
        const permisosGestionRol = document.querySelector('input[data-permiso="gestionar_usuarios_rol"]');
        const tabGestionTiendas = document.getElementById('tiendas-gestion-tab');
        const tabGestionRoles = document.getElementById('roles-gestion-tab');
        
        // Gestionar pestaña de tiendas
        if (permisosGestionTienda && permisosGestionTienda.checked) {
            tabGestionTiendas.classList.remove('disabled');
        } else {
            tabGestionTiendas.classList.add('disabled');
            
            // Si la pestaña está activa, cambiar a la pestaña general
            if (tabGestionTiendas.classList.contains('active')) {
                document.getElementById('general-tab').click();
            }
        }
        
        // Gestionar pestaña de roles
        if (permisosGestionRol && permisosGestionRol.checked) {
            tabGestionRoles.classList.remove('disabled');
        } else {
            tabGestionRoles.classList.add('disabled');
            
            // Si la pestaña está activa, cambiar a la pestaña general
            if (tabGestionRoles.classList.contains('active')) {
                document.getElementById('general-tab').click();
            }
        }
    }
    
    // Ejecutar al cargar la página
    actualizarPestanasGestion();

    document.querySelectorAll('.permiso-check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const permisoId = parseInt(this.value);
            const permisoCodigo = this.getAttribute('data-permiso');
            
            // Actualizar pestañas si cambian los permisos de gestión
            if (permisoCodigo === 'gestionar_usuarios_tienda' || permisoCodigo === 'gestionar_usuarios_rol') {
                actualizarPestanasGestion();
            }
            
            const seccionGestionUsuarios = document.getElementById('seccionGestionUsuarios');
            const opcionesGestionUsuarios = document.getElementById('opcionesGestionUsuarios');
            const esAdmin = document.getElementById('es_admin');

            // Si se desmarca el permiso, limpiar las selecciones correspondientes
            if (!this.checked) {
                if (permisoId === parseInt(permisoGestionUsuariosRolId)) {
                    rolesSeleccionados = [];
                    mostrarBadgesRoles();
                } else if (permisoId === parseInt(permisoGestionUsuariosTiendaId)) {
                    tiendasSeleccionadas = [];
                    mostrarBadgesTiendas();
                }
            }

            // Mostrar sección principal si hay algún permiso de gestión
            const tienePermisosGestion = Array.from(document.querySelectorAll('.permiso-check')).some(cb =>
                cb.checked && (
                    parseInt(cb.value) === parseInt(permisoGestionUsuariosId) ||
                    parseInt(cb.value) === parseInt(permisoGestionUsuariosTiendaId) ||
                    parseInt(cb.value) === parseInt(permisoGestionUsuariosRolId)
                )
            );

            seccionGestionUsuarios.style.display = tienePermisosGestion ? 'block' : 'none';

            if (!tienePermisosGestion) {
                esAdmin.checked = false;
                opcionesGestionUsuarios.style.display = 'none';
                rolesSeleccionados = [];
                tiendasSeleccionadas = [];
                mostrarBadgesRoles();
                mostrarBadgesTiendas();
            } else {
                // Manejar permisos específicos
                if (permisoId === parseInt(permisoGestionUsuariosId)) {
                    if (this.checked) {
                        esAdmin.checked = false;
                        opcionesGestionUsuarios.style.display = 'none';
                    }
                } else if (permisoId === parseInt(permisoGestionUsuariosRolId) || permisoId === parseInt(permisoGestionUsuariosTiendaId)) {
                    opcionesGestionUsuarios.style.display = 'block';
                }
            }
        });
    });

    // Manejar el checkbox de administrador
    document.getElementById('es_admin').addEventListener('change', function() {
        const opcionesGestionUsuarios = document.getElementById('opcionesGestionUsuarios');
        if (this.checked) {
            opcionesGestionUsuarios.style.display = 'none';
            rolesSeleccionados = [];
            tiendasSeleccionadas = [];
            mostrarBadgesRoles();
            mostrarBadgesTiendas();
        } else {
            const tieneGestionUsuariosRol = document.querySelector(`input[value="${permisoGestionUsuariosRolId}"]`).checked;
            const tieneGestionUsuariosTienda = document.querySelector(`input[value="${permisoGestionUsuariosTiendaId}"]`).checked;
            opcionesGestionUsuarios.style.display = (tieneGestionUsuariosRol || tieneGestionUsuariosTienda) ? 'block' : 'none';
        }
    });

    // Manejar el envío del formulario
    document.getElementById('formRol').addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = document.querySelector('#formRol button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
        
        // Capturar los valores de los select2 antes de enviar
        const rolesGestionables = $('#roles_gestionables').val() || [];
        const tiendasGestionables = $('#tiendas_gestionables').val() || [];
        
        // Actualizar los campos ocultos
        $('#roles_rel').val(JSON.stringify(rolesGestionables));
        $('#tiendas_rel').val(JSON.stringify(tiendasGestionables));

        // Determinar la URL y método correctos
        let url = '/admin/roles';
        let method = 'POST';
        
        const rolId = document.getElementById('rol_id').value;
        if (rolId) {
            // En lugar de cambiar el URL y método, mantenemos POST y añadimos _method=PUT
            // lo que Laravel reconoce como un método PUT
            url = '/admin/roles/' + rolId;
            method = 'POST';  // Usar POST pero con _method=PUT en el cuerpo
        }
        
        const formData = new FormData(this);

                // Convertir FormData a objeto JSON
        const formDataObj = {};
        formData.forEach((value, key) => {
            if (key.endsWith('[]')) {
                const cleanKey = key.slice(0, -2);
                if (!formDataObj[cleanKey]) {
                    formDataObj[cleanKey] = [];
                }
                formDataObj[cleanKey].push(value);
            } else if (key === 'roles_seleccionados' || key === 'tiendas_seleccionadas' || 
                      key === 'roles_rel' || key === 'tiendas_rel') {
                // Parsear JSON para estos campos
                try {
                    formDataObj[key] = value ? JSON.parse(value) : [];
                } catch (e) {
                    console.error(`Error parsing JSON for ${key}:`, e);
                    formDataObj[key] = [];
                }
            } else if (key === 'es_admin') {
                // Convertir checkbox a booleano
                formDataObj[key] = true;
            } else {
                formDataObj[key] = value;
            }
        });
        
        // Verificar que los datos se estén construyendo correctamente
        console.log('Enviando datos:', formDataObj);

        // Si es_admin no está en el FormData, significa que no está marcado
        if (!formData.has('es_admin')) {
            formDataObj.es_admin = false;
        }

        // Si estamos editando un rol existente, añadir el campo _method="PUT"
        if (rolId) {
            formDataObj._method = "PUT";
        }

        fetch(url, {
                method: 'POST',  // Siempre usar POST, Laravel determinará el método real con _method
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formDataObj)
            })
            .then(async response => {
                // Si la respuesta es exitosa (2xx) pero no es JSON, probablemente sea una redirección
                if (response.ok) {
                    const contentType = response.headers.get('content-type');
                    const isJson = contentType && contentType.includes('application/json');
                    
                    // Si no es JSON, es probablemente una redirección exitosa
                    if (!isJson) {
                        window.location.reload();
                        return; // Detener ejecución aquí
                    }
                    
                    // Si es JSON, intentar parsearlo
                    try {
                        const data = await response.json();
                        if (data.success) {
                            window.location.reload();
                            return;
                        } else {
                            throw new Error(data.message || 'Error al guardar el rol');
                        }
                    } catch (parseError) {
                        console.error('Error parsing JSON:', parseError);
                        // Si hay error de parseo pero la respuesta fue exitosa, recargar la página
                        window.location.reload();
                        return;
                    }
                }
                
                // Si la respuesta no es exitosa (no 2xx)
                try {
                    const contentType = response.headers.get('content-type');
                    const isJson = contentType && contentType.includes('application/json');
                    const data = isJson ? await response.json() : null;
                    throw new Error(data?.message || 'Error en el servidor');
                } catch (error) {
                    if (error.name === 'SyntaxError') {
                        throw new Error('Error en el servidor. Por favor, verifica la consola para más detalles.');
                    }
                    throw error;
                }
            })
            .catch(async error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                
                // Intentar obtener detalles del error
                let errorDetails = {};
                if (error.response) {
                    try {
                        errorDetails = await error.response.json();
                    } catch (e) { /* Error no es JSON */ }
                }
                
                // Crear un contenedor para los mensajes de error si no existe
                let errorContainer = document.getElementById('form-errors');
                if (!errorContainer) {
                    errorContainer = document.createElement('div');
                    errorContainer.id = 'form-errors';
                    errorContainer.className = 'alert alert-danger mt-3';
                    document.querySelector('.modal-body').prepend(errorContainer);
                }
                
                // Verificar si hay errores de validación de Laravel
                if (errorDetails.errors) {
                    // Mostrar errores específicos
                    let errorHtml = '<strong>Por favor corrige los siguientes errores:</strong><ul>';
                    for (const field in errorDetails.errors) {
                        // Resaltar el campo con error
                        const inputField = document.getElementById(field);
                        if (inputField) inputField.classList.add('is-invalid');
                        
                        // Agregar mensajes de error
                        errorDetails.errors[field].forEach(msg => {
                            errorHtml += `<li>${msg}</li>`;
                        });
                    }
                    errorHtml += '</ul>';
                    errorContainer.innerHTML = errorHtml;
                    errorContainer.style.display = 'block';
                    
                    // Mostrar la primera pestaña si tiene errores
                    document.getElementById('general-tab').click();
                } else {
                    // Error general
                    const errorMessage = errorDetails.message || error.message || 'Ocurrió un error al procesar la solicitud';
                    errorContainer.innerHTML = `<strong>Error:</strong> ${errorMessage}`;
                    errorContainer.style.display = 'block';
                }
                
                // Hacer scroll al inicio del modal para mostrar los errores
                document.querySelector('.modal-body').scrollTop = 0;
            });
    });
</script>
@endsection