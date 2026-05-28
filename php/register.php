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

    // Insertar el usuario con id_rol
    $sql = mysqli_query($con, "INSERT INTO usuarios (nombre, email, contrasenia, id_rol) VALUES ('$nombre', '$email', '$password', '$id_rol')");

    if ($sql) {
        // Redirigir al registro con un mensaje de éxito
        header("Location: ../html/register.html?success=1");
        exit();
    } else {
        echo "Error: " . mysqli_error($con);
    }

    mysqli_close($con);
} else {
    echo "Faltan datos en el formulario.";
}
?>








