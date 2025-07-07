/**
 * VALIDACIONES Y FUNCIONALIDADES PARA REGISTRO DE PROPIEDADES
 * ===========================================================
 * Archivo JS optimizado con SweetAlert2
 */

// INICIALIZACIÓN JQUERY
$(function() {
    // Búsqueda en tiempo real
    $("#txtbusqueda").on("keyup", function() {
        buscar($(this).val());
    });
    
    // Validación en tiempo real de campos
    $("#titulo").on("blur", function() {
        validarCampo(this, "El título es obligatorio");
    });
    
    $("#precio").on("blur", function() {
        if (this.value && this.value <= 0) {
            mostrarError("El precio debe ser mayor a 0");
        }
    });
    
    // Prevenir envío accidental del formulario
    $("#formprop").on("submit", function(e) {
        e.preventDefault();
        return false;
    });
});

// FUNCIÓN DE BÚSQUEDA AJAX
function buscar(txt) {
    $.ajax({
        type: "POST",
        url: "filtrar_prop.php",
        data: "txt=" + txt,
        success: function(respuesta) {
            $('#mostrarpropiedades').html(respuesta);
        },
        error: function() {
            console.log("Error en la búsqueda");
        }
    });
}

// FUNCIÓN PRINCIPAL: ENVIAR FORMULARIO
function enviar(valor) {
    // Mostrar loading inicial
    mostrarLoading("Validando datos...");
    
    // Validar según la operación
    if (valor === 'Ingresar') {
        validarInsercion(valor);
    } else if (valor === 'Modificar') {
        validarModificacion(valor);
    } else {
        procesarOperacion(valor);
    }
}

// VALIDAR INSERCIÓN DE NUEVA PROPIEDAD
function validarInsercion(valor) {
    const datos = obtenerDatosFormulario();
    const errores = [];
    
    // Validaciones obligatorias
    if (!datos.titulo.trim()) errores.push("El título es obligatorio");
    if (!datos.precio || datos.precio <= 0) errores.push("El precio debe ser mayor a 0");
    if (!datos.tipo) errores.push("Debe seleccionar un tipo de propiedad");
    if (!datos.sector) errores.push("Debe seleccionar un sector");
    if (datos.fotos.length === 0) errores.push("Debe subir al menos una fotografía");
    
    // Validaciones de archivos
    if (datos.fotos.length > 10) errores.push("Máximo 10 fotografías permitidas");
    
    const archivosInvalidos = validarTiposArchivo(datos.fotos);
    if (archivosInvalidos.length > 0) {
        errores.push("Archivos no válidos: " + archivosInvalidos.join(', '));
    }
    
    if (errores.length > 0) {
        Swal.close();
        mostrarErrores(errores);
        return;
    }
    
    // Confirmar inserción
    Swal.close();
    confirmarInsercion(datos, valor);
}

// VALIDAR MODIFICACIÓN DE PROPIEDAD
function validarModificacion(valor) {
    const datos = obtenerDatosFormulario();
    const errores = [];
    
    // Validaciones básicas
    if (!datos.titulo.trim()) errores.push("El título es obligatorio");
    if (!datos.precio || datos.precio <= 0) errores.push("El precio debe ser mayor a 0");
    if (!datos.tipo) errores.push("Debe seleccionar un tipo de propiedad");
    if (!datos.sector) errores.push("Debe seleccionar un sector");
    
    // Validar nuevas fotos si se subieron
    if (datos.fotos.length > 10) errores.push("Máximo 10 fotografías permitidas");
    
    const archivosInvalidos = validarTiposArchivo(datos.fotos);
    if (archivosInvalidos.length > 0) {
        errores.push("Archivos no válidos: " + archivosInvalidos.join(', '));
    }
    
    if (errores.length > 0) {
        Swal.close();
        mostrarErrores(errores);
        return;
    }
    
    // Confirmar modificación
    Swal.close();
    confirmarModificacion(datos, valor);
}

// OBTENER DATOS DEL FORMULARIO
function obtenerDatosFormulario() {
    return {
        titulo: $("#titulo").val(),
        precio: $("#precio").val(),
        tipo: $("#tipo_propiedad").val(),
        sector: $("#sector").val(),
        fotos: document.getElementById('frm_foto').files
    };
}

// VALIDAR TIPOS DE ARCHIVO
function validarTiposArchivo(archivos) {
    const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    const archivosInvalidos = [];
    
    for (let i = 0; i < archivos.length; i++) {
        if (!tiposPermitidos.includes(archivos[i].type)) {
            archivosInvalidos.push(archivos[i].name);
        }
    }
    
    return archivosInvalidos;
}

