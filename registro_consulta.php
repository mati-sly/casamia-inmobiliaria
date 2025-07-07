<?php
// ✅ CONFIGURACIÓN UTF-8 Y CORS
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: http://192.168.1.17:3000');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Allow-Credentials: true');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include("setup/config.php");
$conexion = conectar();
$conexion->set_charset("utf8");

// Solo procesar si es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validar que se recibieron datos
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos']);
    exit;
}

// Validar campos requeridos
$campos_requeridos = ['rut', 'nombres', 'apellidoPaterno', 'apellidoMaterno', 'usuario', 'clave', 'tipoUsuario'];
foreach ($campos_requeridos as $campo) {
    if (empty($input[$campo])) {
        echo json_encode(['success' => false, 'message' => "El campo $campo es requerido"]);
        exit;
    }
}

try {
    // Verificar si el usuario ya existe (por email)
    $sql_check_email = "SELECT id FROM usuarios WHERE usuario = ?";
    $stmt_check_email = $conexion->prepare($sql_check_email);
    $stmt_check_email->bind_param("s", $input['usuario']);
    $stmt_check_email->execute();
    $result_email = $stmt_check_email->get_result();
    
    if ($result_email->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Este correo electrónico ya está registrado']);
        exit;
    }

    // Verificar si el RUT ya existe
    $sql_check_rut = "SELECT id FROM usuarios WHERE rut = ?";
    $stmt_check_rut = $conexion->prepare($sql_check_rut);
    $stmt_check_rut->bind_param("s", $input['rut']);
    $stmt_check_rut->execute();
    $result_rut = $stmt_check_rut->get_result();
    
    if ($result_rut->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Este RUT ya está registrado']);
        exit;
    }

    // Hashear la contraseña
    $clave_hash = password_hash($input['clave'], PASSWORD_BCRYPT);

    // Preparar datos con valores por defecto
    $estado = $input['estado'] ?? 1;
    $fechaNacimiento = $input['fechaNacimiento'] ?? '1990-01-01';
    $sexo = $input['sexo'] ?? '';
    $cel = $input['cel'] ?? '';
    $foto = null;

    // Insertar nuevo usuario
    $sql = "INSERT INTO usuarios (
        rut, nombres, apellidoPaterno, apellidoMaterno, usuario, clave, 
        estado, tipoUsuario, fechaNacimiento, sexo, cel, foto
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $conexion->error);
    }

    $stmt->bind_param("ssssssssssss",
        $input['rut'],
        $input['nombres'],
        $input['apellidoPaterno'],
        $input['apellidoMaterno'],
        $input['usuario'],
        $clave_hash,
        $estado,
        $input['tipoUsuario'],
        $fechaNacimiento,
        $sexo,
        $cel,
        $foto
    );

    if ($stmt->execute()) {
        $usuario_id = $conexion->insert_id;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Usuario registrado exitosamente',
            'user_id' => $usuario_id,
            'user_data' => [
                'id' => $usuario_id,
                'rut' => $input['rut'],
                'nombres' => $input['nombres'],
                'apellidoPaterno' => $input['apellidoPaterno'],
                'apellidoMaterno' => $input['apellidoMaterno'],
                'usuario' => $input['usuario'],
                'tipoUsuario' => $input['tipoUsuario'],
                'nombreCompleto' => $input['nombres'] . ' ' . $input['apellidoPaterno'] . ' ' . $input['apellidoMaterno']
            ]
        ]);
    } else {
        throw new Exception("Error ejecutando consulta: " . $stmt->error);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>