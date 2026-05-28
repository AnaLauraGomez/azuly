<?php
session_start();
include "config.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT p.id_pedido, p.fecha_pedido, p.fecha_recogida, p.total AS total_pedido,
               p.estado_pedido, p.metodo_pago,
               pr.nombre, pr.imagen_url, dp.cantidad,
               (dp.subtotal / dp.cantidad) AS precio_unitario, dp.subtotal
        FROM pedido p
        JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
        JOIN producto pr ON dp.id_producto = pr.id_producto
        WHERE p.id_usuario = ?
        ORDER BY p.fecha_pedido DESC";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

// Agrupar por pedido
$pedidos = [];
while ($row = $resultado->fetch_assoc()) {
    $id_pedido = $row['id_pedido'];
    if (!isset($pedidos[$id_pedido])) {
        $pedidos[$id_pedido] = [
            'fecha_pedido' => $row['fecha_pedido'],
            'fecha_recogida' => $row['fecha_recogida'],
            'total_pedido' => $row['total_pedido'],
            'estado_pedido' => $row['estado_pedido'],
            'metodo_pago' => $row['metodo_pago'],
            'productos' => []
        ];
    }
    $pedidos[$id_pedido]['productos'][] = $row;
}

// Función para asignar clases CSS por estado
function obtenerClaseEstado($estado) {
    return match($estado) {
        'Pendiente' => 'estado-pendiente',
        'Pagado' => 'estado-pagado',
        'En proceso' => 'estado-proceso',
        'Completado' => 'estado-completado',
        'Cancelado' => 'estado-cancelado',
        default => '',
    };
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Compras</title>
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    
 <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
        
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .historial-container {
            max-width: 900px;
            margin: auto;
        }

        .pedido-group {
            margin-bottom: 40px;
            padding: 20px;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            border-left: 5px solid #ccc;
        }

        .estado-pendiente { border-left-color: #ff9800; }     /* Naranja */
        .estado-pagado { border-left-color: #2196f3; }        /* Azul */
        .estado-proceso { border-left-color: #3f51b5; }       /* Azul oscuro */
        .estado-completado { border-left-color: #4caf50; }    /* Verde */
        .estado-cancelado { border-left-color: #f44336; }     /* Rojo */

        .pedido-info {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ccc;
        }

        .pedido-info p {
            margin: 5px 0;
        }

        .compra-item {
            display: flex;
            gap: 20px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .compra-item img {
            border-radius: 10px;
        }

        .detalle p {
            margin: 5px 0;
        }

        .btn-descargar {
            margin-top:5px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff; /* azul */
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-descargar:hover {
            background-color:rgb(57, 146, 241); /* azul oscuro */
        }

    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../images/logo.png" alt="Azuly Flores">
        </div>
        <nav>
            <ul>
                <li><a href="../html/pag_principal_cliente.html">Inicio</a></li>
                <li><a href="../php/productos.php">Productos</a></li>
                <li><a href="../html/categorias.html">Categorías</a></li>
                <li><a href="#contacto">Contacto</a></li>
                <li><a href="../php/historial_compras.php">Mis compras</a></li>  
                <div class="carrito-boton">
                <a href="../php/carrito.php"><i class="fas fa-shopping-cart"></i> Ver Carrito</a>
                </div>
            </ul>
        </nav>
        <div class="auth-buttons">
            <button class="logout" onclick="window.location.href='../php/logout.php'">Cerrar Sesión</button>
        </div>
    </header>

     <h2>Historial de Compras</h2>

    <div class="historial-container">
        <?php if (!empty($pedidos)): ?>
            <?php foreach ($pedidos as $id_pedido => $pedido): ?>
                <?php $clase_estado = obtenerClaseEstado($pedido['estado_pedido']); ?>
                <div class="pedido-group <?= $clase_estado ?>">
                    <div class="pedido-info">
                        <p><strong>ID del Pedido:</strong> <?= $id_pedido ?></p>
                        <p><strong>Fecha del Pedido:</strong> <?= $pedido['fecha_pedido'] ?></p>
                        <p><strong>Fecha de Recogida:</strong> <?= $pedido['fecha_recogida'] ?? 'No especificada' ?></p>
                        <p><strong>Estado:</strong> <?= $pedido['estado_pedido'] ?></p>
                        <p><strong>Método de Pago:</strong> <?= $pedido['metodo_pago'] ?></p>
                        <p><strong>Total del Pedido:</strong> $<?= number_format($pedido['total_pedido'], 2) ?></p>
                        <!-- Botón de descarga del PDF -->
                        <a href="descargar_ticket.php?id_pedido=<?= $id_pedido ?>" target="_blank" class="btn-descargar">Descargar Ticket</a>
                    </div>

                    <?php foreach ($pedido['productos'] as $producto): ?>
                        <div class="compra-item">
                            <img src="<?= $producto['imagen_url'] ?>" alt="<?= $producto['nombre'] ?>" width="120">
                            <div class="detalle">
                                <p><strong>Producto:</strong> <?= $producto['nombre'] ?></p>
                                <p><strong>Cantidad:</strong> <?= $producto['cantidad'] ?></p>
                                <p><strong>Precio Unitario:</strong> $<?= number_format($producto['precio_unitario'], 2) ?></p>
                                <p><strong>Subtotal:</strong> $<?= number_format($producto['subtotal'], 2) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No has realizado ninguna compra aún.</p>
        <?php endif; ?>
    </div>

    <?php include '../php/footer.php'; ?>
</body>
</html>

