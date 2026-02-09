<?php
// File: quick_qr.php
// Simple QR Code Generator for quick use

require_once 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Logo\Logo;

function generateQuickQR($data, $filename = null, $logo = null) {
    $writer = new PngWriter();
    
    // Create QR code
    $qrCode = QrCode::create($data)
        ->setSize(300)
        ->setMargin(10)
        ->setForegroundColor(new Color(0, 0, 0))
        ->setBackgroundColor(new Color(255, 255, 255));
    
    // Add logo if provided
    if ($logo && file_exists($logo)) {
        $logo = Logo::create($logo)->setResizeToWidth(50);
        $result = $writer->write($qrCode, $logo);
    } else {
        $result = $writer->write($qrCode);
    }
    
    // Save to file or display
    if ($filename) {
        $result->saveToFile($filename);
        echo "QR Code saved as: $filename\n";
        echo '<img src="' . $filename . '" alt="QR Code">';
    } else {
        header('Content-Type: ' . $result->getMimeType());
        echo $result->getString();
    }
}

// Example Usage:
if (isset($_GET['text'])) {
    generateQuickQR($_GET['text'], 'qrcode.png');
    exit;
}

// HTML Form for Quick QR
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick QR Generator</title>
</head>
<body>
    <h2>Quick QR Code Generator</h2>
    <form method="GET">
        <textarea name="text" placeholder="Enter text, URL, or any data" rows="4" cols="50"></textarea><br><br>
        <button type="submit">Generate QR</button>
    </form>
    
    <h3>Examples:</h3>
    <ul>
        <li><a href="?text=https://google.com">Google URL QR</a></li>
        <li><a href="?text=Hello World">Hello World Text QR</a></li>
        <li><a href="?text=+919876543210">Phone Number QR</a></li>
        <li><a href="?text=WIFI:S:MyWiFi;T:WPA;P:mypassword123;;">WiFi QR</a></li>
    </ul>
</body>
</html>
