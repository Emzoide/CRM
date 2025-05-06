<div class="sidebar-container">
    <!-- Sidebar principal -->
    <aside class="sidebar" id="sidebar">
        <!-- Cabecera del Sidebar -->
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="logo-text">
                    <span>INSAC CRM</span>
                </div>
            </div>
        </div>

        <!-- Botón de toggle -->
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-chevron-left"></i>
        </button>

        <!-- Contenido principal del Sidebar -->
        <div class="sidebar-content">
            <!-- Navegación -->
            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    <!-- Dashboard -->
                    <li class="sidebar-menu-item active" data-tooltip="Dashboard">
                        <a href="/" class="sidebar-menu-link">
                            <div class="sidebar-menu-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <span class="sidebar-menu-text">Dashboard</span>
                        </a>
                    </li>

                    <!-- Proforma -->
                    <!-- <li class="sidebar-menu-item" data-tooltip="PROFORMA">
                        <a href="#" class="sidebar-menu-link">
                            <div class="sidebar-menu-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <span class="sidebar-menu-text">PROFORMA</span>
                            <span class="sidebar-badge">3</span>
                        </a>
                    </li> -->

                    <!-- Clientes -->
                    <li class="sidebar-menu-item" data-tooltip="CLIENTES">
                        <a href="/clients" class="sidebar-menu-link">
                            <div class="sidebar-menu-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="sidebar-menu-text">CLIENTES</span>
                        </a>
                    </li>

                    <!-- Administrador -->
                    @if(Auth::user()->rol == 'admin')
                    <li class="sidebar-menu-item" data-tooltip="CHATS">
                        <a href="/chat" class="sidebar-menu-link">
                            <div class="sidebar-menu-icon">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <span class="sidebar-menu-text">CHATS</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item has-submenu" data-tooltip="ADMINISTRADOR">
                        <a href="#" class="sidebar-menu-link">
                            <div class="sidebar-menu-icon">
                                <!-- <i class="fas fa-ellipsis-h"></i> -->
                                <i class="fas fa-cog"></i>
                            </div>
                            <span class="sidebar-menu-text">ADMINISTRADOR</span>
                            <span class="sidebar-menu-arrow">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li class="sidebar-submenu-item">
                                <a href="{{ route('admin.tiendas') }}" class="sidebar-submenu-link">
                                    <i class="fas fa-store sidebar-submenu-icon"></i>
                                    <span class="sidebar-submenu-text">Sucursales</span>
                                </a>
                            </li>
                            <li class="sidebar-submenu-item">
                                <a href="{{ route('admin.vehiculos') }}" class="sidebar-submenu-link">
                                    <i class="fas fa-car sidebar-submenu-icon"></i>
                                    <span class="sidebar-submenu-text">Vehículos</span>
                                </a>
                            </li>
                            <li class="sidebar-submenu-item">
                                <a href="{{ route('admin.usuarios.index') }}" class="sidebar-submenu-link">
                                    <i class="fas fa-users sidebar-submenu-icon"></i>
                                    <span class="sidebar-submenu-text">Usuarios</span>
                                </a>
                            </li>
                            <li class="sidebar-submenu-item">
                                <a href="{{ route('admin.menus') }}" class="sidebar-submenu-link">
                                    <i class="fas fa-ellipsis-h sidebar-submenu-icon"></i>
                                    <span class="sidebar-submenu-text">Otros</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    <!-- Otros (Con submenú) hola git -->
                    <li class="sidebar-menu-item has-submenu" data-tooltip="OTROS">
                        <a href="#" class="sidebar-menu-link">
                            <div class="sidebar-menu-icon">
                                <i class="fas fa-ellipsis-h"></i>
                            </div>
                            <span class="sidebar-menu-text">OTROS</span>
                            <span class="sidebar-menu-arrow">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </a>
                        <ul class="sidebar-submenu">
                            <!-- <li class="sidebar-submenu-item">
                                <a href="#" class="sidebar-submenu-link">
                                    <i class="fas fa-boxes sidebar-submenu-icon"></i>
                                    <span class="sidebar-submenu-text">Inventario</span>
                                </a>
                            </li> -->
                            <li class="sidebar-submenu-item">
                                <a href="/admin/reportes" class="sidebar-submenu-link">
                                    <i class="fas fa-chart-bar sidebar-submenu-icon"></i>
                                    <span class="sidebar-submenu-text">Reportes</span>
                                </a>
                            </li>
                            <li class="sidebar-submenu-item">
                                <a href="#" class="sidebar-submenu-link">
                                    <i class="fas fa-cog sidebar-submenu-icon"></i>
                                    <span class="sidebar-submenu-text">Configuración</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Ventas
                    <li class="sidebar-menu-item" data-tooltip="VENTAS">
                        <a href="#" class="sidebar-menu-link">
                            <div class="sidebar-menu-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <span class="sidebar-menu-text">VENTAS</span>
                        </a>
                    </li> -->
                </ul>
            </nav>
        </div>

        @php
        $nombreCompleto = Auth::user()->first_name;
        $partes = explode(" ", $nombreCompleto);
        $nombre = $partes[0]; // Esto extraerá "Juan"
        $apeCompleto = Auth::user()->last_name;
        $partes2 = explode(" ", $apeCompleto);
        $apellido = $partes2[0]; // Esto extraerá el primer apellido
        $roles = [
        'admin' => 'Administrador',
        'seller' => 'Asesor',
        'user' => 'Usuario',
        'supervisor'=> 'Supervisor'
        ];
        @endphp
        <!-- Perfil de Usuario -->
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                    <span class="user-status"></span>
                </div>
                <div class="user-info">
                    <div class="user-name">{{ $nombre . " " . $apellido }}</div>
                    <div class="user-email">{{ $roles[Auth::user()->rol] }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Botón de toggle para móviles -->
    <button class="sidebar-mobile-toggle" id="sidebarMobileToggle">
        <span class="line"></span>
        <span class="line"></span>
        <span class="line"></span>
    </button>
</div>