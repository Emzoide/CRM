@props([
'name' => '',
'email' => '',
'timeAgo' => ''
])

<div class="dashboard-client-item">
    <div class="dashboard-client-avatar">
        <i class="fas fa-user"></i>
    </div>
    <div class="dashboard-client-info">
        <p class="dashboard-client-name">{{ $name }}</p>
        <p class="dashboard-client-email">{{ $email }}</p>
    </div>
    <div class="dashboard-client-time">
        {{ $timeAgo }}
    </div>
</div>