/**
 * Sistema de filtros dinámicos para clientes
 */

// Configuración global
let filtroActual = null;
let criteriosCount = 0;

// Al cargar el documento
document.addEventListener('DOMContentLoaded', function() {
    // Ocultar el template de criterio
    if (document.getElementById('criterio-template')) {
        document.getElementById('criterio-template').style.display = 'none';
    }
    
    // Comprobar si hay un filtro personalizado en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const filtroPersonalizado = urlParams.get('filtro_personalizado');
    if (filtroPersonalizado) {
        try {
            filtroActual = JSON.parse(decodeURIComponent(filtroPersonalizado));
            mostrarPanelFiltros();
            cargarFiltroActual();
        } catch (e) {
            console.error('Error al cargar filtro personalizado:', e);
        }
    }
    
    // Configurar listeners para eventos dinámicos
    configurarEventListeners();
});

/**
 * Configura listeners de eventos para elementos dinámicos
 */
function configurarEventListeners() {
    // Delegación de eventos para elementos dinámicos
    document.addEventListener('click', function(event) {
        // Botones para eliminar criterios
        if (event.target.classList.contains('eliminar-criterio') || 
            event.target.closest('.eliminar-criterio')) {
            const criterio = event.target.closest('.criterio-item');
            if (criterio) {
                criterio.remove();
            }
        }
        
        // Manejar cambios en el tipo de campo
        const campoSelect = event.target.closest('.campo-filtro');
        if (campoSelect && event.type === 'change') {
            actualizarOperadoresYValores(campoSelect);
        }
    });
}

/**
 * Muestra el panel de filtros
 */
function mostrarPanelFiltros() {
    const panel = document.getElementById('panelFiltros');
    if (panel) {
        panel.style.display = 'block';
        agregarCriterio(); // Añadir al menos un criterio vacío
    }
}

/**
 * Cierra el panel de filtros
 */
function cerrarPanelFiltros() {
    const panel = document.getElementById('panelFiltros');
    if (panel) {
        panel.style.display = 'none';
    }
    filtroActual = null;
}

/**
 * Añade un nuevo criterio de filtrado
 */
function agregarCriterio() {
    const template = document.getElementById('criterio-template');
    const contenedor = document.getElementById('criteriosFiltro');
    
    if (template && contenedor) {
        criteriosCount++;
        const criterioId = 'criterio-' + criteriosCount;
        
        // Clonar el template
        const nuevoCriterio = template.cloneNode(true);
        nuevoCriterio.id = criterioId;
        nuevoCriterio.classList.add('criterio-item');
        nuevoCriterio.style.display = 'block';
        
        // Añadir al contenedor
        contenedor.appendChild(nuevoCriterio);
    }
}

/**
 * Actualiza los operadores y valores disponibles según el campo seleccionado
 */
