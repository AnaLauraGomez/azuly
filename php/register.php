<?php
include "config.php"; 

if (isset($_POST['nombre'], $_POST['email'], $_POST['password'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $id_rol = 2; // Rol predeterminado (cliente)

    if (!$con) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    // Hashear la contraseña y insertar el usuario con id_rol
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($con, "INSERT INTO usuarios (nombre, email, contrasenia, id_rol) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssi", $nombre, $email, $hashed_password, $id_rol);
    if (mysqli_stmt_execute($stmt)) {
        // Redirigir al registro con un mensaje de éxito
        header("Location: ../html/register.html?success=1");
        exit();
    } else {
        echo "Error: " . mysqli_error($con);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);
} else {
    echo "Faltan datos en el formulario.";
}
?>








