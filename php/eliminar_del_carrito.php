<?php
session_start();
include "config.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_carrito = $_POST['id_carrito'];

    $sql = "DELETE FROM carrito WHERE id_carrito = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id_carrito);
    $stmt->execute();
}

header("Location: carrito.php");
exit();
?>