function actualizarOperadoresYValores(campoSelect) {
    const criterio = campoSelect.closest('.criterio-item');
    if (!criterio) return;
    
    const operadorSelect = criterio.querySelector('.operador-filtro');
    const valorInput = criterio.querySelector('.valor-filtro');
    
    // Vaciar opciones actuales del operador
    while (operadorSelect.options.length > 0) {
        operadorSelect.remove(0);
    }
    
    // Configurar según el tipo de campo
    switch (campoSelect.value) {
        case 'cotizacion_activa':
            // Operadores para boolean
            agregarOpcion(operadorSelect, '=', 'Es igual a');
            
            // Convertir input a select
            const selectValor = document.createElement('select');
            selectValor.className = 'form-control mr-2 valor-filtro';
            agregarOpcion(selectValor, 'true', 'Sí');
            agregarOpcion(selectValor, 'false', 'No');
            
            valorInput.parentNode.replaceChild(selectValor, valorInput);
            break;
            
        case 'ultimo_seguimiento':
            // Operadores para fechas relativas
            agregarOpcion(operadorSelect, '>', 'Más de');
            agregarOpcion(operadorSelect, '<', 'Menos de');
            
            // Mantener input pero añadir sufijo
            valorInput.placeholder = 'Número de días (ej: 5)';
            valorInput.type = 'number';
            valorInput.min = '0';
            valorInput.value = '5';
            break;
            
        case 'monto_cotizacion':
        case 'probabilidad':
            // Operadores para números
            agregarOpcion(operadorSelect, '=', 'Igual a');
            agregarOpcion(operadorSelect, '!=', 'Diferente de');
            agregarOpcion(operadorSelect, '>', 'Mayor que');
            agregarOpcion(operadorSelect, '<', 'Menor que');
            agregarOpcion(operadorSelect, '>=', 'Mayor o igual que');
            agregarOpcion(operadorSelect, '<=', 'Menor o igual que');
            
            valorInput.type = 'number';
            valorInput.placeholder = 'Valor numérico';
            break;
            
        case 'asignado_a':
            // Operadores para usuarios
            agregarOpcion(operadorSelect, '=', 'Es');
            agregarOpcion(operadorSelect, '!=', 'No es');
            
            // Mostrar opciones de usuarios
            fetch('/api/usuarios')
                .then(response => response.json())
                .then(data => {
                    // Convertir input a select
                    const selectValor = document.createElement('select');
                    selectValor.className = 'form-control mr-2 valor-filtro';
                    
                    // Añadir opción para usuario actual
                    agregarOpcion(selectValor, 'CURRENT_USER', 'Usuario actual');
                    
                    // Añadir usuarios de la respuesta
                    data.forEach(usuario => {
                        agregarOpcion(selectValor, usuario.id, usuario.nombre_completo || usuario.email);
                    });
                    
                    valorInput.parentNode.replaceChild(selectValor, valorInput);
                })
                .catch(error => {
                    console.error('Error al cargar usuarios:', error);
                });
            break;
            
        case 'tienda_id':
            // Operadores para tiendas
            agregarOpcion(operadorSelect, '=', 'Es');
            agregarOpcion(operadorSelect, '!=', 'No es');
            
            // Mostrar opciones de tiendas
            fetch('/api/tiendas')
                .then(response => response.json())
                .then(data => {
                    // Convertir input a select
                    const selectValor = document.createElement('select');
                    selectValor.className = 'form-control mr-2 valor-filtro';
                    
                    // Añadir tiendas de la respuesta
                    data.forEach(tienda => {
                        agregarOpcion(selectValor, tienda.id, tienda.nombre);
                    });
                    
                    valorInput.parentNode.replaceChild(selectValor, valorInput);
                })
                .catch(error => {
                    console.error('Error al cargar tiendas:', error);
                });
            break;
            
        case 'rol_vendedor':
            // Operadores para roles
            agregarOpcion(operadorSelect, '=', 'Es');
            agregarOpcion(operadorSelect, '!=', 'No es');
            
            // Mostrar opciones de roles
            fetch('/api/roles')
                .then(response => response.json())
                .then(data => {
                    // Convertir input a select
                    const selectValor = document.createElement('select');
                    selectValor.className = 'form-control mr-2 valor-filtro';
                    
                    // Añadir roles de la respuesta
                    data.forEach(rol => {
                        agregarOpcion(selectValor, rol.id, rol.nombre);
                    });
                    
                    valorInput.parentNode.replaceChild(selectValor, valorInput);
                })
                .catch(error => {
                    console.error('Error al cargar roles:', error);
                });
            break;
            
        default:
            // Operadores para texto
            agregarOpcion(operadorSelect, '=', 'Es igual a');
            agregarOpcion(operadorSelect, '!=', 'No es igual a');
            agregarOpcion(operadorSelect, 'contiene', 'Contiene');
            agregarOpcion(operadorSelect, 'empieza_con', 'Empieza con');
            agregarOpcion(operadorSelect, 'termina_con', 'Termina con');
            
            valorInput.type = 'text';
            valorInput.placeholder = 'Valor...';
    }
}

/**
 * Función auxiliar para añadir opciones a un select
 */
function agregarOpcion(selectElement, value, text) {
    const option = document.createElement('option');
    option.value = value;
    option.textContent = text;
    selectElement.appendChild(option);
}

/**
 * Aplica el filtro actual y recarga la página
 */
function aplicarFiltro() {
    const configuracion = obtenerConfiguracionFiltro();
    
    if (configuracion) {
        const filtroJson = encodeURIComponent(JSON.stringify(configuracion));
        window.location.href = `${window.location.pathname}?filtro_personalizado=${filtroJson}`;
    }
}

/**
 * Guarda el filtro actual para uso futuro
 */
function guardarFiltro() {
    const configuracion = obtenerConfiguracionFiltro();
    
    if (!configuracion) return;
    
    // Abrir modal para guardar filtro
    const nombreFiltro = prompt('Ingrese un nombre para el filtro:');
    if (!nombreFiltro) return;
    
    const esPredeterminado = confirm('¿Desea establecer este filtro como predeterminado?');
    
    // Datos para enviar al servidor
    const datos = {
        nombre: nombreFiltro,
        es_predeterminado: esPredeterminado,
        configuracion: configuracion,
        orden: 10
    };
    
    // Enviar datos al servidor mediante fetch
    fetch('/filtros', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(datos)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error al guardar el filtro');
        }
        return response.json();
    })
    .then(data => {
        alert('Filtro guardado correctamente');
        window.location.href = `${window.location.pathname}?filtro_id=${data.id}`;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar el filtro: ' + error.message);
    });
}

