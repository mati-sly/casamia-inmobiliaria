<?php
//aqui vamos a tener todos los metodos que deberiamos tener 

function conectar()
{                       //servidor,usuario,contraseña,base de datos
    $con= mysqli_connect("localhost","TU_USUARIO_DB","","inmobiliaria_casamia");
    return $con;
}

//contar a los usuarios que esten en la base de datos 
function contarusu()
{
    $sql="select * from usuarios";
    $result=mysqli_query(conectar(),$sql);
    $contar=mysqli_num_rows($result);

    return $contar;
}


?>