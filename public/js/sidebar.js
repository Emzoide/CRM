document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarMobileToggle = document.getElementById('sidebarMobileToggle');
    const menuItems = document.querySelectorAll('.sidebar-menu-item');
    const hasSubmenuItems = document.querySelectorAll('.has-submenu');
    
    // Comprobar si el sidebar estaba colapsado previamente
    const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    // Aplicar estado inicial del sidebar
    if (isSidebarCollapsed) {
        sidebar.classList.add('collapsed');
    }
    
    // Función para alternar el sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        
        // Guardar el estado en localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }
    
    // Función para activar el menú móvil
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarMobileToggle = document.getElementById('sidebarMobileToggle');
        
        sidebar.classList.toggle('mobile-visible');
        sidebarMobileToggle.classList.toggle('active');
        
        // Mostrar/ocultar overlay
        let overlay = document.querySelector('.sidebar-overlay');
        
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
            
            // Añadir listener al overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-visible');
                sidebarMobileToggle.classList.remove('active');
                overlay.classList.remove('visible');
            });
        }
        
        if (sidebar.classList.contains('mobile-visible')) {
            overlay.classList.add('visible');
        } else {
            overlay.classList.remove('visible');
        }
    }
    
    // Función para manejo de submenús
    function toggleSubmenu(menuItem) {
        // Si el sidebar está colapsado, no hacer nada
        if (sidebar.classList.contains('collapsed')) return;
        
        const isOpen = menuItem.classList.contains('open');
        
        // Cerrar todos los submenús
        hasSubmenuItems.forEach(item => {
            item.classList.remove('open');
        });
        
        // Abrir el submenú actual si no estaba abierto
        if (!isOpen) {
            menuItem.classList.add('open');
        }
    }
    
    // Añadir funcionalidad para activar/desactivar elementos del menú
    menuItems.forEach(item => {
        const link = item.querySelector('.sidebar-menu-link');
        
        link.addEventListener('click', function(e) {
            if (item.classList.contains('has-submenu')) {
                e.preventDefault();
                toggleSubmenu(item);
            } else {
                // Eliminar clase active de todos los elementos
                menuItems.forEach(menuItem => {
                    menuItem.classList.remove('active');
                });
                
                // Añadir clase active al elemento actual
                item.classList.add('active');
            }
        });
    });
    
    // Listener para el botón de toggle
    sidebarToggle.addEventListener('click', toggleSidebar);
    
    // Listener para el botón móvil
    sidebarMobileToggle.addEventListener('click', toggleMobileSidebar);
    
    // Manejar redimensionamiento de ventana
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            // Remover clase de visibilidad móvil al redimensionar a desktop
            sidebar.classList.remove('mobile-visible');
            sidebarMobileToggle.classList.remove('active');
            
            // Ocultar overlay si existe
            const overlay = document.querySelector('.sidebar-overlay');
            if (overlay) {
                overlay.classList.remove('visible');
            }
        }
    });
});