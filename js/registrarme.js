function validarFormulario() {

    const form = document.forms.formRegistro; //almacena el formulario en una variable y asi ya no es necesario escribir todo eso

    //el "tipo de usuario no puede quedar vacio"
    if (form.tipoUsuario.value == "") {
        Swal.fire({
            icon: "error",
            title: "Debe seleccionar un tipo de usuario",
        });
        form.tipoUsuario.focus();
        return false;
    }

    //validamos rut (vacio y formato con digito verificador)
    let rut = form.rut.value.trim(); // trim elimina espacios en blanco al inicio y al final
    if (rut == "") {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar su RUT",
        });
        form.rut.focus();
        return false;
    }

    // si no tiene guión, lo agregamos automáticamente
    if (!rut.includes("-") && rut.length >= 8) {
        rut = rut.slice(0, -1) + "-" + rut.slice(-1);
        form.rut.value = rut;
    }

    const rutRegex = /^\d{7,8}-[kK0-9]$/;
    if (!rutRegex.test(rut)) {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar un RUT válido",
            text: "Ejemplo: 12345678-9",
        });
        form.rut.focus();
        return false;
    }

    // validar nombres, solo letras y sin simbolos especiales
    const nombres = form.nombres.value.trim();
    const soloLetras = /^[A-Za-zÁÉÍÓÚÑáéíóúñ\s]+$/;
    if (nombres == "") {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar su nombre",
        });
        form.nombres.focus();
        return false;
    }
    if (!soloLetras.test(nombres)) {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar un nombre válido",
            text: "Solo letras, sin números ni símbolos",
        });
        form.nombres.focus();
        return false;
    }

    //  Apellido Paterno, aqui no creamos constante, ya que ya esta creada "soloLetras"
    const apellidoP = form.apellidoPaterno.value.trim();
    if (apellidoP == "") {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar su apellido paterno",
        });
        form.apellidoPaterno.focus();
        return false;
    }
    if (!soloLetras.test(apellidoP)) {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar un apellido paterno válido",
            text: "Solo letras, sin números ni símbolos",
        });
        form.apellidoPaterno.focus();
        return false;
    }

    // Apellido Materno
    const apellidoM = form.apellidoMaterno.value.trim();
    if (apellidoM == "") {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar su apellido materno",
        });
        form.apellidoMaterno.focus();
        return false;
    }
    if (!soloLetras.test(apellidoM)) {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar un apellido materno válido",
            text: "Solo letras, sin números ni símbolos",
        });
        form.apellidoMaterno.focus();
        return false;
    }

    //VALIDAR FECHA DE NACIMIENTO QUE LA PERSONA TENGA MAYOR DE 18 AÑOS y no mas de 120
    if (form.fechaNacimiento.value == "") {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar su fecha de nacimiento",
        });
        form.fechaNacimiento.focus();
        return false;
    } else {
        const fechaNacimiento = new Date(form.fechaNacimiento.value);
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
        const mes = hoy.getMonth() - fechaNacimiento.getMonth();
        const dia = hoy.getDate() - fechaNacimiento.getDate();

        if (mes < 0 || (mes == 0 && dia < 0)) {
            edad = edad - 1;
        }

        if (edad < 18 || edad > 120) {
            Swal.fire({
                icon: "error",
                title: "Edad no válida",
                text: "Debe tener entre 18 y 120 años para registrarse",
            });
            form.fechaNacimiento.focus();
            return false;
        }
    }

    // validar Correo electrónico
    const correo = form.correo.value.trim();
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (correo == "") {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar su correo",
        });
        form.correo.focus();
        return false;
    }
    if (!emailRegex.test(correo)) {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar un correo válido",
            text: "Ejemplo: nombre@correo.com",
        });
        form.correo.focus();
        return false;
    }

    // validar Contraseña robusta
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

    //  Confirmar contraseña
    const confirmar = form.confirmarclave.value;
    if (confirmar == "") {
        Swal.fire({
            icon: "error",
            title: "Debe confirmar su contraseña",
        });
        form.confirmarclave.focus();
        return false;
    }
    if (confirmar != clave) { // no creamos la constante clave, porque arriba ya la creamos
        Swal.fire({
            icon: "error",
            title: "Las contraseñas no coinciden",
        });
        form.confirmarclave.focus();
        return false;
    }

    // Sexo NO vacio
    if (form.sexo.value == "") {
        Swal.fire({
            icon: "error",
            title: "Debe seleccionar su sexo",
        });
        form.sexo.focus();
        return false;
    }

    // Teléfono móvil (chileno), se le agrega solo el +56 si es que el usario no lo pone
    let celular = form.cel.value.trim();
    if (celular == "") {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar su número de teléfono",
        });
        form.cel.focus();
        return false;
    }

    celular = celular.replace(/\s+/g, ''); // quitar espacios
    const celRegex = /^\+56\d{9}$/;
    if (!celular.startsWith("+56")) {
        celular = "+56" + celular;
    }
    if (!celRegex.test(celular)) {
        Swal.fire({
            icon: "error",
            title: "Debe ingresar un teléfono válido",
            text: "Ejemplo: +56912345678",
        });
        form.cel.focus();
        return false;
    }

    // Si todo está correcto
    form.submit();
}
