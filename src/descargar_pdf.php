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

// Consulta a la base de datos para obtener datos del vendedor y su foto
$sql = "SELECT v.nombre, v.cedula, v.zona, v.producto, u.foto_perfil 
        FROM vendedores v 
        INNER JOIN usuregistro u ON v.cedula = u.cedula 
        WHERE v.id = $id 
        LIMIT 1";
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
$fotoPath = $vendedor['foto_perfil']; // Esto debería contener la ruta como: 'uploads/fotos/12345678.jpg'
$fotoW = 22;
$fotoH = 26;
$espacioEntreLogoYFoto = -31;
$fotoY = $logoY + $logoWidth + $espacioEntreLogoYFoto;
$fotoX = ($pdfWidth - $fotoW) / 2;

if (!empty($fotoPath) && file_exists($fotoPath)) {
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.9);
    $pdf->Rect($fotoX, $fotoY, $fotoW, $fotoH);
    $pdf->SetLineWidth(0.2);
    $pdf->Image($fotoPath, $fotoX, $fotoY, $fotoW, $fotoH);
} else {
    // Imagen por defecto si no se encuentra la foto personalizada
    $fotoDefault = 'assets/img/carnetlogo.png';
    if (file_exists($fotoDefault)) {
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.9);
        $pdf->Rect($fotoX, $fotoY, $fotoW, $fotoH);
        $pdf->SetLineWidth(0.2);
        $pdf->Image($fotoDefault, $fotoX, $fotoY, $fotoW, $fotoH);
    }
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

// ---------------- Producto que vende ----------------
$textoProductoTitulo = utf8_decode("Producto autorizado:");
$pdf->SetFont('Arial', 'B', 9);
$xPos = ($pdfWidth - $pdf->GetStringWidth($textoProductoTitulo)) / 2;
$pdf->SetXY($xPos, $pdf->GetY());
$pdf->Cell($pdf->GetStringWidth($textoProductoTitulo), 5, $textoProductoTitulo, 0, 1, 'C');

// Producto del vendedor
$productoTexto = utf8_decode($vendedor['producto']);
$pdf->SetFont('Arial', '', 9);
$maxWidth = $pdfWidth - 10;
$words = explode(' ', $productoTexto);
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
    $pdf->Ln(3);
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

// ---------------- Franja verde inferior ----------------
$pdf->SetFillColor(0, 128, 0);
$franjaAltura = 6;
$franjaY = $pdfHeight - $franjaAltura;
$pdf->Rect(0, $franjaY, $pdfWidth, $franjaAltura, 'F');



$pdf->Output();
?>
