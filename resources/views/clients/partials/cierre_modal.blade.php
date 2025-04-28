{{-- resources/views/clients/partials/cierre_modal.blade.php --}}

{{-- Modal de Confirmación de Cierre --}}
<div id="confirmacionCierreModal" class="modal" style="display: none; z-index: 3000;">
    <div class="modal-content" style="max-width: 400px; text-align: center; padding: 2rem;">
        <div class="modal-header" style="border: none; justify-content: center; display: flex; flex-direction: column; align-items: center;">
            <div style="color: #dc2626; margin-bottom: 1.5rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem;"></i>
            </div>

            <div style="margin-bottom: 0.5rem;">
                <h3 style="font-size: 1.5rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">
                    ¿Estás seguro de que quieres cerrar esta oportunidad?
                </h3>

                <p style="color: #6b7280;">
                    Esta acción no se puede deshacer
                </p>
            </div>
        </div>

        <div class="modal-body" style="display: flex; justify-content: center; gap: 1rem;">
            <button type="button"
                onclick="cerrarConfirmacionCierre()"
                style="padding: 0.75rem 1.5rem; border-radius: 0.375rem; background: #f3f4f6; color: #374151; font-weight: 500; border: 1px solid #d1d5db; transition: all 0.2s;">
                Cancelar
            </button>

            <button type="button"
                onclick="procederConCierre()"
                style="padding: 0.75rem 1.5rem; border-radius: 0.375rem; background: #dc2626; color: white; font-weight: 500; border: none; transition: all 0.2s;">
                Sí, Cerrar
            </button>
        </div>
    </div>
</div>

{{-- Modal de Cierre de Oportunidad --}}
<div id="cierreModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
        <div class="modal-header" style="border-bottom: 1px solid #e5e7eb; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="modal-title" style="font-size: 1.25rem; font-weight: 600; color: #1f2937; margin: 0;">Cerrar Oportunidad</h3>
            <button type="button" onclick="closeCierreModal()" class="close-button" style="background: none; border: none; font-size: 1.5rem; color: #6b7280; cursor: pointer; padding: 0.5rem;">&times;</button>
        </div>

        <form id="cierreForm" method="POST" action="{{ route('oportunidades.cierre.store', ['oportunidad' => 0]) }}" style="padding: 1.5rem;">
            @csrf
            @if($errors->any())
            <div class="alert alert-danger" style="background-color: #fee2e2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1.5rem;">
                <ul class="mb-0" style="margin: 0; padding-left: 1.5rem;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <input type="hidden" name="oportunidad_id" id="cierreOportunidadId" value="">

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="resultado" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Resultado</label>
                <select name="resultado" id="resultadoCierre" class="form-control" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    <option value="">Seleccione resultado</option>
                    <option value="won">Ganada</option>
                    <option value="lost">Perdida</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="fecha_cierre" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Fecha de Cierre</label>
                <input type="datetime-local" name="fecha_cierre" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
            </div>

            <div id="campoMontoFinal" class="form-group" style="margin-bottom: 1.5rem; display: none;">
                <label for="monto_final" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Monto Final</label>
                <input type="number" name="monto_final" class="form-control" step="0.01" min="0" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="motivo_cierre" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Motivo de Cierre</label>
                <textarea name="motivo_cierre" rows="3" class="form-control" required placeholder="Detalla el motivo por el que se cierra esta oportunidad..." style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;"></textarea>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <button type="button" onclick="closeCierreModal()" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; border-radius: 0.375rem; background: #f3f4f6; color: #374151; font-weight: 500; border: 1px solid #d1d5db;">Cancelar</button>
                <button type="submit" class="btn btn-danger" style="padding: 0.75rem 1.5rem; border-radius: 0.375rem; background: #dc2626; color: white; font-weight: 500; border: none;">Cerrar Oportunidad</button>
            </div>
        </form>
    </div>
</div>

<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        position: relative;
        width: 100%;
        max-width: 500px;
        margin: 1rem;
        animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .btn-danger {
        background-color: #dc2626;
        color: white;
        transition: all 0.2s;
    }

    .btn-danger:hover {
        background-color: #b91c1c;
    }

    .form-control:focus {
        outline: none;
        border-color: #dc2626;
        box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const resultadoCierre = document.getElementById('resultadoCierre');
        const campoMontoFinal = document.getElementById('campoMontoFinal');
        const cierreForm = document.getElementById('cierreForm');

        // Actualizar la URL del formulario cuando se carga la oportunidad
        if (cierreForm) {
            cierreForm.addEventListener('submit', function(e) {
                e.preventDefault();
                mostrarConfirmacionCierre();
            });
        }

        // Mostrar/ocultar campo de monto final según el resultado
        if (resultadoCierre && campoMontoFinal) {
            resultadoCierre.addEventListener('change', function() {
                const esGanada = this.value === 'won';
                campoMontoFinal.style.display = esGanada ? 'block' : 'none';

                // Si es ganada, el monto es requerido
                const montoInput = document.querySelector('[name="monto_final"]');
                if (montoInput) {
                    montoInput.required = esGanada;
                }
            });
        }
    });

    function mostrarConfirmacionCierre() {
        const modal = document.getElementById('confirmacionCierreModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function cerrarConfirmacionCierre() {
        const modal = document.getElementById('confirmacionCierreModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function procederConCierre() {
        cerrarConfirmacionCierre();
        const cierreForm = document.getElementById('cierreForm');
        const oportunidadId = document.getElementById('cierreOportunidadId').value;
        cierreForm.action = "{{ url('oportunidades') }}/" + oportunidadId + "/cierre";
        cierreForm.submit();
    }
</script>
@endpush