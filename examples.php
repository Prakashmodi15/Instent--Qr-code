<?php
// File: examples.php
// Various examples of QR code generation

require_once 'vendor/autoload.php';
require_once 'qr_generator.php';

echo "<h1>QR Code Examples</h1>";

// Example 1: Simple Text QR
echo "<h2>1. Simple Text QR</h2>";
$qr1 = AdvancedQRGenerator::quickGenerate("Hello World!", 200);
echo '<img src="' . $qr1->getBase64() . '" alt="Text QR">';

// Example 2: URL QR with Logo
echo "<h2>2. URL QR with Logo</h2>";
$qr2 = new AdvancedQRGenerator();
$qr2->setData("https://github.com")
    ->setSize(250)
    ->addLogo('logo.png', 40) // Make sure logo.png exists
    ->setColors([0, 0, 255], [255, 255, 255]);
echo '<img src="' . $qr2->getBase64() . '" alt="URL QR">';

// Example 3: WhatsApp QR
echo "<h2>3. WhatsApp QR Code</h2>";
$qr3 = QRHelper::forWhatsApp("+919876543210", "Hello, I'm interested!");
echo '<img src="' . $qr3->getBase64() . '" alt="WhatsApp QR">';

// Example 4: WiFi QR
echo "<h2>4. WiFi QR Code</h2>";
$qr4 = QRHelper::forWiFi("MyHomeWiFi", "SecurePass123", "WPA");
echo '<img src="' . $qr4->getBase64() . '" alt="WiFi QR">';

// Example 5: Email QR
echo "<h2>5. Email QR Code</h2>";
$qr5 = QRHelper::forEmail("contact@example.com", "Inquiry", "Hello, I have a question...");
echo '<img src="' . $qr5->getBase64() . '" alt="Email QR">';

// Example 6: vCard QR
echo "<h2>6. Contact Card QR</h2>";
$qr6 = QRHelper::forContact("John Doe", "+919876543210", "john@example.com", "ABC Corp");
echo '<img src="' . $qr6->getBase64() . '" alt="Contact QR">';

// Example 7: SMS QR
echo "<h2>7. SMS QR Code</h2>";
$qr7 = QRHelper::forSMS("+919876543210", "Please call me back");
echo '<img src="' . $qr7->getBase64() . '" alt="SMS QR">';

// Example 8: Colored QR
echo "<h2>8. Custom Colored QR</h2>";
$qr8 = new AdvancedQRGenerator();
$qr8->setData("Custom Color QR")
    ->setSize(200)
    ->setColors([255, 0, 0], [255, 255, 200]); // Red on light yellow
echo '<img src="' . $qr8->getBase64() . '" alt="Colored QR">';

echo "<hr><h3>Usage Code:</h3>";
echo '<pre>
// Simple usage:
$qr = new AdvancedQRGenerator();
$qr->setData("Your data here")
   ->setSize(300)
   ->addLogo("path/to/logo.png")
   ->saveToFile("output.png");

// OR use helper classes:
$qr = QRHelper::forURL("https://example.com");
$qr->display();

// Get as base64 for HTML:
echo \'&lt;img src="\' . $qr->getBase64() . \'"&gt;\';
</pre>';
