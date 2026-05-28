<?php
$server = "localhost"; 
$database = "azuly"; 
$user = "root"; 
$password = ""; // ← Si usas XAMPP normalmente va vacío

$con = mysqli_connect($server, $user, $password, $database);

if (!$con) {
    die("❌ Error al conectar a la base de datos: " . mysqli_connect_error());
} else {
    //echo "✅ Conexión exitosa";
}
?>

