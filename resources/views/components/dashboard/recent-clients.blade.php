@props(['title' => 'Clientes Recientes'])

<div class="dashboard-box">
    <div class="dashboard-box-header">
        <div class="dashboard-box-header-icon">
            <i class="fas fa-users"></i>
        </div>
        <h3 class="dashboard-box-title">{{ $title }}</h3>
    </div>
    <div class="dashboard-box-body">
        <div class="dashboard-client-list">
            {{ $slot }}
        </div>
    </div>
</div>