// CONFIRMAR INSERCIÓN
function confirmarInsercion(datos, valor) {
    Swal.fire({
        title: '¿Ingresar nueva propiedad?',
        html: `
            <div class="text-left">
                <p><strong>Título:</strong> ${datos.titulo}</p>
                <p><strong>Precio:</strong> $${Number(datos.precio).toLocaleString()}</p>
                <p><strong>Fotografías:</strong> ${datos.fotos.length}</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, ingresar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarLoading("Ingresando propiedad...", "Subiendo fotografías y guardando datos");
            procesarOperacion(valor);
        }
    });
}

// CONFIRMAR MODIFICACIÓN
function confirmarModificacion(datos, valor) {
    const mensajefotos = datos.fotos.length > 0 ? 
        `<p><strong>Nuevas fotos:</strong> ${datos.fotos.length}</p>` : 
        '<p><em>No se subirán nuevas fotos</em></p>';
    
    Swal.fire({
        title: '¿Modificar propiedad?',
        html: `
            <div class="text-left">
                <p><strong>Título:</strong> ${datos.titulo}</p>
                <p><strong>Precio:</strong> $${Number(datos.precio).toLocaleString()}</p>
                ${mensajefotos}
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, modificar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarLoading("Modificando propiedad...", "Actualizando datos");
            procesarOperacion(valor);
        }
    });
}

// PROCESAR OPERACIÓN
function procesarOperacion(valor) {
    document.formulario.opoculto.value = valor;
    document.formulario.submit();
}

// FUNCIÓN CANCELAR
function cancelar() {
    const datos = obtenerDatosFormulario();
    const esEdicion = document.querySelector('input[name="idoculto"]').value !== '';
    
    // Verificar si hay cambios
    let hayCambios = datos.titulo.trim() !== '' || 
                     datos.precio !== '' || 
                     datos.fotos.length > 0;
    
    if (hayCambios) {
        Swal.fire({
            title: '¿Salir sin guardar?',
            text: 'Se perderán todos los cambios realizados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'Continuar editando',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarLoading("Cancelando...", "Regresando al listado", 1000);
                setTimeout(() => {
                    window.location = "registro_propiedades.php";
                }, 1000);
            }
        });
    } else {
        window.location = "registro_propiedades.php";
    }
}

// CONFIRMAR ELIMINACIÓN (DESDE TABLA)
function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Eliminar esta propiedad?',
        text: 'Esta acción eliminará permanentemente la propiedad y todas sus fotografías',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarLoading("Eliminando propiedad...", "Eliminando datos y fotografías");
            window.location = "crudpropiedades.php?idprop=" + id;
        }
    });
}

// CONFIRMAR ELIMINACIÓN (DESDE FORMULARIO)
function confirmarEliminarPOST() {
    Swal.fire({
        title: '¿Eliminar esta propiedad?',
        text: 'Esta acción eliminará permanentemente la propiedad y todas sus fotografías',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarLoading("Eliminando propiedad...", "Eliminando datos y fotografías");
            enviar('Eliminar');
        }
    });
}

// VISTA PREVIA DE IMÁGENES
function previewImages(input) {
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    
    imagePreview.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        previewContainer.style.display = 'block';
        
        // Validar cantidad
        if (input.files.length > 10) {
            mostrarAdvertencia('Máximo 10 imágenes permitidas');
        }
        
        // Validar tipos
        const archivosInvalidos = validarTiposArchivo(input.files);
        if (archivosInvalidos.length > 0) {
            mostrarError('Archivos no válidos: ' + archivosInvalidos.join(', '));
        }
        
        const maxImages = Math.min(input.files.length, 10);
        
        for (let i = 0; i < maxImages; i++) {
            const file = input.files[i];
            
            if (!file.type.startsWith('image/')) {
                continue;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.createElement('div');
                imgContainer.style.cssText = 'position: relative; display: inline-block; margin: 5px;';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.cssText = 'width: 80px; height: 60px; object-fit: cover; border-radius: 5px; border: 2px solid #28a745;';
                
                const fileName = document.createElement('small');
                fileName.textContent = file.name;
                fileName.style.cssText = 'display: block; text-align: center; margin-top: 2px; font-size: 10px; color: #666; max-width: 80px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;';
                
                const fileSize = document.createElement('small');
                fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                fileSize.style.cssText = 'display: block; text-align: center; font-size: 9px; color: #999;';
                
                imgContainer.appendChild(img);
                imgContainer.appendChild(fileName);
                imgContainer.appendChild(fileSize);
                imagePreview.appendChild(imgContainer);
            };
            
            reader.readAsDataURL(file);
        }
    } else {
        previewContainer.style.display = 'none';
    }
}

