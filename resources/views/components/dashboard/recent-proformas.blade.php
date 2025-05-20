@props(['title' => 'Cotizaciones Recientes'])

<div class="dashboard-box">
    <div class="dashboard-box-header">
        <div class="dashboard-box-header-icon">
            <i class="fas fa-file-invoice"></i>
        </div>
        <h3 class="dashboard-box-title">{{ $title }}</h3>
    </div>
    <div class="dashboard-box-body dashboard-table-container">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th># Cotización</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
    <div class="dashboard-box-footer">
        <a href="/clients" class="dashboard-link">Ver todas las cotiaciones →</a>
    </div>
</div>