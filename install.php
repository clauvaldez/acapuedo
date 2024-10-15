<?php
include 'adams_pay_config.php'; // Archivo de configuración

// Conectar a MySQL
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);

// Verificar la conexión
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Crear la base de datos
$sql = "CREATE DATABASE IF NOT EXISTS ".DB_NAME." CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if ($mysqli->query($sql) === TRUE) {
    echo "\nBase de datos creada exitosamente.\n";
} else {
    echo "Error al crear la base de datos: " . $mysqli->error . "\n";
}

// Seleccionar la base de datos
$mysqli->select_db(DB_NAME);

// Crear tabla `debts`
$sql = "
CREATE TABLE IF NOT EXISTS debts (
  id INT(11) NOT NULL AUTO_INCREMENT,
  doc_id VARCHAR(255) NOT NULL,
  product_id INT(11) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  pay_status ENUM('pending','paid') DEFAULT 'pending',
  created_at DATETIME DEFAULT current_timestamp(),
  updated_at DATETIME DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";
if ($mysqli->query($sql) === TRUE) {
    echo "\nTabla `debts` creada exitosamente.\n";
} else {
    echo "Error al crear la tabla `debts`: " . $mysqli->error . "\n";
}


// Crear tabla `orders`
$sql = "
CREATE TABLE IF NOT EXISTS orders (
  id INT(11) NOT NULL AUTO_INCREMENT,
  product_id INT(11) DEFAULT NULL,
  status ENUM('pending','paid','cancelled') DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";
if ($mysqli->query($sql) === TRUE) {
    echo "\nTabla `orders` creada exitosamente.\n";
} else {
    echo "Error al crear la tabla `orders`: " . $mysqli->error . "\n";
}


// Crear tabla `products`
$sql = "
CREATE TABLE IF NOT EXISTS products (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  price DECIMAL(10,2) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";
if ($mysqli->query($sql) === TRUE) {
    echo "\nTabla `products` creada exitosamente.\n";
} else {
    echo "Error al crear la tabla `products`: " . $mysqli->error . "\n";
}

// Insertar datos en `products`
$sql = "
INSERT INTO products (name, description, price) VALUES
('Producto 1', 'Descripción del producto 1', 1000.00),
('Producto 2', 'Descripción del producto 2', 7000.00),
('Producto 3', 'Descripción del producto 3', 3000.00),
('Producto 4', 'Descripción del producto 4', 5000.00),
('Producto 5', 'Descripción del producto 5', 2000.00),
('Producto 6', 'Descripción del producto 6', 7000.00);
";
if ($mysqli->query($sql) === TRUE) {
    echo "\nDatos insertados en `products` exitosamente.\n";
} else {
    echo "Error al insertar datos en `products`: " . $mysqli->error . "\n";
}

// Cerrar conexión
$mysqli->close();
?>
