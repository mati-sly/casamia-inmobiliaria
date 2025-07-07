<?php
// Configurar codificación y tipo de respuesta
header('Content-Type: application/json; charset=UTF-8');

// Conexión a la base de datos
include("setup/config.php");
$conexion = conectar();

// Configurar charset
$conexion->set_charset("utf8");

// Verificar que se recibió una acción
if (!isset($_GET['action'])) {
    echo json_encode(['error' => 'No se especificó acción']);
    exit;
}

$action = $_GET['action'];

switch ($action) {
    case 'get_comunas':
        if (isset($_GET['provincia_id']) && !empty($_GET['provincia_id'])) {
            $provincia_id = intval($_GET['provincia_id']);
            
            $sql = "SELECT idcomunas, comuna 
                    FROM comunas 
                    WHERE idprovincias = ? AND estado = 1 
                    ORDER BY comuna ASC";
            
            $stmt = $conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('i', $provincia_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                $comunas = [];
                while ($fila = $resultado->fetch_assoc()) {
                    $comunas[] = $fila;
                }
                
                echo json_encode($comunas);
                $stmt->close();
            } else {
                echo json_encode(['error' => 'Error en la consulta de comunas']);
            }
        } else {
            echo json_encode(['error' => 'ID de provincia no válido']);
        }
        break;
        
    case 'get_sectores':
        if (isset($_GET['comuna_id']) && !empty($_GET['comuna_id'])) {
            $comuna_id = intval($_GET['comuna_id']);
            
            $sql = "SELECT idsectores, sector 
                    FROM sectores 
                    WHERE idcomunas = ? AND estado = 1 
                    ORDER BY sector ASC";
            
            $stmt = $conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('i', $comuna_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                $sectores = [];
                while ($fila = $resultado->fetch_assoc()) {
                    $sectores[] = $fila;
                }
                
                echo json_encode($sectores);
                $stmt->close();
            } else {
                echo json_encode(['error' => 'Error en la consulta de sectores']);
            }
        } else {
            echo json_encode(['error' => 'ID de comuna no válido']);
        }
        break;
        
    case 'get_provincias':
        if (isset($_GET['region_id']) && !empty($_GET['region_id'])) {
            $region_id = intval($_GET['region_id']);
            
            $sql = "SELECT idprovincias, provincia 
                    FROM provincias 
                    WHERE idregiones = ? AND estado = 1 
                    ORDER BY provincia ASC";
            
            $stmt = $conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('i', $region_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                $provincias = [];
                while ($fila = $resultado->fetch_assoc()) {
                    $provincias[] = $fila;
                }
                
                echo json_encode($provincias);
                $stmt->close();
            } else {
                echo json_encode(['error' => 'Error en la consulta de provincias']);
            }
        } else {
            echo json_encode(['error' => 'ID de región no válido']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

// Cerrar conexión
$conexion->close();
?>