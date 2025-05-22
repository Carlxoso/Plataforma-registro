<?php
require('fpdf/fpdf.php');

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="carnet.pdf"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Datos conexión MySQL
$host = "localhost";
$usuario = "root";
$contrasena = "";
$baseDeDatos = "vendetors";

// Conexión
$conn = new mysqli($host, $usuario, $contrasena, $baseDeDatos);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID inválido.");
}

// Consulta a la base de datos sin rol
$sql = "SELECT nombre, cedula, zona FROM vendedores WHERE id = $id LIMIT 1";
$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) {
    die("No se encontró el vendedor con ID = $id");
}

$vendedor = $result->fetch_assoc();

// Dimensiones carnet tipo ID (vertical)
$pdfWidth = 65;  // mm
$pdfHeight = 97; // mm

$pdf = new FPDF('P', 'mm', [$pdfWidth, $pdfHeight]);
$pdf->AddPage();

// ---------------- Logo superior ----------------
$logoPath = 'assets/img/utlvtecarnet.png';
if (file_exists($logoPath)) {
    $logoWidth = 53;
    $logoY = 5;
    $pdf->Image($logoPath, ($pdfWidth - $logoWidth) / 2, $logoY, $logoWidth);
}

// ---------------- Foto con marco ----------------
$fotoPath = 'assets/img/carnetlogo.png';
$fotoW = 22;
$fotoH = 26;
$espacioEntreLogoYFoto = -31;
$fotoY = $logoY + $logoWidth + $espacioEntreLogoYFoto;
$fotoX = ($pdfWidth - $fotoW) / 2;

if (file_exists($fotoPath)) {
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.9);
    $pdf->Rect($fotoX, $fotoY, $fotoW, $fotoH);
    $pdf->SetLineWidth(0.2);
    $pdf->Image($fotoPath, $fotoX, $fotoY, $fotoW, $fotoH);
}

// ---------------- Datos del vendedor ----------------
$espacioEntreFotoYTexto = 5;

// Nombre
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(0, $fotoY + $fotoH + $espacioEntreFotoYTexto);
$pdf->Cell($pdfWidth, 6, utf8_decode($vendedor['nombre']), 0, 1, 'C');

// Cédula
$pdf->SetFont('Arial', '', 10);
$pdf->SetX(0);
$pdf->Cell($pdfWidth, 5, utf8_decode("CI: " . $vendedor['cedula']), 0, 1, 'C');

// Texto fijo "Vendedor"
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetX(0);
$pdf->Cell($pdfWidth, 6, utf8_decode("Vendedor"), 0, 1, 'C');

// ---------------- Posición zona asignada ----------------
$posYZonaAsignada = 80;
$pdf->SetY($posYZonaAsignada);

// Mostrar "Zona asignada:" en negrita y centrado
$textoNegrita = utf8_decode("Zona asignada:");
$pdf->SetFont('Arial', 'B', 9);
$xPos = ($pdfWidth - $pdf->GetStringWidth($textoNegrita)) / 2;
$pdf->SetXY($xPos, $pdf->GetY());
$pdf->Cell($pdf->GetStringWidth($textoNegrita), 5, $textoNegrita, 0, 1, 'C');

// Texto normal de la zona asignada (puede ser largo)
// Lo dividimos en líneas centradas que no sobrepasen el ancho máximo
$textoZona = utf8_decode($vendedor['zona']);
$pdf->SetFont('Arial', '', 9);

$maxWidth = $pdfWidth - 10; // deja margen de 5mm a cada lado
$words = explode(' ', $textoZona);
$line = '';
$yCurrent = $pdf->GetY();

foreach ($words as $word) {
    $testLine = $line ? $line . ' ' . $word : $word;
    if ($pdf->GetStringWidth($testLine) > $maxWidth) {
        // imprimir línea centrada
        $lineWidth = $pdf->GetStringWidth($line);
        $xPos = ($pdfWidth - $lineWidth) / 2;
        $pdf->SetXY($xPos, $yCurrent);
        $pdf->Cell($lineWidth, 5, $line, 0, 1, 'C');
        $line = $word;
        $yCurrent += 5;
    } else {
        $line = $testLine;
    }
}
// Imprime la última línea
if ($line) {
    $lineWidth = $pdf->GetStringWidth($line);
    $xPos = ($pdfWidth - $lineWidth) / 2;
    $pdf->SetXY($xPos, $yCurrent);
    $pdf->Cell($lineWidth, 5, $line, 0, 1, 'C');
    $pdf->Ln(4);
}


// ---------------- Mensaje pérdida ----------------
$textoNegrita = utf8_decode("En caso de pérdida,");
$pdf->SetFont('Arial', 'B', 9);
$xPos = ($pdfWidth - $pdf->GetStringWidth($textoNegrita)) / 2;
$pdf->SetXY($xPos, $pdf->GetY());
$pdf->Cell($pdf->GetStringWidth($textoNegrita), 5, $textoNegrita, 0, 1, 'C');

$textoNormal = utf8_decode("el portador debe tramitar un nuevo carnet en las oficinas correspondientes para continuar con su autorización de venta.");
$pdf->SetFont('Arial', '', 9);

$maxWidth = $pdfWidth - 10;
$words = explode(' ', $textoNormal);
$line = '';
$yCurrent = $pdf->GetY();

foreach ($words as $word) {
    $testLine = $line ? $line . ' ' . $word : $word;
    if ($pdf->GetStringWidth($testLine) > $maxWidth) {
        $lineWidth = $pdf->GetStringWidth($line);
        $xPos = ($pdfWidth - $lineWidth) / 2;
        $pdf->SetXY($xPos, $yCurrent);
        $pdf->Cell($lineWidth, 5, $line, 0, 1, 'C');
        $line = $word;
        $yCurrent += 5;
    } else {
        $line = $testLine;
    }
}
if ($line) {
    $lineWidth = $pdf->GetStringWidth($line);
    $xPos = ($pdfWidth - $lineWidth) / 2;
    $pdf->SetXY($xPos, $yCurrent);
    $pdf->Cell($lineWidth, 5, $line, 0, 1, 'C');
}

// ---------------- Aviso restricción ----------------
$avisoRestriccion = utf8_decode("Este carnet es válido únicamente dentro de las instalaciones autorizadas.");

$pdf->SetY($pdf->GetY() + 7);
$pdf->SetFont('Arial', 'B', 9);

$maxWidth = $pdfWidth - 10;
$words = explode(' ', $avisoRestriccion);
$line = '';
$yCurrent = $pdf->GetY();

foreach ($words as $word) {
    $testLine = $line ? $line . ' ' . $word : $word;
    if ($pdf->GetStringWidth($testLine) > $maxWidth) {
        $lineWidth = $pdf->GetStringWidth($line);
        $xPos = ($pdfWidth - $lineWidth) / 2;
        $pdf->SetXY($xPos, $yCurrent);
        $pdf->Cell($lineWidth, 5, $line, 0, 1, 'C');
        $line = $word;
        $yCurrent += 5;
    } else {
        $line = $testLine;
    }
}
if ($line) {
    $lineWidth = $pdf->GetStringWidth($line);
    $xPos = ($pdfWidth - $lineWidth) / 2;
    $pdf->SetXY($xPos, $yCurrent);
    $pdf->Cell($lineWidth, 5, $line, 0, 1, 'C');
}

// ---------------- Franja verde inferior ----------------
$pdf->SetFillColor(0, 128, 0);
$franjaAltura = 6;
$franjaY = $pdfHeight - $franjaAltura;
$pdf->Rect(0, $franjaY, $pdfWidth, $franjaAltura, 'F');



$pdf->Output();
?>
