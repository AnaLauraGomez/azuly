<?php
session_start();
header('Content-Type: application/json');

echo json_encode([
    "nombre" => $_SESSION['usuario'] ?? null,
    "id_usuario" => $_SESSION['id_usuario'] ?? null,
    "id_rol" => $_SESSION['id_rol'] ?? null
]);
?>


