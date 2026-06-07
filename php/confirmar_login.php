<?php
session_start();
include "config.php";

if (!isset($_GET['token']) || !isset($_GET['accion'])) {
    die("Parámetros inválidos.");
}

$token = trim($_GET['token']);
$accion = trim($_GET['accion']);

if (!in_array($accion, ['confirmar', 'rechazar'])) {
    die("Acción inválida.");
}

// Buscar el token en la BD
$stmt = $con->prepare("SELECT id_usuario, confirmado, expiracion FROM confirmacion_login WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Token inválido o expirado.");
}

$registro = $resultado->fetch_assoc();
$id_usuario = $registro['id_usuario'];

// Verificar que el token no esté expirado
if (strtotime($registro['expiracion']) < time()) {
    // Eliminar el token expirado
    $stmt = $con->prepare("DELETE FROM confirmacion_login WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    header("Location: ../html/login.html?error=token_expirado");
    exit();
}

// Verificar que el token no haya sido confirmado ya
if ($registro['confirmado']) {
    die("Este token ya ha sido utilizado.");
}

if ($accion === 'confirmar') {
    // Marcar como confirmado
    $stmt = $con->prepare("UPDATE confirmacion_login SET confirmado = TRUE WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    // Obtener datos del usuario
    $stmt = $con->prepare("SELECT id_usuario, nombre, email, id_rol FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    
    if ($usuario) {
        // Iniciar la sesión
        $_SESSION['usuario'] = $usuario['nombre'];
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['id_rol'] = $usuario['id_rol'];
        $_SESSION['email'] = $usuario['email'];
        
        $usuario_sesion = $_SESSION['usuario'];
        mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");
        
        // Limpiar variables de login pendiente
        unset($_SESSION['login_pendiente']);
        unset($_SESSION['nombre_usuario']);
        
        // Redirigir según rol
        if ($usuario['id_rol'] == 1) {
            header("Location: ../php/productos_admin.php");
        } else {
            header("Location: ../html/pag_principal_cliente.html");
        }
        exit();
    }
} 
elseif ($accion === 'rechazar') {
    // Eliminar el token y rechazar el login
    $stmt = $con->prepare("DELETE FROM confirmacion_login WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    // Mostrar página de rechazo
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Inicio de Sesión Rechazado</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .container {
                background: white;
                border-radius: 10px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                max-width: 500px;
                width: 100%;
                padding: 40px;
                text-align: center;
            }

            .icon {
                font-size: 60px;
                margin-bottom: 20px;
            }

            h1 {
                color: #d32f2f;
                margin-bottom: 20px;
                font-size: 28px;
            }

            .message {
                background-color: #ffebee;
                border-left: 4px solid #d32f2f;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
                color: #c62828;
                text-align: left;
            }

            .info {
                color: #666;
                margin: 20px 0;
                line-height: 1.6;
            }

            .actions {
                margin-top: 30px;
                display: flex;
                gap: 10px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn {
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                transition: background-color 0.3s;
                border: none;
                cursor: pointer;
                font-size: 16px;
            }

            .btn-login {
                background-color: #800020;
                color: white;
            }

            .btn-login:hover {
                background-color: #600018;
            }

            .btn-contact {
                background-color: #667eea;
                color: white;
            }

            .btn-contact:hover {
                background-color: #5568d3;
            }

            .warning {
                background-color: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
                color: #856404;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="icon">🔒</div>
            <h1>Inicio de Sesión Rechazado</h1>

            <div class="message">
                <strong>✗ Acción de rechazo confirmada</strong><br>
                El intento de inicio de sesión ha sido rechazado. Tu cuenta permanece segura.
            </div>

            <div class="info">
                <p>Si <strong>fuiste tú</strong> quien rechazó este acceso, todo está bien. Tu cuenta está protegida.</p>
                <p style="margin-top: 15px;">Si <strong>NO fuiste tú</strong>, alguien más podría haber intentado acceder a tu cuenta.</p>
            </div>

            <div class="warning">
                <strong>⚠️ Recomendación de Seguridad:</strong><br>
                Si no reconoces este intento de acceso, considera cambiar tu contraseña inmediatamente.
            </div>

            <div class="actions">
                <a href="../html/login.html" class="btn btn-login">← Volver al Login</a>
                <a href="mailto:a23310150@ceti.mx?subject=Seguridad%20de%20Cuenta" class="btn btn-contact">📧 Contactar Soporte</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$con->close();
?>
