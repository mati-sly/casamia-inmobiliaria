<?php
session_start();


// Verificar autenticación y que sea propietario
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipoUsuario'] != 'propietario') {
    header('Location: login.php');
    exit;
}

include("setup/config.php");
$conexion = conectar();
$conexion->set_charset("utf8");

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';

// Verificar que el usuario sea realmente propietario en BD
$verificar_propietario = $conexion->prepare("SELECT tipoUsuario FROM usuarios WHERE id = ? AND tipoUsuario = 'propietario'");
$verificar_propietario->bind_param("i", $usuario_id);
$verificar_propietario->execute();
$resultado_verificacion = $verificar_propietario->get_result();

if ($resultado_verificacion->num_rows == 0) {
    echo "Error: Solo los propietarios pueden acceder a esta página.";
    exit;
}

// Procesar formulario si se envió
if ($_POST) {
    $operacion = $_POST['opoculto'];
    
    // Procesar checkboxes (si no están marcados, enviar 0)
    $bodega = isset($_POST['bodega']) ? 1 : 0;
    $estacionamiento = isset($_POST['estacionamiento']) ? 1 : 0;
    $logia = isset($_POST['logia']) ? 1 : 0;
    $cocinaamoblada = isset($_POST['cocinaamoblada']) ? 1 : 0;
    $antejardin = isset($_POST['antejardin']) ? 1 : 0;
    $patiotrasero = isset($_POST['patiotrasero']) ? 1 : 0;
    $piscina = isset($_POST['piscina']) ? 1 : 0;
    
    if ($operacion == 'Ingresar') {
        // Insertar nueva propiedad
        $sql = "INSERT INTO propiedades (
            titulopropiedad, descripcion, direccion, cant_banos, cant_domitorios, 
            area_total, area_construida, precio_pesos, precio_uf, 
            fecha_publicacion, estado, idtipo_propiedad, idsector, id_usuario,
            bodega, estacionamiento, logia, cocinaamoblada, 
            antejardin, patiotrasero, piscina
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssiiiiiiiiiiiiiiiii",
            $_POST['titulopropiedad'], $_POST['descripcion'], $_POST['direccion'],
            $_POST['cant_banos'], $_POST['cant_domitorios'], $_POST['area_total'], 
            $_POST['area_construida'], $_POST['precio_pesos'], $_POST['precio_uf'], 
            $_POST['estado'], $_POST['tipo_propiedad'], $_POST['sector'], $usuario_id,
            $bodega, $estacionamiento, $logia, $cocinaamoblada, 
            $antejardin, $patiotrasero, $piscina
        );
        
        if ($stmt->execute()) {
            $mensaje = "Propiedad registrada exitosamente";
        } else {
            $mensaje = "Error al registrar propiedad: " . $stmt->error;
        }
    } 
    elseif ($operacion == 'Modificar') {
        // Validar si quiere activar la propiedad
        if ($_POST['estado'] == 1) {
            $id_propiedad = $_POST['idoculto'];
            $contar_imagenes = $conexion->prepare("SELECT COUNT(*) as total FROM galeria WHERE idpropiedades = ? AND estado = 1");
            $contar_imagenes->bind_param("i", $id_propiedad);
            $contar_imagenes->execute();
            $resultado_contar = $contar_imagenes->get_result();
            $total_imagenes_visibles = $resultado_contar->fetch_assoc()['total'];
            
            if ($total_imagenes_visibles == 0) {
                $mensaje = "Error: Debes tener al menos 1 imagen visible para activar la propiedad";
            } else {
                // Tiene imágenes, proceder con actualización
                $sql = "UPDATE propiedades SET 
                    titulopropiedad=?, descripcion=?, direccion=?, cant_banos=?, cant_domitorios=?, 
                    area_total=?, area_construida=?, precio_pesos=?, precio_uf=?, 
                    estado=?, idtipo_propiedad=?, idsector=?, bodega=?, estacionamiento=?, 
                    logia=?, cocinaamoblada=?, antejardin=?, patiotrasero=?, piscina=?
                    WHERE idpropiedades=? AND id_usuario=?";
                
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("sssiiiiiiiiiiiiiiiiii",
                    $_POST['titulopropiedad'], $_POST['descripcion'], $_POST['direccion'],
                    $_POST['cant_banos'], $_POST['cant_domitorios'], $_POST['area_total'], 
                    $_POST['area_construida'], $_POST['precio_pesos'], $_POST['precio_uf'], 
                    $_POST['estado'], $_POST['tipo_propiedad'], $_POST['sector'], 
                    $bodega, $estacionamiento, $logia, $cocinaamoblada, 
                    $antejardin, $patiotrasero, $piscina, $_POST['idoculto'], $usuario_id
                );
                
                if ($stmt->execute()) {
                    $mensaje = "Propiedad actualizada exitosamente";
                } else {
                    $mensaje = "Error al actualizar propiedad: " . $stmt->error;
                }
            }
        } else {
            // No la está activando, actualizar normal sin validar imágenes
            $sql = "UPDATE propiedades SET 
                titulopropiedad=?, descripcion=?, direccion=?, cant_banos=?, cant_domitorios=?, 
                area_total=?, area_construida=?, precio_pesos=?, precio_uf=?, 
                estado=?, idtipo_propiedad=?, idsector=?, bodega=?, estacionamiento=?, 
                logia=?, cocinaamoblada=?, antejardin=?, patiotrasero=?, piscina=?
                WHERE idpropiedades=? AND id_usuario=?";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sssiiiiiiiiiiiiiiiiii",
                $_POST['titulopropiedad'], $_POST['descripcion'], $_POST['direccion'],
                $_POST['cant_banos'], $_POST['cant_domitorios'], $_POST['area_total'], 
                $_POST['area_construida'], $_POST['precio_pesos'], $_POST['precio_uf'], 
                $_POST['estado'], $_POST['tipo_propiedad'], $_POST['sector'], 
                $bodega, $estacionamiento, $logia, $cocinaamoblada, 
                $antejardin, $patiotrasero, $piscina, $_POST['idoculto'], $usuario_id
            );
            
            if ($stmt->execute()) {
                $mensaje = "Propiedad actualizada exitosamente";
            } else {
                $mensaje = "Error al actualizar propiedad: " . $stmt->error;
            }
        }
    }
}

