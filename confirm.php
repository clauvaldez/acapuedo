<?php
$pageTitle = 'Confirmaciones - Don Onofre'; // Título de la página
include 'header.php'; // Incluir encabezado

// Conectar a la base de datos
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexión
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Obtener los parámetros de la URL
$intent = $_GET['intent'] ?? null;
$merchant = $_GET['merchant'] ?? null;
$app = $_GET['app'] ?? null;
$type = $_GET['type'] ?? null;
$docId = $_GET['doc_id'] ?? null;

// Verificar que todos los parámetros
if ($intent === 'pay-debt' && $merchant && $app && $type === 'debt' && $docId) {
    // Consulta el estado de la deuda usando el docId
    $stmt = $mysqli->prepare("SELECT * FROM debts WHERE doc_id = ?");
    $stmt->bind_param("s", $docId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $debt = $result->fetch_assoc();
        $payStatus = $debt['pay_status']; // Obtener el estado de pago
        
        // Comprobar si el pago no está aún marcado como 'paid'
        if ($payStatus !== 'paid') {
            // Actualizar el estado del pago a 'paid'
            $updateStmt = $mysqli->prepare("UPDATE debts SET pay_status = 'paid' WHERE doc_id = ?");
            $updateStmt->bind_param("s", $docId);
            if ($updateStmt->execute()) {
                // Mensaje de éxito
                $responseMessage = ["message" => "El pago ha sido procesado correctamente'."];
                
                // Después de actualizar el estado, invocar el webhook
                $webhookUrl = 'webhook.php'; // URL del webhook
                $postData = [
                    'doc_id' => $docId,
                    'status' => 'paid',
                ];

                // Inicializar cURL
                $ch = curl_init($webhookUrl);

                // Configurar la solicitud POST
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Solo para evitar SSL localmente 
                $response = curl_exec($ch);

                // Comprobar si hubo errores en la solicitud
                if (curl_errno($ch)) {
                    error_log("Error al llamar al webhook: " . curl_error($ch));
                } else {
                    error_log("Webhook llamado exitosamente, respuesta: " . $response);
                }

                // Cerrar cURL
                curl_close($ch);

            } else {
                $responseMessage = ["message" => "Error al actualizar el estado del pago."];
            }
            $updateStmt->close();
        } else {
            $responseMessage = ["message" => "El pago ya está marcado como pagado."];
        }
    } else {
        $responseMessage = ["message" => "No se encontró la deuda con ID: $docId."];
    }
} else {
    $responseMessage = ["message" => "Parámetros inválidos."];
}

// Mostrar el resultado
echo '
<body>
    <div class="container mt-5">
        <div class="alert alert-info">
            <h4 class="alert-heading">Resultado de la operación</h4>
            <p>' . htmlspecialchars($responseMessage['message']) . '</p>
        </div>
        <a href="index.php" class="btn btn-primary">Volver al inicio</a>
    </div>
   </body>
</html>
';

include 'footer.php';
// Cerrar conexión
$mysqli->close();
?>
