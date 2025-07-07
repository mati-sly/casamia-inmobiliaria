<?php
// Configurar codificación UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

// Conexión a la base de datos
include("setup/config.php");
$conexion = conectar();
$conexion->set_charset("utf8");

// Procesar búsqueda si se envió el formulario
$where_conditions = [];
$params = [];

if ($_GET) {
    if (!empty($_GET['region'])) {
        $where_conditions[] = "r.idregiones = ?";
        $params[] = $_GET['region'];
    }
    if (!empty($_GET['provincia'])) {
        $where_conditions[] = "pr.idprovincias = ?";
        $params[] = $_GET['provincia'];
    }
    if (!empty($_GET['comuna'])) {
        $where_conditions[] = "c.idcomunas = ?";
        $params[] = $_GET['comuna'];
    }
    if (!empty($_GET['sector'])) {
        $where_conditions[] = "s.idsectores = ?";
        $params[] = $_GET['sector'];
    }
    if (!empty($_GET['tipo_propiedad'])) {
        $where_conditions[] = "tp.idtipo_propiedad = ?";
        $params[] = $_GET['tipo_propiedad'];
    }
}

// Consulta para obtener propiedades
$sql = "SELECT 
            p.idpropiedades,
            p.titulopropiedad,
            p.precio_pesos,
            p.cant_domitorios,
            p.cant_banos,
            p.area_total,
            g.foto
        FROM propiedades p
        JOIN galeria g ON p.idpropiedades = g.idpropiedades
        JOIN sectores s ON p.idsector = s.idsectores
        JOIN comunas c ON s.idcomunas = c.idcomunas  
        JOIN provincias pr ON c.idprovincias = pr.idprovincias
        JOIN regiones r ON pr.idregiones = r.idregiones
        JOIN tipo_propiedad tp ON p.idtipo_propiedad = tp.idtipo_propiedad
        WHERE g.principal = 1 AND p.estado = 1 AND g.estado = 1";

if (!empty($where_conditions)) {
    $sql .= " AND " . implode(" AND ", $where_conditions);
}

$sql .= " LIMIT 20";

// Ejecutar consulta con filtros
if (!empty($params)) {
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $types = str_repeat('i', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $resultado = $stmt->get_result();
    }
} else {
    $resultado = $conexion->query($sql);
}

