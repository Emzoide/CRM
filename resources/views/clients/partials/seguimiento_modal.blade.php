{{-- resources/views/clients/partials/seguimiento_modal.blade.php --}}

@php
$sinOportActiva = empty($activa);
// Si hay activa pero su etapa es won/lost la tratamos como cerrada:
if ($activa && in_array($activa->etapa_actual, ['won','lost'])) $sinOportActiva = true;
@endphp

{{-- Modal de Seguimiento --}}
<div id="seguimientoModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            @if($sinOportActiva)
            <h3 class="modal-title">Nueva Oportunidad</h3>
            @else
            <h3 class="modal-title">Nuevo Seguimiento</h3>
            @endif
            <button type="button" onclick="closeSeguimientoModal()" class="close-button">&times;</button>
        </div>

        <form id="seguimientoForm" method="POST" action="{{ route('seguimientos.store') }}">
            @csrf
            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
            <input type="hidden" name="oportunidad_id" value="{{ $sinOportActiva ? '' : $activa->id }}">

            <div class="form-group">
                <label for="contacto_en">Fecha y Hora</label>
                <input type="datetime-local" name="contacto_en" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="resultado">Medio de Contacto</label>
                <select name="resultado" class="form-control" required>
                    <option value="called">Llamada</option>
                    <option value="emailed">Email</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="visited">Visita</option>
                </select>
            </div>

            {{-- Campos de cotización (solo para nuevas oportunidades) --}}
            @if($sinOportActiva)
            <div id="camposCotizacion">
                <h4 class="text-md font-medium mb-2">Información de Vehículo</h4>
                {{-- Selects de Marca-Modelo-Versión --}}
                <div class="form-group">
                    <label for="marca_id">Marca</label>
                    <select name="marca_id" class="form-control" id="marcaSelect" data-required="true">
                        <option value="">Seleccione una marca</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="modelo_id">Modelo</label>
                    <select name="modelo_id" class="form-control" id="modeloSelect" data-required="true">
                        <option value="">Seleccione un modelo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="version_vehiculo_id">Versión</label>
                    <select name="version_vehiculo_id" class="form-control" id="versionSelect" data-required="true">
                        <option value="">Seleccione una versión</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="precio_unit">Precio Unitario</label>
                    <input type="number" name="precio_unit" class="form-control" step="0.01" data-required="true">
                </div>
                
                <h4 class="text-md font-medium mb-2 mt-4">Información de Contacto</h4>
                <input type="hidden" name="update_client_info" id="update_client_info" value="1">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ $cliente->email }}" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ $cliente->phone }}" maxlength="50">
                </div>
                <div class="form-group">
                    <label for="address">Dirección</label>
                    <input type="text" name="address" id="address" class="form-control" value="{{ $cliente->address }}" maxlength="150">
                </div>
                <div class="form-group">
                    <label for="occupation">Ocupación</label>
                    <input type="text" name="occupation" id="occupation" class="form-control" value="{{ $cliente->occupation }}" maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="canal_id">Canal de Contacto</label>
                    <select name="canal_id" class="form-control" id="canalSelect">
                        <option value="">Seleccione un canal</option>
                        @foreach(\App\Models\CanalContacto::orderBy('nombre')->get() as $canal)
                            <option value="{{ $canal->id }}" {{ $cliente->canal_id == $canal->id ? 'selected' : '' }}>{{ $canal->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="tipo_compra">Tipo de Compra</label>
                    <select name="tipo_compra" class="form-control" data-required="true">
                        <option value="">Seleccione tipo de compra</option>
                        <option value="contado">Contado</option>
                        <option value="credito">Financiado</option>
                    </select>
                </div>

                <div id="camposFinanciamiento" style="display: none;">
                    <div class="form-group">
                        <label for="banco_id">Banco</label>
                        <select name="banco_id" class="form-control" id="bancoSelect">
                            <option value="">Seleccione un banco</option>
                            <option value="otro">Otro</option>
                            @if(isset($bancos) && count($bancos) > 0)
                            @foreach($bancos as $banco)
                            <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    <div id="bancoOtroContainer" style="display: none;">
                        <div class="form-group">
                            <label for="banco_otro">Especifique el banco</label>
                            <input type="text" name="banco_otro" class="form-control" id="bancoOtroInput">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="compra_plazos">Compra a Plazos</label>
                    <select name="compra_plazos" class="form-control" id="compraPlazosSelect">
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div id="razonNoPlazos" style="display: none;">
                    <div class="form-group">
                        <label for="razon_no_plazos">Razón de no plazos</label>
                        <input type="text" name="razon_no_plazos" class="form-control" id="razonNoPlazosInput">
                    </div>
                </div>

                <div class="form-group">
                    <label for="seguro_vehicular">Seguro Vehicular</label>
                    <select name="seguro_vehicular" class="form-control" id="seguroVehicularSelect" required>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div id="razonNoSeguro" style="display: none;">
                    <div class="form-group">
                        <label for="razon_no_seguro">Razón de no seguro</label>
                        <input type="text" name="razon_no_seguro" class="form-control" id="razonNoSeguroInput">
                    </div>
                </div>
            </div>
            @endif
            <div class="form-group">
                <label for="comentario">Comentario</label>
                <textarea name="comentario" rows="3" class="form-control" required></textarea>
            </div>

            <div class="form-group proxima-accion">
                <label for="proxima_accion">Próxima Acción</label>
                <input type="text" name="proxima_accion" class="form-control" required>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeSeguimientoModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        padding: 1rem;
        border-radius: 0.5rem;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .close-button {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #666;
        padding: 0;
    }

    .form-group {
        margin-bottom: 0.75rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.25rem;
        font-size: 0.875rem;
        color: #4b5563;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    textarea.form-control {
        min-height: 80px;
        resize: vertical;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background-color: #3b82f6;
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background-color: #2563eb;
    }

    .btn-secondary {
        background-color: #6b7280;
        color: white;
        border: none;
    }

    .btn-secondary:hover {
        background-color: #4b5563;
    }

    /* Estilos para los campos de cotización */
    #camposCotizacion {
        background-color: #f9fafb;
        padding: 1rem;
        border-radius: 0.375rem;
        margin-bottom: 1rem;
    }

    #camposCotizacion .form-group {
        margin-bottom: 0.5rem;
    }

    /* Estilos para los campos condicionales */
    #camposFinanciamiento,
    #razonNoSeguro,
    #razonNoPlazos {
        background-color: #f3f4f6;
        padding: 0.75rem;
        border-radius: 0.375rem;
        margin-top: 0.5rem;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tipoCompraSelect = document.querySelector('[name="tipo_compra"]');
        const camposFinanciamiento = document.getElementById('camposFinanciamiento');
        const seguroVehicularSelect = document.querySelector('[name="seguro_vehicular"]');
        const razonNoSeguro = document.getElementById('razonNoSeguro');
        const compraPlazosSelect = document.querySelector('[name="compra_plazos"]');
        const razonNoPlazos = document.getElementById('razonNoPlazos');
        const bancoSelect = document.getElementById('bancoSelect');
        const bancoOtroContainer = document.getElementById('bancoOtroContainer');

        // Cargar marcas al inicio
        fetch('/api/marcas')
            .then(response => response.json())
            .then(data => {
                const marcaSelect = document.getElementById('marcaSelect');
                if (!marcaSelect) return; // Si no existe el elemento, salimos

                data.forEach(marca => {
                    const option = document.createElement('option');
                    option.value = marca.id;
                    option.textContent = marca.nombre;
                    marcaSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error al cargar marcas:', error));

        // Evento para cargar modelos cuando se selecciona una marca
        const marcaSelect = document.getElementById('marcaSelect');
        if (marcaSelect) {
            marcaSelect.addEventListener('change', function() {
                const marcaId = this.value;
                const modeloSelect = document.getElementById('modeloSelect');
                const versionSelect = document.getElementById('versionSelect');

                // Limpiar selects
                modeloSelect.innerHTML = '<option value="">Seleccione un modelo</option>';
                versionSelect.innerHTML = '<option value="">Seleccione una versión</option>';

                if (marcaId) {
                    fetch(`/api/modelos/${marcaId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(modelo => {
                                const option = document.createElement('option');
                                option.value = modelo.id;
                                option.textContent = modelo.nombre;
                                modeloSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error al cargar modelos:', error));
                }
            });
        }

        // Evento para cargar versiones cuando se selecciona un modelo
        const modeloSelect = document.getElementById('modeloSelect');
        if (modeloSelect) {
            modeloSelect.addEventListener('change', function() {
                const modeloId = this.value;
                const versionSelect = document.getElementById('versionSelect');

                // Limpiar select
                versionSelect.innerHTML = '<option value="">Seleccione una versión</option>';

                if (modeloId) {
                    fetch(`/api/versiones/${modeloId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(version => {
                                const option = document.createElement('option');
                                option.value = version.id;
                                option.textContent = version.nombre;
                                versionSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error al cargar versiones:', error));
                }
            });
        }

        // Función para manejar la visibilidad del campo "Otro Banco"
        function handleBancoChange() {
            if (!bancoSelect || !bancoOtroContainer) return;
            const mostrarOtroBanco = bancoSelect.value === 'otro';
            bancoOtroContainer.style.display = mostrarOtroBanco ? 'block' : 'none';

            // Hacer el campo banco_otro requerido cuando se selecciona "otro"
            const bancoOtroInput = document.getElementById('bancoOtroInput');
            if (bancoOtroInput) {
                if (mostrarOtroBanco) {
                    bancoOtroInput.setAttribute('required', 'required');
                } else {
                    bancoOtroInput.removeAttribute('required');
                }
            }
        }

        // Función para manejar cambios en el tipo de compra
        function handleTipoCompraChange() {
            if (!tipoCompraSelect || !camposFinanciamiento) return;
            const esFinanciado = tipoCompraSelect.value === 'credito';
            camposFinanciamiento.style.display = esFinanciado ? 'block' : 'none';
        }

        // Función para manejar cambios en seguro vehicular
        function handleSeguroVehicularChange() {
            if (!seguroVehicularSelect || !razonNoSeguro) return;
            const mostrarRazon = seguroVehicularSelect.value === '0';
            razonNoSeguro.style.display = mostrarRazon ? 'block' : 'none';
        }

        // Función para manejar cambios en compra a plazos
        function handleCompraPlazosChange() {
            if (!compraPlazosSelect || !razonNoPlazos) return;
            const mostrarRazon = compraPlazosSelect.value === '0';
            razonNoPlazos.style.display = mostrarRazon ? 'block' : 'none';
        }

        // Agregar event listeners
        if (tipoCompraSelect) {
            tipoCompraSelect.addEventListener('change', handleTipoCompraChange);
            handleTipoCompraChange();
        }

        if (seguroVehicularSelect) {
            seguroVehicularSelect.addEventListener('change', handleSeguroVehicularChange);
            handleSeguroVehicularChange();
        }

        if (compraPlazosSelect) {
            compraPlazosSelect.addEventListener('change', handleCompraPlazosChange);
            handleCompraPlazosChange();
        }

        if (bancoSelect) {
            bancoSelect.addEventListener('change', handleBancoChange);
            handleBancoChange();
        }

        // Función para manejar la visibilidad de los campos de cotización
        function toggleCamposCotizacion(mostrar) {
            const camposCotizacion = document.getElementById('camposCotizacion');
            if (!camposCotizacion) return;

            const camposRequeridos = camposCotizacion.querySelectorAll('[data-required="true"]');
            camposRequeridos.forEach(campo => {
                if (mostrar) {
                    campo.setAttribute('required', 'required');
                } else {
                    campo.removeAttribute('required');
                }
            });
        }

        // Modificar las funciones de apertura de modales
        window.openSeguimientoModal = function(oportunidadId, sinOportunidadActiva) {
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
                toggleCamposCotizacion(sinOportunidadActiva);
            }

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };

        window.openNuevaOportunidadModal = function(sinOportunidadActiva) {
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
                toggleCamposCotizacion(true);
            }

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };
        // Inicializar el estado de los campos al cargar la página
        const camposCotizacion = document.getElementById('camposCotizacion');
        if (camposCotizacion) {
            toggleCamposCotizacion(camposCotizacion.style.display !== 'none');
        }

        // Mostrar/ocultar campos de banco según tipo de compra
        tipoCompraSelect.addEventListener('change', function() {
            if (this.value === 'credito') {
                camposFinanciamiento.style.display = 'block';
                bancoSelect.setAttribute('required', 'required');
            } else {
                camposFinanciamiento.style.display = 'none';
                bancoSelect.removeAttribute('required');
                bancoOtroContainer.style.display = 'none';
            }
        });

        // Mostrar/ocultar campo de banco otro
        bancoSelect.addEventListener('change', function() {
            if (this.value === 'otro') {
                bancoOtroContainer.style.display = 'block';
                document.getElementById('bancoOtroInput').setAttribute('required', 'required');
            } else {
                bancoOtroContainer.style.display = 'none';
                document.getElementById('bancoOtroInput').removeAttribute('required');
            }
        });

        // Mostrar/ocultar razón de no seguro
        const razonNoSeguroInput = document.getElementById('razonNoSeguroInput');

        seguroVehicularSelect.addEventListener('change', function() {
            if (this.value === '0') {
                razonNoSeguro.style.display = 'block';
                razonNoSeguroInput.setAttribute('required', 'required');
            } else {
                razonNoSeguro.style.display = 'none';
                razonNoSeguroInput.removeAttribute('required');
            }
        });
    });
</script>
@endpush