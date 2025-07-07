<?php
// Incluye el archivo de configuración que contiene la función conectar() para la conexión a la base de datos
include("setup/config.php");

// Función que cuenta el total de propiedades registradas en la base de datos
function contarprop() {
    // Ejecuta una consulta SQL para contar todas las filas de la tabla 'propiedades'
    $res = mysqli_query(conectar(), "SELECT COUNT(*) as total FROM propiedades");
    // Obtiene el resultado como un array asociativo
    $row = mysqli_fetch_assoc($res);
    // Retorna el número total de propiedades encontradas
    return $row['total'];
}
?>

<!-- Inicio del contenedor visual principal con estilo de tarjeta -->
<div class="card">
    
    <!-- Cuerpo de la tarjeta que contendrá la tabla con datos -->
    <div class="card-body">
        <!-- Tabla HTML que muestra la lista de propiedades -->
        <table class="table table-hover">
            <thead>
                <tr>
                    <!-- Encabezados de columnas para cada atributo de la propiedad -->
                    <th>N°</th> <!-- Número secuencial de fila -->
                    <th>Foto</th> <!-- Imagen principal de la propiedad -->
                    <th>Título</th> <!-- Título o nombre de la propiedad -->
                    <th>Tipo</th> <!-- Tipo de propiedad (casa, departamento, etc.) -->
                    <th>Ubicación</th> <!-- Sector y comuna donde se encuentra -->
                    <th>Dormitorios</th> <!-- Cantidad de dormitorios -->
                    <th>Baños</th> <!-- Cantidad de baños -->
                    <th>Área (m²)</th> <!-- Área total en metros cuadrados -->
                    <th>Precio ($)</th> <!-- Precio en pesos chilenos -->
                    <th>Precio (UF)</th> <!-- Precio en unidades de fomento -->
                    <th>Estado</th> <!-- Disponibilidad de la propiedad -->
                    <th>Acciones</th> <!-- Opciones para editar o eliminar -->
                </tr>
            </thead>
            <tbody>
                <?php
                // Inicializa un contador para numerar las filas en la tabla
                $num = 1;

                // Obtiene el texto de búsqueda enviado vía POST para filtrar propiedades
                $txt = isset($_POST['txt']) ? $_POST['txt'] : '';
                
                // Consulta SQL que busca propiedades que contengan el texto de búsqueda en varios campos relacionados
                $sql = "SELECT p.*, g.foto, tp.tipo as tipo_prop, s.sector, c.comuna 
                        FROM propiedades p
                        LEFT JOIN galeria g ON p.idpropiedades = g.idpropiedades AND g.principal = 1
                        JOIN tipo_propiedad tp ON p.idtipo_propiedad = tp.idtipo_propiedad
                        JOIN sectores s ON p.idsector = s.idsectores
                        JOIN comunas c ON s.idcomunas = c.idcomunas
                        WHERE p.titulopropiedad LIKE '%$txt%' 
                           OR p.descripcion LIKE '%$txt%'
                           OR tp.tipo LIKE '%$txt%'
                           OR s.sector LIKE '%$txt%'
                           OR c.comuna LIKE '%$txt%'
                           OR p.precio_pesos LIKE '%$txt%'
                           OR p.precio_uf LIKE '%$txt%'
                        ORDER BY p.idpropiedades DESC";
                
                // Ejecuta la consulta en la base de datos
                $result = mysqli_query(conectar(), $sql);

                // Ciclo para recorrer cada fila devuelta por la consulta
                while($prop = mysqli_fetch_array($result)) {
                ?>
                    <tr>
                        <!-- Número secuencial de la propiedad -->
                        <td><?php echo $num; ?></td>

                        <!-- Celda que muestra la imagen principal de la propiedad -->
                        <td>
                            <?php if($prop['foto'] == ''): ?>
                                <!-- Si no hay imagen, muestra una imagen predeterminada -->
                                <img src="propiedades/comodin_casa.png" width="60" style="object-fit:cover; border-radius:5px;">
                            <?php else: ?>
                                <!-- Si hay imagen, muestra la imagen principal asociada -->
                                <img src="propiedades/<?php echo $prop['foto']; ?>" width="60" style="object-fit:cover; border-radius:5px;">
                            <?php endif; ?>
                        </td>

                        <!-- Título de la propiedad, protegido contra inyección HTML -->
                        <td><?php echo htmlspecialchars($prop['titulopropiedad']); ?></td>

                        <!-- Tipo de propiedad, obtenido de la tabla relacionada -->
                        <td><?php echo $prop['tipo_prop']; ?></td>

                        <!-- Ubicación: concatenación de sector y comuna -->
                        <td><?php echo $prop['sector'].', '.$prop['comuna']; ?></td>

                        <!-- Número de dormitorios -->
                        <td><?php echo $prop['cant_domitorios']; ?></td>

                        <!-- Número de baños -->
                        <td><?php echo $prop['cant_banos']; ?></td>

                        <!-- Área total en metros cuadrados -->
                        <td><?php echo $prop['area_total']; ?></td>

                        <!-- Precio en pesos, formateado con separadores de miles -->
                        <td>$<?php echo number_format($prop['precio_pesos'], 0, ',', '.'); ?></td>

                        <!-- Precio en UF, formateado con 2 decimales -->
                        <td><?php echo number_format($prop['precio_uf'], 2, ',', '.'); ?> UF</td>

                        <!-- Estado de disponibilidad: muestra un icono según si está disponible o no -->
                        <td>
                            <?php if($prop['estado'] == 1): ?>
                                <img src="IMG/check.png" width="25px" title="Disponible">
                            <?php else: ?>
                                <img src="IMG/x.png" width="25px" title="No disponible">
                            <?php endif; ?>
                        </td>

                        <!-- Acciones posibles para la propiedad: editar y eliminar -->
                        <td>
                            <!-- Enlace para editar la propiedad, enviando el id como parámetro GET -->
                            <a href="registro_propiedades.php?idprop=<?php echo $prop['idpropiedades']; ?>">
                                <img src="IMG/actualizar.png" width="25px" title="Editar">
                            </a>
                            | 
                            <!-- Enlace para eliminar, llama a función JavaScript para confirmar -->
                            <a href="#" onclick="confirmarEliminacion(<?php echo $prop['idpropiedades']; ?>); return false;">
                                <img src="IMG/basura.png" width="25px" title="Eliminar">
                            </a>
                        </td>
                    </tr>
                <?php
                    // Incrementa el contador para la siguiente fila
                    $num++;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Función JavaScript para confirmar eliminación de una propiedad
function confirmarEliminacion(id) {
    // Muestra un cuadro de diálogo para confirmar la acción
    if(confirm("¿Está seguro que desea eliminar esta propiedad?")) {
        // Si se confirma, redirige a la página que realiza la eliminación pasando el id por URL
        window.location = "crudpropiedades.php?idprop=" + id;
    }
}
</script>