/**
 * Obtiene la configuración completa del filtro actual
 */
function obtenerConfiguracionFiltro() {
    const criteriosElements = document.querySelectorAll('.criterio-item');
    const criterios = [];
    
    criteriosElements.forEach(criterio => {
        if (criterio.id === 'criterio-template') return;
        
        const campoSelect = criterio.querySelector('.campo-filtro');
        const operadorSelect = criterio.querySelector('.operador-filtro');
        const valorInput = criterio.querySelector('.valor-filtro');
        
        if (campoSelect && operadorSelect && valorInput && 
            campoSelect.value && operadorSelect.value) {
            
            let tipo = 'texto';
            let valor = valorInput.value;
            
            // Determinar tipo según el campo
            if (campoSelect.value === 'ultimo_seguimiento') {
                tipo = 'tiempo';
                valor = valor + 'd'; // Añadir 'd' para indicar días
            } else if (campoSelect.value === 'monto_cotizacion' || 
                     campoSelect.value === 'probabilidad') {
                tipo = 'numero';
            } else if (campoSelect.value === 'cotizacion_activa') {
                tipo = 'boolean';
                valor = valor === 'true';
            }
            
            criterios.push({
                campo: campoSelect.value,
                operador: operadorSelect.value,
                valor: valor,
                tipo: tipo
            });
        }
    });
    
    if (criterios.length === 0) {
        alert('Debe agregar al menos un criterio de filtrado');
        return null;
    }
    
    // Obtener ordenamiento
    const ordenSelect = document.getElementById('ordenarPor');
    let ordenamiento = [];
    
    if (ordenSelect && ordenSelect.value) {
        const [campo, direccion] = ordenSelect.value.split('-');
        ordenamiento.push({
            campo: campo,
            direccion: direccion
        });
    }
    
    return {
        criterios: criterios,
        ordenamiento: ordenamiento
    };
}

/**
 * Carga el filtro actual en el panel de filtros
 */
function cargarFiltroActual() {
    if (!filtroActual) return;
    
    // Limpiar criterios existentes
    const contenedor = document.getElementById('criteriosFiltro');
    const criteriosElements = contenedor.querySelectorAll('.criterio-item');
    criteriosElements.forEach(criterio => {
        if (criterio.id !== 'criterio-template') {
            criterio.remove();
        }
    });
    
    // Cargar los criterios del filtro actual
    if (filtroActual.criterios && filtroActual.criterios.length > 0) {
        filtroActual.criterios.forEach(criterio => {
            agregarCriterio();
            const nuevoCriterio = contenedor.lastElementChild;
            
            if (nuevoCriterio) {
                const campoSelect = nuevoCriterio.querySelector('.campo-filtro');
                campoSelect.value = criterio.campo;
                
                // Actualizar operadores y valores disponibles
                actualizarOperadoresYValores(campoSelect);
                
                // Establecer operador y valor
                const operadorSelect = nuevoCriterio.querySelector('.operador-filtro');
                operadorSelect.value = criterio.operador;
                
                const valorInput = nuevoCriterio.querySelector('.valor-filtro');
                if (valorInput) {
                    // Manejar casos especiales
                    if (criterio.tipo === 'tiempo' && typeof criterio.valor === 'string') {
                        valorInput.value = criterio.valor.replace('d', '');
                    } else {
                        valorInput.value = criterio.valor;
                    }
                }
            }
        });
    } else {
        // Si no hay criterios, añadir uno vacío
        agregarCriterio();
    }
    
    // Establecer ordenamiento
    if (filtroActual.ordenamiento && filtroActual.ordenamiento.length > 0) {
        const ordenSelect = document.getElementById('ordenarPor');
        const ordenamiento = filtroActual.ordenamiento[0];
        ordenSelect.value = `${ordenamiento.campo}-${ordenamiento.direccion}`;
    }
}

/**
 * Abre el modal de gestión de filtros
 */
function openFiltroModal(filtroId) {
    // Aquí iría el código para abrir un modal más complejo
    // Por ahora simplemente mostramos el panel de filtros
    mostrarPanelFiltros();
    
    if (filtroId) {
        // Cargar datos del filtro existente
        fetch(`/filtros/${filtroId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('filtroTitulo').textContent = `Editar: ${data.nombre}`;
                filtroActual = data.configuracion;
                cargarFiltroActual();
            })
            .catch(error => {
                console.error('Error al cargar filtro:', error);
            });
    } else {
        document.getElementById('filtroTitulo').textContent = 'Nuevo filtro';
        filtroActual = null;
    }
}
