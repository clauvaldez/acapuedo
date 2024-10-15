<?php

// Configuraciones de la base de datos
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'don_onofre_db');

// Verificar si las configuraciones necesarias están definidas
if (!defined('DB_HOST') || !defined('DB_USERNAME') || !defined('DB_PASSWORD') || !defined('DB_NAME')) {
    die("Error: Configuración de base de datos no definida.");
}

// Configuraciones de AdamsPay
define('ADAMS_PAY_API_URL', 'https://simulador.adamspay.com/api');
define('MERCHANT_ID', '1593');
define('API_KEY', 'ap-df3932deb366e64320fd96be');
define('RETURN_URL', 'https://localhost/confirm.php'); // Corregido

// Verificar si las configuraciones de AdamsPay están definidas
if (!defined('ADAMS_PAY_API_URL') || !defined('MERCHANT_ID') || !defined('API_KEY') || !defined('RETURN_URL')) {
    die("Error: Configuración de AdamsPay no definida.");
}

?>
