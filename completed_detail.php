<?php
$pageTitle = 'Detalle del Pedido - Don Onofre'; // Título de la página
include 'header.php'; // Incluir encabezado

// Conectar a la base de datos
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexión
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Obtener el ID del pedido desde la URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id > 0) {
    // Consulta para obtener el detalle del pedido
    $stmt = $mysqli->prepare("SELECT orders.id, orders.doc_id, orders.payurl, orders.status, products.name, products.description, products.price 
                              FROM orders 
                              LEFT JOIN products ON orders.product_id = products.id 
                              WHERE orders.id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $order = $result->fetch_assoc();
    } else {
        $order = null;
    }
    $stmt->close();
} else {
    $order = null;
}

?>

<div class="container mt-5">
    <h1>Detalle del Pedido</h1>

    <?php if ($order): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Pedido #<?php echo htmlspecialchars($order['id']); ?></h5>
                <p class="card-text">Producto: <?php echo htmlspecialchars($order['name']); ?></p>
                <p class="card-text">Descripción: <?php echo htmlspecialchars($order['description']); ?></p>
                <p class="card-text">Precio: <?php echo number_format($order['price'], 0, ',', '.'); ?> Gs.</p>
                <p class="card-text">Estado: <?php echo htmlspecialchars($order['status']); ?></p>
                <p class="card-text">Enlace de Pago utilizado <a href="<?php echo htmlspecialchars($order['payurl']); ?>" target="_blank"><?php echo htmlspecialchars($order['payurl']); ?></a></p>
                <a href="completed_orders.php" class="btn btn-secondary">Volver a Pedidos Completados</a>
            </div>
        </div>
    <?php else: ?>
        <p class="alert alert-warning">Pedido no encontrado.</p>
    <?php endif; ?>
</div>

<?php
include 'footer.php'; // Pie de página
?>
