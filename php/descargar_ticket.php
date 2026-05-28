<?php
ob_clean(); // Limpiar cualquier salida previa
session_start();

include "config.php";

define('FPDF_FONTPATH', __DIR__ . '/../fpdf/font/'); 
require_once __DIR__ . '/../fpdf/fpdf.php';

//require('../fpdf/fpdf.php'); // ..Asegúrate de que FPDF esté incluido en tu proyecto

// Verificar si el ID de pedido está presente
if (!isset($_GET['id_pedido'])) {
    die("No se ha proporcionado un ID de pedido válido.");
}

$id_pedido = $_GET['id_pedido'];

// Obtener los detalles del pedido
$sql = "SELECT p.id_pedido, p.fecha_pedido, p.total AS total_pedido, p.estado_pedido, p.metodo_pago,
               pr.nombre, pr.imagen_url, dp.cantidad, dp.subtotal
        FROM pedido p
        JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
        JOIN producto pr ON dp.id_producto = pr.id_producto
        WHERE p.id_pedido = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_pedido);
$stmt->execute();
$resultado = $stmt->get_result();

$sql_usuario = "SELECT u.nombre, u.email, u.fecha_registro 
                FROM pedido p 
                JOIN usuarios u ON p.id_usuario = u.id_usuario 
                WHERE p.id_pedido = ?";
$stmt_usuario = $con->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $id_pedido);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
$usuario = $result_usuario->fetch_assoc();


// Verificar que la consulta ha devuelto datos
if ($resultado->num_rows == 0) {
    die("No se han encontrado detalles de la compra.");
}

// Crear una nueva instancia de FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Título del ticket
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(200, 10, 'Ticket de Compra', 0, 1, 'C');
// Título del ticket
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(200, 10, 'Azuly - Flores', 0, 1, 'C');

// Detalles del pedido
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(10);



$productos = [];
while ($row = $resultado->fetch_assoc()) {
    $productos[] = $row;
}

// Asumimos que todos los productos del pedido tienen los mismos datos de encabezado (pedido)
$infoPedido = $productos[0];

$pdf->Cell(100, 10, 'ID del Pedido: ' . $infoPedido['id_pedido']);
$pdf->Ln(10);
$pdf->Cell(100, 10, 'Fecha del Pedido: ' . $infoPedido['fecha_pedido']);
$pdf->Ln(10);
$pdf->Cell(100, 10, 'Estado: ' . $infoPedido['estado_pedido']);
$pdf->Ln(10);
$pdf->Cell(100, 10, 'Metodo de Pago: ' . mb_convert_encoding($infoPedido['metodo_pago'], 'UTF-8'));

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Datos del Cliente', 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, 'Nombre: ' . $usuario['nombre'], 0, 1);
$pdf->Cell(0, 8, 'Correo: ' . $usuario['email'], 0, 1);
$pdf->Ln(10);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Imagen', 1, 0, 'C');
$pdf->Cell(50, 10, 'Producto', 1, 0, 'C');
$pdf->Cell(30, 10, 'Cantidad', 1, 0, 'C');
$pdf->Cell(40, 10, 'Subtotal', 1, 1, 'C');

// Contenido de la tabla
$pdf->SetFont('Arial', '', 10);
foreach ($productos as $producto) {
    $imgPath = "../images/" . $producto['imagen_url'];

    // Imagen (ajustada a 20x20)
    if (file_exists($imgPath)) {
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell(40, 20, '', 1); // Reserva espacio para la imagen
        $pdf->Image($imgPath, $x + 5, $y + 2, 20, 16); // Imagen centrada en la celda
    } else {
        $pdf->Cell(40, 20, 'Sin imagen', 1, 0, 'C');
    }

    // Producto
    $pdf->Cell(50, 20, $producto['nombre'], 1);
    $pdf->Cell(30, 20, $producto['cantidad'], 1, 0, 'C');
    $pdf->Cell(40, 20, '$' . number_format($producto['subtotal'], 2), 1, 1, 'C');
}

$pdf->Ln(10);
$pdf->Cell(40, 10, 'Total:', 0);
$pdf->Cell(40, 10, '$' . number_format($infoPedido['total_pedido'], 2));

// Output PDF
$pdf->Output('D', 'Ticket_Compra_Azuly Flores' . $id_pedido . '.pdf');
?>




