<?php
$pageTitle = 'Pedidos Completados - Don Onofre'; // Título de la página
include 'header.php'; // Incluir encabezado

// Conectar a la base de datos
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexión
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Obtener pedidos completados
$stmt = $mysqli->prepare("SELECT orders.id, orders.doc_id, orders.payurl, orders.status, products.name 
                          FROM orders 
                          LEFT JOIN products ON orders.product_id = products.id
                          WHERE orders.status = ?");
$status = 'paid';
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-5">
    <h1>Pedidos Completados</h1>

    <!-- Lista de pedidos completados -->
    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($order = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Pedido #<?php echo htmlspecialchars($order['id']); ?></h5>
                            <p class="card-text">Producto: <?php echo htmlspecialchars($order['name']); ?></p>
                            <p class="card-text">Estado: <?php echo htmlspecialchars($order['status']); ?></p>
                            <p class="card-text">
                                <a href="completed_detail.php?order_id=<?php echo urlencode($order['id']); ?>" class="btn btn-info">Ver Detalles</a>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="alert alert-warning">No hay pedidos completados.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include 'footer.php'; // Pie de página
?>
