<?php
// REGISTRO DE PROPIEDADES - VERSIÓN SIN POP-UPS

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("setup/config.php");

// Variable para almacenar datos de propiedad
$datosprop = null;
$modo = 'nuevo'; // 'nuevo' o 'editar'

// Verificar si se está editando una propiedad (SOLO SI EL PARÁMETRO ES VÁLIDO)
if (isset($_GET['idprop']) && !empty($_GET['idprop']) && is_numeric($_GET['idprop'])) {
    $idprop = intval($_GET['idprop']);
    
    try {
        $conexion = conectar();
        $sql = "SELECT * FROM propiedades WHERE idpropiedades = $idprop";
        $result = mysqli_query($conexion, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $datosprop = mysqli_fetch_array($result);
            $modo = 'editar';
        } else {
            // NO mostrar pop-up, solo redirigir silenciosamente
            header("Location: registro_propiedades.php");
            exit;
        }
    } catch (Exception $e) {
        // En caso de error, redirigir sin pop-up
        header("Location: registro_propiedades.php");
        exit;
    }
}

// Función para contar propiedades
function contarprop() {
    try {
        $conexion = conectar();
        $result = mysqli_query($conexion, "SELECT COUNT(*) as total FROM propiedades");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row['total'];
        }
        return 0;
    } catch (Exception $e) {
        return 0;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Registro de Propiedades</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card { margin-bottom: 2rem; }
        .debug-info { background: #e9ecef; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container mt-4">
        
        <!-- INFORMACIÓN DE DEBUG -->
        <div class="debug-info">
            <h6><i class="fas fa-info-circle"></i> Información de Debug</h6>
            <p><strong>Modo:</strong> <?= $modo ?></p>
            <p><strong>ID Propiedad:</strong> <?= isset($_GET['idprop']) ? $_GET['idprop'] : 'No especificado' ?></p>
            <p><strong>Total Propiedades:</strong> <?= contarprop() ?></p>
            <p><strong>Datos encontrados:</strong> <?= $datosprop ? 'Sí' : 'No' ?></p>
        </div>

        <!-- FORMULARIO DE PROPIEDADES -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-home"></i> 
                    <?= $modo == 'editar' ? 'Editar Propiedad' : 'Nueva Propiedad' ?>
                </h3>
            </div>
            <div class="card-body">
                <form action="crudpropiedades.php" method="post" enctype="multipart/form-data">
                    
                    <!-- FILA 1: Título y Tipo -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="titulo" class="form-label">Título *</label>
                            <input type="text" name="titulopropiedad" id="titulo" class="form-control" 
                                   value="<?= $datosprop ? htmlspecialchars($datosprop['titulopropiedad']) : '' ?>" 
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label for="tipo" class="form-label">Tipo de Propiedad *</label>
                            <select name="idtipo_propiedad" id="tipo" class="form-select" required>
                                <option value="">Seleccionar tipo</option>
                                <?php
                                try {
                                    $conexion = conectar();
                                    $sql_tipos = "SELECT * FROM tipo_propiedad WHERE estado = 1";
                                    $res_tipos = mysqli_query($conexion, $sql_tipos);
                                    
                                    if ($res_tipos) {
                                        while ($tipo = mysqli_fetch_assoc($res_tipos)) {
                                            $selected = ($datosprop && $datosprop['idtipo_propiedad'] == $tipo['idtipo_propiedad']) ? 'selected' : '';
                                            echo "<option value='" . $tipo['idtipo_propiedad'] . "' $selected>" . htmlspecialchars($tipo['tipo']) . "</option>";
                                        }
                                    }
                                } catch (Exception $e) {
                                    echo "<option value=''>Error al cargar tipos</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- FILA 2: Sector y Precio -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="sector" class="form-label">Sector *</label>
                            <select name="idsector" id="sector" class="form-select" required>
                                <option value="">Seleccionar sector</option>
                                <?php
                                try {
                                    $conexion = conectar();
                                    $sql_sectores = "SELECT s.idsectores, s.sector, c.comuna FROM sectores s 
                                                   JOIN comunas c ON s.idcomunas = c.idcomunas 
                                                   WHERE s.estado = 1";
                                    $res_sectores = mysqli_query($conexion, $sql_sectores);
                                    
                                    if ($res_sectores) {
                                        while ($sector = mysqli_fetch_assoc($res_sectores)) {
                                            $selected = ($datosprop && $datosprop['idsector'] == $sector['idsectores']) ? 'selected' : '';
                                            echo "<option value='" . $sector['idsectores'] . "' $selected>" . htmlspecialchars($sector['sector']) . ", " . htmlspecialchars($sector['comuna']) . "</option>";
                                        }
                                    }
                                } catch (Exception $e) {
                                    echo "<option value=''>Error al cargar sectores</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="precio" class="form-label">Precio (CLP) *</label>
                            <input type="number" name="precio_pesos" id="precio" class="form-control" 
                                   value="<?= $datosprop ? $datosprop['precio_pesos'] : '' ?>" 
                                   required min="0">
                        </div>
                    </div>

                    <!-- FILA 3: Descripción -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="3"><?= $datosprop ? htmlspecialchars($datosprop['descripcion']) : '' ?></textarea>
                        </div>
                    </div>

                    <!-- FILA 4: Operación, Dormitorios, Baños -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="operacion" class="form-label">Operación</label>
                            <select name="tipo_operacion" id="operacion" class="form-select">
                                <option value="Venta" <?= ($datosprop && $datosprop['tipo_operacion'] == 'Venta') ? 'selected' : '' ?>>Venta</option>
                                <option value="Arriendo" <?= ($datosprop && $datosprop['tipo_operacion'] == 'Arriendo') ? 'selected' : '' ?>>Arriendo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="dormitorios" class="form-label">Dormitorios</label>
                            <input type="number" name="cant_domitorios" id="dormitorios" class="form-control" 
                                   value="<?= $datosprop ? $datosprop['cant_domitorios'] : 0 ?>" 
                                   min="0">
                        </div>
                        <div class="col-md-4">
                            <label for="banos" class="form-label">Baños</label>
                            <input type="number" name="cant_banos" id="banos" class="form-control" 
                                   value="<?= $datosprop ? $datosprop['cant_banos'] : 0 ?>" 
                                   min="0">
                        </div>
                    </div>

                    <!-- FILA 5: Áreas y Estado -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="area_total" class="form-label">Área Total (m²)</label>
                            <input type="number" name="area_total" id="area_total" class="form-control" 
                                   value="<?= $datosprop ? $datosprop['area_total'] : '' ?>" 
                                   min="0" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label for="area_construida" class="form-label">Área Construida (m²)</label>
                            <input type="number" name="area_construida" id="area_construida" class="form-control" 
                                   value="<?= $datosprop ? $datosprop['area_construida'] : '' ?>" 
                                   min="0" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label for="precio_uf" class="form-label">Precio UF</label>
                            <input type="number" name="precio_uf" id="precio_uf" class="form-control" 
                                   value="<?= $datosprop ? $datosprop['precio_uf'] : '' ?>" 
                                   min="0" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="1" <?= ($datosprop && $datosprop['estado'] == 1) ? 'selected' : '' ?>>Disponible</option>
                                <option value="0" <?= ($datosprop && $datosprop['estado'] == 0) ? 'selected' : '' ?>>No disponible</option>
                            </select>
                        </div>
                    </div>

                    <!-- FILA 6: Fotos -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="fotos" class="form-label">Subir Fotos</label>
                            <input type="file" name="frm_foto[]" id="fotos" class="form-control" 
                                   multiple accept="image/*">
                            <small class="form-text text-muted">Puedes seleccionar múltiples imágenes</small>
                        </div>
                    </div>

                    <!-- BOTONES -->
                    <div class="text-center">
                        <?php if ($modo == 'nuevo'): ?>
                            <button type="submit" name="action" value="insertar" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus"></i> INGRESAR PROPIEDAD
                            </button>
                        <?php else: ?>
                            <button type="submit" name="action" value="modificar" class="btn btn-success btn-lg">
                                <i class="fas fa-edit"></i> MODIFICAR PROPIEDAD
                            </button>
                            <button type="submit" name="action" value="eliminar" class="btn btn-danger btn-lg" 
                                    onclick="return confirm('¿Está seguro de que desea eliminar esta propiedad?')">
                                <i class="fas fa-trash"></i> ELIMINAR PROPIEDAD
                            </button>
                        <?php endif; ?>
                        
                        <a href="registro_propiedades.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> CANCELAR
                        </a>
                    </div>

                    <!-- CAMPOS OCULTOS -->
                    <input type="hidden" name="idoculto" value="<?= $datosprop ? $datosprop['idpropiedades'] : '' ?>">
                </form>
            </div>
        </div>

        <!-- LISTADO DE PROPIEDADES -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">
                    <i class="fas fa-list"></i> 
                    Listado de Propiedades (Total: <?= contarprop() ?>)
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Sector</th>
                                <th>Precio (CLP)</th>
                                <th>Dormitorios</th>
                                <th>Baños</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $conexion = conectar();
                                $sql = "SELECT p.*, tp.tipo, s.sector, c.comuna 
                                       FROM propiedades p 
                                       LEFT JOIN tipo_propiedad tp ON p.idtipo_propiedad = tp.idtipo_propiedad
                                       LEFT JOIN sectores s ON p.idsector = s.idsectores
                                       LEFT JOIN comunas c ON s.idcomunas = c.idcomunas
                                       ORDER BY p.idpropiedades DESC";
                                $result = mysqli_query($conexion, $sql);
                                
                                if ($result && mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . $row['idpropiedades'] . "</td>";
                                        echo "<td>" . htmlspecialchars($row['titulopropiedad']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['tipo'] ?? 'N/A') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['sector'] ?? 'N/A') . "</td>";
                                        echo "<td>$" . number_format($row['precio_pesos'], 0, ',', '.') . "</td>";
                                        echo "<td>" . $row['cant_domitorios'] . "</td>";
                                        echo "<td>" . $row['cant_banos'] . "</td>";
                                        echo "<td>";
                                        if ($row['estado'] == 1) {
                                            echo "<span class='badge bg-success'>Disponible</span>";
                                        } else {
                                            echo "<span class='badge bg-secondary'>No disponible</span>";
                                        }
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<a href='registro_propiedades.php?idprop=" . $row['idpropiedades'] . "' class='btn btn-sm btn-primary me-1'>";
                                        echo "<i class='fas fa-edit'></i> Editar";
                                        echo "</a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-center text-muted'>No hay propiedades registradas</td></tr>";
                                }
                            } catch (Exception $e) {
                                echo "<tr><td colspan='9' class='text-center text-danger'>Error al cargar propiedades: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