// Eliminar propiedad si se solicita
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    
    // Verificar que la propiedad pertenece al usuario
    $verificar = $conexion->prepare("SELECT * FROM propiedades WHERE idpropiedades = ? AND id_usuario = ?");
    $verificar->bind_param("ii", $id_eliminar, $usuario_id);
    $verificar->execute();
    $resultado_verificar = $verificar->get_result();
    
    if ($resultado_verificar->num_rows > 0) {
        $eliminar = $conexion->prepare("DELETE FROM propiedades WHERE idpropiedades = ? AND id_usuario = ?");
        $eliminar->bind_param("ii", $id_eliminar, $usuario_id);
        if ($eliminar->execute()) {
            $mensaje = "Propiedad eliminada exitosamente";
        }
    } else {
        $mensaje = "Error: No puedes eliminar propiedades que no te pertenecen";
    }
}

// Obtener datos de propiedad para editar (solo si pertenece al usuario)
$datos_propiedad = null;
if (isset($_GET['idprop'])) {
    $sql_prop = "SELECT p.*, s.idcomunas, c.idprovincias, pr.idregiones 
             FROM propiedades p
             JOIN sectores s ON p.idsector = s.idsectores
             JOIN comunas c ON s.idcomunas = c.idcomunas
             JOIN provincias pr ON c.idprovincias = pr.idprovincias
             WHERE p.idpropiedades = ? AND p.id_usuario = ?";
    $stmt_prop = $conexion->prepare($sql_prop);
    $stmt_prop->bind_param("ii", $_GET['idprop'], $usuario_id);
    $stmt_prop->execute();
    $resultado_prop = $stmt_prop->get_result();
    $datos_propiedad = $resultado_prop->fetch_assoc();
    
    if (!$datos_propiedad) {
        echo "Error: No puedes editar propiedades que no te pertenecen";
        exit;
    }
}

// Cargar datos para selects
$tipos_propiedad = $conexion->query("SELECT * FROM tipo_propiedad WHERE estado = 1");
$regiones = $conexion->query("SELECT * FROM regiones WHERE estado = 1");
$provincias = $conexion->query("SELECT * FROM provincias WHERE estado = 1");

