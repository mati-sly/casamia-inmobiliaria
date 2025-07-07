<?php
include("setup/config.php");
$conexion = conectar();
$conexion->set_charset("utf8");

// Eliminar usuario
if(isset($_GET['idusu'])) {
    $sql = "DELETE FROM usuarios WHERE id = " . $_GET['idusu'];
    mysqli_query($conexion, $sql);
    header("Location: registro_usuario.php");
    exit;
}

// Procesar formulario
if(isset($_POST['opoculto'])) {
    $operacion = $_POST['opoculto'];
    
    if($operacion == 'Ingresar') {
        // Insertar usuario
        $foto_nombre = '';
        if($_FILES["frm_foto"]["name"] != '') {
            $foto_nombre = $_FILES["frm_foto"]["name"];
            move_uploaded_file($_FILES["frm_foto"]["tmp_name"], "IMG/usuarios/" . $_FILES['frm_foto']['name']);
        }
        
        $clave_hash = password_hash($_POST['clave'], PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO usuarios (rut, nombres, apellidoPaterno, apellidoMaterno, usuario, clave, estado, foto) 
                VALUES ('".$_POST['rut']."', '".$_POST['nombre']."', '".$_POST['apellidoP']."', '".$_POST['apellidoM']."', 
                '".$_POST['usuario']."', '".$clave_hash."', '".$_POST['estado']."', '".$foto_nombre."')";
        
        mysqli_query($conexion, $sql);
        header("Location: registro_usuario.php");
        exit;
    }
    
    if($operacion == 'Modificar') {
        // Modificar usuario
        if($_FILES['frm_foto']['name'] != '') {
            $foto_nombre = $_FILES["frm_foto"]["name"];
            move_uploaded_file($_FILES["frm_foto"]["tmp_name"], "IMG/usuarios/" . $_FILES['frm_foto']['name']);
            
            $sql = "UPDATE usuarios SET 
                    rut = '".$_POST['rut']."', 
                    nombres = '".$_POST['nombre']."', 
                    apellidoPaterno = '".$_POST['apellidoP']."', 
                    apellidoMaterno = '".$_POST['apellidoM']."', 
                    usuario = '".$_POST['usuario']."', 
                    estado = '".$_POST['estado']."', 
                    foto = '".$foto_nombre."' 
                    WHERE id = ".$_POST['idoculto'];
        } else {
            $sql = "UPDATE usuarios SET 
                    rut = '".$_POST['rut']."', 
                    nombres = '".$_POST['nombre']."', 
                    apellidoPaterno = '".$_POST['apellidoP']."', 
                    apellidoMaterno = '".$_POST['apellidoM']."', 
                    usuario = '".$_POST['usuario']."', 
                    estado = '".$_POST['estado']."' 
                    WHERE id = ".$_POST['idoculto'];
        }
        
        mysqli_query($conexion, $sql);
        header("Location: registro_usuario.php");
        exit;
    }
    
    if($operacion == 'Eliminar') {
        $sql = "DELETE FROM usuarios WHERE id = " . $_POST['idoculto'];
        mysqli_query($conexion, $sql);
        header("Location: registro_usuario.php");
        exit;
    }
}
?>