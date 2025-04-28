<div class="modal-header">
    <h2 class="text-xl font-semibold">Editar Cliente</h2>
    <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<div class="modal-body">
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

        <div class="form-group">
            <label class="form-label">Canal de Contacto</label>
            <select name="canal_id" class="form-control">
                <option value="">-- ninguno --</option>
                @foreach($canales as $canal)
                <option
                    value="{{ $canal->id }}"
                    @if(old('canal_id', $cliente->canal_id)==$canal->id) selected @endif
                    >
                    {{ $canal->nombre }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input
                    name="email"
                    type="email"
                    value="{{ old('email', $cliente->email) }}"
                    maxlength="100"
                    class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input
                    name="phone"
                    value="{{ old('phone', $cliente->phone) }}"
                    maxlength="50"
                    class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Dirección</label>
            <input
                name="address"
                value="{{ old('address', $cliente->address) }}"
                maxlength="150"
                class="form-control">
        </div>

        <div class="form-group">
            <label class="form-label">Ocupación</label>
            <input
                name="occupation"
                value="{{ old('occupation', $cliente->occupation) }}"
                maxlength="100"
                class="form-control">
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