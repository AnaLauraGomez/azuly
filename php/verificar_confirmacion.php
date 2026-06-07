<?php
session_start();
include "config.php";
header('Content-Type: application/json');

if (!isset($_SESSION['login_pendiente'])) {
    echo json_encode(['confirmado' => false]);
    exit();
}

$id_usuario = $_SESSION['login_pendiente'];

// Verificar si hay un token confirmado para este usuario
$stmt = $con->prepare("SELECT COUNT(*) as confirmados FROM confirmacion_login WHERE id_usuario = ? AND confirmado = TRUE");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$fila = $resultado->fetch_assoc();

echo json_encode(['confirmado' => $fila['confirmados'] > 0]);

$con->close();
?>
