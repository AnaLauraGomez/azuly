<?php
#$server = "sql309.infinityfree.com"; 
#$database = "if0_42036749_azuly"; 
#$user = "if0_42036749"; 
#$password = "9gt4MnLYunmz4mr"; // ← Si usas XAMPP normalmente va vacío

#$con = mysqli_connect($server, $user, $password, $database);

#if (!$con) {
#    die("❌ Error al conectar a la base de datos: " . mysqli_connect_error());
#} else {
    //echo "✅ Conexión exitosa";
#}

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

