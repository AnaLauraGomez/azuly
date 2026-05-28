<?php
$server = "localhost";
$user = "root";
$password = "1234";
$database = "azuly";

$con = mysqli_connect($server, $user, $password, $database);

if ($con) {
    echo "Conexión exitosa a la base de datos.";
} else {
    echo "Error de conexión: " . mysqli_connect_error();
}
?>
