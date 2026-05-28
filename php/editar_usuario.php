<?php
session_start();
include "config.php";

// Verificar sesión y rol
if (!isset($_SESSION['usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../html/login.html");
    exit();
}

$usuario_sesion = $_SESSION['usuario'] ?? 'desconocido';
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
    $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $id_rol = (int) $_POST['id_rol'];

    // Verificar si se envió una contraseña nueva
    if (!empty($_POST['contrasenia'])) {
        $contrasenia = mysqli_real_escape_string($con, $_POST['contrasenia']);
        $query = "UPDATE usuarios SET 
                    nombre='$nombre', 
                    email='$email', 
                    contrasenia='$contrasenia', 
                    id_rol=$id_rol 
                  WHERE id_usuario=$id";
    } else {
        $query = "UPDATE usuarios SET 
                    nombre='$nombre', 
                    email='$email', 
                    id_rol=$id_rol 
                  WHERE id_usuario=$id";
    }

    if (mysqli_query($con, $query)) {
        header("Location: usuarios_admin.php");
        exit();
    } else {
        echo "Error al actualizar el usuario: " . mysqli_error($con);
    }
} else {
    echo "Método no permitido.";
}
?>
