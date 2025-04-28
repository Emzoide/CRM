document.addEventListener('DOMContentLoaded', function () {
  // Inicializar todos los desplegables de oportunidades
  console.log('timeline.js v2.0 cargado');
  const oportunidadHeaders = document.querySelectorAll('.oportunidad-header');

  oportunidadHeaders.forEach(header => {
    header.addEventListener('click', function () {
      // Obtener el contenido asociado a este encabezado
      const content = this.nextElementSibling;
      const toggle = this.querySelector('.oportunidad-toggle');

      // Alternar la clase active para mostrar/ocultar el contenido
      content.classList.toggle('active');
      toggle.classList.toggle('active');

      // Cerrar otros desplegables (opcional - comenta esta sección si quieres que múltiples puedan estar abiertos)
      oportunidadHeaders.forEach(otherHeader => {
        if (otherHeader !== header) {
          const otherContent = otherHeader.nextElementSibling;
          const otherToggle = otherHeader.querySelector('.oportunidad-toggle');
          otherContent.classList.remove('active');
          otherToggle.classList.remove('active');
        }
      });
    });
  });
});

// Función para abrir el modal de seguimiento
function openSeguimientoModal(oportunidadId, sinOportunidadActiva) {
  const modal = document.getElementById('seguimientoModal');
  if (!modal) {
    console.error("No se encontró el modal de seguimiento");
    return;
  }

  // Establecer el ID de la oportunidad en el formulario
  document.querySelector('#seguimientoForm input[name="oportunidad_id"]').value = oportunidadId;

  // Mostrar/ocultar campos de cotización según sinOportunidadActiva
  const camposCotizacion = document.getElementById('camposCotizacion');
  if (camposCotizacion) {
    camposCotizacion.style.display = sinOportunidadActiva ? 'block' : 'none';
  }

  // Establecer la fecha y hora actual en el campo de contacto
  const contactoEnInput = document.querySelector('input[name="contacto_en"]');
  if (contactoEnInput) {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    contactoEnInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
  }

  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

// Función para cerrar el modal de seguimiento
function closeSeguimientoModal() {
  const modal = document.getElementById('seguimientoModal');
  if (!modal) return;
  modal.style.display = 'none';
  document.body.style.overflow = '';
  const form = document.getElementById('seguimientoForm');
  if (form) form.reset();
}

// Función para manejar el envío del formulario
document.getElementById('seguimientoForm')?.addEventListener('submit', async function (e) {
  e.preventDefault();

  // Validar campos requeridos
  const requiredFields = this.querySelectorAll('[data-required="true"]');
  let isValid = true;

  requiredFields.forEach(field => {
    if (!field.value) {
      isValid = false;
      field.classList.add('is-invalid');
    } else {
      field.classList.remove('is-invalid');
    }
  });

  if (!isValid) {
    alert('Por favor, complete todos los campos requeridos');
    return;
  }

  // Obtener los datos del formulario
  const formData = new FormData(this);
  const data = Object.fromEntries(formData.entries());

  // Convertir valores booleanos
  data.seguro_vehicular = data.seguro_vehicular === '1';
  data.compra_plazos = data.compra_plazos === '1';

  try {
    // Asegurarnos de que la URL base es correcta
    const baseUrl = window.location.origin;
    const oportunidadId = data.oportunidad_id;
    
    if (!oportunidadId) {
      throw new Error('No se ha especificado el ID de la oportunidad');
    }

    const response = await fetch(`${baseUrl}/api/oportunidades/${oportunidadId}/cotizaciones`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Error al crear la cotización');
    }

    const result = await response.json();

    if (result.success) {
      closeSeguimientoModal();
      // Recargar la página para mostrar los cambios
      window.location.reload();
    } else {
      alert(result.message || 'Error al crear la cotización');
    }
  } catch (error) {
    console.error('Error:', error);
    alert(error.message || 'Error al procesar la solicitud');
  }
});

// Función para abrir el modal de nueva cotización
function openNuevaCotizacionModal(oportunidadId) {
  const modal = document.getElementById('seguimientoModal');
  if (!modal) {
    console.error("No se encontró el modal de seguimiento");
    return;
  }

  // Establecer el ID de la oportunidad en el formulario
  const oportunidadInput = document.querySelector('#seguimientoForm input[name="oportunidad_id"]');
  if (!oportunidadInput) {
    console.error("No se encontró el campo de ID de oportunidad");
    return;
  }
  oportunidadInput.value = oportunidadId;

  // Mostrar campos de cotización y hacerlos requeridos
  const camposCotizacion = document.getElementById('camposCotizacion');
  if (camposCotizacion) {
    camposCotizacion.style.display = 'block';
    const camposRequeridos = camposCotizacion.querySelectorAll('[data-required="true"]');
    camposRequeridos.forEach(campo => {
      campo.setAttribute('required', 'required');
    });
  }

  // Establecer la fecha y hora actual en el campo de contacto
  const contactoEnInput = document.querySelector('input[name="contacto_en"]');
  if (contactoEnInput) {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    contactoEnInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
  }

  // Cambiar el título del modal
  const modalTitle = modal.querySelector('.modal-title');
  if (modalTitle) {
    modalTitle.textContent = 'Nueva Cotización';
  }

  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
} 