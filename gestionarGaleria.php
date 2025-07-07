<?php
session_start();

// Para testing - Usuario hardcodeado (Noa - propietario)
$_SESSION['usuario_id'] = 15;
$_SESSION['tipoUsuario'] = 'propietario';

// Verificar autenticación y que sea propietario
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipoUsuario'] != 'propietario') {
    header('Location: login.php');
    exit;
}

// Verificar que se reciba el ID de la propiedad
if (!isset($_GET['idprop']) || empty($_GET['idprop'])) {
    echo "Error: ID de propiedad no válido";
    exit;
}

include("setup/config.php");
$conexion = conectar();
$conexion->set_charset("utf8");

$usuario_id = $_SESSION['usuario_id'];
$propiedad_id = $_GET['idprop'];
$mensaje = '';

// Verificar que la propiedad pertenece al usuario
$verificar_propiedad = $conexion->prepare("SELECT titulopropiedad FROM propiedades WHERE idpropiedades = ? AND id_usuario = ?");
$verificar_propiedad->bind_param("ii", $propiedad_id, $usuario_id);
$verificar_propiedad->execute();
$resultado_verificacion = $verificar_propiedad->get_result();

if ($resultado_verificacion->num_rows == 0) {
    echo "Error: No puedes gestionar imágenes de propiedades que no te pertenecen";
    exit;
}

$datos_propiedad = $resultado_verificacion->fetch_assoc();

// Procesar subida de imágenes
if ($_POST && isset($_POST['accion']) && $_POST['accion'] == 'subir') {
    // Verificar cuántas imágenes ya tiene la propiedad
    $contar_imagenes = $conexion->prepare("SELECT COUNT(*) as total FROM galeria WHERE idpropiedades = ?");
    $contar_imagenes->bind_param("i", $propiedad_id);
    $contar_imagenes->execute();
    $resultado_contar = $contar_imagenes->get_result();
    $total_imagenes = $resultado_contar->fetch_assoc()['total'];

    if ($total_imagenes >= 10) {
        $mensaje = "Error: No puedes subir más de 10 imágenes por propiedad";
    } else {
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $archivo = $_FILES['imagen'];
            $nombre_archivo = $archivo['name'];
            $tipo_archivo = $archivo['type'];
            $tamano_archivo = $archivo['size'];
            $archivo_temporal = $archivo['tmp_name'];

            // Validar tipo de archivo
            $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array($tipo_archivo, $tipos_permitidos)) {
                $mensaje = "Error: Solo se permiten archivos JPG, PNG y WEBP";
            } else if ($tamano_archivo > 5000000) { // 5MB máximo
                $mensaje = "Error: El archivo es demasiado grande (máximo 5MB)";
            } else {
                // Generar nombre único para el archivo
                $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
                $nuevo_nombre = "prop_" . $propiedad_id . "_" . time() . "." . $extension;
                $ruta_destino = "propiedades/" . $nuevo_nombre;

                // Crear directorio si no existe
                if (!file_exists("propiedades/")) {
                    mkdir("propiedades/", 0777, true);
                }

                if (move_uploaded_file($archivo_temporal, $ruta_destino)) {
                    // Determinar si es la primera imagen (será principal por defecto)
                    $es_principal = ($total_imagenes == 0) ? 1 : 0;

                    // Insertar en base de datos
                    $insertar = $conexion->prepare("INSERT INTO galeria (foto, estado, principal, idpropiedades) VALUES (?, 1, ?, ?)");
                    $insertar->bind_param("sii", $nuevo_nombre, $es_principal, $propiedad_id);

                    if ($insertar->execute()) {
                        $mensaje = "Imagen subida exitosamente";
                    } else {
                        $mensaje = "Error al guardar la imagen en la base de datos";
                        unlink($ruta_destino); // Eliminar archivo si falla la BD
                    }
                } else {
                    $mensaje = "Error al subir el archivo";
                }
            }
        } else {
            $mensaje = "Error: No se seleccionó ningún archivo";
        }
    }
}

