<?php
session_start();
include "config.php";

// Verificar que hay un login pendiente
if (!isset($_SESSION['login_pendiente'])) {
    header("Location: ../html/login.html");
    exit();
}

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$id_usuario = $_SESSION['login_pendiente'];

// Obtener datos del usuario
$stmt = $con->prepare("SELECT id_usuario, nombre, email FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    header("Location: ../html/login.html?error=usuario_no_encontrado");
    exit();
}

// Eliminar tokens antiguos
$stmt = $con->prepare("DELETE FROM confirmacion_login WHERE id_usuario = ? AND confirmado = FALSE");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

// Generar nuevo token
function generarToken() {
    return bin2hex(random_bytes(32));
}

$token = generarToken();
$expiracion = date('Y-m-d H:i:s', strtotime('+30 minutes'));

// Guardar nuevo token
$stmt = $con->prepare("INSERT INTO confirmacion_login (id_usuario, token, expiracion) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $id_usuario, $token, $expiracion);
$stmt->execute();

// URLs de confirmación y rechazo
$base_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$base_url .= $_SERVER['HTTP_HOST'] . "/WEB II/azylu_flores/php/";

$url_confirmar = $base_url . "confirmar_login.php?token=" . urlencode($token) . "&accion=confirmar";
$url_rechazar = $base_url . "confirmar_login.php?token=" . urlencode($token) . "&accion=rechazar";

$fecha_hora = date('d/m/Y H:i:s');

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'a23310150@ceti.mx';
    $mail->Password = 'pupu qhdt grgg ojin';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('a23310150@ceti.mx', 'Azuly - Flores');
    $mail->addAddress($usuario['email'], $usuario['nombre']);

    $mail->Subject = "Confirma tu inicio de sesion - Azuly Flores (Reenvio)";
    $mail->isHTML(true);
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }
            .header { background-color: #800020; color: white; padding: 20px; text-align: center; }
            .content { background-color: white; padding: 20px; }
            .button { display: inline-block; padding: 12px 30px; margin: 10px 5px; text-decoration: none; border-radius: 5px; font-weight: bold; }
            .button-confirmar { background-color: #28a745; color: white; }
            .button-rechazar { background-color: #dc3545; color: white; }
            .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; }
            .info-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌸 Azuly - Flores</h1>
            </div>
            <div class='content'>
                <p>Hola <strong>" . htmlspecialchars($usuario['nombre']) . "</strong>,</p>
                <p>Se ha detectado un intento de inicio de sesión en tu cuenta de Azuly Flores.</p>
                
                <div class='info-box'>
                    <strong>Detalles del intento:</strong><br>
                    Correo: " . htmlspecialchars($usuario['email']) . "<br>
                    Fecha y hora: $fecha_hora
                </div>
                
                <p><strong>¿Fuiste tú?</strong></p>
                <p>Haz clic en el botón de abajo para confirmar tu inicio de sesión. Este enlace expira en 30 minutos.</p>
                
                <center>
                    <a href='$url_confirmar' class='button button-confirmar'>✓ Confirmar Inicio de Sesión</a>
                    <a href='$url_rechazar' class='button button-rechazar'>✗ Rechazar / No fui yo</a>
                </center>
                
                <p style='color: #666; font-size: 12px; margin-top: 20px;'>Si no solicitaste este acceso, ignora este correo y tu sesión no será activada.</p>
                
                <p>Saludos,<br><strong>Equipo Azuly - Flores</strong></p>
            </div>
            <div class='footer'>
                <p>&copy; 2024 Azuly - Flores. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $mail->send();
    
    // Redirigir de vuelta con confirmación
    header("Location: ../php/confirmar_sesion.php?reenviado=1");
    exit();
    
} catch (Exception $e) {
    error_log("Error al reenviar correo: " . $mail->ErrorInfo);
    header("Location: ../php/confirmar_sesion.php?error_envio=1");
    exit();
}

$con->close();
?>
