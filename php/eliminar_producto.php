<?php
session_start();
include "config.php";

// Obtener el usuario en sesión
$usuario_sesion = $_SESSION['usuario'] ?? 'desconocido';
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");

// Verificar si se recibió un ID válido por GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM producto WHERE id_producto = $id";
    if (mysqli_query($con, $sql)) {
        header("Location: productos_admin.php");
        exit();
    } else {
        echo "Error al eliminar: " . mysqli_error($con);
    }
}

mysqli_close($con);
?>

