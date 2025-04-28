<header class="header">
    <div class="header-container">
        <!-- Menú y búsqueda -->
        <div class="header-left">
            <button class="menu-button">
                <i class="fas fa-bars"></i>
            </button>
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Buscar...">
            </div>
        </div>

        <!-- Notificaciones y Perfil -->
        <div class="header-right">
            <div class="notifications">
                <button class="notification-button" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="notification-dot"></span>
                </button>
                <div id="notificationsMenu" class="notifications-menu">
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text">Nuevo cliente registrado</p>
                            <p class="notification-time">Hace 10 minutos</p>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon green">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text">Nueva proforma creada</p>
                            <p class="notification-time">Hace 1 hora</p>
                        </div>
                    </div>
                    <a href="#" class="view-all">Ver todas las notificaciones</a>
                </div>
            </div>

            <div class="profile">
                <button class="profile-button" onclick="toggleProfile()">
                    @auth
                    @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}"
                        alt="Avatar de usuario"
                        class="profile-image">
                    @else
                    <div class="profile-image default-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    @endif
                    @else
                    <div class="profile-image default-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    @endauth
                </button>
                <div id="profileMenu" class="profile-menu">
                    <a href="{{ route('profile.show') }}" class="profile-menu-item">
                        <i class="fas fa-user"></i> Perfil
                    </a>
                    <a href="{{ route('settings.index') }}" class="profile-menu-item">
                        <i class="fas fa-cog"></i> Configuración
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="profile-menu-item logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
    .header {
        background-color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 10;
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1.5rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .menu-button {
        background: none;
        border: none;
        font-size: 1.25rem;
        color: #666;
        cursor: pointer;
        padding: 0.5rem;
    }

    .menu-button:hover {
        color: #333;
    }

    .search-container {
        position: relative;
    }

    .search-input {
        width: 300px;
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: #4a90e2;
        box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    /* Estilos para notificaciones */
    .notifications {
        position: relative;
    }

    .notification-button {
        background: none;
        border: none;
        font-size: 1.25rem;
        color: #666;
        cursor: pointer;
        padding: 0.5rem;
        position: relative;
    }

    .notification-dot {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        width: 8px;
        height: 8px;
        background-color: #ff4757;
        border-radius: 50%;
    }

    .notifications-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        width: 320px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-top: 0.5rem;
    }

    .notification-item {
        display: flex;
        padding: 1rem;
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
    }

    .notification-icon {
        background-color: #4a90e2;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }

    .notification-icon.green {
        background-color: #2ecc71;
    }

    .notification-content {
        flex: 1;
    }

    .notification-text {
        margin: 0;
        font-size: 0.875rem;
        color: #333;
    }

    .notification-time {
        margin: 0.25rem 0 0;
        font-size: 0.75rem;
        color: #666;
    }

    .view-all {
        display: block;
        text-align: center;
        padding: 0.75rem;
        background-color: #4a90e2;
        color: white;
        text-decoration: none;
        font-size: 0.875rem;
    }

    .view-all:hover {
        background-color: #357abd;
    }

    /* Estilos para perfil */
    .profile {
        position: relative;
    }

    .profile-button {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
    }

    .profile-image {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ddd;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f0f0f0;
    }

    .profile-image.default-avatar {
        color: #666;
        font-size: 1.2rem;
    }

    .profile-image:hover {
        border-color: #4a90e2;
        transform: scale(1.05);
    }

    .profile-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        width: 200px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-top: 0.5rem;
        overflow: hidden;
    }

    .profile-menu-item {
        display: block;
        padding: 0.75rem 1rem;
        color: #333;
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.2s;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }

    .profile-menu-item:hover {
        background-color: #f8f9fa;
        color: #4a90e2;
    }

    .profile-menu-item.logout:hover {
        background-color: #fff5f5;
        color: #e53e3e;
    }

    /* Clases de utilidad */
    .show {
        display: block;
    }
</style>

<script>
    function toggleNotifications() {
        const menu = document.getElementById('notificationsMenu');
        const profileMenu = document.getElementById('profileMenu');
        menu.classList.toggle('show');
        profileMenu.classList.remove('show');
    }

    function toggleProfile() {
        const menu = document.getElementById('profileMenu');
        const notificationsMenu = document.getElementById('notificationsMenu');
        menu.classList.toggle('show');
        notificationsMenu.classList.remove('show');
    }

    // Cerrar menús al hacer clic fuera
    document.addEventListener('click', function(event) {
        const notificationsMenu = document.getElementById('notificationsMenu');
        const profileMenu = document.getElementById('profileMenu');

        if (!event.target.closest('.notifications') && !event.target.closest('.profile')) {
            notificationsMenu.classList.remove('show');
            profileMenu.classList.remove('show');
        }
    });
</script>