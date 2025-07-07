
<?php
//esta FUNCION LA MANDAMOS A SETUP/CONFIG 
//function conectar()
//{                       //servidor,usuario,contraseña,base de datos
//    $con= mysqli_connect("localhost","root","","inmobiliaria_casamia");
//    return $con;
//}

// Aqui llamamos la funcion conectar que esta en config.php
include("setup/config.php");

// Solo ejecutamos si el formulario fue enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Creamos la query para buscar al usuario por su correo (input name="usuario")
    $sql = "SELECT * FROM usuarios WHERE usuario='".$_POST['usuario']."' and estado=1";// si el estado es inactivo (0) no puede loguearse

    // Ejecutamos la query
    $result = mysqli_query(conectar(), $sql);

    // Contamos cuántos registros trae la consulta
    $contador = mysqli_num_rows($result);

    if ($contador != 0) {
        // Si encontró un usuario con ese correo, sacamos los datos
        $datos = mysqli_fetch_array($result);

        // Comparamos la clave ingresada (input name="clave") con la almacenada en la BD usando password_verify
        if (password_verify($_POST['clave'], $datos['clave'])) {
            // Si la clave es correcta, iniciamos sesión
            session_start(); //Inicializar sesión
            $_SESSION['usuario'] = $datos['nombres'] . " " . $datos['apellidoPaterno'] . " " . $datos['apellidoMaterno'];

            // Redirigimos al dashboard
            header("Location: dashboard.php");
            exit;
        }
    }

    // Si el usuario no existe o la clave no coincide
    header("Location: login.html");
    exit;
}
?>