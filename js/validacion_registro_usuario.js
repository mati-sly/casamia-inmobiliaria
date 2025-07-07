// VALIDAR TODOS LOS DATOS DE ENTRADA PARA apretar el boton INGRESAR o MOIDIFICAR
// de la pag regsitro_usuario.php
function validarFormulario(op) {
  const form = document.forms.formulario;

  // Validar RUT
  let rut = form.rut.value.trim();
  if (rut == "") {
    Swal.fire({
      icon: "error",
      title: "Debe ingresar el RUT",
    });
    form.rut.focus();
    return false;
  }

  // Agrega guion si falta
  if (!rut.includes("-") && rut.length >= 8) {
    rut = rut.slice(0, -1) + "-" + rut.slice(-1);
    form.rut.value = rut;
  }

  const rutRegex = /^\d{7,8}-[kK0-9]$/;
  if (!rutRegex.test(rut)) {
    Swal.fire({
      icon: "error",
      title: "RUT no válido",
      text: "Ej: 12345678-9",
    });
    form.rut.focus();
    return false;
  }

  // Validar Nombre
  const nombre = form.nombre.value.trim();
  const soloLetras = /^[A-Za-zÁÉÍÓÚÑáéíóúñ\s]+$/;
  if (nombre == "") {
    Swal.fire({
      icon: "error",
      title: "Debe ingresar un nombre",
    });
    form.nombre.focus();
    return false;
  } else if (!soloLetras.test(nombre)) {
    Swal.fire({
      icon: "error",
      title: "Nombre no válido",
      text: "Solo debe contener letras",
    });
    form.nombre.focus();
    return false;
  }

  // Apellido Paterno
  const apellidoP = form.apellidoP.value.trim();
  if (apellidoP == "") {
    Swal.fire({
      icon: "error",
      title: "Debe ingresar el apellido paterno",
    });
    form.apellidoP.focus();
    return false;
  } else if (!soloLetras.test(apellidoP)) {
    Swal.fire({
      icon: "error",
      title: "Apellido paterno no válido",
      text: "Solo debe contener letras",
    });
    form.apellidoP.focus();
    return false;
  }

  // Apellido Materno
  const apellidoM = form.apellidoM.value.trim();
  if (apellidoM == "") {
    Swal.fire({
      icon: "error",
      title: "Debe ingresar el apellido materno",
    });
    form.apellidoM.focus();
    return false;
  } else if (!soloLetras.test(apellidoM)) {
    Swal.fire({
      icon: "error",
      title: "Apellido materno no válido",
      text: "Solo debe contener letras",
    });
    form.apellidoM.focus();
    return false;
  }

  // Usuario (Correo)
  const correo = form.usuario.value.trim();
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (correo == "") {
    Swal.fire({
      icon: "error",
      title: "Debe ingresar un correo electrónico",
    });
    form.usuario.focus();
    return false;
  } else if (!emailRegex.test(correo)) {
    Swal.fire({
      icon: "error",
      title: "Correo no válido",
      text: "Ej: nombre@correo.com",
    });
    form.usuario.focus();
    return false;
  }

  // Validar Contraseña solo si es INGRESAR
  if (op == "Ingresar") {
    const clave = form.clave.value;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d])[\S]{8,}$/;
    if (clave == "") {
      Swal.fire({
        icon: "error",
        title: "Debe ingresar una contraseña",
      });
      form.clave.focus();
      return false;
    }
    if (!passwordRegex.test(clave)) {
      Swal.fire({
        icon: "error",
        title: "Contraseña no válida",
        text: "Debe tener al menos 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 símbolo",
      });
      form.clave.focus();
      return false;
    }
  }

  // Todo OK
  return true;
}

// Modificamos la función enviar(op)
function enviar(op) {
  if (!validarFormulario(op)) return;

  document.formulario.opoculto.value = op;
  document.formulario.submit();
}


function cancelar() {
  Swal.fire({
    title: "¿Estás seguro que deseas cancelar?",
    text: "Se perderán los cambios no guardados.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, cancelar",
    cancelButtonText: "No"
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = "registro_usuario.php"; // recarga y limpia todo
    }
  });
}





//  VALIDACIÓN PARA ELIMINAR CON ICONO "BASURERO"
// Función para confirmar eliminación con GET 
function confirmarEliminacion(id) {
  Swal.fire({
    title: "¿Estás seguro?",
    text: "Esta acción eliminará el usuario permanentemente.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar"
  }).then((result) => {
    if (result.isConfirmed) {
      // Redirigir al backend para eliminar
      window.location.href = "crudusuarios.php?idusu=" + id;
    }
  });
}

// VALIDACIÓN PARA ELIMINAR CON BOTÓN "ELIMINAR"
// Función para confirmar eliminación con POST
function confirmarEliminarPOST() {
  Swal.fire({
    title: "¿Estás segura/o?",
    text: "¡Esto eliminará al usuario permanentemente!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar"
  }).then((result) => {
    if (result.isConfirmed) {
      document.formulario.opoculto.value = "Eliminar";
      document.formulario.submit();
    }
  });
}
