<?php
require('fpdf/fpdf.php');
include 'conexion.php';

if (!isset($_GET['id'])) {
    die("ID no proporcionado.");
}

$id = intval($_GET['id']);

// Buscar datos del vendedor
$stmt = $conn->prepare("SELECT * FROM vendedores WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Vendedor no encontrado.");
}

$vendedor = $result->fetch_assoc();
$stmt->close();

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Carnet del Vendedor', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Nombre: ' . $vendedor['nombre'], 0, 1);
$pdf->Cell(0, 10, 'Día: ' . $vendedor['dia'], 0, 1);
$pdf->Cell(0, 10, 'Hora de Entrada: ' . $vendedor['entrada'], 0, 1);
$pdf->Cell(0, 10, 'Hora de Salida: ' . $vendedor['salida'], 0, 1);
$pdf->Cell(0, 10, 'Producto: ' . $vendedor['producto'], 0, 1);

// Salida del PDF
$pdf->Output('I', 'Carnet_' . $vendedor['nombre'] . '.pdf'); // Muestra directamente
?>
