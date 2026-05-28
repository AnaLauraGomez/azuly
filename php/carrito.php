<?php
session_start();
include "config.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT c.id_carrito, p.nombre, p.precio, p.imagen_url, c.cantidad, (p.precio * c.cantidad) AS subtotal
        FROM carrito c
        JOIN producto p ON c.id_producto = p.id_producto
        WHERE c.id_usuario = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/carrito.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
            </ul>
        </nav>
        <div class="auth-buttons">
            <button class="logout" onclick="window.location.href='../php/logout.php'">Cerrar Sesión</button>
        </div>
    </header>

    <div class="container">
        <h2>Mi Carrito</h2>

        <?php if ($resultado->num_rows > 0): ?>
            <table class="carrito-tabla">
                <tr>
                    <th>Producto</th>
                    <th>Imagen</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
                <?php 
                $total = 0;
                while ($row = $resultado->fetch_assoc()): 
                    $total += $row['subtotal'];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><img src="../images/<?php echo htmlspecialchars($row['imagen_url']); ?>" width="50"></td>
                    <td>$<?php echo number_format($row['precio'], 2); ?></td>
                    <td>
                        <form action="actualizar_carrito.php" method="POST">
                            <input type="hidden" name="id_carrito" value="<?php echo $row['id_carrito']; ?>">
                            <input type="number" name="cantidad" value="<?php echo $row['cantidad']; ?>" min="1" style="width: 50px;">
                            <button type="submit" class="btn-actualizar">Actualizar</button>
                        </form>
                    </td>
                    <td>$<?php echo number_format($row['subtotal'], 2); ?></td>
                    <td>
                        <form action="eliminar_del_carrito.php" method="POST">
                            <input type="hidden" name="id_carrito" value="<?php echo $row['id_carrito']; ?>">
                            <button type="submit" class="btn-eliminar">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="4" class="carrito-total"><strong>Total:</strong></td>
                    <td colspan="2" class="carrito-total"><strong>$<?php echo number_format($total, 2); ?></strong></td>
                </tr>
            </table>
            <br>

            <form id="form-compra" method="POST" action="finalizar_compra.php">
                <br>
                <label><strong>Selecciona la fecha y hora de recogida:</strong></label><br>
                <input type="datetime-local" name="fecha_recogida" required><br><br>

                <label><strong>Selecciona el método de pago:</strong></label><br>
                <select name="metodo_pago" id="metodo_pago" required>
                    <option value="">-- Selecciona una opción --</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                </select><br><br>

                <div style="display: flex; justify-content: center; margin-top: 20px;">
                <div id="paypal-button-container" style="display: none;"></div>
                </div>

                <button type="submit" id="btn-finalizar" class="btn-finalizar">Finalizar Compra</button>
            </form>

        <?php else: ?>
            <p>Tu carrito está vacío.</p>
        <?php endif; ?>
    </div>

    <?php include '../php/footer.php'; ?>

    <script src="https://www.paypal.com/sdk/js?client-id=Aawn9UgMGpqD0aIvpep-O-Doshhoaeavgiu5xlEHXg9bPepS3Xo3KOOtsty2DjIVksx4k_wl8ureI5ty&currency=MXN"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const metodoPago = document.getElementById('metodo_pago');
        const paypalContainer = document.getElementById('paypal-button-container');
        const form = document.getElementById('form-compra');
        const btnFinalizar = document.getElementById('btn-finalizar');

        metodoPago.addEventListener('change', function () {
            if (this.value === 'Tarjeta') {
                paypalContainer.style.display = 'block';
                btnFinalizar.style.display = 'none'; // Oculta el botón normal
            } else {
                paypalContainer.style.display = 'none';
                btnFinalizar.style.display = 'inline-block';
            }
        });

        paypal.Buttons({
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo number_format($total, 2, '.', ''); ?>'
                        }
                    }]
                });
            },
            onApprove: function (data, actions) {
            return actions.order.capture().then(function (details) {
                alert('Pago aprobado por ' + details.payer.name.given_name);

                const fecha = document.querySelector('input[name="fecha_recogida"]').value;
                window.location.href = "finalizar_compra.php?paypal=1&fecha_recogida=" + 
                    encodeURIComponent(fecha) + "&metodo_pago=Tarjeta";
            });
            },

            onCancel: function (data) {
                alert("Pago cancelado.");
            },
            onError: function (err) {
                console.error(err);
                alert("Error en el pago: " + err);
            }
        }).render('#paypal-button-container');

        // Confirmación general de formulario
        form.addEventListener('submit', function (e) {
            if (!confirm("¿Deseas finalizar tu compra?")) {
                e.preventDefault();
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
    // Cambiar el texto del título del carrito con el DOM
    const titulo = document.querySelector("h2");
    titulo.innerText = "Carrito actualizado - ¡Gracias por tu visita!";

    // Crear un nuevo mensaje dinámico
    const mensaje = document.createElement("p");
    mensaje.innerText = "Recuerda revisar tus productos antes de finalizar tu compra.";
    mensaje.style.color = "green";
    mensaje.style.fontWeight = "bold";

    // Insertarlo justo debajo del título
    titulo.parentNode.insertBefore(mensaje, titulo.nextSibling);
    });

    document.addEventListener("DOMContentLoaded", function () {
    const filas = document.querySelectorAll(".carrito-tabla tr");
    let cantidadProductos = filas.length - 2; // Restamos encabezado y total

    if (cantidadProductos > 0) {
        const contador = document.createElement("div");
        contador.innerText = `✿ Tienes ${cantidadProductos} producto${cantidadProductos > 1 ? 's' : ''} en tu carrito ✿`;

        // Estilos para que se vea bonito y centrado
        contador.style.backgroundColor = "#cceeff";       // Azul cielo
        contador.style.color = "#003366";                 // Texto azul oscuro
        contador.style.padding = "10px 20px";
        contador.style.borderRadius = "10px";
        contador.style.fontWeight = "bold";
        contador.style.fontSize = "16px";
        contador.style.boxShadow = "0 2px 6px rgba(0,0,0,0.1)";
        contador.style.margin = "10px auto";              // Centramos horizontalmente
        contador.style.textAlign = "center";
        contador.style.width = "fit-content";

        // Insertar antes del formulario de compra
        const formulario = document.getElementById("form-compra");
        formulario.parentNode.insertBefore(contador, formulario);
    }
    });

    </script>

</body>
</html>

<?php
$stmt->close();
$con->close();
?>

