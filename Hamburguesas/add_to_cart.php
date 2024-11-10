<?php
session_start();

// Asegúrate de que los datos del producto llegan bien
if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // Si no hay carrito, creamos uno
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Verificamos si el producto ya está en el carrito
    $product_found = false;
    foreach ($_SESSION['cart'] as &$cart_item) {
        if ($cart_item['product_id'] == $product_id) {
            // Si el producto ya está en el carrito, solo aumentamos la cantidad
            $cart_item['quantity'] += $quantity;
            $product_found = true;
            break;
        }
    }
    
    // Si el producto no está, lo agregamos al carrito
    if (!$product_found) {
        $_SESSION['cart'][] = [
            'product_id' => $product_id,
            'quantity' => $quantity
        ];
    }

    echo json_encode(['status' => 'success', 'message' => 'Producto agregado al carrito']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
}
?>
