<?php

include("setup/config.php");

switch ($_POST['op']) {
    case 1:
        provincias();
        break;
    case 2:
        comunas();
        break;
    case 3:
        sectores();
        break;
}
?>

<?php 

function provincias()
{
    ?>
    <select id="provincias" class="form-select">
        <option value="0" selected>Seleccionar</option>
        <?php
        $sql = "SELECT * FROM provincias WHERE idregiones = ".$_POST['idregion'];
        $result = mysqli_query(conectar(), $sql);
        while($datos = mysqli_fetch_array($result)) {
            ?>
            <option value="<?php echo $datos['idprovincias']; ?>"><?php echo $datos['provincia']; ?></option>
            <?php 
        }
        ?>
    </select>
    <?php
}

function comunas(){
    ?>
    <select id="comunas" class="form-select">
        <option value="0" selected>Seleccionar</option>
        <?php
        $sql = "SELECT * FROM comunas WHERE idprovincias = ".$_POST['idprovincias'];
        $result = mysqli_query(conectar(), $sql);
        while($datos = mysqli_fetch_array($result)) {
            ?>
            <option value="<?php echo $datos['idcomunas']; ?>"><?php echo $datos['comuna']; ?></option>
            <?php 
        }
        ?>
    </select>
    <?php
}

function sectores(){
    ?>
    <select id="sectores" class="form-select">
        <option value="0" selected>Seleccionar</option>
        <?php
        $sql = "SELECT * FROM sectores WHERE idcomunas = ".$_POST['idcomunas'];
        $result = mysqli_query(conectar(), $sql);
        while($datos = mysqli_fetch_array($result)) {
            ?>
            <option value="<?php echo $datos['idsectores']; ?>"><?php echo $datos['sector']; ?></option>
            <?php 
        }
        ?>
    </select>
    <?php
}
?>
