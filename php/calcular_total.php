<?php
session_start();
include "config.php";

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT p.precio, c.cantidad FROM carrito c JOIN producto p ON c.id_producto = p.id_producto WHERE c.id_usuario = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

$total = 0;
while ($row = $res->fetch_assoc()) {
    $total += $row['precio'] * $row['cantidad'];
}

echo json_encode(['total' => number_format($total, 2, '.', '')]);
?>
