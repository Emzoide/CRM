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
            <h3 class="modal-title">Nuevo Seguimiento</h3>
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
                    <label for="seguro_vehicular">Seguro Vehicular</label>
                    <select name="seguro_vehicular" class="form-control" data-required="true">
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div id="razonNoSeguroContainer" style="display: none;">
                    <div class="form-group">
                        <label for="razon_no_seguro">Razón por la que no toma el seguro</label>
                        <input type="text" name="razon_no_seguro" class="form-control" id="razonNoSeguroInput">
                    </div>
                </div>

                <div class="form-group">
                    <label for="compra_plazos">Compra a Plazos</label>
                    <select name="compra_plazos" class="form-control">
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div id="razonNoPlazosContainer" style="display: none;">
                    <div class="form-group">
                        <label for="razon_no_plazos">Razón por la que no compra a plazos</label>
                        <input type="text" name="razon_no_plazos" class="form-control" id="razonNoPlazosInput">
                    </div>
                </div>

                <div class="form-group">
                    <label for="observacion_call_center">Observaciones del Call Center</label>
                    <textarea name="observacion_call_center" class="form-control" rows="3"></textarea>
                </div>
            </div>
            @endif

            {{-- Campos de seguimiento (siempre visibles) --}}
            <div id="camposSeguimiento">
                <h4 class="text-md font-medium mb-2">Información de Seguimiento</h4>

                <div class="form-group">
                    <label for="contacto_en">Fecha y Hora de Contacto</label>
                    <input type="datetime-local" name="contacto_en" class="form-control" data-required="true">
                </div>

                <div class="form-group">
                    <label for="resultado">Resultado del Contacto</label>
                    <select name="resultado" class="form-control" data-required="true">
                        <option value="">Seleccione un resultado</option>
                        <option value="called">Llamada</option>
                        <option value="emailed">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="visited">Visita</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="comentario">Comentario</label>
                    <textarea name="comentario" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="proxima_accion">Próxima Acción</label>
                    <input type="text" name="proxima_accion" class="form-control" maxlength="150">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeSeguimientoModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal de Cotización Activa --}}
<div id="cotizacionActivaModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Cotización Activa</h3>
            <button type="button" onclick="closeCotizacionActivaModal()" class="close-button">&times;</button>
        </div>
        <div id="cotizacionContent">
            {{-- El contenido se cargará dinámicamente --}}
        </div>
        <div class="modal-footer">
            <button type="button" onclick="descargarCotizacionPDF()" class="btn btn-primary">
                <i class="fas fa-download mr-2"></i>Descargar PDF
            </button>
            <button type="button" onclick="closeCotizacionActivaModal()" class="btn btn-secondary">Cerrar</button>
            <button type="button" onclick="mostrarFormularioRechazo()" class="btn btn-danger">Cerrar Cotización</button>
        </div>
    </div>
</div>

<div id="formularioRechazo" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Rechazar Cotización</h3>
            <button type="button" onclick="cerrarFormularioRechazo()" class="close-button">&times;</button>
        </div>
        <div class="modal-body">
            <form id="rechazoForm">
                <div class="form-group">
                    <label for="motivoRechazo">Motivo de Rechazo</label>
                    <textarea id="motivoRechazo" name="motivoRechazo" class="form-control" rows="3" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" onclick="cerrarFormularioRechazo()" class="btn btn-secondary">Cancelar</button>
                    <button type="button" onclick="confirmarRechazo()" class="btn btn-danger">Confirmar Rechazo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="confirmacionRechazo" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 400px; text-align: center; padding: 2rem;">
        <div class="modal-header" style="border: none; justify-content: center; display: flex; flex-direction: column; align-items: center;">
            <div style="color: #dc2626; margin-bottom: 1.5rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem;"></i>
            </div>

            <div style="margin-bottom: 0.5rem;">
                <h3 style="font-size: 1.5rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">
                    ¿Estás seguro de rechazar la cotización?
                </h3>

                <p style="color: #6b7280;">
                    Esta acción no se puede deshacer
                </p>
            </div>
        </div>

        <div class="modal-body" style="display: flex; justify-content: center; gap: 1rem;">
            <button type="button"
                onclick="cerrarConfirmacionRechazo()"
                style="padding: 0.75rem 1.5rem; border-radius: 0.375rem; background: #f3f4f6; color: #374151; font-weight: 500; border: 1px solid #d1d5db; transition: all 0.2s;">
                Cancelar
            </button>

            <button type="button"
                onclick="rechazarCotizacion()"
                style="padding: 0.75rem 1.5rem; border-radius: 0.375rem; background: #dc2626; color: white; font-weight: 500; border: none; transition: all 0.2s;">
                Sí, Rechazar
            </button>
        </div>
    </div>
