@props([
'icon' => 'users',
'iconColor' => 'blue',
'title' => '',
'value' => '',
'change' => '',
'changeType' => 'up'
])

<div class="bg-white rounded-lg shadow p-4 flex flex-col justify-center h-full min-h-[140px]">
    <div class="flex items-center">
        <div class="rounded-full bg-{{ $iconColor }}-100 p-3 mr-4">
            <i class="fas fa-{{ $icon }} text-{{ $iconColor }}-600 text-lg"></i>
        </div>
        <div>
            <h5 class="text-gray-500 text-xs">{{ $title }}</h5>
            <p class="text-xl font-bold text-gray-800">{{ $value }}</p>
            <p class="text-{{ $changeType === 'up' ? 'green' : 'red' }}-500 text-xs">
                <i class="fas fa-arrow-{{ $changeType }}"></i> {{ $change }} desde el mes pasado
            </p>
        </div>
    </div>
</div>