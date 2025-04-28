{{-- CRM__index-seguimiento.blade.php --}}
{{-- This file is part of a Laravel Blade template for a CRM system. --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Seguimiento de Clientes</h1>
        <a href="#">
            <x-button variant="primary" icon="plus">
                Nuevo Seguimiento
            </x-button>
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Canales de Contacto (Prueba de BD)</h2>

        {{-- Formulario para añadir un canal --}}
        <form action="{{ route('home') }}" method="POST" class="flex gap-2 mb-4">
            @csrf
            <input
                type="text"
                name="nombre"
                placeholder="Nombre del canal"
                class="form-input rounded-md border-gray-300 flex-1"
                required>
            <button type="submit" class="btn btn-primary">
                Agregar
            </button>
        </form>

        {{-- Lista de canales existentes --}}
        <ul class="list-disc pl-5">
            @forelse($canales as $canal)
            <li class="py-1">
                <span class="font-medium">{{ $canal->id }}</span> – {{ $canal->nombre }}
            </li>
            @empty
            <li class="text-gray-500">Aún no hay canales registrados.</li>
            @endforelse
        </ul>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <input type="text" placeholder="Buscar cliente o seguimiento..." class="form-input rounded-md pl-10 pr-4 py-2 border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
            <select class="form-select rounded-md border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="en_proceso">En Proceso</option>
                <option value="finalizado">Finalizado</option>
            </select>
            <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-filter mr-2"></i> Filtrar
            </button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-clipboard-list text-blue-600 mr-3"></i>
            <h3 class="text-lg font-medium text-gray-800">Lista de Seguimientos</h3>
        </div>
        <div class="card-body overflow-x-auto">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Asunto</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Responsable</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 8; $i++)
                        @php
                        $estados=[
                        ['label'=> 'Pendiente', 'class' => 'badge-warning'],
                        ['label' => 'En Proceso', 'class' => 'badge-success'],
                        ['label' => 'Finalizado', 'class' => 'badge-danger'],
                        ];
                        $estado = $estados[$i % 3];
                        @endphp
                        <tr class="{{ $i % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                            <td class="font-medium text-gray-900">SEG-{{ 1000 + $i }}</td>
                            <td>
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Cliente {{ $i }}</div>
                                        <div class="text-xs text-gray-500">cliente{{ $i }}@ejemplo.com</div>
                                    </div>
                                </div>
                            </td>
                            <td>Seguimiento sobre propuesta {{ $i }}</td>
                            <td>{{ now()->subDays($i)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $estado['class'] }}">{{ $estado['label'] }}</span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="ml-2 text-sm text-gray-900">Asesor {{ $i }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="flex space-x-2">
                                    <a href="#" class="text-blue-600 hover:text-blue-900" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="text-red-600 hover:text-red-900" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endfor
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Mostrando <span class="font-medium">1</span> a <span class="font-medium">8</span> de <span class="font-medium">24</span> seguimientos
                </div>
                <div class="flex justify-end">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Anterior</span>
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>
                        <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600 hover:bg-blue-100">2</a>
                        <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">3</a>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                        <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">4</a>
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Siguiente</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection