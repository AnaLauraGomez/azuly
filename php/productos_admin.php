<?php

session_start();

// Verificar si la sesión está activa, si no, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

include "config.php";

$usuario_sesion = $_SESSION['usuario'] ?? 'desconocido';
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");

// Obtener productos de la base de datos
$sql = "SELECT id_producto, nombre, descripcion, precio, imagen_url, categoria FROM producto";
$resultado = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Productos</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/productos_admin.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
    .modal {
        display: none;
        position: fixed;
        z-index: 10;
        left: 0; top: 0; width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.5);
        }

    .modal-content {
        background: linear-gradient(to right, #a0c4ff, #d9ecff); /* Degradado azul */ 
        margin: 10% auto;
        padding: 20px;
        border-radius: 10px;
        width: 400px;
        
    }

    .close {
        float: right;
        cursor: pointer;
        font-size: 20px;
        color: #00796b; /* Color verde oscuro para el ícono de cerrar */
    }

    button {
        background-color:rgb(240, 97, 41); /* Color verde para el botón */
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color:rgb(71, 140, 197); /* Verde más oscuro cuando se pasa el mouse */
    }

    input[type="text"], input[type="number"], textarea {
        width: 100%;
        padding: 10px;
        margin-top: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    textarea {
        height: 50px;
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

    <div class="container">
    <h2>Administrar Productos</h2>

    <div style="display:flex; gap:15px; margin-bottom:15px;">
    <button onclick="mostrarGraficas()">Ver Gráficas</button>
    </div>

    <table id="productosTable" border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Imagen</th>
                <th>Categoría</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php while ($producto = mysqli_fetch_assoc($resultado)) { ?>
            <tr>
                <td><?php echo $producto['id_producto']; ?></td>
                <td><?php echo $producto['nombre']; ?></td>
                <td><?php echo $producto['descripcion']; ?></td>
                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                <td><img src="../images/<?php echo htmlspecialchars($producto['imagen_url']); ?>" width="50"></td>
                <td><?php echo $producto['categoria']; ?></td>

                <td class="acciones">
                    <a href="#" onclick="abrirModal(
                        '<?php echo $producto['id_producto']; ?>',
                        '<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($producto['descripcion'], ENT_QUOTES); ?>',
                        '<?php echo $producto['precio']; ?>',
                        '<?php echo htmlspecialchars($producto['imagen_url'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($producto['categoria'], ENT_QUOTES); ?>'
                    )">Editar</a>

                    <a href="eliminar_producto.php?id=<?php echo $producto['id_producto']; ?>" 
                    onclick="return confirm('¿Seguro que quieres eliminar este producto?');">Eliminar</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    </div>

    <?php include '../php/footer.php'; ?>

    <div id="modalEditar" class="modal">
    <div class="modal-content">
    <span class="close" onclick="cerrarModal()">&times;</span>
    <form id="formEditar" method="POST" action="actualizar_producto.php" enctype="multipart/form-data">

        <input type="hidden" name="id_producto" id="edit_id">
        
        <label>Nombre:</label>
        <input type="text" name="nombre" id="edit_nombre" required><br><br>

        <label>Descripción:</label>
        <textarea name="descripcion" id="edit_descripcion" required></textarea><br><br>

        <label>Precio:</label>
        <input type="number" step="0.01" name="precio" id="edit_precio" required><br><br>

        <label>Imagen:</label>
        <input type="file" name="imagen" id="edit_imagen"><br><br>
        <small>Si no seleccionas una imagen, se mantendrá la actual.</small>


        <label>Categoría:</label>
        <select name="categoria" id="edit_categoria" required>
        <option value="">-- Selecciona una categoría --</option>
        <option value="Cumpleaños">Cumpleaños</option>
        <option value="Bodas">Bodas</option>
        <option value="Aniversarios">Aniversarios</option>
        <option value="Celebra con Flores">Celebra con Flores</option>
        </select><br><br>

        <button type="submit">Guardar Cambios</button>
    </form>
  </div>
</div>

<!-- GRÁFICAS -->
<div id="modalGraficas" class="modal">
  <div class="modal-content" style="width: 600px;">
    <span class="close" onclick="cerrarGraficas()">&times;</span>
    <h3>Dashboard de Gráficas</h3>

    <!-- PESTAÑAS -->
    <div style="display:flex; gap:10px; margin-bottom:10px;">
      <button onclick="cambiarGrafica(1)">Productos por Categoría</button>
      <button onclick="cambiarGrafica(2)">Prom. Precios</button>
      <button onclick="cambiarGrafica(3)">Más Vendidos</button>
      <button onclick="cambiarGrafica(4)">Ventas por Mes</button>
    </div>

    <!-- CONTENEDORES DE GRÁFICAS -->
    <div id="graf1"><canvas id="g1"></canvas></div>
    <div id="graf2" style="display:none;"><canvas id="g2"></canvas></div>
    <div id="graf3" style="display:none;"><canvas id="g3"></canvas></div>
    <div id="graf4" style="display:none;"><canvas id="g4"></canvas></div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let g1Chart, g2Chart, g3Chart, g4Chart;

function mostrarGraficas() {
    document.getElementById("modalGraficas").style.display = "block";

    fetch("datos_dashboard.php")
        .then(res => res.json())
        .then(data => {

            /* 1. Productos por categoría */
            const ctx1 = document.getElementById("g1").getContext("2d");
            g1Chart = new Chart(ctx1, {
                type: "bar",
                data: {
                    labels: data.categorias,
                    datasets: [{
                        label: "Productos por categoría",
                        data: data.total_por_categoria
                    }]
                }
            });

            /* 2. Promedio de precios */
            const ctx2 = document.getElementById("g2").getContext("2d");
            g2Chart = new Chart(ctx2, {
                type: "line",
                data: {
                    labels: data.categorias_precio,
                    datasets: [{
                        label: "Promedio de precios",
                        data: data.promedio_precio
                    }]
                }
            });

            /* 3. Productos más vendidos */
            const ctx3 = document.getElementById("g3").getContext("2d");
            g3Chart = new Chart(ctx3, {
                type: "bar",
                data: {
                    labels: data.productos_mas_vendidos,
                    datasets: [{
                        label: "Ventas",
                        data: data.ventas_producto
                    }]
                }
            });

            /* 4. Ventas por mes */
            const ctx4 = document.getElementById("g4").getContext("2d");
            g4Chart = new Chart(ctx4, {
                type: "line",
                data: {
                    labels: data.meses,
                    datasets: [{
                        label: "Ventas por mes",
                        data: data.ventas_por_mes
                    }]
                }
            });

        });
}

function cerrarGraficas() {
    document.getElementById("modalGraficas").style.display = "none";
}

function cambiarGrafica(num) {
    document.getElementById("graf1").style.display = "none";
    document.getElementById("graf2").style.display = "none";
    document.getElementById("graf3").style.display = "none";
    document.getElementById("graf4").style.display = "none";

    document.getElementById("graf" + num).style.display = "block";
}
</script>

<script>
$(document).ready(function() {
    $('#productosTable').DataTable({
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        }
    });
});
</script>

</body>
</html>
<?php mysqli_close($con); ?>

<script>
function abrirModal(id, nombre, descripcion, precio, imagen, categoria) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_descripcion').value = descripcion;
    document.getElementById('edit_precio').value = precio;


    const select = document.getElementById('edit_categoria');
    for (let i = 0; i < select.options.length; i++) {
        if (select.options[i].value === categoria) {
            select.selectedIndex = i;
            break;
        }
    }

    document.getElementById('modalEditar').style.display = "block";
}

function cerrarModal() {
    document.getElementById('modalEditar').style.display = "none";
}
</script>

