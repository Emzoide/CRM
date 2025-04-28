<div class="bg-white overflow-hidden shadow-sm rounded-lg {{ isset($class) ? $class : '' }}">
    @if(isset($title) && $title)
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center">
        @if(isset($icon) && $icon)
        <i class="fas fa-{{ $icon }} text-blue-600 mr-3"></i>
        @endif
        <h3 class="text-lg font-medium text-gray-800">{{ $title }}</h3>
    </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>