<?php

session_start();

// Verificar si la sesión está activa, si no, redirigir al login
//if (!isset($_SESSION['usuario'])) {
    //header("Location: ../html/login.html");
    //exit();
//}

include "config.php";

$sql = "SELECT id_producto, nombre, descripcion, precio, imagen_url, categoria FROM producto";
$resultado = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Productos</title>
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/productos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>

<header>
        <button class="nav-toggle" aria-label="Abrir menú">☰</button>
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
            </ul>
            <div class="carrito-boton">
                <a href="carrito.php"><i class="fas fa-shopping-cart"></i> Ver Carrito</a>
            </div>
        </nav>
        <div class="auth-buttons">
            <button class="logout" onclick="window.location.href='../php/logout.php'">Cerrar Sesión</button>
        </div>
    </header>

    <div class="container">
        <h2>Lista de Productos</h2>

        <div class="tarjetas-contenedor">
            <?php while ($producto = mysqli_fetch_assoc($resultado)) { ?>
            <div class="tarjeta-producto">
                <img src="../images/<?php echo htmlspecialchars($producto['imagen_url']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                <h3><?php echo $producto['nombre']; ?></h3>
                <p><strong>Descripción:</strong> <?php echo $producto['descripcion']; ?></p>
                <p><strong>Precio:</strong> $<?php echo number_format($producto['precio'], 2); ?></p>
                <p><strong>Categoría:</strong> <?php echo $producto['categoria']; ?></p>

                <?php if (isset($_SESSION['usuario'])) { ?>
                    <!-- Mostrar botón para agregar al carrito si está logueado -->
                    <form action="agregar_carrito.php" method="POST">
                        <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                        <input type="hidden" name="cantidad" value="1">
                        <button type="submit" class="btn-agregar"><i class="fas fa-cart-plus"></i> Agregar al carrito</button>
                    </form>
                <?php } else { ?>
                    <!-- Mostrar botón que redirige al login si NO está logueado -->
                    <button class="btn-agregar" onclick="window.location.href='../html/login.html'">
                        <i class="fas fa-cart-plus"></i> Inicia sesión para agregar
                    </button>
                <?php } ?>
            </div>
        <?php } ?>

        </div>
    </div>

    <?php include '../php/footer.php'; ?>

    <script src="../js/nav.js" defer></script>

</body>
</html>

<?php mysqli_close($con); ?>

