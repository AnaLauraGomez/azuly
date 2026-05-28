<?php
session_start();
$_SESSION = []; // Limpia las variables de sesión
session_destroy();

// Borrar cookies de sesión
setcookie(session_name(), '', time() - 3600, '/'); // Eliminar la cookie de sesión

// Evitar que la página se guarde en caché
header("Cache-Control: no-cache, no-store, must-revalidate"); // Prohibir el almacenamiento en caché
header("Pragma: no-cache"); // Para HTTP/1.0
header("Expires: 0"); // Para HTTP/1.1

// Redirigir al login
header("Location: ../html/login.html");
exit();
?>





