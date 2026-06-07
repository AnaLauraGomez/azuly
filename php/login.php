<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();
include "config.php";

// PHPMailer para enviar confirmación de login
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Función para generar un token seguro
function generarToken() {
    return bin2hex(random_bytes(32));
}

// Función para enviar correo de confirmación de login
function enviarConfirmacionLogin($con, $email, $nombre, $id_usuario) {
    // Generar token único
    $token = generarToken();
    $expiracion = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    
    // Guardar token en la BD
    $stmt = $con->prepare("INSERT INTO confirmacion_login (id_usuario, token, expiracion) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_usuario, $token, $expiracion);
    $stmt->execute();
    $stmt->close();
    
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
        $mail->addAddress($email, $nombre);

        $mail->Subject = "Confirma tu inicio de sesion - Azuly Flores";
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
                    <p>Hola <strong>$nombre</strong>,</p>
                    <p>Se ha detectado un intento de inicio de sesión en tu cuenta de Azuly Flores.</p>
                    
                    <div class='info-box'>
                        <strong>Detalles del intento:</strong><br>
                        Correo: $email<br>
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
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo de confirmación: " . $mail->ErrorInfo);
        return false;
    }
}

if (isset($_POST['email'], $_POST['password'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!$con) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    // Prepara la consulta para mayor seguridad
    $stmt = mysqli_prepare($con, "SELECT id_usuario, nombre, contrasenia, id_rol FROM usuarios WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($usuario = mysqli_fetch_assoc($resultado)) {
        // Verificar la contraseña usando password_verify
        if (password_verify($password, $usuario['contrasenia'])) {
            // NO iniciar sesión aquí - solo enviar correo de confirmación
            enviarConfirmacionLogin($con, $email, $usuario['nombre'], $usuario['id_usuario']);
            
            // Guardar en sesión temporal para mostrar mensaje
            $_SESSION['login_pendiente'] = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre'];
            
            header("Location: ../php/confirmar_sesion.php?pendiente=1");
            exit();
        } else {
            // Compatibilidad: si la contraseña en la BD está en texto plano
            if ($password === $usuario['contrasenia']) {
                // Re-hashear y actualizar la contraseña en la BD
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = mysqli_prepare($con, "UPDATE usuarios SET contrasenia = ? WHERE id_usuario = ?");
                mysqli_stmt_bind_param($upd, "si", $newHash, $usuario['id_usuario']);
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);

                // NO iniciar sesión aquí - solo enviar correo de confirmación
                enviarConfirmacionLogin($con, $email, $usuario['nombre'], $usuario['id_usuario']);
                
                // Guardar en sesión temporal para mostrar mensaje
                $_SESSION['login_pendiente'] = $usuario['id_usuario'];
                $_SESSION['nombre_usuario'] = $usuario['nombre'];
                
                header("Location: ../php/confirmar_sesion.php?pendiente=1");
                exit();
            }

            header("Location: ../html/login.html?error=contrasena");
            exit();
        }
    } else {
        header("Location: ../html/login.html?error=correo");
        exit();
    }

    mysqli_close($con);
} else {
    header("Location: ../html/login.html?error=incompleto");
    exit();
}
?>