$(document).ready(function() {
   // Inicializar eventos
   inicializarEventos();
   cargarUbicacionInicial();
});

function inicializarEventos() {
   // Conversión automática de pesos a UF
   $('#precio_pesos').on('input', function() {
       calcularPrecioUF();
   });

   // Validaciones por tipo de propiedad
   $('#tipo_propiedad').on('change', function() {
       validarPorTipoPropiedad();
   });



   // Validación de áreas
   $('#area_total, #area_construida').on('input', function() {
       validarAreas();
   });

   // Filtros dependientes de ubicación
   $('#region').on('change', function() {
       cargarProvincias();
   });

   $('#provincia').on('change', function() {
       cargarComunas();
   });

   $('#comuna').on('change', function() {
       cargarSectores();
   });

   // Validaciones de solo números
   $('.form-control[type="number"]').on('keypress', function(e) {
       return validarSoloNumeros(e);
   });

   // Validaciones de solo texto
   $('#titulopropiedad').on('keypress', function(e) {
       return validarSoloTexto(e);
   });
}

// Conversión automática CLP a UF
function calcularPrecioUF() {
   const precioPesos = parseFloat($('#precio_pesos').val()) || 0;
   const valorUF = 39278; // 1 UF = $39.278 CLP
   
   if (precioPesos > 0) {
       const precioUF = Math.round(precioPesos / valorUF);
       $('#precio_uf').val(precioUF);
   } else {
       $('#precio_uf').val('');
   }
}

// Validaciones por tipo de propiedad (auto-completar campos)
function validarPorTipoPropiedad() {
   // Solo auto-completar si es una propiedad NUEVA (no hay ID en el campo oculto)
   const esNueva = !document.querySelector('input[name="idoculto"]').value;
   
   if (!esNueva) {
       return; // Si está editando, no hacer nada automático
   }
   
   const tipoSeleccionado = parseInt($('#tipo_propiedad').val()) || 0;
   
   if (tipoSeleccionado === 3) { // 3 = Terreno
       $('#cant_domitorios').val(0);
       $('#cant_banos').val(0);
       $('#area_construida').val(0);
       
       mostrarMensaje('info', 'Terreno seleccionado', 'Se han configurado valores típicos para terrenos.');
   } else if (tipoSeleccionado === 1 || tipoSeleccionado === 2) { // 1=Casa, 2=Departamento
       if ($('#cant_domitorios').val() == 0) $('#cant_domitorios').val(1);
       if ($('#cant_banos').val() == 0) $('#cant_banos').val(1);
   }
}

// Validar relación entre áreas
function validarAreas() {
   const areaTotal = parseFloat($('#area_total').val()) || 0;
   const areaConstruida = parseFloat($('#area_construida').val()) || 0;
   
   if (areaConstruida > areaTotal && areaTotal > 0) {
       mostrarMensaje('warning', 'Área inválida', 'El área construida no puede ser mayor al área total.');
       $('#area_construida').val(areaTotal);
   }
}

// Validar solo números
function validarSoloNumeros(e) {
   const char = String.fromCharCode(e.which);
   if (!/[0-9]/.test(char)) {
       e.preventDefault();
       return false;
   }
   return true;
}

// Validar solo texto (letras, espacios, algunos caracteres especiales)
function validarSoloTexto(e) {
   const char = String.fromCharCode(e.which);
   if (!/[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s\-\.,]/.test(char)) {
       e.preventDefault();
       return false;
   }
   return true;
}

// Cargar ubicación inicial si estamos editando
function cargarUbicacionInicial() {
   // Obtener valores de los data-selected
   const regionSeleccionada = $('#region').data('selected');
   const provinciaSeleccionada = $('#provincia').data('selected');
   const comunaSeleccionada = $('#comuna').data('selected');
   const sectorSeleccionado = $('#sector').data('selected');
   
   // Si hay región seleccionada, seleccionarla y cargar provincias
   if (regionSeleccionada) {
       $('#region').val(regionSeleccionada);
       cargarProvincias(provinciaSeleccionada);
   }
   
   if (provinciaSeleccionada && comunaSeleccionada) {
       setTimeout(() => {
           $('#provincia').val(provinciaSeleccionada);
           cargarComunas(comunaSeleccionada);
       }, 500);
   }
   
   if (comunaSeleccionada && sectorSeleccionado) {
       setTimeout(() => {
           cargarSectores(sectorSeleccionado);
       }, 1000);
   }
}

