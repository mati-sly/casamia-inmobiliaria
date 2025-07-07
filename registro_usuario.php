<?php
// ✅ CONFIGURACIÓN UTF-8 IGUAL QUE EL INDEX QUE FUNCIONA
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

include("setup/config.php");
$conexion = conectar();
$conexion->set_charset("utf8"); // ✅ Misma configuración que index.php

if(isset($_GET['idusu'])) {
    $sql = "select * from usuarios where id = " . $_GET['idusu'];
    $result = mysqli_query($conexion, $sql);
    $datosusu = mysqli_fetch_array($result);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Formulario registro de usuario</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="IMG/favicon.ico" />
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="css/registro_usuario.css" rel="stylesheet"> 
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/jquery.Rut.js"></script>
</head>
<body>
    <div id="formulario">
        <div class="card">
            <div class="card-header">CRUD USUARIOS</div>
            <div class="card-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-3">
                            <?php 
                            if(isset($_GET['idusu'])) {
                                if($datosusu['foto'] != '') {
                            ?>
                                <img src="IMG/usuarios/<?php echo htmlspecialchars($datosusu['foto'], ENT_QUOTES, 'UTF-8'); ?>" width="130px"> 
                            <?php
                                } else { 
                            ?>
                                <img src="IMG/usuarios/comodin.png" width="130px"> 
                            <?php
                                }
                            } else {
                            ?>
                                <img src="IMG/usuarios/comodin.png" width="130px"> 
                            <?php
                            }
                            ?>
                        </div>
                        <div class="col-sm-9">
                            <form action="crudusuarios.php" name="formulario" method="post" enctype="multipart/form-data">
                                <div id="campos">
                                    <div class="row">
                                        <div class="col-sm"><label for="rut" class="form-label">Rut:</label></div>
                                        <div class="col-sm">
                                            <input type="text" class="form-control" id="rut" placeholder="xx.xxx.xxx-x" name="rut" 
                                                   value="<?php if(isset($_GET['idusu'])){ echo htmlspecialchars($datosusu['rut'], ENT_QUOTES, 'UTF-8'); } ?>">
                                        </div>
                                        <div class="col-sm"><label for="nombre" class="form-label">Nombre:</label></div>
                                        <div class="col-sm">
                                            <input type="text" class="form-control" id="nombre" placeholder="Ej: Susana" name="nombre" 
                                                   value="<?php if(isset($_GET['idusu'])){ echo htmlspecialchars($datosusu['nombres'], ENT_QUOTES, 'UTF-8'); } ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-sm"><label for="apellidoP" class="form-label">Apellido Paterno:</label></div>
                                        <div class="col-sm">
                                            <input type="text" class="form-control" id="apellidoP" placeholder="Ej: Torres" name="apellidoP" 
                                                   value="<?php if(isset($_GET['idusu'])){ echo htmlspecialchars($datosusu['apellidoPaterno'], ENT_QUOTES, 'UTF-8'); } ?>">
                                        </div>
                                        <div class="col-sm"><label for="apellidoM" class="form-label">Apellido Materno:</label></div>
                                        <div class="col-sm">
                                            <input type="text" class="form-control" id="apellidoM" placeholder="Ej: Gallardo" name="apellidoM" 
                                                   value="<?php if(isset($_GET['idusu'])){ echo htmlspecialchars($datosusu['apellidoMaterno'], ENT_QUOTES, 'UTF-8'); } ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-sm"><label for="usuario" class="form-label">Usuario(Email):</label></div>
                                        <div class="col-sm">
                                            <input type="email" class="form-control" id="usuario" placeholder="susana@ejemplo.com" name="usuario" 
                                                   value="<?php if(isset($_GET['idusu'])){ echo htmlspecialchars($datosusu['usuario'], ENT_QUOTES, 'UTF-8'); } ?>">
                                        </div>
                                        <?php    
                                        if(!isset($_GET['idusu'])) {
                                        ?>
                                            <div class="col-sm"><label for="clave" class="form-label">Clave:</label></div>
                                            <div class="col-sm"><input type="password" class="form-control" id="clave" placeholder="xxxxxxxx" name="clave"></div>
                                        <?php
                                        } else {
                                        ?>
                                            <div class="col-sm"></div>
                                            <div class="col-sm"></div>
                                        <?php
                                        } 
                                        ?>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm"><label class="form-label">Estado:</label></div>
                                        <div class="col-sm">
                                            <select class="form-select" name="estado">
                                                <option selected value="3">Seleccionar</option>
                                                <option value="1" <?php if(isset($_GET['idusu'])){ if($datosusu['estado']==1){ echo 'selected'; }} ?>>Activo</option>
                                                <option value="0" <?php if(isset($_GET['idusu'])){ if($datosusu['estado']==0){ echo 'selected'; }} ?>>Inactivo</option>
                                            </select>
                                        </div>
                                        <div class="col-sm"><label class="form-label">Subir Fotografía:</label></div>
                                        <div class="col-sm">
                                            <input type="file" class="form-control" name="frm_foto">
                                        </div>
                                    </div>
                                </div>
                                
                                <br><center>
                                    <?php
                                    if(!isset($_GET['idusu'])) {
                                    ?>
                                        <button type="button" onclick="enviar(this.value);" value="Ingresar" class="btn btn-primary">INGRESAR</button>
                                    <?php
                                    } else {
                                    ?>
                                        <button type="button" onclick="enviar(this.value);" class="btn btn-success" value="Modificar">MODIFICAR</button>                      
                                        <button type="button" onclick="confirmarEliminarPOST();" class="btn btn-danger">ELIMINAR</button>
                                    <?php
                                    }
                                    ?>
                                    <button type="button" onclick="cancelar();" class="btn btn-secondary">CANCELAR</button>
                                </center>

                                <input type="hidden" name="opoculto">
                                <input type="hidden" name="idoculto" value="<?php if(isset($_GET['idusu'])){echo $_GET['idusu'];} ?>">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
    </div>
    
    <div id="busqueda">
        <div class="card">
            <div class="card-header"><img src="IMG/lupa.png" width="27px">Buscador</div>
            <div class="card-body"><input type="text" class="form-control" id="txtbusqueda" name="txtbusqueda"></div>
        </div>
    </div>
    
    <div id="mostrarusuarios">
        <div class="card">
            <div class="card-header">Listado Usuarios ( <b>Total de usuarios: <?php echo contarusu();?></b> ) - <a href="exportarexcel.php"> Exportar a Excel <img src="IMG/icono-excel.png" width="27px" ></a></div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Foto</th>
                            <th>Rut</th>
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>Correo Electrónico</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $num = 1;
                        $sql = "select * from usuarios";
                        $result = mysqli_query($conexion, $sql);
                        while($datos = mysqli_fetch_array($result)) {
                        ?>
                            <tr>
                                <td><?php echo $num; ?></td>
                                <td>
                                    <?php
                                    if($datos['foto'] == '') {
                                    ?>
                                        <img src="IMG/usuarios/comodin.png" width="32px">
                                    <?php
                                    } else {
                                    ?>
                                        <img src="IMG/usuarios/<?php echo htmlspecialchars($datos['foto'], ENT_QUOTES, 'UTF-8'); ?>" width="32px">
                                    <?php
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($datos['rut'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($datos['nombres'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($datos['apellidoPaterno'] . " " . $datos['apellidoMaterno'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($datos['usuario'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php
                                    if($datos['estado'] == 1) {
                                    ?>
                                        <img src="IMG/check.png" width="25px">
                                    <?php
                                    } else {
                                    ?>
                                        <img src="IMG/x.png" width="25px">
                                    <?php
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="registro_usuario.php?idusu=<?php echo $datos['id']; ?>"><img src="IMG/actualizar.png" width="25px"></a>
                                    | 
                                    <a href="#" onclick="confirmarEliminacion(<?php echo $datos['id']; ?>); return false;"><img src="IMG/basura.png" width="25px"></a>
                                </td>
                            </tr> 
                        <?php
                            $num++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
    $(function() {
        $("#txtbusqueda").on("keyup", function() {
            buscar($("#txtbusqueda").val());
        });
    });

    function buscar(txt) {
        $.ajax({
            type: "POST",
            url: "filtrar.php",
            data: "txt=" + txt,
            success: function(respuesta) {
                $('#mostrarusuarios').html(respuesta);
            }
        });
    }
    </script>
    
    <script src="js/sweetalert2.all.min.js"></script>
    <script src="js/validacion_registro_usuario.js"></script>
</body>
</html>