// FUNCIONES DE UTILIDAD PARA SWEETALERT2

// Mostrar loading con mensaje personalizable
function mostrarLoading(titulo, texto = "Por favor espere", tiempo = 0) {
    const config = {
        title: titulo,
        text: texto,
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    };
    
    if (tiempo > 0) {
        config.timer = tiempo;
        config.timerProgressBar = true;
    }
    
    Swal.fire(config);
}

// Mostrar error simple
function mostrarError(mensaje) {
    Swal.fire({
        title: 'Error',
        text: mensaje,
        icon: 'error',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#dc3545'
    });
}

// Mostrar múltiples errores
function mostrarErrores(errores) {
    const listaErrores = errores.map(error => `• ${error}`).join('\n');
    
    Swal.fire({
        title: 'Campos requeridos',
        text: listaErrores,
        icon: 'warning',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#ffc107'
    });
}

// Mostrar advertencia
function mostrarAdvertencia(mensaje) {
    Swal.fire({
        title: 'Atención',
        text: mensaje,
        icon: 'warning',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#ffc107'
    });
}

// Mostrar éxito
function mostrarExito(mensaje) {
    Swal.fire({
        title: 'Éxito',
        text: mensaje,
        icon: 'success',
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#28a745'
    });
}

// Validar campo individual
function validarCampo(campo, mensaje) {
    if (!campo.value.trim()) {
        campo.classList.add('is-invalid');
        mostrarError(mensaje);
        return false;
    } else {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        return true;
    }
}

// Mostrar información de propiedad (función extra)
function mostrarInfoPropiedad(id) {
    mostrarLoading("Cargando información...", "Obteniendo datos de la propiedad");
    
    setTimeout(() => {
        Swal.fire({
            title: 'Propiedad #' + id,
            html: `
                <div class="text-left">
                    <p><strong>ID:</strong> ${id}</p>
                    <p><strong>Estado:</strong> Activa</p>
                    <p><em>Esta funcionalidad puede expandirse para mostrar más detalles</em></p>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#3085d6'
        });
    }, 800);
}

// Validación en tiempo real mejorada
function validarFormularioCompleto() {
    const campos = [
        { id: 'titulo', mensaje: 'El título es obligatorio' },
        { id: 'precio', mensaje: 'El precio es obligatorio' },
        { id: 'tipo_propiedad', mensaje: 'Debe seleccionar un tipo' },
        { id: 'sector', mensaje: 'Debe seleccionar un sector' }
    ];
    
    let esValido = true;
    
    campos.forEach(campo => {
        const elemento = document.getElementById(campo.id);
        if (!validarCampo(elemento, campo.mensaje)) {
            esValido = false;
        }
    });
    
    return esValido;
}

// Toast notifications para acciones rápidas
function mostrarToast(mensaje, tipo = 'success') {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: tipo,
        title: mensaje,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// Función para manejar errores del servidor
function manejarErrorServidor(mensaje) {
    Swal.fire({
        title: 'Error del servidor',
        text: mensaje,
        icon: 'error',
        confirmButtonText: 'Reintentar',
        confirmButtonColor: '#dc3545',
        showCancelButton: true,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.reload();
        }
    });
}

// Validaciones adicionales para campos específicos
function validarPrecio(precio) {
    if (!precio || precio <= 0) {
        mostrarError('El precio debe ser mayor a 0');
        return false;
    }
    if (precio > 999999999) {
        mostrarError('El precio es demasiado alto');
        return false;
    }
    return true;
}

// Confirmar antes de abandonar página si hay cambios
let formularioModificado = false;

$(document).ready(function() {
    // Detectar cambios en el formulario
    $('#formprop input, #formprop select, #formprop textarea').on('change', function() {
        formularioModificado = true;
    });
    
    // Advertir al salir si hay cambios no guardados
    window.addEventListener('beforeunload', function(e) {
        if (formularioModificado) {
            e.preventDefault();
            e.returnValue = '¿Está seguro de que desea salir? Los cambios no guardados se perderán.';
        }
    });
});

// Funciones adicionales para mejorar UX
function limpiarFormulario() {
    document.getElementById('formprop').reset();
    document.getElementById('preview-container').style.display = 'none';
    document.getElementById('image-preview').innerHTML = '';
    formularioModificado = false;
}

function enfocarPrimerError() {
    const primerError = document.querySelector('.is-invalid');
    if (primerError) {
        primerError.focus();
        primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}