<?php
include "config.php";

$tabla_creada = false;
$mensaje = "";

$sql = "CREATE TABLE IF NOT EXISTS confirmacion_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expiracion DATETIME NOT NULL,
    confirmado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
)";

if ($con->query($sql) === TRUE) {
    $tabla_creada = true;
    $mensaje = "✅ Tabla 'confirmacion_login' creada exitosamente o ya existe.";
} else {
    $tabla_creada = false;
    $mensaje = "❌ Error al crear tabla: " . $con->error;
}

$con->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Tabla de Confirmación</title>
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
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .message-box {
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 16px;
        }

        .success {
            background-color: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            border: 2px solid #f44336;
            color: #721c24;
        }

        .info {
            background-color: #d1ecf1;
            border: 2px solid #17a2b8;
            color: #0c5460;
            margin-top: 20px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        .back-link:hover {
            background-color: #5568d3;
        }

        .code {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Configuración de Base de Datos</h1>

        <div class="message-box <?php echo $tabla_creada ? 'success' : 'error'; ?>">
            <?php echo $mensaje; ?>
        </div>

        <?php if ($tabla_creada): ?>
            <div class="message-box info">
                <strong>✓ Configuración completada</strong><br>
                La tabla de confirmación de login está lista. Los usuarios ahora recibirán un correo de confirmación al iniciar sesión.
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="../html/login.html" class="back-link">← Ir al Login</a>
            </div>
        <?php else: ?>
            <div class="message-box info">
                <strong>⚠️ Solución:</strong><br>
                Contacta con el administrador o ejecuta el siguiente SQL en tu base de datos:
            </div>

            <div class="code">
                CREATE TABLE IF NOT EXISTS confirmacion_login (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_usuario INT NOT NULL,
                    token VARCHAR(255) UNIQUE NOT NULL,
                    expiracion DATETIME NOT NULL,
                    confirmado BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
                );
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="crear_tabla_confirmacion.php" class="back-link">🔄 Reintentar</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

