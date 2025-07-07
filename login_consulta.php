<?php
session_start();
header('Content-Type: application/json');

// CORS dinámico para ambas IPs
$allowed_origins = [
    'http://192.168.1.17:3000',
    'http://201.214.53.231:3000'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include("setup/config.php");
$conexion = conectar();
$conexion->set_charset("utf8");

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email']) || !isset($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$email = $input['email'];
$password = $input['password'];

$sql = "SELECT id, nombres, apellidoPaterno, apellidoMaterno, usuario, clave, tipoUsuario, estado FROM usuarios WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();
    
    if ($usuario['estado'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario inactivo']);
        exit;
    }
    
    if (password_verify($password, $usuario['clave'])) {
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario'] = $usuario['usuario'];
        $_SESSION['nombreCompleto'] = $usuario['nombres'] . ' ' . $usuario['apellidoPaterno'] . ' ' . $usuario['apellidoMaterno'];
        $_SESSION['tipoUsuario'] = $usuario['tipoUsuario'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'usuario' => [
                'id' => $usuario['id'],
                'nombreCompleto' => $_SESSION['nombreCompleto'],
                'tipoUsuario' => $usuario['tipoUsuario']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
}
?>
