{{-- resources/views/oportunidades/cierre.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6">Cerrar Oportunidad</h2>

            <form action="{{ route('oportunidades.cierre.store', $oportunidad) }}" method="POST">
                @csrf
                
                <div class="space-y-4">
                    {{-- Resultado --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Resultado
                        </label>
                        <select name="resultado" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required>
                            <option value="won">Venta Ganada</option>
                            <option value="lost">Venta Perdida</option>
                        </select>
                    </div>

                    {{-- Fecha de Cierre --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Fecha de Cierre
                        </label>
                        <input type="datetime-local" 
                               name="fecha_cierre"
                               value="{{ now()->format('Y-m-d\TH:i') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                               required>
                    </div>

                    {{-- Monto Final (solo si es won) --}}
                    <div class="monto-final-container">
                        <label class="block text-sm font-medium text-gray-700">
                            Monto Final
                        </label>
                        <input type="number"
                               name="monto_final"
                               step="0.01"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    {{-- Motivo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Motivo del Cierre
                        </label>
                        <textarea name="motivo_cierre"
                                  rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                  required></textarea>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('oportunidades.show', $oportunidad) }}"
                       class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Confirmar Cierre
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.querySelector('[name="resultado"]').addEventListener('change', function() {
            const montoContainer = document.querySelector('.monto-final-container');
            const montoInput = document.querySelector('[name="monto_final"]');
            
            if (this.value === 'won') {
                montoContainer.style.display = 'block';
                montoInput.required = true;
            } else {
                montoContainer.style.display = 'none';
                montoInput.required = false;
                montoInput.value = '';
            }
        });
    </script>
    @endpush
</x-app-layout>
