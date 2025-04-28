@props(['variant' => 'primary', 'size' => 'md', 'icon' => '', 'class' => ''])

@php
    $variantClasses = [
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white',
        'success' => 'bg-green-600 hover:bg-green-700 text-white',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
        'info' => 'bg-blue-400 hover:bg-blue-500 text-white',
        'light' => 'bg-gray-200 hover:bg-gray-300 text-gray-800',
        'dark' => 'bg-gray-800 hover:bg-gray-900 text-white',
        'outline-primary' => 'border border-blue-600 text-blue-600 hover:bg-blue-50',
        'outline-secondary' => 'border border-gray-600 text-gray-600 hover:bg-gray-50',
    ];

    $sizeClasses = [
        'sm' => 'py-1 px-3 text-sm',
        'md' => 'py-2 px-4',
        'lg' => 'py-3 px-6 text-lg',
    ];

    $classes = $variantClasses[$variant] . ' ' . $sizeClasses[$size] . ' rounded-md font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 inline-flex items-center justify-center ' . $class;
@endphp

<button class="{{ $classes }}">
    @if($icon)
    <i class="fas fa-{{ $icon }} {{ $slot->isEmpty() ? '' : 'mr-2' }}"></i>
    @endif
    {{ $slot }}
</button>