<?php
session_start();
include "config.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario']; // solo si existe
$id_producto = $_POST['id_producto'];
$cantidad = $_POST['cantidad'] ?? 1;

// Verificar si el producto ya está en el carrito
$sql_check = "SELECT * FROM carrito WHERE id_usuario = ? AND id_producto = ?";
$stmt = $con->prepare($sql_check);
$stmt->bind_param("ii", $id_usuario, $id_producto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Si ya existe, actualiza la cantidad
    $sql_update = "UPDATE carrito SET cantidad = cantidad + ? WHERE id_usuario = ? AND id_producto = ?";
    $stmt = $con->prepare($sql_update);
    $stmt->bind_param("iii", $cantidad, $id_usuario, $id_producto);
    $stmt->execute();
} else {
    // Si no, inserta un nuevo producto al carrito
    $sql_insert = "INSERT INTO carrito (id_usuario, id_producto, cantidad) VALUES (?, ?, ?)";
    $stmt = $con->prepare($sql_insert);
    $stmt->bind_param("iii", $id_usuario, $id_producto, $cantidad);
    $stmt->execute();
}

$stmt->close();
$con->close();

header("Location: productos.php");
exit();
