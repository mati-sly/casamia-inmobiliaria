<?php
include("setup/config.php");

if(isset($_POST['txt'])) {
    $txt = $_POST['txt'];
    
    $sql = "SELECT * FROM usuarios WHERE 
            nombres LIKE '%$txt%' OR 
            apellidoPaterno LIKE '%$txt%' OR 
            apellidoMaterno LIKE '%$txt%' OR 
            rut LIKE '%$txt%' OR 
            usuario LIKE '%$txt%'";
    
    $result = mysqli_query(conectar(), $sql);
?>

<div class="card">
    <div class="card-header">Resultados de búsqueda para: "<?php echo $txt; ?>" (<b>Total: <?php echo mysqli_num_rows($result); ?></b>)</div>
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
                while($datos = mysqli_fetch_array($result)) {
                ?>
                <tr>
                    <td><?php echo $num; ?></td>
                    <td>
                        <?php if($datos['foto'] == '') { ?>
                            <img src="IMG/usuarios/comodin.png" width="32px">
                        <?php } else { ?>
                            <img src="IMG/usuarios/<?php echo $datos['foto']; ?>" width="32px">
                        <?php } ?>
                    </td>
                    <td><?php echo $datos['rut']; ?></td>
                    <td><?php echo $datos['nombres']; ?></td>
                    <td><?php echo $datos['apellidoPaterno']." ".$datos['apellidoMaterno']; ?></td>
                    <td><?php echo $datos['usuario']; ?></td>
                    <td>
                        <?php if($datos['estado'] == 1) { ?>
                            <img src="IMG/check.png" width="25px">
                        <?php } else { ?>
                            <img src="IMG/x.png" width="25px">
                        <?php } ?>
                    </td>
                    <td>
                        <a href="registro_usuario.php?idusu=<?php echo $datos['id']; ?>">
                            <img src="IMG/actualizar.png" width="25px">
                        </a>
                        | 
                        <a href="#" onclick="confirmarEliminacion(<?php echo $datos['id']; ?>); return false;">
                            <img src="IMG/basura.png" width="25px">
                        </a>
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

<?php
}
?>