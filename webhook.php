<?php
include 'adams_pay_config.php'; // Archivo de configuración

// Obtener el contenido del POST enviado al webhook
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

// Validar si se recibió el contenido correctamente
if (!isset($data['notify'])) {
    error_log("Notificación no válida.");
    http_response_code(400); // Solicitud incorrecta
    exit();
}

// Validar el HMAC
$secret = API_KEY; // Reemplazar con tu secreto real
$hmacEsperado = md5('adams' . $postData . $secret);
$hmacRecibido = $_SERVER['HTTP_X_ADAMS_NOTIFY_HASH'] ?? '';

if ($hmacEsperado !== $hmacRecibido) {
    error_log("Validación de HMAC fallida.");
    http_response_code(403); // Prohibido
    exit();
}

// Obtener los datos importantes
$notify = $data['notify'];
$type = $notify['type'];
$docId = $data['debt']['docId'] ?? null;
$payStatus = $data['debt']['payStatus']['status'] ?? null;

// Solo procesar si es un evento 'debtStatus' y se tiene un docId
if ($type === 'debtStatus' && $docId && $payStatus) {
    // Si el estado es 'paid', enviar correos
    if ($payStatus === 'paid') {
        $toOwner = 'dueno@ejemplo.com'; // Correo del dueño del comercio
        $toCustomer = 'cliente@ejemplo.com'; // Correo del cliente
        $subject = 'Pago Confirmado';
        $message = "Estimado cliente,\n\nSu pago ha sido confirmado. Gracias por su compra.\n\nAtentamente,\nEl equipo de Don Onofre.";
        $headers = 'From: noreply@tu_dominio.com' . "\r\n" .
                   'Reply-To: noreply@tu_dominio.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        // Enviar correo al cliente
        mail($toCustomer, $subject, $message, $headers);

        // Enviar correo al dueño del comercio
        $messageOwner = "Se ha recibido un pago para la orden: $docId.\nEstado: $payStatus.";
        mail($toOwner, $subject, $messageOwner, $headers);

        error_log("Correos enviados correctamente.");
    }

    http_response_code(200); // OK
} else {
    error_log("Evento no reconocido o datos faltantes.");
    http_response_code(400); // Solicitud incorrecta
}

?>