</div>

{{-- Agregar las librerías necesarias --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

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

    .btn-danger {
        background-color: #dc2626;
        color: white;
        border: none;

    }

    .btn-danger:hover {
        background-color: #b91c1c;
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
    #razonNoSeguroContainer,
    #razonNoPlazosContainer {
        background-color: #f3f4f6;
        padding: 0.75rem;
        border-radius: 0.375rem;
        margin-top: 0.5rem;
    }

    .cotizacion-info {
        padding: 1rem;
    }

    .cotizacion-info-header {
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .cotizacion-info-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .cotizacion-info-date {
        color: #666;
        font-size: 0.875rem;
    }

    .cotizacion-info-section {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background-color: #f9fafb;
        border-radius: 0.5rem;
    }

    .cotizacion-info-subtitle {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #374151;
    }

    .cotizacion-info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .cotizacion-info-label {
        font-weight: 500;
        color: #4b5563;
    }

    .cotizacion-info-value {
        color: #111827;
    }
</style>

@push('scripts')
<script>
    let cotizacionActualId = null;


    function openCotizacionActivaModal(oportunidadId) {
        const modal = document.getElementById('cotizacionActivaModal');
        if (!modal) {
            console.error(" No se encontró el modal de cotización activa");
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

                    cotizacionActualId = cotizacion.id;

                    // Actualizar el contenido del modal
                    if (cotizacionContent) {
                        cotizacionContent.innerHTML = `
                            <div class="cotizacion-info">
                                <div class="cotizacion-info-header">
                                    <h2 class="cotizacion-info-title">Cotización Activa</h2>
                                    <p class="cotizacion-info-date">Fecha: ${new Date(cotizacion.created_at).toLocaleDateString()}</p>
                                </div>
                                <div class="cotizacion-info-section">
                                    <h3 class="cotizacion-info-subtitle">Información del Cliente</h3>
                                    <div class="cotizacion-info-item">
                                        <span class="cotizacion-info-label">Nombre:</span>
                                        <span class="cotizacion-info-value">${cliente.nombre}</span>
                                    </div>
                                    <div class="cotizacion-info-item">
                                        <span class="cotizacion-info-label">Email:</span>
                                        <span class="cotizacion-info-value">${cliente.email}</span>
                                    </div>
                                    <div class="cotizacion-info-item">
                                        <span class="cotizacion-info-label">Teléfono:</span>
                                        <span class="cotizacion-info-value">${cliente.telefono}</span>
                                    </div>
                                </div>
                                <div class="cotizacion-info-section">
                                    <h3 class="cotizacion-info-subtitle">Información del Vehículo</h3>
                                    <div class="cotizacion-info-item">
                                        <span class="cotizacion-info-label">Marca:</span>
                                        <span class="cotizacion-info-value">${vehiculo.marca.nombre}</span>
                                    </div>
                                    <div class="cotizacion-info-item">
                                        <span class="cotizacion-info-label">Modelo:</span>
                                        <span class="cotizacion-info-value">${vehiculo.modelo.nombre}</span>
                                    </div>
                                    <div class="cotizacion-info-item">
                                        <span class="cotizacion-info-label">Versión:</span>
                                        <span class="cotizacion-info-value">${vehiculo.version.nombre}</span>
                                    </div>
                                    <div class="cotizacion-info-item">
                                        <span class="cotizacion-info-label">Precio Unitario:</span>
                                        <span class="cotizacion-info-value">${vehiculo.precio_unitario.toLocaleString('es-ES', { style: 'currency', currency: 'EUR' })}</span>
                                    </div>
                                </div>
                                <div class="cotizacion-info-section">
                                    <h3 class="cotizacion-info-subtitle">Información de la Cotización</h3>
                                    <div class="cotizacion-info-item">
                                        <span class="cotizacion-info-label">Tipo de Compra:</span>
                                        <span class="cotizacion-info-value">${cotizacion.tipo_compra === 'credito' ? 'Financiado' : 'Contado'}</span>
                                    </div>
                                    <div class="cotizacion-info-item">
                                        <span class="cotizacion-info-label">Seguro Vehicular:</span>
                                        <span class="cotizacion-info-value">${cotizacion.seguro_vehicular ? 'Sí' : 'No'}</span>
                                    </div>
                                    <div class="cotizacion-info-item">
                                        <span class="cotizacion-info-label">Banco:</span>
                                        <span class="cotizacion-info-value">${banco ? banco.nombre : 'Sin especificar'}</span>
                                    </div>
                                </div>
                            </div>
                        `;
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

    function cerrarFormularioRechazo() {
        const modal = document.getElementById('formularioRechazo');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            document.getElementById('motivoRechazo').value = '';
        }
    }

    function confirmarRechazo() {
        const motivoRechazo = document.getElementById('motivoRechazo').value.trim();
        if (!motivoRechazo) {
            alert('Por favor, ingresa un motivo de rechazo');
            return;
        }

        const modal = document.getElementById('confirmacionRechazo');
        console.log('modal');
        document.getElementById('confirmacionRechazo').style.display = 'flex';
    }

    function cerrarConfirmacionRechazo() {
        const modal = document.getElementById('confirmacionRechazo');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function rechazarCotizacion() {
        console.log('cotizacionActualId: ', cotizacionActualId);
        if (!cotizacionActualId) {
            console.error('No hay una cotización seleccionada');
            return;
        }

        const motivoRechazo = document.getElementById('motivoRechazo').value.trim();

        // Enviar la solicitud para rechazar la cotización
        fetch(`/api/cotizaciones/${cotizacionActualId}/rechazar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    motivo_rechazo: motivoRechazo
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al rechazar la cotización');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Cotización rechazada correctamente');
                    cerrarConfirmacionRechazo();
                    cerrarFormularioRechazo();
                    closeCotizacionActivaModal();
                    // Recargar la página para actualizar la información
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al rechazar la cotización');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al rechazar la cotización. Por favor, intente nuevamente.');
            });
    }

    function mostrarFormularioRechazo() {
        const modal = document.getElementById('formularioRechazo');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function descargarCotizacionPDF() {
        const element = document.getElementById('cotizacionContent');
        const modal = document.getElementById('cotizacionActivaModal');

        // Ocultar los botones del footer temporalmente
        const footer = modal.querySelector('.modal-footer');
        const originalDisplay = footer.style.display;
        footer.style.display = 'none';

        html2canvas(element, {
            scale: 2, // Mejor calidad
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new window.jspdf.jsPDF('p', 'mm', 'a4');
            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            // Agregar el logo o encabezado si lo tienes
            // pdf.addImage(logoData, 'PNG', 10, 10, 30, 30);

            // Agregar el contenido
            pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);

            // Agregar pie de página
            const pageCount = pdf.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                pdf.setPage(i);
                pdf.setFontSize(8);
                pdf.text('Documento generado automáticamente por el CRM', pdfWidth / 2, pdf.internal.pageSize.getHeight() - 10, {
                    align: 'center'
                });
                pdf.text(`Página ${i} de ${pageCount}`, pdfWidth / 2, pdf.internal.pageSize.getHeight() - 5, {
                    align: 'center'
                });
            }

            // Restaurar los botones del footer
            footer.style.display = originalDisplay;

            // Descargar el PDF
            pdf.save('cotizacion.pdf');
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const tipoCompraSelect = document.querySelector('[name="tipo_compra"]');
        const camposFinanciamiento = document.getElementById('camposFinanciamiento');
        const seguroVehicularSelect = document.querySelector('[name="seguro_vehicular"]');
        const razonNoSeguroContainer = document.getElementById('razonNoSeguroContainer');
        const compraPlazosSelect = document.querySelector('[name="compra_plazos"]');
        const razonNoPlazosContainer = document.getElementById('razonNoPlazosContainer');
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
        }

        // Función para manejar cambios en el tipo de compra
        function handleTipoCompraChange() {
            if (!tipoCompraSelect || !camposFinanciamiento) return;
            const esFinanciado = tipoCompraSelect.value === 'credito';
            camposFinanciamiento.style.display = esFinanciado ? 'block' : 'none';
        }

        // Función para manejar cambios en seguro vehicular
        function handleSeguroVehicularChange() {
            if (!seguroVehicularSelect || !razonNoSeguroContainer) return;
            const mostrarRazon = seguroVehicularSelect.value === '0';
            razonNoSeguroContainer.style.display = mostrarRazon ? 'block' : 'none';
        }

        // Función para manejar cambios en compra a plazos
        function handleCompraPlazosChange() {
            if (!compraPlazosSelect || !razonNoPlazosContainer) return;
            const mostrarRazon = compraPlazosSelect.value === '0';
            razonNoPlazosContainer.style.display = mostrarRazon ? 'block' : 'none';
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
    });
</script>
@endpush