// Cargar provincias según región
function cargarProvincias() {
   const regionId = $('#region').val();
   $('#provincia, #comuna, #sector').html('<option value="">Seleccionar</option>');
   
   if (regionId) {
       $.ajax({
           url: 'indexfiltro.php',
           type: 'GET',
           data: {
               action: 'get_provincias',
               region_id: regionId
           },
           dataType: 'json',
           success: function(data) {
               let opciones = '<option value="">Seleccionar</option>';
               $.each(data, function(index, provincia) {
                   opciones += `<option value="${provincia.idprovincias}">${provincia.provincia}</option>`;
               });
               $('#provincia').html(opciones);
           },
           error: function() {
               mostrarMensaje('error', 'Error', 'No se pudieron cargar las provincias');
           }
       });
   }
}

// Cargar comunas según provincia
function cargarComunas(comunaSeleccionada = '') {
   const provinciaId = $('#provincia').val();
   $('#comuna, #sector').html('<option value="">Seleccionar</option>');
   
   if (provinciaId) {
       $.ajax({
           url: 'indexfiltro.php',
           type: 'GET',
           data: {
               action: 'get_comunas',
               provincia_id: provinciaId
           },
           dataType: 'json',
           success: function(data) {
               let opciones = '<option value="">Seleccionar</option>';
               $.each(data, function(index, comuna) {
                   const selected = (comunaSeleccionada == comuna.idcomunas) ? 'selected' : '';
                   opciones += `<option value="${comuna.idcomunas}" ${selected}>${comuna.comuna}</option>`;
               });
               $('#comuna').html(opciones);
           },
           error: function() {
               mostrarMensaje('error', 'Error', 'No se pudieron cargar las comunas');
           }
       });
   }
}

// Cargar sectores según comuna
function cargarSectores(sectorSeleccionado = '') {
   const comunaId = $('#comuna').val();
   $('#sector').html('<option value="">Seleccionar</option>');
   
   if (comunaId) {
       $.ajax({
           url: 'indexfiltro.php',
           type: 'GET',
           data: {
               action: 'get_sectores',
               comuna_id: comunaId
           },
           dataType: 'json',
           success: function(data) {
               let opciones = '<option value="">Seleccionar</option>';
               $.each(data, function(index, sector) {
                   const selected = (sectorSeleccionado == sector.idsectores) ? 'selected' : '';
                   opciones += `<option value="${sector.idsectores}" ${selected}>${sector.sector}</option>`;
               });
               $('#sector').html(opciones);
           },
           error: function() {
               mostrarMensaje('error', 'Error', 'No se pudieron cargar los sectores');
           }
       });
   }
}

// Validar y enviar formulario
function validarYEnviar(operacion) {
   // Validaciones básicas
   if (!validarCamposObligatorios()) {
       return false;
   }

   if (!validarValoresNumericos()) {
       return false;
   }

   if (!validarTipoPropiedad()) {
       return false;
   }

   if (!validarEstadoPropiedad()) {    // ← ESTA LÍNEA ES NUEVA
        return false;
    }

   // Si todo está bien, enviar
   $('input[name="opoculto"]').val(operacion);
   
   Swal.fire({
       title: '¿Estás seguro?',
       text: `¿Deseas ${operacion.toLowerCase()} esta propiedad?`,
       icon: 'question',
       showCancelButton: true,
       confirmButtonColor: '#3085d6',
       cancelButtonColor: '#d33',
       confirmButtonText: 'Sí, continuar',
       cancelButtonText: 'Cancelar'
   }).then((result) => {
       if (result.isConfirmed) {
           document.formulario.submit();
       }
   });
}

// Validar campos obligatorios
function validarCamposObligatorios() {
   const camposObligatorios = [
       { campo: '#titulopropiedad', nombre: 'Título de la propiedad' },
       { campo: '#tipo_propiedad', nombre: 'Tipo de propiedad' },
       { campo: '#sector', nombre: 'Sector' },
       { campo: '#area_total', nombre: 'Área total' },
       { campo: '#precio_pesos', nombre: 'Precio en pesos' }
   ];

   for (let item of camposObligatorios) {
       if (!$(item.campo).val() || $(item.campo).val().trim() === '') {
           mostrarMensaje('error', 'Campo obligatorio', `El campo "${item.nombre}" es obligatorio.`);
           $(item.campo).focus();
           return false;
       }
   }
   return true;
}

