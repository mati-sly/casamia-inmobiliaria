<?php
include("setup/config.php");

if (!isset($_GET['idpro']) || !is_numeric($_GET['idpro'])) {
    echo "Error: Propiedad no válida.";
    exit;
}
$idpropiedad = (int)$_GET['idpro'];
$conexion = conectar();

// Consulta para obtener los detalles de la propiedad
$sqlProp = "SELECT p.*, t.tipo, s.sector, c.comuna, pr.provincia, r.region
    FROM propiedades p
    JOIN tipo_propiedad t ON p.idtipo_propiedad = t.idtipo_propiedad
    JOIN sectores s ON p.idsector = s.idsectores
    JOIN comunas c ON s.idcomunas = c.idcomunas
    JOIN provincias pr ON c.idprovincias = pr.idprovincias
    JOIN regiones r ON pr.idregiones = r.idregiones
    WHERE p.idpropiedades = $idpropiedad";

$resProp = $conexion->query($sqlProp);
if (!$resProp || $resProp->num_rows == 0) {
    echo "Propiedad no encontrada.";
    exit;
}
$row = $resProp->fetch_assoc();

// Consulta de galería de imágenes
$sqlGaleria = "SELECT foto, principal FROM galeria WHERE idpropiedades = $idpropiedad";
$resGaleria = $conexion->query($sqlGaleria);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($row['titulopropiedad']); ?></title>
  <link rel="icon" type="image/x-icon" href="IMG/favicon.ico" />
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/vermasprop.css">
</head>
<body>
  <div class="container my-5">

    <!-- Carrusel -->
    <div id="galeriaCar" class="carousel slide mb-4" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <?php
        $i = 0;
        $resGaleria->data_seek(0);
        while ($img = $resGaleria->fetch_assoc()) {
        ?>
        <button type="button" data-bs-target="#galeriaCar" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo ($img['principal']) ? "active" : ""; ?>"></button>
        <?php $i++; } ?>
      </div>
      <div class="carousel-inner">
        <?php
        $resGaleria->data_seek(0);
        while ($img = $resGaleria->fetch_assoc()) {
        ?>
        <div class="carousel-item <?php echo ($img['principal']) ? "active" : ""; ?>">
          <img src="propiedades/<?php echo htmlspecialchars($img['foto']); ?>" class="d-block w-100 carousel-img" alt="">
        </div>
        <?php } ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#galeriaCar" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#galeriaCar" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    </div>

    <!-- Información de la propiedad -->
    <div class="propiedad-info card p-4">
      <h2><?php echo htmlspecialchars($row['titulopropiedad']); ?></h2>
      <p class="precio">$<?php echo number_format($row['precio_pesos'], 0, ',', '.'); ?></p>
      <ul class="lista-info">
        <li><strong>Tipo:</strong> <?php echo $row['tipo']; ?></li>
        <li><strong>Ubicación:</strong> <?php echo "{$row['sector']}, {$row['comuna']}, {$row['provincia']}, {$row['region']}"; ?></li>
        <li><strong>Dormitorios:</strong> <?php echo $row['cant_domitorios']; ?></li>
        <li><strong>Baños:</strong> <?php echo $row['cant_banos']; ?></li>
        <li><strong>Área Total:</strong> <?php echo $row['area_total']; ?> m²</li>
        <li><strong>Área Construida:</strong> <?php echo $row['area_construida']; ?> m²</li>
        <li><strong>Estacionamiento:</strong> <?php echo $row['estacionamiento'] ? "Sí" : "No"; ?></li>
        <li><strong>Bodega:</strong> <?php echo $row['bodega'] ? "Sí" : "No"; ?></li>
        <li><strong>Logia:</strong> <?php echo $row['logia'] ? "Sí" : "No"; ?></li>
        <li><strong>Piscina:</strong> <?php echo $row['piscina'] ? "Sí" : "No"; ?></li>
      </ul>
      <p><strong>Descripción:</strong><br><?php echo nl2br(htmlspecialchars($row['descripcion'])); ?></p>
      <a href="index.php" class="btn btn-primary">← Volver al Inicio</a>
    </div>

  </div>

  <!-- Scripts locales -->
  <script src="javascrip/jquery-3.7.1.min.js"></script>
  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
