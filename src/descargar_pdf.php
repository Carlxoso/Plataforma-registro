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
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        $op = ($style == 'F') ? 'f' : (($style == 'FD' || $style == 'DF') ? 'B' : 'S');
        $MyArc = 4 / 3 * (sqrt(2) - 1);

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

$pdf = new PDF('P', 'mm', [85, 55]); // Alto = 85 mm, Ancho = 55 mm
$pdf->AddPage();

// Márgenes
$margin = 3;
$width = 55;
$height = 85;
$usableWidth = $width - 2 * $margin;

// Fondo
$pdf->SetFillColor(236, 240, 241);
$pdf->RoundedRect($margin, $margin, $usableWidth, $height - 2 * $margin, 3, 'F');

// Logo
$logoPath = 'assets/img/logo_universidad.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, $margin + 1, $margin + 1, 14, 14);
}

// Universidad (centrado)
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(52, 73, 94);
$pdf->SetXY($margin, $margin + 3);
$pdf->Cell($usableWidth, 5, utf8_decode('Universidad Técnica Luis Vargas Torres'), 0, 1, 'C');

// Foto
$fotoW = 20;
$fotoH = 26;
$fotoX = $width - $margin - $fotoW;
$fotoY = $margin + 20;
$fotoPath = isset($vendedor['foto']) ? 'assets/img/vendedores/' . $vendedor['foto'] : '';

if ($fotoPath && file_exists($fotoPath)) {
    $pdf->Image($fotoPath, $fotoX, $fotoY, $fotoW, $fotoH);
} else {
    $pdf->Rect($fotoX, $fotoY, $fotoW, $fotoH);
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->SetTextColor(150);
    $pdf->SetXY($fotoX, $fotoY + $fotoH / 2 - 3);
    $pdf->Cell($fotoW, 6, 'Sin Foto', 0, 0, 'C');
}

// Datos del vendedor (alineados a la izquierda)
$datosX = $margin + 1;
$datosY = $fotoY;
$datosW = $fotoX - $datosX - 2;

$pdf->SetXY($datosX, $datosY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(0);
$pdf->MultiCell($datosW, 5, utf8_decode($vendedor['nombre']), 0, 'L');

$pdf->SetFont('Arial', '', 8);
$pdf->SetX($datosX);
$pdf->Cell($datosW, 4, 'Día: ' . utf8_decode($vendedor['dia']), 0, 1, 'L');
$pdf->SetX($datosX);
$pdf->Cell($datosW, 4, 'Entrada: ' . $vendedor['entrada'], 0, 1, 'L');
$pdf->SetX($datosX);
$pdf->Cell($datosW, 4, 'Salida: ' . $vendedor['salida'], 0, 1, 'L');
$pdf->SetX($datosX);
$pdf->Cell($datosW, 4, 'Producto: ' . utf8_decode($vendedor['producto']), 0, 1, 'L');

// Pie
$pdf->SetFont('Arial', 'I', 7);
$pdf->SetTextColor(120);
$pdf->SetXY($margin, $height - $margin - 5);
$pdf->Cell($usableWidth, 4, 'Carnet válido sólo con sello oficial', 0, 0, 'C');

// Mostrar PDF en el navegador (sin descarga)
$pdf->Output('I', 'Carnet_' . $vendedor['nombre'] . '.pdf');
