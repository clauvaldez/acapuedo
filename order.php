<?php
$pageTitle = 'Confirmar Orden - Don Onofre'; // Título de la página
include 'header.php'; // Incluir encabezado

// Conectar a la base de datos
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión
if ($mysqli->connect_error) {
    die('Error de conexión (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Obtener el ID del producto 
$product_id = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;

if ($product_id > 0) {
    // Realizar la consulta de forma segura para evitar inyecciones SQL
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();


    if (!$product) {
        echo "Producto no encontrado.";
        exit;
    }
} else {
    echo "ID de producto inválido.";
    exit;
}


?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Confirmar Orden</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
            <p class="card-text">Precio: <?php echo number_format($product['price'], 0, ',', '.'); ?> Gs.</p>
            <form action="payment_status.php" method="post">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                <button type="submit" class="btn btn-success">Confirmar Pedido</button>
                <a href="index.php" class="btn btn-secondary">Volver a la tienda</a>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; // Pie de página ?>
