@extends('layouts.app')

@section('content')
<style>

</style>

<div class="container mx-auto py-6">
    {{-- Cabecera --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-semibold">Clientes</h1>
        <button
            onclick="openCreateModal()"
            class="btn btn-primary flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Nuevo Cliente
        </button>
    </div>

    {{-- Tabla de Clientes --}}
    <div class="table-container">
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
        <div class="p-4 border-t border-gray-200">
            {{ $clientes->links() }}
        </div>
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