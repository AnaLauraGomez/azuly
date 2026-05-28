<?php
include "config.php";

// 1. Usuarios por rol
$q1 = mysqli_query($con, "SELECT 
    SUM(id_rol=1) AS admins,
    SUM(id_rol=2) AS usuarios
FROM usuarios");
$d1 = mysqli_fetch_assoc($q1);

// 2. Registros por mes
$q2 = mysqli_query($con, "
    SELECT DATE_FORMAT(fecha_registro, '%M') AS mes, COUNT(*) AS total 
    FROM usuarios
    GROUP BY MONTH(fecha_registro)
");
$meses = [];
$totales = [];
while ($r = mysqli_fetch_assoc($q2)) {
    $meses[] = $r['mes'];
    $totales[] = $r['total'];
}

// 3. Correos más repetidos
$q3 = mysqli_query($con, "
    SELECT email, COUNT(*) AS rep 
    FROM usuarios 
    GROUP BY email 
    ORDER BY rep DESC LIMIT 5
");
$correos = [];
$reps = [];
while ($r = mysqli_fetch_assoc($q3)) {
    $correos[] = $r['email'];
    $reps[] = $r['rep'];
}

// 4. Usuarios más recientes
$q4 = mysqli_query($con, "
    SELECT nombre, id_usuario
    FROM usuarios
    ORDER BY fecha_registro DESC
    LIMIT 5
");
$nom = [];
$ids = [];
while ($r = mysqli_fetch_assoc($q4)) {
    $nom[] = $r['nombre'];
    $ids[] = $r['id_usuario'];
}

echo json_encode([
    "usuarios_por_rol" => [$d1['admins'], $d1['usuarios']],
    "meses" => $meses,
    "registros_por_mes" => $totales,
    "top_correos" => $correos,
    "repeticiones_correo" => $reps,
    "usuarios_recientes" => $nom,
    "usuarios_id" => $ids
]);