// Procesar acciones sobre imágenes existentes
if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    $id_imagen = $_GET['idimg'] ?? 0;

    switch ($accion) {
        case 'principal':
            // Quitar principal a todas las imágenes de esta propiedad
            $quitar_principal = $conexion->prepare("UPDATE galeria SET principal = 0 WHERE idpropiedades = ?");
            $quitar_principal->bind_param("i", $propiedad_id);
            $quitar_principal->execute();

            // Marcar la seleccionada como principal
            $marcar_principal = $conexion->prepare("UPDATE galeria SET principal = 1 WHERE idgaleria = ? AND idpropiedades = ?");
            $marcar_principal->bind_param("ii", $id_imagen, $propiedad_id);
            if ($marcar_principal->execute()) {
                $mensaje = "Imagen marcada como principal";
            }
            break;

        case 'eliminar':
            // Obtener nombre del archivo antes de eliminar
            $obtener_archivo = $conexion->prepare("SELECT foto FROM galeria WHERE idgaleria = ? AND idpropiedades = ?");
            $obtener_archivo->bind_param("ii", $id_imagen, $propiedad_id);
            $obtener_archivo->execute();
            $resultado_archivo = $obtener_archivo->get_result();
            
            if ($resultado_archivo->num_rows > 0) {
                $datos_archivo = $resultado_archivo->fetch_assoc();
                $nombre_archivo = $datos_archivo['foto'];

                // Eliminar de base de datos
                $eliminar_bd = $conexion->prepare("DELETE FROM galeria WHERE idgaleria = ? AND idpropiedades = ?");
                $eliminar_bd->bind_param("ii", $id_imagen, $propiedad_id);
                
                if ($eliminar_bd->execute()) {
                    // Eliminar archivo físico
                    if (file_exists("propiedades/" . $nombre_archivo)) {
                        unlink("propiedades/" . $nombre_archivo);
                    }
                    $mensaje = "Imagen eliminada exitosamente";
                } else {
                    $mensaje = "Error al eliminar la imagen";
                }
            }
            break;

        case 'activar':
            $activar = $conexion->prepare("UPDATE galeria SET estado = 1 WHERE idgaleria = ? AND idpropiedades = ?");
            $activar->bind_param("ii", $id_imagen, $propiedad_id);
            if ($activar->execute()) {
                $mensaje = "Imagen activada";
            }
            break;

        case 'desactivar':
            $desactivar = $conexion->prepare("UPDATE galeria SET estado = 0 WHERE idgaleria = ? AND idpropiedades = ?");
            $desactivar->bind_param("ii", $id_imagen, $propiedad_id);
            if ($desactivar->execute()) {
                $mensaje = "Imagen desactivada";
            }
            break;
    }
}

// Obtener todas las imágenes de la propiedad
$obtener_imagenes = $conexion->prepare("SELECT * FROM galeria WHERE idpropiedades = ? ORDER BY principal DESC, idgaleria ASC");
$obtener_imagenes->bind_param("i", $propiedad_id);
$obtener_imagenes->execute();
$imagenes = $obtener_imagenes->get_result();

