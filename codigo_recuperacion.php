<?php
// Mostrar errores para depurar (quitar o poner en 0 en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);
// CORS específico para React
$allowed_origins = [
    "http://192.168.1.17:3000",
    "http://201.214.53.231:3000"
];

$origin = $_SERVER["HTTP_ORIGIN"] ?? "";
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Manejar solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/setup/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function respuesta_json($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['correo'])) {
    respuesta_json(false, 'Correo no recibido');
}

$correo = filter_var($data['correo'], FILTER_VALIDATE_EMAIL);
if (!$correo) {
    respuesta_json(false, 'Correo inválido');
}

$conn = conectar();

// Verificar que el usuario exista
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
if (!$stmt) {
    respuesta_json(false, 'Error en la consulta SQL');
}
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    respuesta_json(false, 'Correo no registrado en Casa Mía Inmobiliaria');
}
$stmt->close();

// Si NO se envía código, significa que se quiere generar y enviar el código
if (!isset($data['codigo'])) {
    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expira = date("Y-m-d H:i:s", time() + 120); // 2 minutos de expiración

    // Crear tabla si no existe
    $conn->query("CREATE TABLE IF NOT EXISTS codigos_recuperacion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        correo VARCHAR(255) NOT NULL,
        codigo VARCHAR(6) NOT NULL,
        expira DATETIME NOT NULL,
        UNIQUE KEY unique_correo (correo)
    )");

    // Insertar o reemplazar código en la tabla
    $stmt = $conn->prepare("REPLACE INTO codigos_recuperacion (correo, codigo, expira) VALUES (?, ?, ?)");
    if (!$stmt) {
        respuesta_json(false, 'Error en la consulta SQL');
    }
    $stmt->bind_param("sss", $correo, $codigo, $expira);
    $stmt->execute();
    $stmt->close();

    // Enviar código con PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'matias34890@gmail.com';  // tu correo SMTP
        $mail->Password = 'kihz wpon pnpb gbdb';    // tu contraseña de aplicación Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('matias34890@gmail.com', 'Casa Mía Inmobiliaria');
        $mail->addAddress($correo);
        $mail->Subject = 'Código de Recuperación - Casa Mía Inmobiliaria';
        $mail->isHTML(true);
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #007bff; text-align: center;'>Casa Mía Inmobiliaria</h2>
                <h3 style='color: #333;'>Recuperación de Contraseña</h3>
                <p>Hola,</p>
                <p>Has solicitado recuperar tu contraseña. Tu código de verificación es:</p>
                <div style='background: #f8f9fa; padding: 20px; text-align: center; border: 2px solid #007bff; border-radius: 5px; margin: 20px 0;'>
                    <h1 style='color: #007bff; margin: 0; font-size: 2em; letter-spacing: 5px;'>$codigo</h1>
                </div>
                <p><strong>Importante:</strong></p>
                <ul>
                    <li>Este código expira en <strong>2 minutos</strong></li>
                    <li>No compartas este código con nadie</li>
                    <li>Si no solicitaste este código, ignora este mensaje</li>
                </ul>
                <hr>
                <p style='color: #666; font-size: 0.9em;'>
                    Este es un mensaje automático de Casa Mía Inmobiliaria.<br>
                    No respondas a este correo.
                </p>
            </div>
        ";

        $mail->send();
        respuesta_json(true, 'Código enviado a tu correo electrónico');
    } catch (Exception $e) {
        respuesta_json(false, 'No se pudo enviar el correo: ' . $mail->ErrorInfo);
    }
}

// Aquí se envía código para validar (y opcionalmente nueva contraseña)
$codigo = $data['codigo'] ?? '';

if (!preg_match('/^\d{6}$/', $codigo)) {
    respuesta_json(false, 'Código debe tener 6 dígitos');
}

$stmt = $conn->prepare("SELECT codigo, expira FROM codigos_recuperacion WHERE correo = ?");
if (!$stmt) {
    respuesta_json(false, 'Error en la consulta SQL');
}
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    respuesta_json(false, 'No hay código pendiente para este correo');
}

$row = $result->fetch_assoc();
$stmt->close();

$codigo_almacenado = $row['codigo'];
$expira = strtotime($row['expira']);
$ahora = time();

if ($ahora > $expira) {
    respuesta_json(false, 'El código ha expirado. Solicita uno nuevo.');
}

if ($codigo !== $codigo_almacenado) {
    respuesta_json(false, 'Código incorrecto');
}

// Si se envía nueva contraseña, cambiarla
if (isset($data['nueva_contrasena'])) {
    $nueva = trim($data['nueva_contrasena']);
    if (strlen($nueva) < 6) {
        respuesta_json(false, 'La nueva contraseña debe tener al menos 6 caracteres');
    }
    
    $hash = password_hash($nueva, PASSWORD_DEFAULT);

    // Actualizar contraseña en la tabla usuarios
    $stmt = $conn->prepare("UPDATE usuarios SET clave = ? WHERE usuario = ?");
    if (!$stmt) {
        respuesta_json(false, 'Error en la consulta SQL');
    }
    $stmt->bind_param("ss", $hash, $correo);
    $stmt->execute();
    $stmt->close();

    // Borrar código para que no se reutilice
    $stmt = $conn->prepare("DELETE FROM codigos_recuperacion WHERE correo = ?");
    if ($stmt) {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->close();
    }

    respuesta_json(true, 'Contraseña actualizada correctamente');
}

// Si solo validamos código y es correcto
respuesta_json(true, 'Código verificado correctamente');
?>