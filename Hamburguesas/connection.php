<?php
// Verifica si la sesión no está activa antes de iniciarla
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluye el archivo de conexión a la base de datos
include 'connection.php';

// Verifica si se recibió el carrito en el cuerpo de la solicitud
$input = json_decode(file_get_contents('php://input'), true);

// Si el carrito está vacío, se responde con un error
if (empty($input)) {
    echo json_encode(['status' => 'error', 'message' => 'El carrito está vacío']);
    exit;
}

// Conectamos a la base de datos
$connection = new Connection();
$total_amount = 0;

try {
    // Inicia la transacción para asegurar la integridad de los datos
    $connection->conn->beginTransaction();
    
    foreach ($input as $cart_item) {
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];

        // Obtenemos el precio del producto
        $stmt = $connection->conn->prepare("SELECT price FROM products WHERE id = :id");
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $total_amount += $product['price'] * $quantity;

            // Insertamos la compra en la base de datos
            $insert_order = $connection->conn->prepare("INSERT INTO orders (product_id, quantity, total) VALUES (:product_id, :quantity, :total)");
            $insert_order->bindParam(':product_id', $product_id);
            $insert_order->bindParam(':quantity', $quantity);
            $insert_order->bindParam(':total', $product['price'] * $quantity);
            $insert_order->execute();
        } else {
            throw new Exception('Producto no encontrado en la base de datos.');
        }
    }

    // Confirmar la transacción
    $connection->conn->commit();

    // Limpiamos el carrito
    unset($_SESSION['cart']);

    // Respuesta exitosa
    echo json_encode(['status' => 'success', 'message' => 'Compra realizada con éxito', 'total' => $total_amount]);

} catch (Exception $e) {
    // Si ocurre un error, revertir la transacción
    $connection->conn->rollBack();

    // Mostrar detalles del error
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