// Obtener SOLO las propiedades del usuario logueado
$sql_mis_propiedades = "SELECT 
    p.*, tp.tipo, s.sector, c.comuna, pr.provincia,
    (SELECT foto FROM galeria WHERE idpropiedades = p.idpropiedades AND principal = 1 LIMIT 1) as foto_principal
    FROM propiedades p
    JOIN tipo_propiedad tp ON p.idtipo_propiedad = tp.idtipo_propiedad
    JOIN sectores s ON p.idsector = s.idsectores
    JOIN comunas c ON s.idcomunas = c.idcomunas
    JOIN provincias pr ON c.idprovincias = pr.idprovincias
    WHERE p.id_usuario = ?
    ORDER BY p.fecha_publicacion DESC";

$stmt_mis_propiedades = $conexion->prepare($sql_mis_propiedades);
$stmt_mis_propiedades->bind_param("i", $usuario_id);
$stmt_mis_propiedades->execute();
$mis_propiedades = $stmt_mis_propiedades->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestión de Propiedades</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="IMG/favicon.ico" />
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="css/usuarioPropiedades.css" rel="stylesheet">
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/sweetalert2.all.min.js"></script>
</head>
<body>
    <!-- Formulario Principal - COMPACTO -->
    <div id="formulario">
        <div class="card">
            <div class="card-header">
                <?php echo isset($_GET['idprop']) ? 'EDITAR PROPIEDAD' : 'NUEVA PROPIEDAD'; ?>
            </div>
            <div class="card-body">
                <form action="usuarioPropiedades.php" name="formulario" method="post" enctype="multipart/form-data">
                    
                    <!-- TRES COLUMNAS: Información Básica + Ubicación + Características -->
                    <div class="row">
                        <!-- COLUMNA 1: Información Básica -->
                        <div class="col-md-4">
                            <div class="seccion-formulario-compacta">
                                <h6 class="titulo-seccion-pequeno">📋 Información Básica</h6>
                                <div class="mb-2">
                                    <label for="titulopropiedad" class="form-label-sm">Título:</label>
                                    <input type="text" class="form-control form-control-sm" id="titulopropiedad" name="titulopropiedad" 
                                           value="<?php echo $datos_propiedad['titulopropiedad'] ?? ''; ?>" required>
                                </div>
                                <div class="mb-2">
                                    <label for="tipo_propiedad" class="form-label-sm">Tipo:</label>
                                    <select class="form-select form-select-sm" id="tipo_propiedad" name="tipo_propiedad" required>
                                        <option value="">Seleccionar</option>
                                        <?php while($tipo = $tipos_propiedad->fetch_assoc()): ?>
                                            <option value="<?php echo $tipo['idtipo_propiedad']; ?>"
                                                    <?php echo (isset($datos_propiedad['idtipo_propiedad']) && 
                                                               $datos_propiedad['idtipo_propiedad'] == $tipo['idtipo_propiedad']) ? 'selected' : ''; ?>>
                                                <?php echo $tipo['tipo']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="direccion" class="form-label-sm">Dirección:</label>
                                    <input type="text" class="form-control form-control-sm" id="direccion" name="direccion" 
                                           placeholder="Ej: Av. Del Mar 123"
                                           value="<?php echo $datos_propiedad['direccion'] ?? ''; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="descripcion" class="form-label-sm">Descripción:</label>
                                    <textarea class="form-control form-control-sm" id="descripcion" name="descripcion" rows="2" placeholder="Descripción breve..."><?php echo $datos_propiedad['descripcion'] ?? ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- COLUMNA 2: Ubicación -->
                        <div class="col-md-4">
                            <div class="seccion-formulario-compacta">
                                <h6 class="titulo-seccion-pequeno">📍 Ubicación</h6>
                                <div class="mb-2">
                                    <label for="region" class="form-label-sm">Región:</label>
                                    <select class="form-select form-select-sm" id="region" name="region" data-selected="<?php echo $datos_propiedad['idregiones'] ?? ''; ?>">
                                        <option value="">Seleccionar</option>
                                        <?php while($region = $regiones->fetch_assoc()): ?>
                                            <option value="<?php echo $region['idregiones']; ?>">
                                                <?php echo $region['region']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="provincia" class="form-label-sm">Provincia:</label>
                                    <select class="form-select form-select-sm" id="provincia" name="provincia" data-selected="<?php echo $datos_propiedad['idprovincias'] ?? ''; ?>">
                                        <option value="">Seleccionar</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="comuna" class="form-label-sm">Comuna:</label>
                                    <select class="form-select form-select-sm" id="comuna" name="comuna" data-selected="<?php echo $datos_propiedad['idcomunas'] ?? ''; ?>">
                                        <option value="">Seleccionar</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="sector" class="form-label-sm">Sector:</label>
                                    <select class="form-select form-select-sm" id="sector" name="sector" data-selected="<?php echo $datos_propiedad['idsector'] ?? ''; ?>">
                                        <option value="">Seleccionar</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- COLUMNA 3: Características -->
                        <div class="col-md-4">
                            <div class="seccion-formulario-compacta">
                                <h6 class="titulo-seccion-pequeno">🏠 Características</h6>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <label for="cant_domitorios" class="form-label-sm">Dormitorios:</label>
                                        <input type="number" class="form-control form-control-sm" id="cant_domitorios" name="cant_domitorios" 
                                               value="<?php echo $datos_propiedad['cant_domitorios'] ?? ''; ?>" min="0">
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label for="cant_banos" class="form-label-sm">Baños:</label>
                                        <input type="number" class="form-control form-control-sm" id="cant_banos" name="cant_banos" 
                                               value="<?php echo $datos_propiedad['cant_banos'] ?? ''; ?>" min="0">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <label for="area_total" class="form-label-sm">Área Total (m²):</label>
                                        <input type="number" class="form-control form-control-sm" id="area_total" name="area_total" 
                                               value="<?php echo $datos_propiedad['area_total'] ?? ''; ?>" min="1" required>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label for="area_construida" class="form-label-sm">Área Construida (m²):</label>
                                        <input type="number" class="form-control form-control-sm" id="area_construida" name="area_construida" 
                                               value="<?php echo $datos_propiedad['area_construida'] ?? ''; ?>" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DOS COLUMNAS: Precio + Características Adicionales -->
                    <div class="row">
                        <!-- COLUMNA IZQUIERDA: Precio -->
                        <div class="col-md-6">
                            <div class="seccion-formulario-compacta">
                                <h6 class="titulo-seccion-pequeno">💰 Precio</h6>
                                <div class="mb-2">
                                    <label for="precio_pesos" class="form-label-sm">Precio (CLP):</label>
                                    <input type="number" class="form-control form-control-sm" id="precio_pesos" name="precio_pesos" 
                                           value="<?php echo $datos_propiedad['precio_pesos'] ?? ''; ?>" min="1" required>
                                </div>
                                <div class="mb-2">
                                    <label for="precio_uf" class="form-label-sm">Precio (UF):</label>
                                    <input type="number" class="form-control form-control-sm" id="precio_uf" name="precio_uf" 
                                           value="<?php echo $datos_propiedad['precio_uf'] ?? ''; ?>" readonly>
                                    <small class="form-text-sm">Se calcula automáticamente</small>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label-sm">Estado:</label>
                                    <select class="form-select form-select-sm" name="estado" required>
                                        <option value="">Seleccionar</option>
                                        <option value="1" <?php echo (isset($datos_propiedad['estado']) && $datos_propiedad['estado'] == 1) ? 'selected' : ''; ?>>Activa</option>
                                        <option value="0" <?php echo (isset($datos_propiedad['estado']) && $datos_propiedad['estado'] == 0) ? 'selected' : (!isset($datos_propiedad['estado']) ? 'selected' : ''); ?>>Inactiva</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- COLUMNA DERECHA: Características Adicionales -->
                        <div class="col-md-6">
                            <div class="seccion-formulario-compacta">
                                <h6 class="titulo-seccion-pequeno">✨ Características Adicionales</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="bodega" name="bodega" value="1"
                                                   <?php echo (isset($datos_propiedad['bodega']) && $datos_propiedad['bodega'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label-sm" for="bodega">Bodega</label>
                                        </div>
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="estacionamiento" name="estacionamiento" value="1"
                                                   <?php echo (isset($datos_propiedad['estacionamiento']) && $datos_propiedad['estacionamiento'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label-sm" for="estacionamiento">Estacionamiento</label>
                                        </div>
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="logia" name="logia" value="1"
                                                   <?php echo (isset($datos_propiedad['logia']) && $datos_propiedad['logia'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label-sm" for="logia">Logia</label>
                                        </div>
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="piscina" name="piscina" value="1"
                                                   <?php echo (isset($datos_propiedad['piscina']) && $datos_propiedad['piscina'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label-sm" for="piscina">Piscina</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="cocinaamoblada" name="cocinaamoblada" value="1"
                                                   <?php echo (isset($datos_propiedad['cocinaamoblada']) && $datos_propiedad['cocinaamoblada'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label-sm" for="cocinaamoblada">Cocina Amoblada</label>
                                        </div>
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="antejardin" name="antejardin" value="1"
                                                   <?php echo (isset($datos_propiedad['antejardin']) && $datos_propiedad['antejardin'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label-sm" for="antejardin">Antejardín</label>
                                        </div>
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" id="patiotrasero" name="patiotrasero" value="1"
                                                   <?php echo (isset($datos_propiedad['patiotrasero']) && $datos_propiedad['patiotrasero'] == 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label-sm" for="patiotrasero">Patio Trasero</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="text-center mt-3">
                        <?php if (!isset($_GET['idprop'])): ?>
                            <button type="button" onclick="validarYEnviar('Ingresar')" class="btn btn-primary">
                                💾 GUARDAR PROPIEDAD
                            </button>
                        <?php else: ?>
                            <button type="button" onclick="validarYEnviar('Modificar')" class="btn btn-success">
                                ✏️ ACTUALIZAR PROPIEDAD
                            </button>
                            <a href="gestionarGaleria.php?idprop=<?php echo $_GET['idprop']; ?>" class="btn btn-info">
                                📸 GESTIONAR IMÁGENES
                            </a>
                        <?php endif; ?>
                        <button type="button" onclick="cancelar()" class="btn btn-secondary">
                            ❌ CANCELAR
                        </button>
                    </div>

                    <input type="hidden" name="opoculto">
                    <input type="hidden" name="idoculto" value="<?php echo $_GET['idprop'] ?? ''; ?>">
                </form>
            </div>
        </div>
    </div>

    <!-- Lista de Propiedades -->
    <div id="mis-propiedades">
        <div class="card">
            <div class="card-header">
                🏠 Mis Propiedades (<b>Total: <?php echo $mis_propiedades->num_rows; ?></b>)
                <a href="usuarioPropiedades.php" class="btn btn-success btn-sm float-end">
                    ➕ Nueva Propiedad
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Dirección</th>
                                <th>Ubicación</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($propiedad = $mis_propiedades->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($propiedad['foto_principal']): ?>
                                        <img src="propiedades/<?php echo $propiedad['foto_principal']; ?>" 
                                             class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="IMG/sin-imagen.png" class="img-thumbnail" style="width: 60px; height: 60px;">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($propiedad['titulopropiedad']); ?></td>
                                <td><?php echo $propiedad['tipo']; ?></td>
                                <td>
                                    <?php echo !empty($propiedad['direccion']) ? htmlspecialchars($propiedad['direccion']) : 'Sin dirección'; ?>
                                </td>
                                <td><?php echo $propiedad['sector'] . ', ' . $propiedad['comuna']; ?></td>
                                <td>$<?php echo number_format($propiedad['precio_pesos'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($propiedad['estado'] == 1): ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactiva</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button onclick="editarPropiedad(<?php echo $propiedad['idpropiedades']; ?>)" 
                                            class="btn btn-sm btn-warning" title="Editar Propiedad">
                                        Editar
                                    </button>
                                    <button onclick="gestionarImagenes(<?php echo $propiedad['idpropiedades']; ?>)" 
                                            class="btn btn-sm btn-info" title="Gestionar Imágenes">
                                        Fotos
                                    </button>
                                    <button onclick="confirmarEliminar(<?php echo $propiedad['idpropiedades']; ?>)" 
                                            class="btn btn-sm btn-danger" title="Eliminar Propiedad">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="js/usuarioPropiedades.js"></script>
    
    <?php if ($mensaje): ?>
    <script>
        Swal.fire({
           icon: '<?php echo strpos($mensaje, "exitosamente") !== false ? "success" : "error"; ?>',
           title: '<?php echo $mensaje; ?>',
           timer: 3000,
           showConfirmButton: false
       });
   </script>
   <?php endif; ?>
</body>
</html>