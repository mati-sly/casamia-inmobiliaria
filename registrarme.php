
<!-- aqui vamos a enviarle los datos del fromulario (registrarme.html) 
 a la Base de datos-->

<?php
include("setup/config.php"); // aqui se incluye la conexion a la base de datos metodo "conectar()"

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    

    $sql = "INSERT INTO usuarios (
                tipoUsuario, rut, nombres, apellidoPaterno, apellidoMaterno,
                fechaNacimiento, usuario, clave, sexo, cel, estado
            ) VALUES (
                '".$_POST['tipoUsuario']."',
                '".$_POST['rut']."',
                '".$_POST['nombres']."',
                '".$_POST['apellidoPaterno']."',
                '".$_POST['apellidoMaterno']."',
                '".$_POST['fechaNacimiento']."',
                '".$_POST['correo']."',
                '".password_hash($_POST['clave'], PASSWORD_BCRYPT)."',
                '".$_POST['sexo']."',
                '".$_POST['cel']."',
                0
            )";


    if (mysqli_query(conectar(), $sql)) {
    header("Location: login.html");
    exit;
    }
        
    
}
?>
