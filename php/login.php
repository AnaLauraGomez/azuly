<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();
include "config.php";

if (isset($_POST['email'], $_POST['password'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!$con) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    // Prepara la consulta para mayor seguridad
    $stmt = mysqli_prepare($con, "SELECT id_usuario, nombre, contrasenia, id_rol FROM usuarios WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($usuario = mysqli_fetch_assoc($resultado)) {
        if ($password === $usuario['contrasenia']) { 
            $_SESSION['usuario'] = $usuario['nombre'];
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['id_rol'] = $usuario['id_rol'];

            $usuario_sesion = $_SESSION['usuario'];
            mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");



            // Redirige según rol
            if ($usuario['id_rol'] == 1) {
                header("Location: ../php/productos_admin.php");
            } else {
                header("Location: ../html/pag_principal_cliente.html");
            }

            exit();
        } else {
            header("Location: ../html/login.html?error=contrasena");
            exit();
        }
    } else {
        header("Location: ../html/login.html?error=correo");
        exit();
    }

    mysqli_close($con);
} else {
    header("Location: ../html/login.html?error=incompleto");
    exit();
}
?>