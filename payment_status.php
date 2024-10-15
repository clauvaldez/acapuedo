<?php
$pageTitle = 'Deuda creada - Don Onofre'; // Título de la página
include 'header.php'; // Incluir encabezado

// Verificar el método de solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conectar a la base de datos
    $mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Verificar la conexión
    if ($mysqli->connect_error) {
        die('Error de conexión (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }

    // Obtener el ID del producto
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($product_id > 0) {
        // Obtener los detalles del producto
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

    // Parámetros de la deuda en AdamsPay
    $idDeuda = 'order_' . uniqid(); // Generar un ID único para la deuda
    $label = 'Pago por ' . $product['name']; // Descripción del producto
    $amount = $product['price']; // Precio del producto (en guaraníes)
    $apiUrl = 'https://staging.adamspay.com/api/v1/debts'; // Endpoint de la API de AdamsPay
    $apiKey = API_KEY; // Obtener API_KEY del archivo de configuración
    $siExiste = 'update';

    // Hora DEBE ser en UTC!
    $ahora = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $expira = $ahora->add(new DateInterval('P2D'));

    // Crear data de la deuda
    $deuda = [
        'docId' => $idDeuda,
        'label' => $label,
        'amount' => ['currency' => 'PYG', 'value' => $amount],
        'validPeriod' => [
            'start' => $ahora->format(DateTime::ATOM),
            'end' => $expira->format(DateTime::ATOM)
        ]
    ];

    // Crear JSON para el post
    $post = json_encode(['debt' => $deuda]);

    // Hacer el POST
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_HTTPHEADER => ['apikey: ' . $apiKey, 'Content-Type: application/json', 'x-if-exists: ' . $siExiste],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_SSL_VERIFYPEER => false // Desactivar la verificación SSL
    ]);

    $response = curl_exec($curl);
    if ($response) {
        $data = json_decode($response, true);

        // Deuda es retornada en la propiedad "debt"
        $payUrl = isset($data['debt']) ? $data['debt']['payUrl'] : null;
        if ($payUrl) {

            // Insertar la deuda en la base de datos
            $stmt = $mysqli->prepare("INSERT INTO debts (doc_id, product_id, amount) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $idDeuda, $product_id, $amount);
            $stmt->execute();
            $stmt->close();

            // Insertar en la tabla orders
            $orderStatus = 'pending'; // Estado inicial de la orden
            $createdAt = $ahora->format('Y-m-d H:i:s'); // Obtener la fecha y hora actual
            $stmt = $mysqli->prepare("INSERT INTO orders (product_id, status, created_at, updated_at) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $product_id, $orderStatus, $createdAt, $createdAt); // Asegúrate de usar el tipo de dato correcto
            $stmt->execute();
            $stmt->close();

            // Mostrar mensaje de éxito con enlace de pago

            echo '
            <div class="container mt-5">
                <div class="alert alert-success">
                    <h4 class="alert-heading">Deuda creada exitosamente!</h4>
                    <p>Haz clic en el siguiente enlace para realizar el pago:</p>
                    <a href="' . htmlspecialchars($payUrl) . '" class="btn btn-success" target="_self">Pagar ahora</a>
                </div>
            </div>';
            include 'footer.php'; // Pie de página
        } else {
            echo "No se pudo crear la deuda<br>";
            print_r($data['meta']);
        }
    } else {
        echo 'curl_error: ', curl_error($curl);
    }
    curl_close($curl);
}
