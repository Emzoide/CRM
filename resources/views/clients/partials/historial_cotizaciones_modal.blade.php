{{-- resources/views/clients/partials/historial_cotizaciones_modal.blade.php --}}

<div id="historialCotizacionesModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Historial de Cotizaciones</h3>
            <button type="button" onclick="closeHistorialCotizacionesModal()" class="close-button">&times;</button>
        </div>

        <div class="modal-body">
            <div class="relative">
                {{-- Línea vertical del timeline --}}
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                {{-- Lista de cotizaciones --}}
                <div id="cotizacionesTimeline" class="space-y-6">
                    {{-- El contenido se cargará dinámicamente --}}
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin"></i> Cargando cotizaciones...
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" onclick="closeHistorialCotizacionesModal()" class="btn btn-secondary">Cerrar</button>
        </div>
    </div>
</div>

<style>
    /* Estilos específicos para el modal de historial de cotizaciones */
    #historialCotizacionesModal .modal-content {
        max-width: 800px;
    }

    .cotizacion-item {
        position: relative;
        margin-bottom: 30px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform-origin: center;
    }

    .cotizacion-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .cotizacion-marker {
        position: absolute;
        left: -30px;
        top: 12px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        transform: translateX(-50%);
        z-index: 1;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .cotizacion-marker.active {
        background: radial-gradient(circle at 30% 30%, #34d399, #10b981);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2), 0 0 10px rgba(16, 185, 129, 0.4);
        animation: glowWon 2s infinite alternate;
    }

    .cotizacion-marker.superseded {
        background: radial-gradient(circle at 30% 30%, #f87171, #ef4444);
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2), 0 0 10px rgba(239, 68, 68, 0.4);
        animation: glowLost 2s infinite alternate;
    }

    .cotizacion-marker.rejected {
        background: radial-gradient(circle at 30% 30%, #f87171, #ef4444);
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2), 0 0 10px rgba(239, 68, 68, 0.4);
        animation: glowLost 2s infinite alternate;
    }

    .cotizacion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    .cotizacion-header.active {
        background: linear-gradient(to right, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
        border-left: 4px solid #10b981;
    }

    .cotizacion-header.superseded {
        background: linear-gradient(to right, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.05));
        border-left: 4px solid #ef4444;
    }

    .cotizacion-header.rejected {
        background: linear-gradient(to right, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.05));
        border-left: 4px solid #ef4444;
    }

    .cotizacion-title {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: transform 0.2s ease;
    }

    .cotizacion-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: white;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .cotizacion-badge.active {
        background: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }

    .cotizacion-badge.superseded {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }

    .cotizacion-badge.rejected {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }

    .cotizacion-toggle {
        font-size: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: #6b7280;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: rgba(0, 0, 0, 0.05);
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .cotizacion-toggle:hover {
        background-color: rgba(0, 0, 0, 0.1);
        transform: scale(1.1);
    }

    .cotizacion-toggle.active {
        transform: rotate(180deg);
        background-color: rgba(0, 0, 0, 0.1);
    }

    .cotizacion-content {
        background-color: white;
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 0;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    .cotizacion-content.active {
        max-height: 2000px;
        opacity: 1;
        transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1) 0.2s;
    }

    .cotizacion-info {
        padding: 20px;
        background: linear-gradient(to bottom, #ffffff, #f9fafb);
    }

    .cotizacion-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        font-size: 14px;
    }

    .cotizacion-info-item {
        margin-bottom: 8px;
        transition: transform 0.2s ease;
    }

    .cotizacion-info-item:hover {
        transform: translateX(3px);
    }

    .cotizacion-info-label {
        display: block;
        color: #6b7280;
        margin-bottom: 5px;
        font-size: 13px;
        font-weight: 500;
    }

    .cotizacion-info-value {
        font-weight: 600;
        color: #374151;
        padding: 3px 0;
    }

    .cotizacion-info-full {
        grid-column: span 2;
    }

    .cotizacion-detalles {
        margin-top: 15px;
        border-top: 1px dashed rgba(0, 0, 0, 0.1);
        padding-top: 15px;
    }

    .cotizacion-detalle-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .cotizacion-detalle-item:last-child {
        border-bottom: none;
    }

    .cotizacion-detalle-info {
        flex: 1;
    }

    .cotizacion-detalle-precio {
        font-weight: 600;
        color: #374151;
        text-align: right;
    }

    .cotizacion-total {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        margin-top: 10px;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        font-weight: 600;
        font-size: 16px;
    }

    @keyframes glowWon {
        0% {
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2), 0 0 5px rgba(16, 185, 129, 0.3);
        }

        100% {
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3), 0 0 15px rgba(16, 185, 129, 0.5);
        }
    }

    @keyframes glowLost {
        0% {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2), 0 0 5px rgba(239, 68, 68, 0.3);
        }

        100% {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.3), 0 0 15px rgba(239, 68, 68, 0.5);
        }
    }

    @media (max-width: 768px) {
        .cotizacion-info-grid {
            grid-template-columns: 1fr;
        }

        .cotizacion-info-full {
            grid-column: span 1;
        }
    }
