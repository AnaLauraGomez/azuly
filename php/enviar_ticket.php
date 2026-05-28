<?php
// Incluye FPDF y PHPMailer
require('fpdf/fpdf.php');
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include "config.php";
session_start();

if (!isset($_GET['id_pedido'])) {
    die("ID de pedido faltante.");
}

$id_pedido = $_GET['id_pedido'];

// ========================
// OBTENER DATOS DEL PEDIDO
// ========================
$sql = "SELECT p.id_pedido, p.fecha_pedido, u.nombre AS cliente, u.email,
                pr.nombre AS producto, dp.cantidad, pr.precio, (dp.cantidad * pr.precio) AS subtotal
        FROM pedido p
        JOIN usuarios u ON p.id_usuario = u.id_usuario
        JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
        JOIN producto pr ON dp.id_producto = pr.id_producto
        WHERE p.id_pedido = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_pedido);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    die("No se encontró el pedido.");
}

$datos = [];
$total = 0;
$cliente = "";
$fecha = "";
$email_usuario = "";

while ($fila = $resultado->fetch_assoc()) {
    $datos[] = $fila;
    $total += $fila['subtotal'];
    $cliente = $fila['cliente'];
    $fecha = $fila['fecha_pedido'];
    $email_usuario = $fila['email'];
}

// ========================
// CREAR PDF CON FPDF
// ========================
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(0, 10, 'Ticket de Compra', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Cliente: ' . $cliente, 0, 1);
$pdf->Cell(0, 10, 'Fecha: ' . $fecha, 0, 1);
$pdf->Cell(0, 10, 'Pedido #: ' . $id_pedido, 0, 1);
$pdf->Ln(5);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, 'Producto', 1);
$pdf->Cell(30, 10, 'Cantidad', 1);
$pdf->Cell(30, 10, 'Precio', 1);
$pdf->Cell(30, 10, 'Subtotal', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($datos as $fila) {
    $pdf->Cell(80, 10, utf8_decode($fila['producto']), 1);
    $pdf->Cell(30, 10, $fila['cantidad'], 1);
    $pdf->Cell(30, 10, '$' . number_format($fila['precio'], 2), 1);
    $pdf->Cell(30, 10, '$' . number_format($fila['subtotal'], 2), 1);
    $pdf->Ln();
}

// Total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(140, 10, 'Total:', 1);
$pdf->Cell(30, 10, '$' . number_format($total, 2), 1);
$pdf->Ln(20);

// Guardar el PDF en el servidor temporalmente
$nombre_archivo = "Ticket_Compra_{$id_pedido}.pdf";
$ruta_pdf = __DIR__ . '/' . $nombre_archivo;
$pdf->Output('F', $ruta_pdf); // F = guardar archivo en disco

// ========================
// ENVIAR EMAIL CON PHPMailer
// ========================
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Cambia si usas otro proveedor
    $mail->SMTPAuth = true;
    $mail->Username = 'a23310150@ceti.mx'; // Cambia por tu correo
    $mail->Password = 'pupu qhdt grgg ojin'; // Usa una contraseña de aplicación en Gmail
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('TUCORREO@gmail.com', 'Azuly - Flores');
    $mail->addAddress($email_usuario, $cliente);

    $mail->Subject = "Tu ticket de compra (Pedido #$id_pedido)";
    $mail->Body = "Hola $cliente,\n\nGracias por tu compra en Azuly. Adjuntamos tu ticket en PDF.\n\nSaludos,\nEquipo Azuly";
    $mail->addAttachment($ruta_pdf);

    $mail->send();
    echo "✅ Correo enviado con éxito a $email_usuario.";
} catch (Exception $e) {
    echo "❌ Error al enviar correo: {$mail->ErrorInfo}";
}

// Eliminar el PDF del servidor después del envío
unlink($ruta_pdf);
?>
