<?php
session_start();
include "config.php";

// Verificar sesión y rol de administrador
$usuario_sesion = $_SESSION['usuario'] ?? 'desconocido';
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");

if (!isset($_SESSION['usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: login.html");
    exit();
}

$resultado = mysqli_query($con, "SELECT * FROM usuarios");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>

    <!-- DATATABLES -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- CHART JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- ESTILOS -->
    <link rel="stylesheet" href="../css/styles_usuarios_admin.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* Modal para gráficas */
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 20px;
            width: 650px;
            border-radius: 12px;
        }
        .close {
            float: right;
            cursor: pointer;
            font-size: 22px;
        }
        table img { width: 50px; }
        .btn {
            background-color: #ff6f61;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn:hover { background-color: #3f8fd8; }
    </style>
</head>
<body>

<header>
    <button class="nav-toggle" aria-label="Abrir menú">☰</button>
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

<h1>Gestión de Usuarios</h1>

<div style="display:flex; gap:15px; margin-bottom:15px;">
    <button class="btn" onclick="abrirModal()">Agregar Nuevo Usuario</button>
    <button class="btn" onclick="mostrarGraficas()">Ver Gráficas</button>
</div>

<!-- Modal Agregar Usuario -->
<div id="modalAgregar" class="modal">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModal()">&times;</span>
        <h2>Agregar Usuario</h2>
        <form method="POST" action="agregar_usuario.php">
            <label>Nombre:</label>
            <input type="text" name="nombre" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Contraseña:</label>
            <input type="password" name="contrasenia" required>

            <label>Rol:</label>
            <select name="id_rol" required>
                <option value="1">Administrador</option>
                <option value="2" selected>Usuario</option>
            </select>
            <button type="submit">Guardar</button>
        </form>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div id="modalEditar" class="modal">
    <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModalEditar()">&times;</span>
        <h2>Editar Usuario</h2>

        <form method="POST" action="editar_usuario.php">
            <input type="hidden" name="id_usuario" id="edit_id">

            <label>Nombre:</label>
            <input type="text" name="nombre" id="edit_nombre" required>

            <label>Email:</label>
            <input type="email" name="email" id="edit_email" required>

            <label>Contraseña (dejar en blanco para conservar):</label>
            <input type="password" name="contrasenia" id="edit_contrasenia">

            <label>Rol:</label>
            <select name="id_rol" id="edit_id_rol" required>
                <option value="1">Administrador</option>
                <option value="2">Usuario</option>
            </select>

            <button type="submit">Actualizar</button>
        </form>
    </div>
</div>

<!-- TABLA DE USUARIOS -->
<table id="usuariosTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Fecha Registro</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        <?php while ($usuario = mysqli_fetch_assoc($resultado)) { ?>
        <tr>
            <td><?= $usuario['id_usuario'] ?></td>
            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
            <td><?= htmlspecialchars($usuario['email']) ?></td>
            <td><?= $usuario['id_rol'] == 1 ? "Admin" : "Cliente" ?></td>
            <td><?= $usuario['fecha_registro'] ?></td>

            <td>
                <a href="#" onclick='abrirModalEditar(<?= json_encode($usuario) ?>); return false;'>Editar</a>
                <a href="eliminar_usuario.php?id=<?= $usuario['id_usuario'] ?>" onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<!-- MODAL GRÁFICAS -->
<div id="modalGraficas" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarGraficas()">&times;</span>
        <h3>Dashboard de Usuarios</h3>

        <div style="display:flex; gap:10px; margin-bottom:10px;">
            <button onclick="cambiarGrafica(1)">Usuarios por Rol</button>
            <button onclick="cambiarGrafica(2)">Registros por Mes</button>
            <button onclick="cambiarGrafica(3)">Top Correos Usados</button>
            <button onclick="cambiarGrafica(4)">Usuarios Recientes</button>
        </div>

        <div id="graf1"><canvas id="g1"></canvas></div>
        <div id="graf2" style="display:none;"><canvas id="g2"></canvas></div>
        <div id="graf3" style="display:none;"><canvas id="g3"></canvas></div>
        <div id="graf4" style="display:none;"><canvas id="g4"></canvas></div>
    </div>
</div>

<script>
/* DATATABLE */
$(document).ready(function() {
    $('#usuariosTable').DataTable({
        pageLength: 10,
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
    });
});

/* MODALES DE USUARIOS */
function abrirModal() { document.getElementById('modalAgregar').style.display = 'block'; }
function cerrarModal() { document.getElementById('modalAgregar').style.display = 'none'; }

function abrirModalEditar(usuario) {
    document.getElementById('edit_id').value = usuario.id_usuario;
    document.getElementById('edit_nombre').value = usuario.nombre;
    document.getElementById('edit_email').value = usuario.email;
    document.getElementById('edit_id_rol').value = usuario.id_rol;
    document.getElementById('modalEditar').style.display = 'block';
}
function cerrarModalEditar() { document.getElementById('modalEditar').style.display = 'none'; }

/* GRÁFICAS */
let g1Chart, g2Chart, g3Chart, g4Chart;

function mostrarGraficas() {
    document.getElementById("modalGraficas").style.display = "block";

    fetch("datos_dashboard_usuarios.php")
        .then(res => res.json())
        .then(data => {

            g1Chart = new Chart(document.getElementById("g1"), {
                type: "pie",
                data: {
                    labels: ["Administradores", "Usuarios"],
                    datasets: [{ data: data.usuarios_por_rol }]
                }
            });

            g2Chart = new Chart(document.getElementById("g2"), {
                type: "line",
                data: {
                    labels: data.meses,
                    datasets: [{ label: "Registros", data: data.registros_por_mes }]
                }
            });

            g3Chart = new Chart(document.getElementById("g3"), {
                type: "bar",
                data: {
                    labels: data.top_correos,
                    datasets: [{ label: "Veces usado", data: data.repeticiones_correo }]
                }
            });

            g4Chart = new Chart(document.getElementById("g4"), {
                type: "bar",
                data: {
                    labels: data.usuarios_recientes,
                    datasets: [{ label: "Usuarios", data: data.usuarios_id }]
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

<?php include '../php/footer.php'; ?>
<script src="../js/nav.js" defer></script>
</body>
</html>

<?php mysqli_close($con); ?>
