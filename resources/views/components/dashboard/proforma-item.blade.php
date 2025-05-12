@props([
'code' => '',
'clientName' => '',
'clientEmail' => '',
'date' => '',
'total' => '',
'status' => 'pending'
])

<tr>
    <td>{{ $code }}</td>
    <td>
        <div class="dashboard-client-item">
            <div class="dashboard-client-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="dashboard-client-info">
                <div class="dashboard-client-name">{{ $clientName }}</div>
                <div class="dashboard-client-email">{{ $clientEmail }}</div>
            </div>
        </div>
    </td>
    <td>{{ $date }}</td>
    <td>{{ $total }}</td>
    <td>
        <span class="dashboard-status {{ $status }}">
            {{ $status === 'approved' ? 'Aprobada' : ($status === 'pending' ? 'Pendiente' : 'Rechazada') }}
        </span>
    </td>
    <td>
        <div class="dashboard-actions">
            <a href="#" class="dashboard-action-link"><i class="fas fa-eye"></i></a>
            <a href="#" class="dashboard-action-link"><i class="fas fa-edit"></i></a>
            <a href="#" class="dashboard-action-link dashboard-action-delete"><i class="fas fa-trash"></i></a>
        </div>
    </td>
</tr>