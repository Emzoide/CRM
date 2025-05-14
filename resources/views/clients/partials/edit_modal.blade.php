<div class="modal-header">
    <h2 class="text-xl font-semibold">Editar Cliente</h2>
    <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<div class="modal-body">
    <div class="alert alert-info mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
        <div class="flex items-center">
            <div class="mr-2"><i class="fas fa-info-circle text-blue-500"></i></div>
            <div>
                <strong>¡Información!</strong> Por favor, actualice los datos del cliente según sea necesario. Estos datos podrán ser utilizados más adelante en las cotizaciones.
            </div>
        </div>
    </div>

    <form id="editForm" action="{{ route('clients.update', $cliente) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Fecha de Nacimiento</label>
                <input
                    name="fec_nac"
                    type="date"
                    value="{{ old('fec_nac', $cliente->fec_nac) }}"
                    class="form-control">
            </div>
            <div></div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">DNI / RUC</label>
                <input
                    name="dni_ruc"
                    value="{{ old('dni_ruc', $cliente->dni_ruc) }}"
                    required
                    maxlength="15"
                    class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Nombre</label>
                <input
                    name="nombre"
                    value="{{ old('nombre', $cliente->nombre) }}"
                    required
                    maxlength="100"
                    class="form-control">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input
                    name="email"
                    type="email"
                    value="{{ old('email', $cliente->email) }}"
                    maxlength="255"
                    placeholder="correo@ejemplo.com"
                    class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input
                    name="phone"
                    value="{{ old('phone', $cliente->phone) }}"
                    maxlength="100"
                    placeholder="Número de contacto"
                    class="form-control">
            </div>
        </div>
    </form>
</div>

<div class="modal-footer">
    <button onclick="closeEditModal()" class="btn btn-secondary">
        Cancelar
    </button>
    <button onclick="document.getElementById('editForm').submit()" class="btn btn-warning">
        Actualizar Cliente
    </button>
</div>