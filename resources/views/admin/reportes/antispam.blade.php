@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reporte de Consentimientos Antispam</h3>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="fecha_inicio">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin">
                        </div>
                        <div class="col-md-3">
                            <label for="estado">Estado</label>
                            <select class="form-control" id="estado">
                                <option value="">Todos</option>
                                <option value="1">Aceptado</option>
                                <option value="0">Rechazado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary btn-block" onclick="filtrar()">Filtrar</button>
                        </div>
                    </div>

                    <!-- Tabla de resultados -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tablaConsentimientos">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>DNI</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Acepta Políticas</th>
                                    <th>Acepta Comunicaciones</th>
                                    <th>IP</th>
                                    <th>User Agent</th>
                                    <th>Fuente Origen</th>
                                    <th>Fecha Aceptación</th>
                                    <th>Documentos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-3">
                        <nav aria-label="Page navigation">
                            <ul class="pagination" id="pagination">
                                <!-- La paginación se generará dinámicamente -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver documentos -->
<div class="modal fade" id="documentoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentoModalTitle">Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="documentoImagen" src="" class="img-fluid" alt="Documento">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentPage = 1;
    const perPage = 10;

    function cargarDatos(page = 1) {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;
        const estado = document.getElementById('estado').value;

        fetch(`/api/reportes/antispam?page=${page}&per_page=${perPage}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&estado=${estado}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Error en la respuesta:', data.message);
                    return;
                }

                const tbody = document.querySelector('#tablaConsentimientos tbody');
                tbody.innerHTML = '';

                data.data.forEach(consentimiento => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${consentimiento.nombre || '-'}</td>
                        <td>${consentimiento.apellido || '-'}</td>
                        <td>${consentimiento.dni || '-'}</td>
                        <td>${consentimiento.email || '-'}</td>
                        <td>${consentimiento.telefono || '-'}</td>
                        <td>
                            <span class="badge ${consentimiento.acepta_politicas ? 'bg-success' : 'bg-danger'}">
                                ${consentimiento.acepta_politicas ? 'Sí' : 'No'}
                            </span>
                        </td>
                        <td>
                            <span class="badge ${consentimiento.acepta_comunicaciones ? 'bg-success' : 'bg-danger'}">
                                ${consentimiento.acepta_comunicaciones ? 'Sí' : 'No'}
                            </span>
                        </td>
                        <td>${consentimiento.ip || '-'}</td>
                        <td>${consentimiento.user_agent || '-'}</td>
                        <td>${consentimiento.fuente_origen || '-'}</td>
                        <td>${new Date(consentimiento.fecha_aceptacion).toLocaleString()}</td>
                        <td>
                            ${consentimiento.foto_dni_url ? `
                                <button class="btn btn-sm btn-link" onclick="mostrarDocumento('${consentimiento.foto_dni_url}', 'DNI')">
                                    <i class="fas fa-id-card"></i>
                                </button>
                            ` : '-'}
                            ${consentimiento.firma_digital_url ? `
                                <button class="btn btn-sm btn-link" onclick="mostrarDocumento('${consentimiento.firma_digital_url}', 'Firma')">
                                    <i class="fas fa-signature"></i>
                                </button>
                            ` : '-'}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="verDetalle('${consentimiento.dni}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Actualizar paginación
                const pagination = document.getElementById('pagination');
                pagination.innerHTML = '';

                // Botón anterior
                const prevLi = document.createElement('li');
                prevLi.className = `page-item ${data.current_page === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML = `<a class="page-link" href="#" onclick="cargarDatos(${data.current_page - 1})">Anterior</a>`;
                pagination.appendChild(prevLi);

                // Números de página
                for (let i = 1; i <= data.last_page; i++) {
                    const li = document.createElement('li');
                    li.className = `page-item ${i === data.current_page ? 'active' : ''}`;
                    li.innerHTML = `<a class="page-link" href="#" onclick="cargarDatos(${i})">${i}</a>`;
                    pagination.appendChild(li);
                }

                // Botón siguiente
                const nextLi = document.createElement('li');
                nextLi.className = `page-item ${data.current_page === data.last_page ? 'disabled' : ''}`;
                nextLi.innerHTML = `<a class="page-link" href="#" onclick="cargarDatos(${data.current_page + 1})">Siguiente</a>`;
                pagination.appendChild(nextLi);
            })
            .catch(error => {
                console.error('Error al cargar datos:', error);
                const tbody = document.querySelector('#tablaConsentimientos tbody');
                tbody.innerHTML = '<tr><td colspan="13" class="text-center text-danger">Error al cargar los datos</td></tr>';
            });
    }

    function filtrar() {
        currentPage = 1;
        cargarDatos(currentPage);
    }

    function verDetalle(dni) {
        window.open(`https://interamericana-norte.com/consentimiento?dni=${dni}`, '_blank');
    }

    function mostrarDocumento(url, tipo) {
        const modal = new bootstrap.Modal(document.getElementById('documentoModal'));
        const imagen = document.getElementById('documentoImagen');
        const titulo = document.getElementById('documentoModalTitle');

        titulo.textContent = tipo;
        imagen.src = url;
        modal.show();
    }

    // Cargar datos iniciales
    document.addEventListener('DOMContentLoaded', () => {
        cargarDatos();
    });
</script>
@endpush