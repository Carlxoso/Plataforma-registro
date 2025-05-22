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

// Dimensiones carnet tipo ID (horizontal)
$pdfWidth = 85;
$pdfHeight = 54;
$margin = 4;
$usableWidth = $pdfWidth - 2 * $margin;

$pdf = new PDF('L', 'mm', [$pdfHeight, $pdfWidth]); // Horizontal

// ---------------- Página 1: Frontal del Carnet ----------------
$pdf->AddPage();
$pdf->SetFillColor(255, 255, 255);
$pdf->RoundedRect($margin, $margin, $usableWidth, $pdfHeight - 2 * $margin, 3, 'F');

// Logo
$logoPath = 'assets/img/utlvtecarnet.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, $pdfWidth / 2 - 7, $margin + 2, 14);
}

// Foto del vendedor
$fotoPath = isset($vendedor['foto']) ? 'assets/img/vendedores/' . $vendedor['foto'] : '';
if ($fotoPath && file_exists($fotoPath)) {
    $pdf->Image($fotoPath, $pdfWidth / 2 - 10, $margin + 18, 20, 24);
}

// Nombre y Cédula
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(0);
$pdf->SetXY($margin, $margin + 45);
$pdf->Cell($usableWidth, 5, utf8_decode($vendedor['nombre']), 0, 1, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->Cell($usableWidth, 5, 'Cédula: ' . utf8_decode($vendedor['cedula']), 0, 1, 'C');
$pdf->Cell($usableWidth, 5, 'Rol: Vendedor', 0, 1, 'C');

// ---------------- Página 2: Reverso del Carnet ----------------
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(40);
$pdf->SetXY($margin, $margin + 5);
$texto = "Este carnet es válido únicamente dentro de las instalaciones de la Universidad Técnica Luis Vargas Torres.\n\n" .
         "Zona autorizada para comercializar productos: " . utf8_decode($vendedor['zona']) . ".\n\n" .
         "En caso de pérdida, el portador deberá tramitar un nuevo carnet ante las autoridades correspondientes.";
$pdf->MultiCell($usableWidth, 6, utf8_decode($texto), 0, 'J');

$pdf->Output('I', 'Carnet_' . $vendedor['nombre'] . '.pdf');
