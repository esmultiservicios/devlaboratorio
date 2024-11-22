<?php
include('phpqrcode/qrlib.php');

// Carpeta donde se guardarán los códigos QR
$tempDir = "qrcodes/";

$codeContents = 'This Goes From File';
$fileName = '005_file_'.md5($codeContents).'.png';
$pngAbsoluteFilePath = $tempDir.$fileName;
$urlRelativeFilePath = $tempDir.$fileName;

// Genera el código QR
if (!file_exists($pngAbsoluteFilePath)) {
    QRcode::png($codeContents, $pngAbsoluteFilePath);
    echo 'File generated!';
    echo '<hr />';
} else {
    echo 'File already generated! We can use this cached file to speed up site on common codes!';
    echo '<hr />';
}

// Abre el código QR generado
$codeQR = imagecreatefrompng($pngAbsoluteFilePath);

// Abre el logotipo
$logo = imagecreatefrompng('logo_qr.png');

// Obtén las dimensiones del código QR y el logotipo
$codeQRWidth = imagesx($codeQR);
$codeQRHeight = imagesy($codeQR);
$logoWidth = imagesx($logo);
$logoHeight = imagesy($logo);

// Calcula la posición para el logotipo en el código QR (centrado)
$logoX = ($codeQRWidth - $logoWidth) / 2;
$logoY = ($codeQRHeight - $logoHeight) / 2;

// Combina el código QR y el logotipo
imagecopy($codeQR, $logo, $logoX, $logoY, 0, 0, $logoWidth, $logoHeight);

// Guarda el código QR con el logotipo
imagepng($codeQR, $pngAbsoluteFilePath);

// Muestra el código QR con el logotipo
echo 'Server PNG File: '.$pngAbsoluteFilePath;
echo '<hr />';
echo '<img src="'.$urlRelativeFilePath.'" />';

?>