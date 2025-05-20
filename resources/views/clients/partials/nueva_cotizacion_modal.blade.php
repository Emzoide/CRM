{{-- resources/views/clients/partials/nueva_cotizacion_modal.blade.php --}}

{{-- Modal de Nueva Cotización --}}
<div id="nuevaCotizacionModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Nueva Cotización</h3>
            <button type="button" onclick="closeNuevaCotizacionModal()" class="close-button">&times;</button>
        </div>

        <form id="nuevaCotizacionForm" method="POST" action="{{ route('oportunidades.cotizaciones.store', ['oportunidad' => 0]) }}">
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
            <input type="hidden" name="oportunidad_id" id="oportunidad_id_input" value="">

            {{-- Información del Vehículo --}}
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
                <input type="number" name="precio_unit" class="form-control" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="cantidad">Cantidad</label>
                <input type="number" name="cantidad" class="form-control" min="1" value="1" required>
            </div>

            {{-- Información de Compra --}}
            <div class="form-group">
                <label for="tipo_compra">Tipo de Compra</label>
                <select name="tipo_compra" class="form-control" id="tipoCompraSelect" required>
                    <option value="">Seleccione tipo de compra</option>
                    <option value="contado">Contado</option>
                    <option value="credito">Financiado</option>
                </select>
            </div>

            <div id="bancoFields" style="display: none;">
                <div class="form-group">
                    <label for="banco_id">Banco</label>
                    <select name="banco_id" class="form-control" id="bancoSelect">
                        <option value="">Seleccione un banco</option>
                        <option value="otro">Otro</option>
                        @foreach($bancos as $banco)
                        <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="bancoOtroContainer" style="display: none;">
                    <div class="form-group">
                        <label for="banco_otro">Especifique el banco</label>
                        <input type="text" name="banco_otro" class="form-control" id="bancoOtroInput" maxlength="100">
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
                    <input type="text" name="razon_no_plazos" class="form-control" id="razonNoPlazosInput" maxlength="200">
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
                    <input type="text" name="razon_no_seguro" class="form-control" id="razonNoSeguroInput" maxlength="200">
                </div>
            </div>

            {{-- Información de Contacto --}}
            <div class="card mt-3 mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información de Contacto</h5>
                </div>
                <div class="card-body">
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
                        <select name="canal_id" class="form-control" id="canalContactoSelect">
                            <option value="">Seleccione un canal</option>
                            @foreach(\App\Models\CanalContacto::orderBy('nombre')->get() as $canal)
                                <option value="{{ $canal->id }}" {{ $cliente->canal_id == $canal->id ? 'selected' : '' }}>{{ $canal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="observacion_call_center">Observaciones</label>
                <textarea name="observacion_call_center" class="form-control" rows="3" maxlength="500"></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeNuevaCotizacionModal()" class="btn btn-secondary">Cancelar</button>
                <button type="button" id="btnGuardarCotizacion" class="btn btn-primary">Guardar Cotización</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
                                option.dataset.precio = version.precio;
                                versionSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error al cargar versiones:', error));
                }
            });
        }

        // Evento para actualizar precio cuando se selecciona una versión
        const versionSelect = document.getElementById('versionSelect');
        const precioInput = document.querySelector('input[name="precio_unit"]');

        if (versionSelect && precioInput) {
            versionSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    precioInput.value = selectedOption.dataset.precio;
                } else {
                    precioInput.value = '';
                }
            });
        }

        // Manejar cambio en tipo de compra
        const tipoCompraSelect = document.getElementById('tipoCompraSelect');
        const bancoFields = document.getElementById('bancoFields');
        const bancoSelect = document.getElementById('bancoSelect');
        const bancoOtroContainer = document.getElementById('bancoOtroContainer');
        const bancoOtroInput = document.getElementById('bancoOtroInput');

        if (tipoCompraSelect && bancoFields) {
            tipoCompraSelect.addEventListener('change', function() {
                if (this.value === 'credito') {
                    bancoFields.style.display = 'block';
                    bancoSelect.setAttribute('required', 'required');
                } else {
                    bancoFields.style.display = 'none';
                    bancoSelect.removeAttribute('required');
                    bancoSelect.value = '';
                    bancoOtroContainer.style.display = 'none';
                    bancoOtroInput.value = '';
                }
            });
        }

        // Manejar cambio en banco
        if (bancoSelect && bancoOtroContainer) {
            bancoSelect.addEventListener('change', function() {
                if (this.value === 'otro') {
                    bancoOtroContainer.style.display = 'block';
                    bancoOtroInput.setAttribute('required', 'required');
                } else {
                    bancoOtroContainer.style.display = 'none';
                    bancoOtroInput.removeAttribute('required');
                    bancoOtroInput.value = '';
                }
            });
        }

        // Manejar cambio en compra a plazos
        const compraPlazosSelect = document.getElementById('compraPlazosSelect');
        const razonNoPlazos = document.getElementById('razonNoPlazos');
        const razonNoPlazosInput = document.getElementById('razonNoPlazosInput');

        if (compraPlazosSelect && razonNoPlazos) {
            compraPlazosSelect.addEventListener('change', function() {
                if (this.value === '0') {
                    razonNoPlazos.style.display = 'block';
                    razonNoPlazosInput.setAttribute('required', 'required');
                } else {
                    razonNoPlazos.style.display = 'none';
                    razonNoPlazosInput.removeAttribute('required');
                    razonNoPlazosInput.value = '';
                }
            });
        }

        // Manejar cambio en seguro vehicular
        const seguroVehicularSelect = document.getElementById('seguroVehicularSelect');
        const razonNoSeguro = document.getElementById('razonNoSeguro');
        const razonNoSeguroInput = document.getElementById('razonNoSeguroInput');

        if (seguroVehicularSelect && razonNoSeguro) {
            seguroVehicularSelect.addEventListener('change', function() {
                if (this.value === '0') {
                    razonNoSeguro.style.display = 'block';
                    razonNoSeguroInput.setAttribute('required', 'required');
                } else {
                    razonNoSeguro.style.display = 'none';
                    razonNoSeguroInput.removeAttribute('required');
                    razonNoSeguroInput.value = '';
                }
            });
        }

        // Botón de guardar cotización con confirmación
        const btnGuardarCotizacion = document.getElementById('btnGuardarCotizacion');
        if (btnGuardarCotizacion) {
            btnGuardarCotizacion.addEventListener('click', function() {
                // Comprobar si ha cambiado algún dato de contacto respecto al cliente original
                const originalEmail = '{{ $cliente->email }}';
                const originalPhone = '{{ $cliente->phone }}';
                const originalAddress = '{{ $cliente->address }}';
                const originalOccupation = '{{ $cliente->occupation }}';
                
                const currentEmail = document.getElementById('email').value;
                const currentPhone = document.getElementById('phone').value;
                const currentAddress = document.getElementById('address').value;
                const currentOccupation = document.getElementById('occupation').value;
                
                const hasChanges = 
                    originalEmail !== currentEmail || 
                    originalPhone !== currentPhone || 
                    originalAddress !== currentAddress || 
                    originalOccupation !== currentOccupation;
                
                if (hasChanges) {
                    // Mostrar modal de confirmación
                    if (confirm('¿Confirma actualizar la información de contacto del cliente? Los datos actualizados se guardarán tanto en esta cotización como en el perfil del cliente.')) {
                        // Si confirma, enviar el formulario
                        document.getElementById('update_client_info').value = '1';
                        document.getElementById('nuevaCotizacionForm').submit();
                    }
                } else {
                    // Si no hay cambios, simplemente enviar el formulario
                    document.getElementById('nuevaCotizacionForm').submit();
                }
            });
        }
    });

    function openNuevaCotizacionModal(oportunidadId) {
        const modal = document.getElementById('nuevaCotizacionModal');
        const form = document.getElementById('nuevaCotizacionForm');
        const oportunidadIdInput = document.getElementById('oportunidad_id_input');

        form.action = `/oportunidades/${oportunidadId}/cotizaciones`;
        oportunidadIdInput.value = oportunidadId;

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Añadir evento para cerrar al hacer clic fuera del modal
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeNuevaCotizacionModal();
            }
        });

        // Añadir evento para cerrar al presionar Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.style.display === 'flex') {
                closeNuevaCotizacionModal();
            }
        });
    }

    function closeNuevaCotizacionModal() {
        const modal = document.getElementById('nuevaCotizacionModal');
        modal.style.display = 'none';
        document.body.style.overflow = '';

        // Limpiar los eventos para evitar duplicados
        const newModal = modal.cloneNode(true);
        modal.parentNode.replaceChild(newModal, modal);
    }
</script>