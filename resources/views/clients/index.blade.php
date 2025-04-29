@extends('layouts.app')

@section('content')
<!-- <link rel="stylesheet" href="{{ asset('css/modal.css') }}"> -->
<style>
    .btn-nuevo-cliente {
        display: inline-flex;
        align-items: center;
        gap: 0.5em;
        padding: 0.5em 1.2em;
        font-size: 1rem;
        font-weight: 500;
        border-radius: 0.5em;
        background: #2563eb;
        color: #fff;
        border: none;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.08);
        transition: background 0.2s, box-shadow 0.2s;
    }

    .btn-nuevo-cliente:hover {
        background: #1d4ed8;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }

    .icon-nuevo-cliente {
        width: 1.1em;
        height: 1.1em;
        fill: currentColor;
        display: inline-block;
        vertical-align: middle;
    }

    /* MODAL ESTILOS MODERNOS */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-container {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
        max-width: 500px;
        width: 95%;
        max-height: 90vh;
        overflow-y: auto;
        animation: fadeInModal 0.2s;
    }

    @keyframes fadeInModal {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        padding: 1.25rem 1.5rem 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f9fafb;
        border-top-left-radius: 14px;
        border-top-right-radius: 14px;
    }

    .modal-header h2 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }

    .modal-header button {
        background: none;
        border: none;
        color: #6b7280;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 50%;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-header button:hover {
        background: #e5e7eb;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        background: #f9fafb;
        border-bottom-left-radius: 14px;
        border-bottom-right-radius: 14px;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
        color: #374151;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 1rem;
        margin-bottom: 0.5rem;
        background: #f9fafb;
        transition: border 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
        background: #fff;
    }

    .badge-primary {
        background-color: #0DB07B !important;
        color: #fff !important;
    }
</style>

<div class="container mx-auto py-6">
    {{-- Cabecera --}}
    <div class="flex justify-between items-center mb-6">
        <h1>Clientes</h1>
        <button
            onclick="openCreateModal()"
            class="btn btn-primary btn-nuevo-cliente">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon-nuevo-cliente" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Nuevo Cliente
        </button>
    </div>

    {{-- Tabla de Clientes --}}
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>DNI/RUC</th>
                    <th>Nombre</th>
                    <th>Canal</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->dni_ruc }}</td>
                    <td class="font-medium">{{ $cliente->nombre }}</td>
                    <td>
                        @if($cliente->canal)
                        <span class="badge badge-primary">
                            {{ $cliente->canal->nombre }}
                        </span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td>
                        @if($cliente->email)
                        <a href="mailto:{{ $cliente->email }}" class="text-blue-600 hover:underline">{{ $cliente->email }}</a>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td>{{ $cliente->phone ?? '—' }}</td>
                    <td class="text-right">
                        <button
                            onclick="openEditModal({{ $cliente->id }})"
                            class="btn btn-warning">
                            Editar
                        </button>
                        <button
                            onclick="window.location.href='{{ route('clients.show', $cliente->id) }}'"
                            class="btn btn-primary">
                            Ver Seguimientos
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8">
                        <p class="text-gray-500">No hay clientes registrados.</p>
                        <button onclick="openCreateModal()" class="btn btn-primary mt-4">
                            Agregar Cliente
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-200">
        {{ $clientes->links() }}
    </div>

    {{-- Modal: Crear Cliente (HTML puro) --}}
    <div id="createModal" class="modal-overlay" style="display: none;">
        <div class="modal-container slide-down">
            <div class="modal-header">
                <h2 class="text-xl font-semibold">Nuevo Cliente</h2>
                <button onclick="closeCreateModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="createForm" action="{{ route('clients.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Fecha de Nacimiento -->
                        <div class="form-group">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input
                                name="fec_nac"
                                type="date"
                                value="{{ old('fec_nac') }}"
                                class="form-control">
                        </div>
                        <div></div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">DNI / RUC</label>
                            <input
                                name="dni_ruc"
                                required
                                maxlength="15"
                                placeholder="Ingrese DNI o RUC"
                                class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nombre</label>
                            <input
                                name="nombre"
                                required
                                maxlength="100"
                                placeholder="Nombre completo"
                                class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Canal de Contacto</label>
                        <select name="canal_id" class="form-control">
                            <option value="">-- ninguno --</option>
                            @foreach($canales as $canal)
                            <option value="{{ $canal->id }}">{{ $canal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input
                                name="email"
                                type="email"
                                maxlength="100"
                                placeholder="correo@ejemplo.com"
                                class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input
                                name="phone"
                                maxlength="50"
                                placeholder="+51 999 999 999"
                                class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <input
                            name="address"
                            maxlength="150"
                            placeholder="Dirección completa"
                            class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ocupación</label>
                        <input
                            name="occupation"
                            maxlength="100"
                            placeholder="Ocupación o profesión"
                            class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="closeCreateModal()" class="btn btn-secondary">
                    Cancelar
                </button>
                <button onclick="document.getElementById('createForm').submit()" class="btn btn-primary">
                    Guardar Cliente
                </button>
            </div>
        </div>
    </div>

    {{-- Modal: Editar Cliente (HTML puro) --}}
    <div id="editModal" class="modal-overlay" style="display: none;">
        <div id="editModalContent" class="modal-container slide-down">
            <!-- El contenido se cargará aquí -->
        </div>
    </div>
</div>

<script>
    // Funciones para el modal de crear
    function openCreateModal() {
        document.getElementById('createModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        document.getElementById('createModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    // Funciones para el modal de editar
    function openEditModal(clienteId) {
        // Mostrar el modal primero (vacío)
        document.getElementById('editModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Luego cargar el contenido
        fetch(`/clients/${clienteId}/edit`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editModalContent').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                closeEditModal();
                alert('Error al cargar el formulario de edición');
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.body.style.overflow = '';
        // Limpiar el contenido después de un breve retraso
        setTimeout(() => {
            document.getElementById('editModalContent').innerHTML = '';
        }, 300);
    }

    // Cerrar modales al hacer clic fuera de ellos
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            closeCreateModal();
            closeEditModal();
        }
    });

    // Cerrar modales con la tecla ESC
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });
</script>
@endsection