// Cargar datos para los selects
$regiones = $conexion->query("SELECT * FROM regiones WHERE estado = 1");
$provincias = $conexion->query("SELECT * FROM provincias WHERE estado = 1");
$tipos_propiedad = $conexion->query("SELECT * FROM tipo_propiedad WHERE estado = 1");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>CasaMía Inmobiliaria</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/index.css" />
    <link rel="icon" type="image/x-icon" href="IMG/favicon.ico" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="cabecera-con-buscador">
        <div class="overlay-cabecera"></div>
        <div class="contenido-cabecera">
            <div class="barra-superior">
                <div class="logo">
                    <img src="IMG/logo.png" alt="Logo CasaMía">
                </div>
                <div class="acceso-usuario">
                    <!-- 🆕 BOTÓN CON FUNCIÓN JAVASCRIPT PARA REDIRECCIÓN DINÁMICA -->
                    <a href="javascript:void(0);" onclick="irALogin()" class="boton-login">Iniciar Sesión</a>
                </div>
            </div>

            <div class="buscador-header">
                <h1>Tu lugar en el mundo</h1>
                <p>Explora las mejores opciones en la Región de Coquimbo</p>

                <form class="formulario-busqueda" method="GET" action="">
                    <div class="fila-busqueda-unica">
                        <div class="grupo-campo">
                            <select id="region" name="region">
                                <option value="">Región</option>
                                <?php while($region = $regiones->fetch_assoc()): ?>
                                    <option value="<?php echo $region['idregiones']; ?>" 
                                            <?php echo (isset($_GET['region']) && $_GET['region'] == $region['idregiones']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($region['region']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="grupo-campo">
                            <select id="tipo-propiedad" name="tipo_propiedad">
                                <option value="">Tipo de propiedad</option>
                                <?php while($tipo = $tipos_propiedad->fetch_assoc()): ?>
                                    <option value="<?php echo $tipo['idtipo_propiedad']; ?>"
                                            <?php echo (isset($_GET['tipo_propiedad']) && $_GET['tipo_propiedad'] == $tipo['idtipo_propiedad']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tipo['tipo']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="grupo-campo">
                            <select id="provincia" name="provincia">
                                <option value="">Seleccione Provincia</option>
                                <?php while($provincia = $provincias->fetch_assoc()): ?>
                                    <option value="<?php echo $provincia['idprovincias']; ?>"
                                            <?php echo (isset($_GET['provincia']) && $_GET['provincia'] == $provincia['idprovincias']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($provincia['provincia']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="grupo-campo">
                            <select id="comuna" name="comuna">
                                <option value="">Seleccione Comuna</option>
                            </select>
                        </div>
                        <div class="grupo-campo">
                            <select id="sector" name="sector">
                                <option value="">Seleccione Sector</option>
                            </select>
                        </div>
                        <button type="submit" class="boton-buscar">Buscar</button>
                    </div>
                </form>
            </div>
        </div>
    </header>

    <main class="contenedor-principal">
        <h2 class="titulo-seccion">Propiedades Destacadas</h2>
        <div class="grid-propiedades">
            <?php 
            if ($resultado && $resultado->num_rows > 0) {
                while($fila = $resultado->fetch_assoc()) {
                    echo '<article class="tarjeta-propiedad">';
                    echo '<img src="propiedades/' . htmlspecialchars($fila['foto']) . '" alt="' . htmlspecialchars($fila['titulopropiedad']) . '">';
                    echo '<div class="info-propiedad">';
                    echo '<span class="codigo-propiedad">COD: ' . htmlspecialchars($fila['idpropiedades']) . '</span>';
                    echo '<h3>' . htmlspecialchars($fila['titulopropiedad']) . '</h3>';
                    echo '<p class="precio">$' . number_format($fila['precio_pesos'], 0, ',', '.') . '</p>';
                    echo '<div class="caracteristicas">';
                    echo '<span>' . htmlspecialchars($fila['cant_domitorios']) . ' Dorm.</span>';
                    echo '<span>' . htmlspecialchars($fila['cant_banos']) . ' Baños</span>';
                    echo '<span>' . htmlspecialchars($fila['area_total']) . ' m²</span>';
                    echo '</div>';
                    echo '<a href="vermasprop.php?idpro=' . htmlspecialchars($fila['idpropiedades']) . '" class="boton-ver-mas">Ver más</a>';
                    echo '</div>';
                    echo '</article>';
                }
            } else {
                echo '<div class="sin-propiedades">';
                echo '<p>No se encontraron propiedades en este momento.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </main>

    <footer class="pie-pagina">
        <div class="contenedor-pie">
            <p>&copy; 2025 CasaMía Inmobiliaria. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
    // 🆕 FUNCIÓN PARA REDIRECCIÓN DINÁMICA AL LOGIN REACT (FUNCIONA CON 2 IPs)
    function irALogin() {
        const currentHost = window.location.hostname;
        let loginURL;
        
        console.log('🔗 Host detectado:', currentHost);
        
        // 🎯 DETECCIÓN AUTOMÁTICA PARA AMBAS IPS
        if (currentHost === '192.168.1.17') {
            loginURL = 'http://192.168.1.17:3000/login';
        } else if (currentHost === '201.214.53.231') {
            loginURL = 'http://201.214.53.231:3000/login';
        } else {
            // Para cualquier otra IP (incluye IP pública nueva)
            loginURL = `http://${currentHost}:3000/login`;
        }
        
        console.log('🚀 Redirigiendo a login React:', loginURL);
        
        // 📝 GUARDAR URL DE ORIGEN PARA FLUJO BIDIRECCIONAL
        localStorage.setItem('origen_url', window.location.href);
        
        // Redirigir al login React
        window.location.href = loginURL;
    }

    $(document).ready(function() {
        console.log('🌐 Index.php cargado desde:', window.location.hostname);
        console.log('✅ Caracteres UTF-8 corregidos');
        
        // Código para filtros de búsqueda (comunas, sectores, etc.)
        var provinciaSeleccionada = $('#provincia').val();
        var comunaSeleccionada = '<?php echo isset($_GET['comuna']) ? $_GET['comuna'] : ''; ?>';
        var sectorSeleccionado = '<?php echo isset($_GET['sector']) ? $_GET['sector'] : ''; ?>';
        
        if (provinciaSeleccionada) {
            cargarComunas(provinciaSeleccionada, comunaSeleccionada);
        }
        
        if (comunaSeleccionada) {
            cargarSectores(comunaSeleccionada, sectorSeleccionado);
        }
        
        $('#provincia').change(function() {
            var provinciaId = $(this).val();
            $('#comuna').html('<option value="">Seleccione Comuna</option>');
            $('#sector').html('<option value="">Seleccione Sector</option>');
            
            if (provinciaId) {
                cargarComunas(provinciaId);
            }
        });
        
        $('#comuna').change(function() {
            var comunaId = $(this).val();
            $('#sector').html('<option value="">Seleccione Sector</option>');
            
            if (comunaId) {
                cargarSectores(comunaId);
            }
        });
        
        function cargarComunas(provinciaId, comunaSeleccionada = '') {
            $.ajax({
                url: 'indexfiltro.php',
                type: 'GET',
                data: {
                    action: 'get_comunas',
                    provincia_id: provinciaId
                },
                dataType: 'json',
                success: function(data) {
                    var opciones = '<option value="">Seleccione Comuna</option>';
                    $.each(data, function(index, comuna) {
                        var selected = (comunaSeleccionada == comuna.idcomunas) ? 'selected' : '';
                        opciones += '<option value="' + comuna.idcomunas + '" ' + selected + '>' + comuna.comuna + '</option>';
                    });
                    $('#comuna').html(opciones);
                },
                error: function() {
                    console.error('Error al cargar comunas');
                }
            });
        }
        
        function cargarSectores(comunaId, sectorSeleccionado = '') {
            $.ajax({
                url: 'indexfiltro.php',
                type: 'GET',
                data: {
                    action: 'get_sectores',
                    comuna_id: comunaId
                },
                dataType: 'json',
                success: function(data) {
                    var opciones = '<option value="">Seleccione Sector</option>';
                    $.each(data, function(index, sector) {
                        var selected = (sectorSeleccionado == sector.idsectores) ? 'selected' : '';
                        opciones += '<option value="' + sector.idsectores + '" ' + selected + '>' + sector.sector + '</option>';
                    });
                    $('#sector').html(opciones);
                },
                error: function() {
                    console.error('Error al cargar sectores');
                }
            });
        }
    });
    </script>
</body>
</html>