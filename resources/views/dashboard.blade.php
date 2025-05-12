@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    <div class="flex-1 flex flex-col overflow-hidden">
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
            <div class="container mx-auto px-6 py-8">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard</h1>

                <!-- Stats Cards -->
                <div class="dashboard-grid">
                    <x-dashboard.stats-card
                        icon="users"
                        iconColor="blue"
                        title="Total Clientes"
                        :value="$totalClientes"
                        :change="$cambioTexto"
                        :changeType="$cambioTipo"
                        class="dashboard-card" />
                    <x-dashboard.stats-card
                        icon="file-invoice"
                        iconColor="gray"
                        title="Proformas"
                        value="Próximamente"
                        class="dashboard-card disabled" />
                    <x-dashboard.stats-card
                        icon="dollar-sign"
                        iconColor="gray"
                        title="Ingresos"
                        value="Próximamente"
                        class="dashboard-card disabled" />
                    <x-dashboard.stats-card
                        icon="boxes"
                        iconColor="gray"
                        title="Productos"
                        value="Próximamente"
                        class="dashboard-card disabled" />
                </div>

                <!-- Charts and Tables -->
                <div class="dashboard-row">
                    <div class="dashboard-col">
                        <x-dashboard.sales-chart />
                    </div>
                    <div class="dashboard-col">
                        <x-dashboard.recent-clients>
                            @foreach($clientesRecientes as $cliente)
                            <x-dashboard.client-item
                                name="{{ $cliente['name'] }}"
                                email="{{ $cliente['email'] }}"
                                timeAgo="{{ $cliente['timeAgo'] }}" />
                            @endforeach
                        </x-dashboard.recent-clients>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="mt-8">
                    <x-dashboard.recent-proformas>
                        @foreach($cotizacionesRecientes as $cotizacion)
                        <x-dashboard.proforma-item
                            code="{{ $cotizacion['code'] }}"
                            clientName="{{ $cotizacion['clientName'] }}"
                            clientEmail="{{ $cotizacion['clientEmail'] }}"
                            date="{{ $cotizacion['date'] }}"
                            total="${{ $cotizacion['total'] }}"
                            status="{{ $cotizacion['status'] }}" />
                        @endforeach
                    </x-dashboard.recent-proformas>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 16px;
        max-width: 1200px;
        margin: 0 auto 24px auto;
    }

    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 600px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    .dashboard-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        padding: 24px;
        display: flex;
        align-items: center;
        min-height: 140px;
    }

    .dashboard-row {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
    }

    .dashboard-col {
        flex: 1 1 0;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

    @media (max-width: 900px) {
        .dashboard-row {
            flex-direction: column;
        }

        .dashboard-col {
            width: 100%;
        }
    }

    .dashboard-box {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .dashboard-box-header {
        display: flex;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .dashboard-box-header-icon {
        color: #2563eb;
        font-size: 22px;
        margin-right: 12px;
    }

    .dashboard-box-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .dashboard-box-body {
        padding: 24px;
    }

    .dashboard-client-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .dashboard-client-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dashboard-client-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e0e7ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .dashboard-client-info {
        display: flex;
        flex-direction: column;
    }

    .dashboard-client-name {
        font-weight: 500;
        color: #111827;
        font-size: 15px;
    }

    .dashboard-client-email {
        color: #6b7280;
        font-size: 13px;
    }

    .dashboard-client-time {
        margin-left: auto;
        color: #6b7280;
        font-size: 13px;
    }

    .dashboard-chart-container {
        height: 320px;
    }

    .dashboard-table-container {
        overflow-x: auto;
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 15px;
    }

    .dashboard-table th,
    .dashboard-table td {
        padding: 12px 16px;
        text-align: left;
    }

    .dashboard-table th {
        background: #f3f4f6;
        color: #6b7280;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        border-bottom: 1px solid #e5e7eb;
    }

    .dashboard-table tbody tr {
        border-bottom: 1px solid #e5e7eb;
    }

    .dashboard-table tbody tr:last-child {
        border-bottom: none;
    }

    .dashboard-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
    }

    .dashboard-status.approved {
        background: #d1fae5;
        color: #065f46;
    }

    .dashboard-status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .dashboard-status.rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .dashboard-actions {
        display: flex;
        gap: 8px;
    }

    .dashboard-action-link {
        color: #2563eb;
        font-size: 16px;
        text-decoration: none;
        transition: color 0.2s;
    }

    .dashboard-action-link:hover {
        color: #1d4ed8;
    }

    .dashboard-action-delete {
        color: #dc2626;
    }

    .dashboard-action-delete:hover {
        color: #991b1b;
    }

    .dashboard-box-footer {
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .dashboard-link {
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .dashboard-link:hover {
        color: #1d4ed8;
    }

    .dashboard-card.disabled {
        opacity: 0.6;
        background: #f3f4f6;
        cursor: not-allowed;
    }

    .dashboard-card.disabled .dashboard-box-header-icon {
        color: #6b7280;
    }

    .dashboard-card.disabled .dashboard-box-title {
        color: #6b7280;
    }
</style>
@endsection