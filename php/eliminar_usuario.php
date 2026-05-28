<?php
session_start();
include "config.php";

$usuario_sesion = $_SESSION['usuario'] ?? 'desconocido';
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");

if (!isset($_SESSION['usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../html/login.html");
    exit();
}

$id = $_GET['id'];
mysqli_query($con, "DELETE FROM usuarios WHERE id_usuario = $id");
header("Location: usuarios_admin.php");
exit();
