// Funcionalidad básica para el sidebar
document.addEventListener('DOMContentLoaded', function() {
    // Toggle para el sidebar en móvil
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-sidebar]');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('hidden');
        });
    }
    
    // Dropdown menus
    const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
    
    dropdownButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-dropdown-toggle');
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.classList.toggle('hidden');
            }
        });
    });
});

// Cerrar dropdowns cuando se hace clic fuera
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.dropdown-content:not(.hidden)');
    
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(event.target) && 
            !event.target.hasAttribute('data-dropdown-toggle')) {
            dropdown.classList.add('hidden');
        }
    });
});