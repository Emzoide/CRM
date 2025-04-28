@extends('layouts.app')

@section('content')
<style>
    .tabs {
        display: flex;
        border-bottom: 1px solid #ddd;
        margin-bottom: 20px;
    }

    .tab {
        padding: 10px 20px;
        cursor: pointer;
        border: 1px solid transparent;
        border-bottom: none;
        margin-bottom: -1px;
    }

    .tab.active {
        background: white;
        border-color: #ddd;
        border-bottom: 1px solid white;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background: white;
        width: 90%;
        max-width: 500px;
        margin: 50px auto;
        padding: 20px;
        border-radius: 5px;
    }

    .close {
        float: right;
        cursor: pointer;
        font-size: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .btn {
        padding: 8px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-warning {
        background: #ffc107;
        color: black;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background: #f8f9fa;
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Gestión de Sucursales y Tiendas</h2>
                </div>

                <div class="card-body">
                    <div class="tabs">
                        <div class="tab active" data-tab="sucursales">Sucursales</div>
                        <div class="tab" data-tab="tiendas">Tiendas</div>
                    </div>

                    <div id="sucursales" class="tab-content active">
                        <button class="btn btn-primary mb-3" onclick="openModal('createSucursalModal')">Nueva Sucursal</button>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Dirección</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sucursales as $sucursal)
                                    <tr>
                                        <td>{{ $sucursal->nombre }}</td>
                                        <td>{{ $sucursal->direccion }}</td>
                                        <td>
                                            <button class="btn btn-warning" onclick="openEditSucursalModal({{ $sucursal->id }}, '{{ $sucursal->nombre }}', '{{ $sucursal->direccion }}')">Editar</button>
                                            <form action="{{ route('sucursales.destroy', $sucursal) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Está seguro?')">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="tiendas" class="tab-content">
                        <button class="btn btn-primary mb-3" onclick="openModal('createTiendaModal')">Nueva Tienda</button>
                        <div class="table-responsive">
                            <table>
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
                                        <td>{{ $tienda->sucursal->nombre }}</td>
                                        <td>
                                            <button class="btn btn-warning" onclick="openEditTiendaModal({{ $tienda->id }}, '{{ $tienda->nombre }}', '{{ $tienda->direccion }}', {{ $tienda->sucursal_id }})">Editar</button>
                                            <form action="{{ route('tiendas.destroy', $tienda) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Está seguro?')">Eliminar</button>
                                            </form>
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

<!-- Modal para crear sucursal -->
<div id="createSucursalModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('createSucursalModal')">&times;</span>
        <h3>Nueva Sucursal</h3>
        <form action="{{ route('sucursales.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" required>
            </div>
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <textarea name="direccion" id="direccion" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Crear Sucursal</button>
        </form>
    </div>
</div>

<!-- Modal para editar sucursal -->
<div id="editSucursalModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editSucursalModal')">&times;</span>
        <h3>Editar Sucursal</h3>
        <form id="editSucursalForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="edit_nombre">Nombre</label>
                <input type="text" name="nombre" id="edit_nombre" required>
            </div>
            <div class="form-group">
                <label for="edit_direccion">Dirección</label>
                <textarea name="direccion" id="edit_direccion" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Sucursal</button>
        </form>
    </div>
</div>

<!-- Modal para crear tienda -->
<div id="createTiendaModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('createTiendaModal')">&times;</span>
        <h3>Nueva Tienda</h3>
        <form action="{{ route('tiendas.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="sucursal_id">Sucursal</label>
                <select name="sucursal_id" id="sucursal_id" required>
                    <option value="">Seleccione una sucursal</option>
                    @foreach($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" required>
            </div>
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <textarea name="direccion" id="direccion" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Crear Tienda</button>
        </form>
    </div>
</div>

<!-- Modal para editar tienda -->
<div id="editTiendaModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editTiendaModal')">&times;</span>
        <h3>Editar Tienda</h3>
        <form id="editTiendaForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="edit_sucursal_id">Sucursal</label>
                <select name="sucursal_id" id="edit_sucursal_id" required>
                    <option value="">Seleccione una sucursal</option>
                    @foreach($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="edit_nombre">Nombre</label>
                <input type="text" name="nombre" id="edit_nombre" required>
            </div>
            <div class="form-group">
                <label for="edit_direccion">Dirección</label>
                <textarea name="direccion" id="edit_direccion" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Tienda</button>
        </form>
    </div>
</div>

<script>
    // Funciones para las pestañas
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            // Remover clase activa de todas las pestañas
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Agregar clase activa a la pestaña seleccionada
            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });

    // Funciones para los modales
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function openEditSucursalModal(id, nombre, direccion) {
        const modal = document.getElementById('editSucursalModal');
        const form = document.getElementById('editSucursalForm');

        form.action = `/sucursales/${id}`;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_direccion').value = direccion;

        modal.style.display = 'block';
    }

    function openEditTiendaModal(id, nombre, direccion, sucursalId) {
        const modal = document.getElementById('editTiendaModal');
        const form = document.getElementById('editTiendaForm');

        form.action = `/tiendas/${id}`;
        document.getElementById('edit_sucursal_id').value = sucursalId;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_direccion').value = direccion;

        modal.style.display = 'block';
    }

    // Cerrar modales al hacer clic fuera de ellos
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
</script>
@endsection