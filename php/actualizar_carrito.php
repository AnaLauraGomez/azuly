<?php
session_start();
include "config.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_carrito = $_POST['id_carrito'];
    $cantidad = max(1, intval($_POST['cantidad'])); // Evitar cantidad menor a 1

    $sql = "UPDATE carrito SET cantidad = ? WHERE id_carrito = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $cantidad, $id_carrito);
    $stmt->execute();
}

header("Location: carrito.php");
exit();
?>
