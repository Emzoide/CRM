@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Editar Sucursal</h1>
            <a href="{{ route('sucursales.index') }}" class="text-blue-500 hover:text-blue-700">
                Volver a la lista
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form action="{{ route('sucursales.update', $sucursal) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="tienda_id" class="block text-gray-700 text-sm font-bold mb-2">Tienda</label>
                    <select name="tienda_id" id="tienda_id"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('tienda_id') border-red-500 @enderror"
                        required>
                        <option value="">Seleccione una tienda</option>
                        @foreach($tiendas as $tienda)
                        <option value="{{ $tienda->id }}" {{ old('tienda_id', $sucursal->tienda_id) == $tienda->id ? 'selected' : '' }}>
                            {{ $tienda->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('tienda_id')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $sucursal->nombre) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('nombre') border-red-500 @enderror"
                        required>
                    @error('nombre')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="direccion" class="block text-gray-700 text-sm font-bold mb-2">Dirección</label>
                    <textarea name="direccion" id="direccion" rows="3"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('direccion') border-red-500 @enderror">{{ old('direccion', $sucursal->direccion) }}</textarea>
                    @error('direccion')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Actualizar Sucursal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection