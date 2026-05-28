<?php
session_start();
include "config.php";

// Usuario en sesión
$usuario_sesion = $_SESSION['usuario'] ?? 'desconocido';
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"] ?? '';
    $descripcion = $_POST["descripcion"] ?? '';
    $precio = $_POST["precio"] ?? 0;
    $categoria = $_POST["categoria"] ?? '';
    $imagen_url = '';

    // Manejo de la imagen subida
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $archivo_tmp = $_FILES['imagen']['tmp_name'];
        $nombre_archivo = uniqid() . "_" . basename($_FILES['imagen']['name']);
        $carpeta_destino = '../images/productos/'; // asegúrate de que exista y tenga permisos
        $ruta_destino = $carpeta_destino . $nombre_archivo;

        // Mover archivo al destino
        if (move_uploaded_file($archivo_tmp, $ruta_destino)) {
            $imagen_url = $ruta_destino; // Esta es la ruta que se guarda en BD
        } else {
            echo "Error al subir la imagen.";
            exit;
        }
    } else {
        echo "Error: Debes subir una imagen.";
        exit;
    }

    // Validar campos
    if (!empty($nombre) && $precio > 0 && !empty($imagen_url)) {
        $sql = "INSERT INTO producto (nombre, descripcion, precio, imagen_url, categoria) 
                VALUES ('$nombre', '$descripcion', '$precio', '$imagen_url', '$categoria')";

        if (mysqli_query($con, $sql)) {
            $mensaje = "Producto registrado exitosamente.";
            header("Location: ../html/registro_productos.html?mensaje=" . urlencode($mensaje));
            exit();
        } else {
            $mensaje = "Error al registrar el producto.";
            header("Location: ../html/registro_productos.html?mensaje=" . urlencode($mensaje));
            exit();
        }

    } elseif (empty($mensaje)) {
        $mensaje = "Faltan campos obligatorios o el precio no es válido.";
        header("Location: ../html/registro_productos.html?mensaje=" . urlencode($mensaje));
        exit();

    }
}
?>