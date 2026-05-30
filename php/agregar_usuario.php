<?php
session_start();
include "config.php";

$usuario_sesion = $_SESSION['usuario'] ?? 'desconocido';
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");

if (!isset($_SESSION['usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $contrasenia = $_POST['contrasenia'];
    $id_rol = $_POST['id_rol'];

    // Hashear la contraseña antes de almacenar
    $hashed = password_hash($contrasenia, PASSWORD_DEFAULT);

    $stmt = $con->prepare("INSERT INTO usuarios (nombre, email, contrasenia, id_rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $nombre, $email, $hashed, $id_rol);
    $stmt->execute();
    header("Location: usuarios_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Usuario</title>
</head>
<body>
    <h1>Agregar Nuevo Usuario</h1>
    <form method="POST">
        <label>Nombre:</label><input type="text" name="nombre" required><br>
        <label>Email:</label><input type="email" name="email" required><br>
        <label>Contraseña:</label><input type="password" name="contrasenia" required><br>
        <label>Rol:</label>
        <select name="id_rol">
            <option value="1">Administrador</option>
            <option value="2" selected>Cliente</option>
        </select><br>
        <button type="submit">Agregar</button>
    </form>
</body>
</html>
