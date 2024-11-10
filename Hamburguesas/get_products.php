<?php
include 'connection.php';

$connection = new Connection();
$sql = "SELECT * FROM products";
$stmt = $connection->conn->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products);
?>

