<?php
session_start();
include "config.php";

if (!isset($_SESSION['login_pendiente'])) {
    header("Location: ../html/login.html");
    exit();
}

$id_usuario = $_SESSION['login_pendiente'];

// Obtener datos del usuario
$stmt = $con->prepare("SELECT id_usuario, nombre, email, id_rol FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    header("Location: ../html/login.html?error=usuario_no_encontrado");
    exit();
}

// Iniciar la sesión completamente
$_SESSION['usuario'] = $usuario['nombre'];
$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['id_rol'] = $usuario['id_rol'];
$_SESSION['email'] = $usuario['email'];

$usuario_sesion = $_SESSION['usuario'];
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");

// Limpiar variables de login pendiente
unset($_SESSION['login_pendiente']);
unset($_SESSION['nombre_usuario']);

// Limpiar tokens confirmados
$stmt = $con->prepare("DELETE FROM confirmacion_login WHERE id_usuario = ? AND confirmado = TRUE");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

// Redirigir según rol
if ($usuario['id_rol'] == 1) {
    header("Location: ../php/productos_admin.php");
} else {
    header("Location: ../html/pag_principal_cliente.html");
}

$con->close();
exit();
?>