// Validar estado según si es nueva propiedad o edición
function validarEstadoPropiedad() {
    const esNueva = !document.querySelector('input[name="idoculto"]').value;
    const estadoSeleccionado = $('select[name="estado"]').val();
    
    // Si es nueva propiedad y quiere activarla
    if (esNueva && estadoSeleccionado == '1') {
        mostrarMensaje('error', 'No se puede activar', 'Primero debes agregar imágenes para activar la propiedad. Guárdala como "Inactiva", agrega imágenes y luego actívala.');
        $('select[name="estado"]').focus();
        return false;
    }
    
    return true;
}

// Validar valores numéricos
function validarValoresNumericos() {
   const precio = parseFloat($('#precio_pesos').val()) || 0;
   const areaTotal = parseFloat($('#area_total').val()) || 0;
   
   if (precio <= 0) {
       mostrarMensaje('error', 'Precio inválido', 'El precio debe ser mayor a cero.');
       $('#precio_pesos').focus();
       return false;
   }

   if (areaTotal <= 0) {
       mostrarMensaje('error', 'Área inválida', 'El área total debe ser mayor a cero.');
       $('#area_total').focus();
       return false;
   }

   return true;
}

// Validar según tipo de propiedad
function validarTipoPropiedad() {
   const tipoSeleccionado = parseInt($('#tipo_propiedad').val()) || 0;
   const dormitorios = parseInt($('#cant_domitorios').val()) || 0;
   const banos = parseInt($('#cant_banos').val()) || 0;
   
   // Solo validar dormitorios y baños si NO es terreno (ID 3)
   if (tipoSeleccionado !== 3) {
       if (dormitorios <= 0) {
           mostrarMensaje('error', 'Dormitorios requeridos', 'Las casas y departamentos deben tener al menos 1 dormitorio.');
           $('#cant_domitorios').focus();
           return false;
       }

       if (banos <= 0) {
           mostrarMensaje('error', 'Baños requeridos', 'Las casas y departamentos deben tener al menos 1 baño.');
           $('#cant_banos').focus();
           return false;
       }
   }

   return true;
}

// Funciones para botones de acción con confirmaciones
function editarPropiedad(idPropiedad) {
   Swal.fire({
       title: '¿Editar propiedad?',
       text: 'Serás redirigido al formulario de edición.',
       icon: 'question',
       showCancelButton: true,
       confirmButtonColor: '#f39c12',
       cancelButtonColor: '#6c757d',
       confirmButtonText: 'Sí, editar',
       cancelButtonText: 'Cancelar'
   }).then((result) => {
       if (result.isConfirmed) {
           window.location.href = `usuarioPropiedades.php?idprop=${idPropiedad}`;
       }
   });
}

function gestionarImagenes(idPropiedad) {
   Swal.fire({
       title: '¿Gestionar imágenes?',
       text: 'Serás redirigido a la galería de fotos.',
       icon: 'info',
       showCancelButton: true,
       confirmButtonColor: '#17a2b8',
       cancelButtonColor: '#6c757d',
       confirmButtonText: 'Sí, gestionar',
       cancelButtonText: 'Cancelar'
   }).then((result) => {
       if (result.isConfirmed) {
           window.location.href = `gestionarGaleria.php?idprop=${idPropiedad}`;
       }
   });
}

// Confirmar eliminación
function confirmarEliminar(idPropiedad) {
   Swal.fire({
       title: '¿Estás seguro?',
       text: 'Esta acción eliminará permanentemente la propiedad y todas sus imágenes.',
       icon: 'warning',
       showCancelButton: true,
       confirmButtonColor: '#e74c3c',
       cancelButtonColor: '#6c757d',
       confirmButtonText: 'Sí, eliminar',
       cancelButtonText: 'Cancelar'
   }).then((result) => {
       if (result.isConfirmed) {
           window.location.href = `usuarioPropiedades.php?eliminar=${idPropiedad}`;
       }
   });
}

// Cancelar y limpiar formulario
function cancelar() {
   Swal.fire({
       title: '¿Cancelar?',
       text: 'Se perderán todos los datos ingresados.',
       icon: 'question',
       showCancelButton: true,
       confirmButtonColor: '#3085d6',
       cancelButtonColor: '#d33',
       confirmButtonText: 'Sí, cancelar',
       cancelButtonText: 'Continuar editando'
   }).then((result) => {
       if (result.isConfirmed) {
           window.location.href = 'usuarioPropiedades.php';
       }
   });
}

// Mostrar mensajes con SweetAlert
function mostrarMensaje(tipo, titulo, texto) {
   Swal.fire({
       icon: tipo,
       title: titulo,
       text: texto,
       timer: tipo === 'info' ? 3000 : 5000,
       showConfirmButton: tipo !== 'info'
   });
}