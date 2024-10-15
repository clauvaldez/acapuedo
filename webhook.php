<?php
include 'adams_pay_config.php'; // Archivo de configuración

// Obtener el contenido del POST enviado al webhook
$docId = $_POST['doc_id'] ?? null;
$status = $_POST['status'] ?? null;

if ($docId && $status) {
    // Conectar a la base de datos 
    $mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($mysqli->connect_error) {
        error_log("Conexión fallida: " . $mysqli->connect_error);
        http_response_code(500);
        exit();
    }

    // Actualizar el estado de la orden
    $stmt = $mysqli->prepare("UPDATE orders SET status = ? WHERE doc_id = ?");
    $stmt->bind_param("ss", $status, $docId);
    
    if ($stmt->execute()) {
        error_log("Estado de la orden actualizado correctamente.");

         // Si el estado es 'paid', enviar correos
         if ($status === 'paid') {
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
            $messageOwner = "Se ha recibido un pago para la orden: $docId.\nEstado: $status.";
            mail($toOwner, $subject, $messageOwner, $headers);
        }

        http_response_code(200); // OK
    } else {
        error_log("Error al actualizar el estado: " . $stmt->error);
        http_response_code(500); // Error interno del servidor
    }

    // Cerrar la conexión
    $stmt->close();
    $mysqli->close();
} else {
    error_log("Datos inválidos recibidos en el webhook.");
    http_response_code(400); // Solicitud incorrecta
}
?>
