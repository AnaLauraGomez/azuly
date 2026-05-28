<?php
session_start();

// Verificar si la sesión está activa, si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

include "config.php";

$usuario_sesion = $_SESSION['usuario'] ?? 'desconocido';
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");


// Obtener datos del formulario
$id_pedido = $_POST['id_pedido'] ?? null;
$estado_pedido = $_POST['estado_pedido'] ?? null;

if ($id_pedido && $estado_pedido) {
    $stmt = mysqli_prepare($con, "UPDATE pedido SET estado_pedido = ? WHERE id_pedido = ?");
    mysqli_stmt_bind_param($stmt, "si", $estado_pedido, $id_pedido);
    mysqli_stmt_execute($stmt);
}

header("Location: admin_pedidos.php");
exit();
