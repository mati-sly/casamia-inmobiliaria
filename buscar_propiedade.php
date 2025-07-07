<?php
header('Content-Type: application/json');
include("setup/config.php");
$conexion = conectar();

// Procesar filtros
$filtro_region = isset($_GET['region']) ? $_GET['region'] : '';
$filtro_tipo = isset($_GET['tipo-propiedad']) ? $_GET['tipo-propiedad'] : '';
$filtro_provincia = isset($_GET['provincia']) ? $_GET['provincia'] : '';
$filtro_comuna = isset($_GET['comuna']) ? $_GET['comuna'] : '';
$filtro_sector = isset($_GET['sector']) ? $_GET['sector'] : '';
$filtro_precio_min = isset($_GET['precio_min']) ? $_GET['precio_min'] : '';
$filtro_precio_max = isset($_GET['precio_max']) ? $_GET['precio_max'] : '';
$filtro_dormitorios = isset($_GET['dormitorios']) ? $_GET['dormitorios'] : '';
$filtro_banos = isset($_GET['banos']) ? $_GET['banos'] : '';

// Construir consulta
$sql = "SELECT 
            p.idpropiedades,
            p.titulopropiedad,
            p.precio_pesos,
            p.cant_domitorios,
            p.cant_banos,
            p.area_total,
            p.tipo_propiedad,
            p.region,
            p.provincia,
            p.comuna,
            p.sector,
            g.foto
        FROM propiedades p
        JOIN galeria g ON p.idpropiedades = g.idpropiedades
        WHERE g.principal = 1";

$condiciones = [];
$parametros = [];

// Agregar condiciones según filtros
if (!empty($filtro_region)) {
    $condiciones[] = "p.region = ?";
    $parametros[] = $filtro_region;
}

if (!empty($filtro_tipo)) {
    $condiciones[] = "p.tipo_propiedad = ?";
    $parametros[] = $filtro_tipo;
}

if (!empty($filtro_provincia)) {
    $condiciones[] = "p.provincia = ?";
    $parametros[] = $filtro_provincia;
}

if (!empty($filtro_comuna)) {
    $condiciones[] = "p.comuna = ?";
    $parametros[] = $filtro_comuna;
}

if (!empty($filtro_sector)) {
    $condiciones[] = "p.sector = ?";
    $parametros[] = $filtro_sector;
}

if (!empty($filtro_precio_min)) {
    $condiciones[] = "p.precio_pesos >= ?";
    $parametros[] = $filtro_precio_min;
}

if (!empty($filtro_precio_max)) {
    $condiciones[] = "p.precio_pesos <= ?";
    $parametros[] = $filtro_precio_max;
}

if (!empty($filtro_dormitorios)) {
    $condiciones[] = "p.cant_domitorios >= ?";
    $parametros[] = $filtro_dormitorios;
}

if (!empty($filtro_banos)) {
    $condiciones[] = "p.cant_banos >= ?";
    $parametros[] = $filtro_banos;
}

// Agregar condiciones a la consulta
if (!empty($condiciones)) {
    $sql .= " AND " . implode(" AND ", $condiciones);
}

$sql .= " LIMIT 12";

// Preparar y ejecutar consulta
$stmt = $conexion->prepare($sql);
if (!empty($parametros)) {
    $tipos = str_repeat('s', count($parametros));
    $stmt->bind_param($tipos, ...$parametros);
}
$stmt->execute();
$resultado = $stmt->get_result();

// Recopilar resultados
$propiedades = [];
while($fila = $resultado->fetch_assoc()) {
    $propiedades[] = $fila;
}

// Devolver JSON
echo json_encode($propiedades);
?>