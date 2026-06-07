<?php
session_start();
include "config.php";

// Verificar que hay un login pendiente
if (!isset($_SESSION['login_pendiente'])) {
    header("Location: ../html/login.html");
    exit();
}

$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirma tu Inicio de Sesión - Azuly Flores</title>
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
            color: #800020;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .message-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
            color: #1565c0;
        }

        .steps {
            text-align: left;
            margin: 30px 0;
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
        }

        .steps ol {
            margin-left: 20px;
            color: #333;
            line-height: 1.8;
        }

        .steps li {
            margin-bottom: 10px;
        }

        .timer {
            font-size: 14px;
            color: #666;
            margin: 20px 0;
        }

        .timer-text {
            color: #d32f2f;
            font-weight: bold;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .back-link:hover {
            background-color: #d32f2f;
        }

        .resend-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .resend-link {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }

        .resend-link:hover {
            text-decoration: underline;
        }

        .confirmation-status {
            margin-top: 30px;
            padding: 15px;
            background-color: #fff9c4;
            border-left: 4px solid #fbc02d;
            border-radius: 5px;
            color: #f57f17;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }

            .icon {
                font-size: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">📧</div>
        <h1>¡Confirmación Pendiente!</h1>
        <p class="subtitle">Hola <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong></p>

        <div class="message-box">
            <strong>✓ Credenciales verificadas correctamente</strong><br>
            Tu identidad ha sido autenticada. Para completar el inicio de sesión, necesitas confirmar tu email.
        </div>

        <?php
        // Mostrar mensajes si aplica
        $reenviado = isset($_GET['reenviado']) ? true : false;
        $error_envio = isset($_GET['error_envio']) ? true : false;
        
        if ($reenviado) {
            echo "<div style='background-color: #c8e6c9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0; border-radius: 5px; color: #2e7d32;'>
                    <strong>✓ Correo reenviado con éxito</strong><br>
                    Hemos reenviado el correo de confirmación. Revisa tu bandeja de entrada nuevamente.
                  </div>";
        }
        
        if ($error_envio) {
            echo "<div style='background-color: #ffcdd2; border-left: 4px solid #f44336; padding: 15px; margin: 20px 0; border-radius: 5px; color: #c62828;'>
                    <strong>✗ Error al reenviar el correo</strong><br>
                    Hubo un problema al reenviar el correo. Intenta más tarde o contacta con soporte.
                  </div>";
        }
        ?>
        

        <div class="steps">
            <strong style="color: #800020;">¿Qué hacer ahora?</strong>
            <ol>
                <li>Revisa tu <strong>bandeja de entrada</strong> de correo electrónico</li>
                <li>Busca un correo de Azuly - Flores con el asunto "Confirma tu inicio de sesión"</li>
                <li>Haz clic en el botón <strong>✓ Confirmar Inicio de Sesión</strong> del correo</li>
                <li>¡Listo! Tu sesión será activada inmediatamente</li>
            </ol>
        </div>

        <div class="timer">
            El enlace de confirmación <strong>expira en 30 minutos</strong>. Si recibes un correo de rechazo después de ese tiempo, tendrás que iniciar sesión de nuevo.
        </div>

        <div class="confirmation-status">
            <strong>⏳ Estado:</strong> En espera de confirmación de correo
        </div>

        <div class="resend-section">
            <p style="color: #666; margin-bottom: 15px;">¿No recibes el correo?</p>
            <a href="reenviar_confirmacion.php" class="resend-link">→ Reenviar correo de confirmación</a>
        </div>

        <a href="../html/login.html" class="back-link">← Volver al Login</a>
    </div>

    <script>
        // Auto-actualizar la página cada 5 segundos para detectar confirmación
        const autoRefresh = setInterval(() => {
            fetch('verificar_confirmacion.php')
                .then(response => response.json())
                .then(data => {
                    if (data.confirmado) {
                        clearInterval(autoRefresh);
                        alert('✓ ¡Correo confirmado! Iniciando sesión...');
                        window.location.href = 'completar_login.php';
                    }
                })
                .catch(error => console.log('Error verificando confirmación:', error));
        }, 5000);
    </script>
</body>
</html>
