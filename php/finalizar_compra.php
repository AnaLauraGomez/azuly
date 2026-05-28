<?php
session_start();
include "config.php";

// FPDF y PHPMailer
require('../fpdf/fpdf.php');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
//$estado_pedido = "Pendiente";
//$estado_pedido = ($_GET['paypal'] ?? null) == "1" ? "Pagado" : "Pendiente";
$estado_pedido = (isset($_GET['paypal']) && $_GET['paypal'] === "1") ? "Pagado" : "Pendiente";


$metodo_pago = $_GET['metodo_pago'] ?? ($_POST['metodo_pago'] ?? null);
$fecha_recogida = $_GET['fecha_recogida'] ?? ($_POST['fecha_recogida'] ?? null);

if (!$fecha_recogida || !$metodo_pago) {
    exit("Faltan datos obligatorios.");
}

if (!in_array($metodo_pago, ['Efectivo', 'Tarjeta', 'Transferencia'])) {
    exit("Método de pago inválido.");
}


$con->begin_transaction();

try {
    // Obtener productos del carrito
    $sql = "SELECT id_producto, cantidad FROM carrito WHERE id_usuario = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    $productos = [];
    $total = 0;

    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;

        $stmtPrecio = $con->prepare("SELECT precio FROM producto WHERE id_producto = ?");
        $stmtPrecio->bind_param("i", $row['id_producto']);
        $stmtPrecio->execute();
        $stmtPrecio->bind_result($precio);
        $stmtPrecio->fetch();
        $stmtPrecio->close();

        $total += $precio * $row['cantidad'];
    }

    if (empty($productos)) {
        throw new Exception("Carrito vacío.");
    }

    // Insertar pedido
    $stmt = $con->prepare("INSERT INTO pedido (id_usuario, fecha_recogida, total, estado_pedido, metodo_pago) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $id_usuario, $fecha_recogida, $total, $estado_pedido, $metodo_pago);
    $stmt->execute();
    $id_pedido = $stmt->insert_id;

    


    // Insertar detalle del pedido
    foreach ($productos as $producto) {
        $stmtPrecio = $con->prepare("SELECT precio FROM producto WHERE id_producto = ?");
        $stmtPrecio->bind_param("i", $producto['id_producto']);
        $stmtPrecio->execute();
        $stmtPrecio->bind_result($precio_unitario);
        $stmtPrecio->fetch();
        $stmtPrecio->close();

        $subtotal = $precio_unitario * $producto['cantidad'];

        $stmtDetalle = $con->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, subtotal) VALUES (?, ?, ?, ?)");
        $stmtDetalle->bind_param("iiid", $id_pedido, $producto['id_producto'], $producto['cantidad'], $subtotal);
        $stmtDetalle->execute();
    }

    // Historial y limpieza de carrito
    $stmt = $con->prepare("INSERT INTO historial_compras (id_usuario, id_pedido) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_usuario, $id_pedido);
    $stmt->execute();

    $stmt = $con->prepare("DELETE FROM carrito WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    // Obtener datos para PDF
    $sql = "SELECT p.fecha_pedido, u.nombre AS cliente, u.email,
       pr.nombre AS producto, dp.cantidad, pr.precio, (dp.cantidad * pr.precio) AS subtotal,
       pr.imagen_url
        FROM pedido p
        JOIN usuarios u ON p.id_usuario = u.id_usuario
        JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
        JOIN producto pr ON dp.id_producto = pr.id_producto
        WHERE p.id_pedido = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $res = $stmt->get_result();

    $datos = [];
    $cliente = "";
    $fecha = "";
    $email_usuario = "";

    while ($fila = $res->fetch_assoc()) {
        $datos[] = $fila;
        $cliente = $fila['cliente'];
        $fecha = $fila['fecha_pedido'];
        $email_usuario = $fila['email'];
    }

    // Generar PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(200, 10, 'Ticket de Compra', 0, 1, 'C');
    $pdf->Cell(200, 10, 'Azuly - Flores', 0, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(10);

    $pdf->Cell(100, 10, 'ID del Pedido: ' . $id_pedido);
    $pdf->Ln(10);
    $pdf->Cell(100, 10, 'Fecha del Pedido: ' . $fecha);
    $pdf->Ln(10);
    $pdf->Cell(100, 10, 'Estado: ' . $estado_pedido);
    $pdf->Ln(10);
    $pdf->Cell(100, 10, 'Metodo de Pago: ' . mb_convert_encoding($metodo_pago, 'UTF-8'));

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Datos del Cliente', 0, 1);

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 8, 'Nombre: ' . $cliente, 0, 1);
    $pdf->Cell(0, 8, 'Correo: ' . $email_usuario, 0, 1);
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Imagen', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Producto', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Cantidad', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Subtotal', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 10);
    foreach ($datos as $producto) {
        $imgPath = "../images/" . $producto['imagen_url'];

        if (file_exists($imgPath)) {
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Cell(40, 20, '', 1);
            $pdf->Image($imgPath, $x + 5, $y + 2, 20, 16);
        } else {
            $pdf->Cell(40, 20, 'Sin imagen', 1, 0, 'C');
        }

        //$pdf->Cell(50, 20, utf8_decode($producto['producto']), 1);
        $pdf->Cell(50, 20, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $producto['producto']), 1);
        $pdf->Cell(30, 20, $producto['cantidad'], 1, 0, 'C');
        $pdf->Cell(40, 20, '$' . number_format($producto['subtotal'], 2), 1, 1, 'C');
    }

    $pdf->Ln(10);
    $pdf->Cell(40, 10, 'Total:', 0);
    $pdf->Cell(40, 10, '$' . number_format($total, 2));

    $nombre_archivo = "Ticket_Compra_{$id_pedido}.pdf";
    $ruta_pdf = __DIR__ . '/' . $nombre_archivo;
    $pdf->Output('F', $ruta_pdf);

    // Enviar correo con el ticket
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'a23310150@ceti.mx';
    $mail->Password = 'pupu qhdt grgg ojin';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('a23310150@ceti.mx', 'Azuly - Flores');
    $mail->addAddress($email_usuario, $cliente);
    $mail->Subject = "Tu ticket de compra (Pedido #$id_pedido)";
    $mail->Body = "Hola $cliente,\n\nGracias por tu compra en Azuly Flores. Adjuntamos tu ticket en PDF.";
    $mail->addAttachment($ruta_pdf);
    $mail->send();

    unlink($ruta_pdf); // Elimina el PDF después de enviar

    $con->commit();

    //echo "<script>alert('¡Compra finalizada con éxito! El ticket fue enviado al correo.'); window.location.href = 'productos.php';</script>";
    if ($_GET['paypal'] ?? null == "1") {
        echo "<script>alert('¡Pago confirmado con PayPal! El ticket fue enviado a tu correo.'); window.location.href = 'productos.php';</script>";
    } else {
        echo "<script>alert('¡Compra finalizada con éxito! El ticket fue enviado al correo.'); window.location.href = 'productos.php';</script>";
    }

} catch (Exception $e) {
    $con->rollback();
    echo "Error al procesar el pedido: " . $e->getMessage();
}



$con->close();
?>