// Contar imágenes
$total_imagenes_actual = $imagenes->num_rows;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestionar Galería - <?php echo htmlspecialchars($datos_propiedad['titulopropiedad']); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="IMG/favicon.ico" />
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="css/usuarioPropiedades.css" rel="stylesheet">
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/sweetalert2.all.min.js"></script>
</head>
<body>
    <!-- Header de navegación -->
    <div class="container-fluid mb-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            📸 Gestionar Galería: <?php echo htmlspecialchars($datos_propiedad['titulopropiedad']); ?>
                        </div>
                        <a href="usuarioPropiedades.php" class="btn btn-secondary">
                            ← Volver a Mis Propiedades
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario para subir nuevas imágenes -->
    <div class="container-fluid mb-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        ➕ Subir Nueva Imagen (<?php echo $total_imagenes_actual; ?>/10)
                    </div>
                    <div class="card-body">
                        <?php if ($total_imagenes_actual < 10): ?>
                            <form action="" method="post" enctype="multipart/form-data" id="formSubirImagen">
                                <div class="row align-items-end">
                                    <div class="col-md-6">
                                        <label for="imagen" class="form-label-sm">Seleccionar Imagen:</label>
                                        <input type="file" class="form-control form-control-sm" id="imagen" name="imagen" 
                                               accept=".jpg,.jpeg,.png,.webp" required>
                                        <small class="form-text-sm">Formatos permitidos: JPG, PNG, WEBP (máximo 5MB)</small>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-success">
                                            📤 Subir Imagen
                                        </button>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="form-text-sm">
                                            <?php if ($total_imagenes_actual == 0): ?>
                                                La primera imagen será la principal automáticamente.
                                            <?php else: ?>
                                                Puedes subir <?php echo (10 - $total_imagenes_actual); ?> imágenes más.
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                <input type="hidden" name="accion" value="subir">
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                ⚠️ Has alcanzado el límite máximo de 10 imágenes por propiedad.
                                Elimina alguna imagen existente para subir una nueva.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Galería de imágenes -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        🖼️ Galería de Imágenes
                    </div>
                    <div class="card-body">
                        <?php if ($total_imagenes_actual == 0): ?>
                            <div class="text-center py-5">
                                <h4>📷 No hay imágenes</h4>
                                <p>Sube la primera imagen de tu propiedad usando el formulario de arriba.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php while($imagen = $imagenes->fetch_assoc()): ?>
                                <div class="col-md-4 col-lg-3 mb-4">
                                    <div class="card imagen-card">
                                        <!-- Imagen -->
                                        <div class="position-relative">
                                            <img src="propiedades/<?php echo $imagen['foto']; ?>" 
                                                 class="card-img-top img-galeria" 
                                                 alt="Imagen de propiedad"
                                                 style="height: 200px; object-fit: cover;">
                                            
                                            <!-- Badges de estado -->
                                            <div class="position-absolute top-0 start-0 m-2">
                                                <?php if ($imagen['principal'] == 1): ?>
                                                    <span class="badge bg-warning">⭐ PRINCIPAL</span>
                                                <?php endif; ?>
                                                <?php if ($imagen['estado'] == 0): ?>
                                                    <span class="badge bg-secondary">❌ INACTIVA</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">✅ ACTIVA</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Botones de acción -->
                                        <div class="card-body p-2">
                                            <div class="btn-group w-100" role="group">
                                                <?php if ($imagen['principal'] != 1): ?>
                                                    <button onclick="marcarPrincipal(<?php echo $imagen['idgaleria']; ?>)" 
                                                            class="btn btn-warning btn-sm" title="Marcar como principal">
                                                        ⭐ Principal
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($imagen['estado'] == 1): ?>
                                                    <button onclick="desactivarImagen(<?php echo $imagen['idgaleria']; ?>)" 
                                                            class="btn btn-secondary btn-sm" title="Desactivar imagen">
                                                        👁️‍🗨️ Ocultar
                                                    </button>
                                                <?php else: ?>
                                                    <button onclick="activarImagen(<?php echo $imagen['idgaleria']; ?>)" 
                                                            class="btn btn-info btn-sm" title="Activar imagen">
                                                        👁️ Mostrar
                                                    </button>
                                                <?php endif; ?>

                                                <button onclick="eliminarImagen(<?php echo $imagen['idgaleria']; ?>)" 
                                                        class="btn btn-danger btn-sm" title="Eliminar imagen">
                                                    🗑️ Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6>ℹ️ Información sobre la gestión de imágenes:</h6>
                        <ul>
                            <li><strong>⭐ Principal:</strong> La imagen principal es la que se muestra en la página principal y listados.</li>
                            <li><strong>✅ Activa:</strong> Las imágenes activas se muestran a los visitantes.</li>
                            <li><strong>❌ Inactiva:</strong> Las imágenes inactivas están ocultas pero no eliminadas.</li>
                            <li><strong>🗑️ Eliminar:</strong> Elimina permanentemente la imagen del servidor y base de datos.</li>
                            <li><strong>📁 Formatos:</strong> Solo se permiten JPG, PNG y WEBP.</li>
                            <li><strong>📏 Tamaño:</strong> Máximo 5MB por imagen, hasta 10 imágenes por propiedad.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Funciones para gestionar imágenes
        function marcarPrincipal(idImagen) {
            Swal.fire({
                title: '¿Marcar como principal?',
                text: 'Esta imagen se mostrará como principal en todos los listados.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f39c12',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, marcar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `gestionarGaleria.php?idprop=<?php echo $propiedad_id; ?>&accion=principal&idimg=${idImagen}`;
                }
            });
        }

        function activarImagen(idImagen) {
            Swal.fire({
                title: '¿Activar imagen?',
                text: 'La imagen será visible para los visitantes.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, activar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `gestionarGaleria.php?idprop=<?php echo $propiedad_id; ?>&accion=activar&idimg=${idImagen}`;
                }
            });
        }

        function desactivarImagen(idImagen) {
            Swal.fire({
                title: '¿Desactivar imagen?',
                text: 'La imagen se ocultará pero no se eliminará.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#17a2b8',
                confirmButtonText: 'Sí, ocultar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `gestionarGaleria.php?idprop=<?php echo $propiedad_id; ?>&accion=desactivar&idimg=${idImagen}`;
                }
            });
        }

        function eliminarImagen(idImagen) {
            Swal.fire({
                title: '¿Eliminar imagen?',
                text: 'Esta acción no se puede deshacer. La imagen se eliminará permanentemente.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `gestionarGaleria.php?idprop=<?php echo $propiedad_id; ?>&accion=eliminar&idimg=${idImagen}`;
                }
            });
        }

        // Validar archivo antes de enviar
        document.getElementById('formSubirImagen').addEventListener('submit', function(e) {
            const archivo = document.getElementById('imagen').files[0];
            
            if (archivo) {
                // Validar tamaño (5MB = 5242880 bytes)
                if (archivo.size > 5242880) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo muy grande',
                        text: 'El archivo debe ser menor a 5MB'
                    });
                    return;
                }

                // Validar tipo
                const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                if (!tiposPermitidos.includes(archivo.type)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Formato no válido',
                        text: 'Solo se permiten archivos JPG, PNG y WEBP'
                    });
                    return;
                }
            }
        });
    </script>

    <?php if ($mensaje): ?>
    <script>
        Swal.fire({
            icon: '<?php 
                if (strpos($mensaje, "exitosamente") !== false || 
                    strpos($mensaje, "marcada como principal") !== false ||
                    strpos($mensaje, "activada") !== false ||
                    strpos($mensaje, "desactivada") !== false ||
                    strpos($mensaje, "eliminada") !== false) {
                    echo "success";
                } else {
                    echo "error";
                }
            ?>',
            title: '<?php echo $mensaje; ?>',
            timer: 3000,
            showConfirmButton: true
        });
    </script>
    <?php endif; ?>
</body>
</html>