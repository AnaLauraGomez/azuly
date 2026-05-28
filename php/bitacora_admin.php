<?php
session_start();
include "config.php";

var_dump($_SESSION);
$usuario_sesion = $_SESSION['usuario'] ?? 'desconocido';
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");

if (!isset($_SESSION['usuario']) || $_SESSION['id_rol'] != 1) {
    echo "Acceso denegado.";
    exit();
}

$consulta = "SELECT * FROM bitacora ORDER BY fecha_hora DESC";
$resultado = mysqli_query($con, $consulta);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bitácora de Administrador</title>
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h2 {
            text-align: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 25px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px;
            length:100%;
            text-align: left;
        }
        th {
            background-color: #0066cc;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
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
                <li><a href="../html/registro_productos.html">Registro de Productos</a></li>
                <li><a href="../php/productos_admin.php">Administrar Productos</a></li>
                <li><a href="../php/usuarios_admin.php">Gestionar Usuarios</a></li>
                <li><a href="../php/admin_pedidos.php">Gestionar Pedidos</a></li>
                <li><a href="../php/bitacora_admin.php">Bitacora</a></li>
            </ul>
        </nav>
        <div class="auth-buttons">
            <button class="logout" onclick="window.location.href='../php/logout.php'">Cerrar Sesión</button>
        </div>
    </header>

    <h2>Bitácora de Actividades</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tabla</th>
                <th>Acción</th>
                <th>Descripción</th>
                <th>Fecha y Hora</th>
                <th>Usuario</th>
                <th>Contrasentencia</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['id']) ?></td>
                    <td><?= htmlspecialchars($fila['tabla_afectada']) ?></td>
                    <td><?= htmlspecialchars($fila['accion']) ?></td>
                    <td><?= htmlspecialchars($fila['descripcion']) ?></td>
                    <td><?= htmlspecialchars($fila['fecha_hora']) ?></td>
                    <td><?= htmlspecialchars($fila['usuario'] ?? 'Consola') ?></td>
                    <td class="code"><?= htmlspecialchars($fila['contrasentencia'] ?? '') ?></td>

                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

     
</body>
</html>

