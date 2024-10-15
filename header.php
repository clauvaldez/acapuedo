<?php
include 'adams_pay_config.php'; // Archivo de configuración

// Conectar a la base de datos
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexión
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Contar pedidos pendientes
$stmt = $mysqli->prepare("SELECT COUNT(*) as pending_count FROM orders WHERE status = ?");
$status = 'pending';
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pendingOrdersCount = $row['pending_count'] ?? 0; // Si no hay pedidos, devolver 0
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle ?? 'Productos - Don Onofre'; ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <a class="navbar-brand" href="index.php">
        <img src="img/logo.png" alt="Logo" width="30" height="30" class="d-inline-block align-top">
        Don Onofre
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Inicio</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="orders.php">
                    Pedidos Pendientes
                    <?php if ($pendingOrdersCount > 0): ?>
                        <span class="badge badge-danger"><?php echo $pendingOrdersCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="completed_orders.php">Pedidos Completados</a>
            </li>
        </ul>
    </div>
</nav>
