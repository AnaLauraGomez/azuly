<?php
include "config.php";

$data = [];

/* 1. Productos por categoría */
$q1 = mysqli_query($con, "SELECT categoria, COUNT(*) AS total FROM producto GROUP BY categoria");
while ($r = mysqli_fetch_assoc($q1)) {
    $data["categorias"][] = $r["categoria"];
    $data["total_por_categoria"][] = $r["total"];
}

/* 2. Promedio de precios por categoría */
$q2 = mysqli_query($con, "SELECT categoria, AVG(precio) AS promedio FROM producto GROUP BY categoria");
while ($r = mysqli_fetch_assoc($q2)) {
    $data["categorias_precio"][] = $r["categoria"];
    $data["promedio_precio"][] = round($r["promedio"], 2);
}

/* 3. Productos más vendidos */
$q3 = mysqli_query($con, "
    SELECT p.nombre, SUM(d.cantidad) AS ventas
    FROM detalle_pedido d
    INNER JOIN producto p ON d.id_producto = p.id_producto
    GROUP BY p.id_producto
    ORDER BY ventas DESC
    LIMIT 5
");
while ($r = mysqli_fetch_assoc($q3)) {
    $data["productos_mas_vendidos"][] = $r["nombre"];
    $data["ventas_producto"][] = $r["ventas"];
}

/* 4. Ventas por mes */
$q4 = mysqli_query($con, "
    SELECT DATE_FORMAT(fecha_pedido, '%M') AS mes, COUNT(*) AS ventas
    FROM pedido
    GROUP BY MONTH(fecha_pedido)
");
while ($r = mysqli_fetch_assoc($q4)) {
    $data["meses"][] = $r["mes"];
    $data["ventas_por_mes"][] = $r["ventas"];
}

echo json_encode($data);

