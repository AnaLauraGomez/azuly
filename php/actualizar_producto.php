<?php
session_start();
include "config.php";

// Obtener el nombre del usuario en sesión
$usuario_sesion = $_SESSION['usuario'] ?? 'desconocido';
mysqli_query($con, "SET @usuario_actual = '$usuario_sesion'");

// Verificar que se enviaron todos los datos
if (
    isset($_POST['id_producto'], $_POST['nombre'], $_POST['descripcion'], $_POST['precio'], $_POST['categoria'])
) {
    $id_producto = $_POST['id_producto'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];

   $imagen_actual = isset($_FILES['imagen']) ? $_FILES['imagen']['name'] : '';
    $directorio_destino = '../images/';
    $nombre_imagen_final = '';

    // Si se subió una nueva imagen, guárdala
    if (!empty($imagen_actual)) {
        $nombre_imagen_final = basename($imagen_actual);
        $ruta_imagen = $directorio_destino . $nombre_imagen_final;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_imagen)) {
            // Imagen subida con éxito
        } else {
            echo "Error al subir la imagen.";
            exit();
        }
    } else {
        // Si no se subió imagen, conservar la que ya tenía
        $query = "SELECT imagen_url FROM producto WHERE id_producto = ?";
        $stmt_img = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt_img, "i", $id_producto);
        mysqli_stmt_execute($stmt_img);
        mysqli_stmt_bind_result($stmt_img, $imagen_guardada);
        mysqli_stmt_fetch($stmt_img);
        $nombre_imagen_final = $imagen_guardada;
        mysqli_stmt_close($stmt_img);
    }

    
    $categoria = $_POST['categoria'];

    // Preparar y ejecutar la actualización
    $sql = "UPDATE producto SET 
                nombre = ?, 
                descripcion = ?, 
                precio = ?, 
                imagen_url = ?, 
                categoria = ? 
            WHERE id_producto = ?";

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssdssi", $nombre, $descripcion, $precio, $nombre_imagen_final, $categoria, $id_producto);


    if (mysqli_stmt_execute($stmt)) {
        header("Location: productos_admin.php");
        exit();
    } else {
        echo "Error al actualizar el producto: " . mysqli_error($con);
    }

    mysqli_stmt_close($stmt);
} else {
    echo "Faltan datos del formulario.";
}

mysqli_close($con);
?>