</style>

<script>
    function openHistorialCotizacionesModal(oportunidadId) {
        const modal = document.getElementById('historialCotizacionesModal');
        if (!modal) {
            console.error("No se encontró el modal de historial de cotizaciones");
            return;
        }

        // Limpiar el contenido anterior
        const cotizacionesTimeline = document.getElementById('cotizacionesTimeline');
        if (cotizacionesTimeline) {
            cotizacionesTimeline.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Cargando cotizaciones...</div>';
        }

        // Mostrar el modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Cargar los datos de las cotizaciones
        fetch(`/api/oportunidades/${oportunidadId}/cotizaciones`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('No se pudo cargar el historial de cotizaciones');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    renderizarCotizaciones(data.data);
                } else {
                    if (cotizacionesTimeline) {
                        cotizacionesTimeline.innerHTML = '<div class="text-center p-4 text-gray-500">No hay cotizaciones registradas para esta oportunidad.</div>';
                    }
                }
            })
            .catch(error => {
                console.error('Error al cargar el historial de cotizaciones:', error);
                if (cotizacionesTimeline) {
                    cotizacionesTimeline.innerHTML = '<div class="text-center p-4 text-red-500">Error al cargar el historial de cotizaciones. Por favor, intente nuevamente.</div>';
                }
            });
    }

    function closeHistorialCotizacionesModal() {
        const modal = document.getElementById('historialCotizacionesModal');
        if (!modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function renderizarCotizaciones(cotizaciones) {
        const cotizacionesTimeline = document.getElementById('cotizacionesTimeline');
        if (!cotizacionesTimeline) return;

        cotizacionesTimeline.innerHTML = '';

        cotizaciones.forEach((cotizacion, index) => {
            const estado = cotizacion.estado;
            const fechaEmision = new Date(cotizacion.emitida_en).toLocaleDateString();

            // Corregir la fecha de vencimiento
            let fechaVencimiento = 'No especificado';
            if (cotizacion.vence_en) {
                try {
                    fechaVencimiento = new Date(cotizacion.vence_en).toLocaleDateString();
                } catch (e) {
                    console.error('Error al formatear fecha de vencimiento:', e);
                }
            }

            // Corregir la fecha de rechazo
            let fechaRechazo = null;
            if (cotizacion.rechazada_en) {
                try {
                    fechaRechazo = new Date(cotizacion.rechazada_en).toLocaleDateString();
                } catch (e) {
                    console.error('Error al formatear fecha de rechazo:', e);
                }
            }

            // Corregir el nombre del rechazador
            let rechazadorNombre = 'No especificado';
            if (cotizacion.rechazador && cotizacion.rechazador.full_name) {
                rechazadorNombre = cotizacion.rechazador.full_name;
            } else if (cotizacion.rechazada_por_nombre) {
                rechazadorNombre = cotizacion.rechazada_por_nombre;
            }

            // Debug logs
            console.log('Cotización:', {
                codigo: cotizacion.codigo,
                estado: estado,
                fechaRechazo: fechaRechazo,
                rechazador: cotizacion.rechazador,
                rechazada_en: cotizacion.rechazada_en,
                rechazada_por: cotizacion.rechazada_por,
                vence_en: cotizacion.vence_en
            });

            // Traducir estado
            let estadoTexto = '';
            let estadoColor = '';
            switch (estado) {
                case 'active':
                    estadoTexto = 'Activa';
                    estadoColor = 'blue';
                    break;
                case 'superseded':
                    estadoTexto = 'Reemplazada';
                    estadoColor = 'gray';
                    break;
                case 'rejected':
                    estadoTexto = 'Rechazada';
                    estadoColor = 'red';
                    break;
                case 'client-rejected':
                    estadoTexto = 'Rechazada por Cliente';
                    estadoColor = 'red';
                    break;
                case 'approved':
                    estadoTexto = 'Aprobada';
                    estadoColor = 'green';
                    break;
                case 'historical':
                    estadoTexto = 'Histórica';
                    estadoColor = 'gray';
                    break;
                default:
                    estadoTexto = estado;
                    estadoColor = 'gray';
            }

            // Crear elemento de cotización
            const cotizacionElement = document.createElement('div');
            cotizacionElement.className = `cotizacion-item ${estado}`;
            cotizacionElement.innerHTML = `
                <div class="cotizacion-marker ${estado}" style="background-color: ${estadoColor}"></div>
                <div class="cotizacion-header ${estado}" style="border-left: 4px solid ${estadoColor}">
                    <div class="cotizacion-title">
                        <span>${cotizacion.codigo} — ${fechaEmision}</span>
                        <span class="cotizacion-badge ${estado}" style="background-color: ${estadoColor}">${estadoTexto}</span>
                    </div>
                    <div class="cotizacion-toggle">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="cotizacion-content">
                    <div class="cotizacion-info">
                        <div class="cotizacion-info-grid">
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">Código</span>
                                <span class="cotizacion-info-value">${cotizacion.codigo}</span>
                            </div>
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">Fecha de Emisión</span>
                                <span class="cotizacion-info-value">${fechaEmision}</span>
                            </div>
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">Vence el</span>
                                <span class="cotizacion-info-value">${fechaVencimiento}</span>
                            </div>
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">Estado</span>
                                <span class="cotizacion-info-value">${estadoTexto}</span>
                            </div>
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">Tipo de Compra</span>
                                <span class="cotizacion-info-value">${cotizacion.tipo_compra === 'credito' ? 'Crédito' : 'Contado'}</span>
                            </div>
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">Compra a Plazos</span>
                                <span class="cotizacion-info-value">${cotizacion.compra_plazos ? 'Sí' : 'No'}</span>
                            </div>
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">Seguro Vehicular</span>
                                <span class="cotizacion-info-value">${cotizacion.seguro_vehicular ? 'Sí' : 'No'}</span>
                            </div>
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">Total</span>
                                <span class="cotizacion-info-value">S/ ${Number(cotizacion.total).toFixed(2)}</span>
                            </div>
                            ${cotizacion.banco_otro ? `
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">Banco (Otro)</span>
                                <span class="cotizacion-info-value">${cotizacion.banco_otro}</span>
                            </div>
                            ` : ''}
                            ${cotizacion.razon_no_plazos ? `
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">Razón de no plazos</span>
                                <span class="cotizacion-info-value">${cotizacion.razon_no_plazos}</span>
                            </div>
                            ` : ''}
                            ${cotizacion.razon_no_seguro ? `
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">Razón de no seguro</span>
                                <span class="cotizacion-info-value">${cotizacion.razon_no_seguro}</span>
                            </div>
                            ` : ''}
                            ${cotizacion.observacion_call_center ? `
                            <div class="cotizacion-info-item cotizacion-info-full">
                                <span class="cotizacion-info-label">Observación Call Center</span>
                                <span class="cotizacion-info-value">${cotizacion.observacion_call_center}</span>
                            </div>
                            ` : ''}
                            ${cotizacion.motivo_rechazo ? `
                            <div class="cotizacion-info-item cotizacion-info-full">
                                <span class="cotizacion-info-label">Motivo de Rechazo</span>
                                <span class="cotizacion-info-value">${cotizacion.motivo_rechazo}</span>
                            </div>
                            ` : ''}
                            ${(estado === 'rejected' || estado === 'client-rejected' || estado === 'approved') ? `
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">${estado === 'approved' ? 'Aprobada el' : 'Rechazada el'}</span>
                                <span class="cotizacion-info-value">${fechaRechazo || 'No especificado'}</span>
                            </div>
                            <div class="cotizacion-info-item">
                                <span class="cotizacion-info-label">${estado === 'approved' ? 'Aprobada por' : 'Rechazada por'}</span>
                                <span class="cotizacion-info-value">${rechazadorNombre}</span>
                            </div>
                            ` : ''}
                        </div>

                        <div class="cotizacion-detalles">
                            <h4 class="text-md font-medium mb-2">Detalles del Vehículo</h4>
                            ${cotizacion.detalles && cotizacion.detalles.length > 0 ? 
                                cotizacion.detalles.map(detalle => `
                                    <div class="cotizacion-detalle-item">
                                        <div class="cotizacion-detalle-info">
                                            <div class="font-medium">${detalle.version_vehiculo.modelo.marca.nombre} ${detalle.version_vehiculo.modelo.nombre}</div>
                                            <div class="text-sm text-gray-500">${detalle.version_vehiculo.nombre} (${detalle.version_vehiculo.anio})</div>
                                        </div>
                                        <div class="cotizacion-detalle-precio">
                                            <div>S/ ${Number(detalle.precio_unit).toFixed(2)}</div>
                                            <div class="text-sm text-gray-500">x ${detalle.cantidad}</div>
                                        </div>
                                    </div>
                                `).join('') : 
                                '<div class="text-center p-4 text-gray-500">No hay detalles de vehículo disponibles para esta cotización.</div>'
                            }
                            <div class="cotizacion-total">
                                <span>Total</span>
                                <span>S/ ${Number(cotizacion.total).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            cotizacionesTimeline.appendChild(cotizacionElement);

            // Agregar evento para expandir/colapsar
            const header = cotizacionElement.querySelector('.cotizacion-header');
            const content = cotizacionElement.querySelector('.cotizacion-content');
            const toggle = cotizacionElement.querySelector('.cotizacion-toggle');

            header.addEventListener('click', function() {
                content.classList.toggle('active');
                toggle.classList.toggle('active');
            });
        });
    }

    // Cerrar modal al hacer clic fuera o presionar ESC
    window.addEventListener('click', e => {
        if (e.target.id === 'historialCotizacionesModal') closeHistorialCotizacionesModal();
    });

    window.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeHistorialCotizacionesModal();
        }
    });
</script>