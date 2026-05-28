<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

include "config.php";

$usuario_sesion = $_SESSION['usuario'] ?? 'desconocido';
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");

// Obtener pedidos
$query = "SELECT p.id_pedido, u.nombre AS usuario, p.fecha_pedido, p.total, 
                 p.estado_pedido, p.metodo_pago
          FROM pedido p
          JOIN usuarios u ON p.id_usuario = u.id_usuario
          ORDER BY p.fecha_pedido DESC";
$result = mysqli_query($con, $query);

/* ------------------- CONSULTAS PARA CHARTS ------------------- */

// 1. Estados de pedido
$sqlEstados = mysqli_query($con, 
    "SELECT estado_pedido AS estado, COUNT(*) AS total 
     FROM pedido GROUP BY estado_pedido");

// 2. Métodos de pago
$sqlPago = mysqli_query($con, 
    "SELECT metodo_pago, COUNT(*) AS total 
     FROM pedido GROUP BY metodo_pago");

// 3. Usuario con más pedidos
$sqlUsuariosPedidos = mysqli_query($con,
    "SELECT u.nombre, COUNT(p.id_pedido) AS total
     FROM pedido p
     JOIN usuarios u ON p.id_usuario = u.id_usuario
     GROUP BY u.nombre");

// 4. Total gastado por usuario
$sqlGastoUsuario = mysqli_query($con,
    "SELECT u.nombre, SUM(p.total) AS total
     FROM pedido p
     JOIN usuarios u ON p.id_usuario = u.id_usuario
     GROUP BY u.nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pedidos</title>

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; border-bottom: 1px solid #ccc; }
    th { background: #6c8ebf; color:white; }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.7);
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .modal-contenido {
        background: white;
        width: 90%;
        padding: 25px;
        border-radius: 12px;
        max-height: 90vh;
        overflow-y: auto;
        text-align: center;
    }

    .cerrar {
        float: right;
        font-size: 25px;
        cursor: pointer;
    }

    .chart-box {
        display: none;               /* por default oculto */
        justify-content: center;     /* centrado horizontal */
        align-items: center;         /* centrado vertical */
        width: 100%;
        margin: 20px auto;
        text-align: center;
    }

    /* CANVAS BIEN CENTRADO Y PROPORCIONAL */
    canvas {
        margin: 0 auto !important;   /* CENTRADO */
        display: block !important;   /* evita que flote */
        width: 75% !important;       /* tamaño grande */
        height: auto !important;     /* mantiene proporción */
        max-width: 600px;            
    }

    /* BOTONES */
    .botonera {
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .botonera button {
        padding: 10px 15px;
        cursor: pointer;
        border: none;
        background: #6c8ebf;
        color: white;
        border-radius: 5px;
    }

    .botonera button:hover {
        background: #5879a3;
    }
</style>

</head>
<body>

<header>
    <div class="logo"><img src="../images/logo.png"></div>
    <nav>
        <ul>
            <li><a href="../html/registro_productos.html">Registro de Productos</a></li>
            <li><a href="../php/productos_admin.php">Administrar Productos</a></li>
            <li><a href="../php/usuarios_admin.php">Gestionar Usuarios</a></li>
            <li><a href="../php/admin_pedidos.php">Gestionar Pedidos</a></li>
            <li><a href="../php/bitacora_admin.php">Bitacora</a></li>
        </ul>
    </nav>
    <button class="logout" onclick="location.href='../php/logout.php'">Cerrar Sesión</button>
</header>

<div class="contenedor">
    <h2>Gestión de Pedidos</h2>

    <button style="padding:10px; margin:10px 0;" onclick="abrirModalGraficas()">Ver Gráficas</button>

    <table id="tablaPedidos">
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>Usuario</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Método de Pago</th>
                <th>Estado</th>
                <th>Actualizar</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($pedido = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $pedido['id_pedido'] ?></td>
                    <td><?= htmlspecialchars($pedido['usuario']) ?></td>
                    <td><?= $pedido['fecha_pedido'] ?></td>
                    <td>$<?= number_format($pedido['total'],2) ?></td>
                    <td><?= $pedido['metodo_pago'] ?></td>

                    <td>
                        <form method="post" action="actualizar_estado.php">
                            <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
                            <select name="estado_pedido">
                                <?php
                                $estados = ['Pendiente','Pagado','En proceso','Completado','Cancelado'];
                                foreach ($estados as $estado) {
                                    $selected = ($pedido['estado_pedido'] == $estado) ? "selected" : "";
                                    echo "<option value='$estado' $selected>$estado</option>";
                                }
                                ?>
                            </select>
                            <button type="submit">Guardar</button>
                        </form>
                    </td>

                    <td><a href="descargar_ticket.php?id_pedido=<?= $pedido['id_pedido'] ?>" target="_blank">📄 Ticket</a></td>

                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- ----------------- MODAL DE GRÁFICAS ----------------- -->
<div id="modalGraficas" class="modal">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModalGraficas()">&times;</span>
        <h2>Gráficas de Pedidos</h2>

        <div class="botonera">
            <button onclick="mostrarGrafica('estadosBox')">Estados</button>
            <button onclick="mostrarGrafica('pagoBox')">Métodos Pago</button>
            <button onclick="mostrarGrafica('usuariosBox')">Usuarios</button>
            <button onclick="mostrarGrafica('gastoBox')">Gasto Total</button>
        </div>

        <!-- GRÁFICAS -->
        <div id="estadosBox" class="chart-box">
            <canvas id="chartEstados"></canvas>
        </div>

        <div id="pagoBox" class="chart-box">
            <canvas id="chartPago"></canvas>
        </div>

        <div id="usuariosBox" class="chart-box">
            <canvas id="chartUsuarios"></canvas>
        </div>

        <div id="gastoBox" class="chart-box">
            <canvas id="chartGasto"></canvas>
        </div>

    </div>
</div>


<!-- JS, DataTables, jQuery, Chart.js -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
        $('#tablaPedidos').DataTable({
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
            }
        });
    });

    function abrirModalGraficas(){ 
        document.getElementById('modalGraficas').style.display = "flex"; 
        mostrarGrafica('estadosBox');
    }
    function cerrarModalGraficas(){ 
        document.getElementById('modalGraficas').style.display = "none"; 
    }

    const estados = <?php 
        $arr = [];
        while($e = mysqli_fetch_assoc($sqlEstados)) $arr[] = $e;
        echo json_encode($arr);
    ?>;

    const pagos = <?php 
        $arr = [];
        while($e = mysqli_fetch_assoc($sqlPago)) $arr[] = $e;
        echo json_encode($arr);
    ?>;

    const usuariosPedidos = <?php 
        $arr = [];
        while($e = mysqli_fetch_assoc($sqlUsuariosPedidos)) $arr[] = $e;
        echo json_encode($arr);
    ?>;

    const gastoUsuarios = <?php 
        $arr = [];
        while($e = mysqli_fetch_assoc($sqlGastoUsuario)) $arr[] = $e;
        echo json_encode($arr);
    ?>;

    // objeto para almacenar instancias Chart.js
    const charts = {};

    function crearGraficaEstados() {
        const ctx = document.getElementById('chartEstados').getContext('2d');
        return new Chart(ctx, {
            type: 'pie',
            data: {
                labels: estados.map(e => e.estado),
                datasets: [{
                    data: estados.map(e => parseInt(e.total)),
                    backgroundColor: ['#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Pedidos por Estado',
                        font: { size: 20, weight: 'bold' }
                    },
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    function crearGraficaPago() {
        const ctx = document.getElementById('chartPago').getContext('2d');
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: pagos.map(e => e.metodo_pago),
                datasets: [{
                    label: 'Pedidos por Método',
                    data: pagos.map(e => parseInt(e.total)),
                    backgroundColor: '#6c8ebf'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Métodos de Pago Utilizados',
                        font: { size: 20, weight: 'bold' }
                    }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    function crearGraficaUsuarios() {
        const ctx = document.getElementById('chartUsuarios').getContext('2d');
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: usuariosPedidos.map(e => e.nombre),
                datasets: [{
                    label: 'Cantidad de pedidos',
                    data: usuariosPedidos.map(e => parseInt(e.total)),
                    backgroundColor: '#76b7b2'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Pedidos por Usuario',
                        font: { size: 20, weight: 'bold' }
                    }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    function crearGraficaGasto() {
        const ctx = document.getElementById('chartGasto').getContext('2d');
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: gastoUsuarios.map(e => e.nombre),
                datasets: [{
                    label: 'Gasto total',
                    data: gastoUsuarios.map(e => parseFloat(e.total)),
                    borderColor: '#e15759',
                    tension: 0.3,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Gasto Total por Usuario',
                        font: { size: 20, weight: 'bold' }
                    }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    function mostrarGrafica(id) {
        document.querySelectorAll('.chart-box').forEach(div => div.style.display = 'none');

        // mostrar la solicitada
        const box = document.getElementById(id);
        box.style.display = 'block';

        // forzamos un pequeño delay para que el DOM actualice el tamaño del canvas
        setTimeout(() => {
            // si ya existe la instancia, solo redimensionar y actualizar
            if (charts[id]) {
                charts[id].resize();
                charts[id].update();
                return;
            }

            // crear según el id
            if (id === 'estadosBox') {
                charts[id] = crearGraficaEstados();
            } else if (id === 'pagoBox') {
                charts[id] = crearGraficaPago();
            } else if (id === 'usuariosBox') {
                charts[id] = crearGraficaUsuarios();
            } else if (id === 'gastoBox') {
                charts[id] = crearGraficaGasto();
            }
        }, 150); // 150ms suele ser suficiente
    }

    // cerrar modal con ESC (opcional)
    document.addEventListener('keydown', function(e){
        if(e.key === "Escape") cerrarModalGraficas();
    });
</script>


<?php include '../php/footer.php'; ?>
</body>
</html>

<?php mysqli_close($con); ?>
