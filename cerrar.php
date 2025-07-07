<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Casa Mía - Logout</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="text-center">
        <div class="card p-4">
            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
            <h5>Sesión cerrada exitosamente</h5>
            <small class="text-muted">Redirigiendo...</small>
        </div>
    </div>
    <script>
        setTimeout(() => {
            location.href = 'http://' + location.hostname + ':3000/';
        }, 1500);
    </script>
</body>
</html>
