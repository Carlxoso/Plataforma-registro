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
    // Función para dibujar un rectángulo con esquinas redondeadas
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';

        $MyArc = 4/3 * (sqrt(2) - 1);

        $this->_out(sprintf('%.2f %.2f m', ($x+$r)*$k, ($hp-$y)*$k ));

        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_Arc($xc, $yc, $r, 180, 270);

        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_Arc($xc, $yc, $r, 270, 360);

        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_Arc($xc, $yc, $r, 0, 90);

        $xc = $x+$r;
        $yc = $y+$r;
        $this->_Arc($xc, $yc, $r, 90, 180);

        $this->_out($op);
    }

    function _Arc($x, $y, $r, $sAngle, $eAngle) {
        $k = $this->k;
        $hp = $this->h;

        $sAngle /= 360;
        $eAngle /= 360;

        $sAngle *= 2 * M_PI;
        $eAngle *= 2 * M_PI;

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

$pdf = new PDF('P', 'mm', [85, 54]);
$pdf->AddPage();

// Margen fijo para todo el contenido
$margin = 5;
$width = 85;
$height = 54;

// Dibuja el marco redondeado completo (marco visible)
$pdf->SetFillColor(236, 240, 241);
$pdf->RoundedRect($margin, $margin, $width - 2*$margin, $height - 2*$margin, 4, 'F');

// Logo universidad
$logoPath = 'assets/img/escudoutlvte.png';
$logoSize = 18;
if (file_exists($logoPath)) {
    // Coloca logo dentro del margen, arriba a la izquierda
    $pdf->Image($logoPath, $margin + 2, $margin + 2, $logoSize, $logoSize);
}

// Título universidad centrado arriba
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(52, 73, 94);

// Coordenada X para centrar texto dentro del marco (sin contar el margen)
$pdf->SetXY($margin, $margin + 4);
$pdf->Cell($width - 2*$margin, 10, utf8_decode('Universidad Técnica Luis Vargas Torres'), 0, 1, 'C');

// Foto vendedor a la derecha, tamaño 22x28mm, dentro del margen
$fotoWidth = 22;
$fotoHeight = 28;
$fotoX = $width - $margin - $fotoWidth;
$fotoY = $margin + 20;

$fotoPath = '';
if (isset($vendedor['foto']) && !empty($vendedor['foto'])) {
    $fotoPath = 'assets/img/vendedores/' . $vendedor['foto'];
}

if ($fotoPath != '' && file_exists($fotoPath)) {
    $pdf->Image($fotoPath, $fotoX, $fotoY, $fotoWidth, $fotoHeight);
} else {
    // Marco vacío con texto "Sin Foto"
    $pdf->Rect($fotoX, $fotoY, $fotoWidth, $fotoHeight);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(150);
    $pdf->SetXY($fotoX, $fotoY + $fotoHeight / 2 - 4);
    $pdf->Cell($fotoWidth, 8, 'Sin Foto', 0, 0, 'C');
    $pdf->SetTextColor(0);
}

// Datos vendedor a la izquierda, espacio disponible a la izquierda de la foto
$datosX = $margin + 3;
$datosY = $fotoY;
$datosWidth = $fotoX - $datosX - 3;

$pdf->SetXY($datosX, $datosY);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell($datosWidth, 7, utf8_decode($vendedor['nombre']), 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell($datosWidth, 6, 'Dia: ' . utf8_decode($vendedor['dia']), 0, 1);
$pdf->Cell($datosWidth, 6, 'Entrada: ' . $vendedor['entrada'], 0, 1);
$pdf->Cell($datosWidth, 6, 'Salida: ' . $vendedor['salida'], 0, 1);
$pdf->Cell($datosWidth, 6, 'Producto: ' . utf8_decode($vendedor['producto']), 0, 1);

// Pie de página centrado abajo, dentro del margen
$pdf->SetFont('Arial', 'I', 7);
$pdf->SetTextColor(120);
$pdf->SetXY($margin, $height - $margin - 6);
$pdf->Cell($width - 2*$margin, 5, 'Carnet válido sólo con sello oficial', 0, 0, 'C');

$pdf->Output('I', 'Carnet_' . $vendedor['nombre'] . '.pdf');
