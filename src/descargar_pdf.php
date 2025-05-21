<?php
require('fpdf/fpdf.php');
include 'conexion.php';

if (!isset($_GET['id'])) {
    die("ID no proporcionado.");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM vendedores WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Vendedor no encontrado.");
}

$vendedor = $result->fetch_assoc();
$stmt->close();

class PDF extends FPDF {
    // Dibujar rectángulo con bordes redondeados
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        $op = ($style == 'F') ? 'f' : (($style == 'FD' || $style == 'DF') ? 'B' : 'S');

        $this->_out(sprintf('%.2f %.2f m', ($x + $r) * $k, ($hp - $y) * $k));
        $this->_Arc($x + $w - $r, $y + $r, $r, 180, 270);
        $this->_Arc($x + $w - $r, $y + $h - $r, $r, 270, 360);
        $this->_Arc($x + $r, $y + $h - $r, $r, 0, 90);
        $this->_Arc($x + $r, $y + $r, $r, 90, 180);
        $this->_out($op);
    }

    function _Arc($x, $y, $r, $sAngle, $eAngle) {
        $k = $this->k;
        $hp = $this->h;
        $sAngle *= M_PI / 180;
        $eAngle *= M_PI / 180;
        $cx = $x * $k;
        $cy = ($hp - $y) * $k;

        $this->_out(sprintf('%.2f %.2f m', $cx + $r * $k * cos($sAngle), $cy - $r * $k * sin($sAngle)));

        $steps = 8;
        for ($i = 1; $i <= $steps; $i++) {
            $angle = $sAngle + ($eAngle - $sAngle) * $i / $steps;
            $this->_out(sprintf('%.2f %.2f l', $cx + $r * $k * cos($angle), $cy - $r * $k * sin($angle)));
        }
    }
}

// Dimensiones tarjeta (ancho x alto)
$pdfWidth = 85;  // mm
$pdfHeight = 54; // mm

$pdf = new PDF('P', 'mm', [$pdfHeight, $pdfWidth]); // altura, ancho
$pdf->AddPage();

$margin = 5; // margen

// Fondo con bordes redondeados
$pdf->SetFillColor(236, 240, 241);
$pdf->RoundedRect($margin, $margin, $pdfWidth - 2 * $margin, $pdfHeight - 2 * $margin, 4, 'F');

// Logo
$logoPath = 'assets/img/logo_universidad.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, $margin + 2, $margin + 2, 18, 18);
}

// Texto universidad (centrado, dejando espacio al logo)
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(52, 73, 94);
$pdf->SetXY($margin + 22, $margin + 6);
$pdf->Cell($pdfWidth - 2 * $margin - 22, 8, utf8_decode('Universidad Técnica Luis Vargas Torres'), 0, 1, 'C');

// Nombre vendedor (centrado, más grande)
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0);
$pdf->SetXY($margin, $margin + 26);
$pdf->Cell($pdfWidth - 2 * $margin, 10, utf8_decode($vendedor['nombre']), 0, 1, 'C');

// Datos (centrados y con espacio entre ellos)
$pdf->SetFont('Arial', '', 11);
$pdf->SetXY($margin, $margin + 38);
$pdf->Cell($pdfWidth - 2 * $margin, 7, 'Día: ' . utf8_decode($vendedor['dia']), 0, 1, 'C');
$pdf->SetX($margin);
$pdf->Cell($pdfWidth - 2 * $margin, 7, 'Entrada: ' . $vendedor['entrada'], 0, 1, 'C');
$pdf->SetX($margin);
$pdf->Cell($pdfWidth - 2 * $margin, 7, 'Salida: ' . $vendedor['salida'], 0, 1, 'C');
$pdf->SetX($margin);
$pdf->Cell($pdfWidth - 2 * $margin, 7, 'Producto: ' . utf8_decode($vendedor['producto']), 0, 1, 'C');

// Pie pequeño centrado
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(120);
$pdf->SetXY($margin, $pdfHeight - $margin - 8);
$pdf->Cell($pdfWidth - 2 * $margin, 6, 'Carnet válido sólo con sello oficial', 0, 0, 'C');

$pdf->Output('I', 'Carnet_' . $vendedor['nombre'] . '.pdf');
