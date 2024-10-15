<?php
$pageTitle = 'Productos - Don Onofre'; // Título de la página
include 'header.php'; // Incluir encabezado

// Conectar a la base de datos
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexión
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Inicializar la variable de búsqueda
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Preparar la consulta para buscar productos
if ($searchTerm) {
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
    $searchParam = "%" . $searchTerm . "%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Obtener todos los productos si no hay búsqueda
    $result = $mysqli->query("SELECT * FROM products");
}
?>

<div class="container mt-5">
    <h1>Productos de Don Onofre</h1>
    
    <!-- Formulario de búsqueda -->
    <form method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar productos" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Buscar</button>
                <a href="index.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </div>
    </form>

    <!-- Lista de productos -->
    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($product = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="card-text">Precio: <?php echo number_format($product['price'], 0, ',', '.'); ?> Gs.</p>
                            <a href="order.php?product_id=<?php echo urlencode($product['id']); ?>" class="btn btn-primary">Comprar</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="alert alert-warning">No se encontraron productos.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; // Pie de página ?>
