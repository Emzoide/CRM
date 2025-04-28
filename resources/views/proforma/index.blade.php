@extends('layouts.app')

@section('content')
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Proformas</h1>
            <a href="{{ route('proforma.create') }}">
                @component('components.button', ['variant' => 'primary', 'icon' => 'plus'])
                    Nueva Proforma
                @endcomponent
            </a>
        </div>
        
        @component('components.card')
            <div class="flex flex-col md:flex-row justify-between mb-4 gap-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="relative">
                        <input type="text" placeholder="Buscar proforma..." class="form-input rounded-md pl-10 pr-4 py-2 border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    
                    <select class="form-select rounded-md border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="approved">Aprobada</option>
                        <option value="rejected">Rechazada</option>
                    </select>
                </div>
                
                <div class="flex gap-2">
                    @component('components.button', ['variant' => 'outline-primary', 'icon' => 'filter', 'size' => 'sm'])
                        Filtros
                    @endcomponent
                    
                    @component('components.button', ['variant' => 'outline-primary', 'icon' => 'download', 'size' => 'sm'])
                        Exportar
                    @endcomponent
                </div>
            </div>
            
            @component('components.table')
                @slot('header')
                    @component('components.th') # @endcomponent
                    @component('components.th') Cliente @endcomponent
                    @component('components.th') Fecha @endcomponent
                    @component('components.th') Total @endcomponent
                    @component('components.th') Estado @endcomponent
                    @component('components.th') Acciones @endcomponent
                @endslot
                
                @slot('body')
                    @for ($i = 1; $i <= 10; $i++)
                        <tr class="{{ $i % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                            @component('components.td', ['class' => 'font-medium text-gray-900'])
                                PRO-{{ 2023 }}-{{ 1000 + $i }}
                            @endcomponent
                            
                            @component('components.td')
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" src="{{ asset('images/default-avatar.png') }}" alt="">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">Cliente {{ $i }}</div>
                                        <div class="text-sm text-gray-500">cliente{{ $i }}@ejemplo.com</div>
                                    </div>
                                </div>
                            @endcomponent
                            
                            @component('components.td')
                                {{ date('d/m/Y', strtotime('-' . rand(1, 30) . ' days')) }}
                            @endcomponent
                            
                            @component('components.td')
                                ${{ number_format(rand(1000, 10000), 2) }}
                            @endcomponent
                            
                            @component('components.td')
                                @php
                                    $statuses = ['Pendiente', 'Aprobada', 'Rechazada'];
                                    $statusColors = ['bg-yellow-100 text-yellow-800', 'bg-green-100 text-green-800', 'bg-red-100 text-red-800'];
                                    $randomIndex = array_rand($statuses);
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$randomIndex] }}">
                                    {{ $statuses[$randomIndex] }}
                                </span>
                            @endcomponent
                            
                            @component('components.td', ['class' => 'text-right text-sm font-medium'])
                                <div class="flex space-x-2">
                                    <a href="{{ route('proforma.show', $i) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('proforma.edit', $i) }}" class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            @endcomponent
                        </tr>
                    @endfor
                @endslot
            @endcomponent
            
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Mostrando <span class="font-medium">1</span> a <span class="font-medium">10</span> de <span class="font-medium">100</span> resultados
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
                        <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">10</a>
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Siguiente</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </nav>
                </div>
            </div>
        @endcomponent
    </div>
@endsection