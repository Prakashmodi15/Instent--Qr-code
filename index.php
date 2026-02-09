<?php
// QR Code Generator - Complete Solution
// File: qr_generator.php

// Error reporting on for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if required libraries are installed
function checkDependencies() {
    if (!class_exists('Endroid\QrCode\QrCode')) {
        die("Error: Endroid QR Code library not installed. Run: composer require endroid/qr-code");
    }
    if (!class_exists('BaconQrCode\Renderer\ImageRenderer')) {
        die("Error: Bacon QR Code library not installed. Run: composer require bacon/bacon-qr-code");
    }
}

checkDependencies();

// Include required classes
require_once 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Logo\Logo;

class AdvancedQRGenerator {
    private $qrCode;
    private $writer;
    private $data;
    private $size = 300;
    private $margin = 10;
    private $foregroundColor = [0, 0, 0]; // Black
    private $backgroundColor = [255, 255, 255]; // White
    private $errorCorrection = 'high'; // low, medium, quartile, high
    private $format = 'png'; // png, svg, jpg
    private $logoPath = null;
    private $logoSize = 50;
    
    public function __construct() {
        $this->writer = new PngWriter();
    }
    
    // Set data for QR code
    public function setData($data) {
        $this->data = $data;
        return $this;
    }
    
    // Set size
    public function setSize($size) {
        $this->size = $size;
        return $this;
    }
    
    // Set margin
    public function setMargin($margin) {
        $this->margin = $margin;
        return $this;
    }
    
    // Set colors (RGB format)
    public function setColors($foreground, $background) {
        $this->foregroundColor = $foreground;
        $this->backgroundColor = $background;
        return $this;
    }
    
    // Set error correction level
    public function setErrorCorrection($level) {
        $this->errorCorrection = $level;
        return $this;
    }
    
    // Set output format
    public function setFormat($format) {
        $this->format = $format;
        return $this;
    }
    
    // Add logo to QR code
    public function addLogo($logoPath, $size = 50) {
        $this->logoPath = $logoPath;
        $this->logoSize = $size;
        return $this;
    }
    
    // Generate QR code
    public function generate() {
        if (empty($this->data)) {
            throw new Exception("QR Code data is required!");
        }
        
        // Create QR Code
        $qrCode = QrCode::create($this->data)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel($this->getErrorCorrectionLevel())
            ->setSize($this->size)
            ->setMargin($this->margin)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(
                $this->foregroundColor[0],
                $this->foregroundColor[1],
                $this->foregroundColor[2]
            ))
            ->setBackgroundColor(new Color(
                $this->backgroundColor[0],
                $this->backgroundColor[1],
                $this->backgroundColor[2]
            ));
        
        // Add logo if provided
        if ($this->logoPath && file_exists($this->logoPath)) {
            $logo = Logo::create($this->logoPath)
                ->setResizeToWidth($this->logoSize);
            
            $result = $this->writer->write($qrCode, $logo);
        } else {
            $result = $this->writer->write($qrCode);
        }
        
        return $result;
    }
    
    // Save QR code to file
    public function saveToFile($filename) {
        $result = $this->generate();
        $result->saveToFile($filename);
        return $filename;
    }
    
    // Display QR code in browser
    public function display() {
        $result = $this->generate();
        header('Content-Type: ' . $result->getMimeType());
        echo $result->getString();
    }
    
    // Get QR code as base64 for embedding in HTML
    public function getBase64() {
        $result = $this->generate();
        return 'data:' . $result->getMimeType() . ';base64,' . base64_encode($result->getString());
    }
    
    // Helper function to get error correction level
    private function getErrorCorrectionLevel() {
        $levels = [
            'low' => ErrorCorrectionLevel::Low,
            'medium' => ErrorCorrectionLevel::Medium,
            'quartile' => ErrorCorrectionLevel::Quartile,
            'high' => ErrorCorrectionLevel::High
        ];
        
        return $levels[$this->errorCorrection] ?? ErrorCorrectionLevel::High;
    }
    
    // Static method for quick generation
    public static function quickGenerate($data, $size = 300, $logo = null) {
        $generator = new self();
        $generator->setData($data)
                  ->setSize($size);
        
        if ($logo) {
            $generator->addLogo($logo);
        }
        
        return $generator;
    }
}

// Helper functions for common use cases
class QRHelper {
    
    // Generate URL QR Code
    public static function forURL($url, $size = 300) {
        $generator = new AdvancedQRGenerator();
        return $generator->setData($url)
                         ->setSize($size)
                         ->setColors([0, 0, 255], [255, 255, 255]); // Blue QR
    }
    
    // Generate Text QR Code
    public static function forText($text, $size = 300) {
        $generator = new AdvancedQRGenerator();
        return $generator->setData($text)
                         ->setSize($size)
                         ->setColors([0, 0, 0], [240, 240, 240]); // Light gray background
    }
    
    // Generate WhatsApp QR Code
    public static function forWhatsApp($number, $message = "", $size = 300) {
        $url = "https://wa.me/$number" . ($message ? "?text=" . urlencode($message) : "");
        $generator = new AdvancedQRGenerator();
        return $generator->setData($url)
                         ->setSize($size)
                         ->setColors([37, 211, 102], [255, 255, 255]); // WhatsApp green
    }
    
    // Generate Email QR Code
    public static function forEmail($email, $subject = "", $body = "", $size = 300) {
        $data = "mailto:$email";
        if ($subject || $body) {
            $data .= "?";
            if ($subject) $data .= "subject=" . urlencode($subject);
            if ($body) $data .= ($subject ? "&" : "") . "body=" . urlencode($body);
        }
        
        $generator = new AdvancedQRGenerator();
        return $generator->setData($data)
                         ->setSize($size)
                         ->setColors([234, 67, 53], [255, 255, 255]); // Red color
    }
    
    // Generate WiFi QR Code
    public static function forWiFi($ssid, $password, $encryption = 'WPA', $size = 300) {
        $data = "WIFI:S:$ssid;T:$encryption;P:$password;;";
        $generator = new AdvancedQRGenerator();
        return $generator->setData($data)
                         ->setSize($size)
                         ->setColors([0, 150, 0], [255, 255, 255]); // Green for WiFi
    }
    
    // Generate Phone Number QR Code
    public static function forPhone($number, $size = 300) {
        $data = "tel:$number";
        $generator = new AdvancedQRGenerator();
        return $generator->setData($data)
                         ->setSize($size)
                         ->setColors([0, 100, 200], [255, 255, 255]); // Blue color
    }
    
    // Generate SMS QR Code
    public static function forSMS($number, $message = "", $size = 300) {
        $data = "sms:$number" . ($message ? "?body=" . urlencode($message) : "");
        $generator = new AdvancedQRGenerator();
        return $generator->setData($data)
                         ->setSize($size)
                         ->setColors([255, 193, 7], [255, 255, 255]); // Yellow color
    }
    
    // Generate Contact Card (vCard) QR Code
    public static function forContact($name, $phone, $email, $company = "", $size = 300) {
        $data = "BEGIN:VCARD\n";
        $data .= "VERSION:3.0\n";
        $data .= "FN:$name\n";
        $data .= "TEL:$phone\n";
        $data .= "EMAIL:$email\n";
        if ($company) $data .= "ORG:$company\n";
        $data .= "END:VCARD";
        
        $generator = new AdvancedQRGenerator();
        return $generator->setData($data)
                         ->setSize($size)
                         ->setColors([128, 0, 128], [255, 255, 255]); // Purple color
    }
}

// HTML Interface Class
class QRWebInterface {
    
    public static function showForm() {
        $html = '
        <!DOCTYPE html>
        <html lang="hi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Advanced QR Code Generator</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                    font-family: "Segoe UI", Arial, sans-serif;
                }
                
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    padding: 20px;
                }
                
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    overflow: hidden;
                }
                
                .header {
                    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                
                .header h1 {
                    font-size: 2.5rem;
                    margin-bottom: 10px;
                }
                
                .header p {
                    font-size: 1.1rem;
                    opacity: 0.9;
                }
                
                .content {
                    display: flex;
                    flex-wrap: wrap;
                    padding: 30px;
                }
                
                .form-section {
                    flex: 1;
                    min-width: 300px;
                    padding: 20px;
                }
                
                .preview-section {
                    flex: 1;
                    min-width: 300px;
                    padding: 20px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                }
                
                .form-group {
                    margin-bottom: 20px;
                }
                
                label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 600;
                    color: #333;
                    font-size: 1rem;
                }
                
                input, textarea, select {
                    width: 100%;
                    padding: 12px 15px;
                    border: 2px solid #e0e0e0;
                    border-radius: 10px;
                    font-size: 1rem;
                    transition: all 0.3s;
                }
                
                input:focus, textarea:focus, select:focus {
                    outline: none;
                    border-color: #4facfe;
                    box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.2);
                }
                
                textarea {
                    min-height: 100px;
                    resize: vertical;
                }
                
                .btn {
                    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
                    color: white;
                    border: none;
                    padding: 15px 30px;
                    font-size: 1rem;
                    font-weight: 600;
                    border-radius: 10px;
                    cursor: pointer;
                    transition: all 0.3s;
                    width: 100%;
                    margin-top: 10px;
                }
                
                .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
                }
                
                .btn-secondary {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                
                .qr-preview {
                    width: 250px;
                    height: 250px;
                    border: 2px dashed #4facfe;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-bottom: 20px;
                    padding: 20px;
                    background: #f8f9fa;
                }
                
                .qr-preview img {
                    max-width: 100%;
                    max-height: 100%;
                }
                
                .download-btn {
                    display: inline-block;
                    background: #28a745;
                    color: white;
                    padding: 12px 25px;
                    text-decoration: none;
                    border-radius: 10px;
                    font-weight: 600;
                    margin-top: 15px;
                    transition: all 0.3s;
                }
                
                .download-btn:hover {
                    background: #218838;
                    transform: translateY(-2px);
                }
                
                .type-buttons {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    margin-bottom: 20px;
                }
                
                .type-btn {
                    flex: 1;
                    min-width: 120px;
                    padding: 12px;
                    background: #f8f9fa;
                    border: 2px solid #e0e0e0;
                    border-radius: 10px;
                    cursor: pointer;
                    font-weight: 600;
                    text-align: center;
                    transition: all 0.3s;
                }
                
                .type-btn.active {
                    background: #4facfe;
                    color: white;
                    border-color: #4facfe;
                }
                
                .color-picker {
                    display: flex;
                    gap: 10px;
                }
                
                .color-box {
                    width: 40px;
                    height: 40px;
                    border-radius: 8px;
                    cursor: pointer;
                    border: 3px solid transparent;
                }
                
                .color-box.active {
                    border-color: #333;
                }
                
                .logo-upload {
                    border: 2px dashed #4facfe;
                    border-radius: 10px;
                    padding: 20px;
                    text-align: center;
                    cursor: pointer;
                    transition: all 0.3s;
                }
                
                .logo-upload:hover {
                    background: #f8f9fa;
                }
                
                .alert {
                    padding: 15px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                }
                
                .alert-success {
                    background: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }
                
                .alert-error {
                    background: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
                }
                
                @media (max-width: 768px) {
                    .content {
                        flex-direction: column;
                    }
                    
                    .header h1 {
                        font-size: 2rem;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🚀 Advanced QR Code Generator</h1>
                    <p>किसी भी टेक्स्ट, नंबर, URL, या डाटा के लिए QR Code बनाएं</p>
                </div>
                
                <div class="content">';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $html .= self::processRequest();
        }
        
        $html .= '
                    <div class="form-section">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="type-buttons">
                                <div class="type-btn active" data-type="text">📝 टेक्स्ट</div>
                                <div class="type-btn" data-type="url">🔗 URL</div>
                                <div class="type-btn" data-type="whatsapp">💬 WhatsApp</div>
                                <div class="type-btn" data-type="email">📧 ईमेल</div>
                                <div class="type-btn" data-type="wifi">📶 WiFi</div>
                                <div class="type-btn" data-type="contact">👤 कॉन्टैक्ट</div>
                            </div>
                            
                            <div class="form-group" id="text-section">
                                <label for="text">टेक्स्ट डालें:</label>
                                <textarea id="text" name="text" placeholder="यहाँ टेक्स्ट लिखें..."></textarea>
                            </div>
                            
                            <div class="form-group" id="url-section" style="display:none;">
                                <label for="url">वेबसाइट URL:</label>
                                <input type="url" id="url" name="url" placeholder="https://example.com">
                            </div>
                            
                            <div class="form-group" id="whatsapp-section" style="display:none;">
                                <label for="whatsapp_number">WhatsApp नंबर (देश कोड के साथ):</label>
                                <input type="tel" id="whatsapp_number" name="whatsapp_number" placeholder="+919876543210">
                                <label for="whatsapp_message" style="margin-top:10px;">संदेश (वैकल्पिक):</label>
                                <textarea id="whatsapp_message" name="whatsapp_message" placeholder="हैलो..."></textarea>
                            </div>
                            
                            <div class="form-group" id="email-section" style="display:none;">
                                <label for="email_address">ईमेल पता:</label>
                                <input type="email" id="email_address" name="email_address" placeholder="example@gmail.com">
                                <label for="email_subject" style="margin-top:10px;">विषय (वैकल्पिक):</label>
                                <input type="text" id="email_subject" name="email_subject" placeholder="विषय">
                                <label for="email_body" style="margin-top:10px;">संदेश (वैकल्पिक):</label>
                                <textarea id="email_body" name="email_body" placeholder="ईमेल का विवरण..."></textarea>
                            </div>
                            
                            <div class="form-group" id="wifi-section" style="display:none;">
                                <label for="wifi_ssid">WiFi नेटवर्क नाम (SSID):</label>
                                <input type="text" id="wifi_ssid" name="wifi_ssid" placeholder="MyWiFi">
                                <label for="wifi_password" style="margin-top:10px;">पासवर्ड:</label>
                                <input type="text" id="wifi_password" name="wifi_password" placeholder="mypassword123">
                                <label for="wifi_encryption" style="margin-top:10px;">एन्क्रिप्शन:</label>
                                <select id="wifi_encryption" name="wifi_encryption">
                                    <option value="WPA">WPA/WPA2</option>
                                    <option value="WEP">WEP</option>
                                    <option value="">कोई नहीं</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="contact-section" style="display:none;">
                                <label for="contact_name">नाम:</label>
                                <input type="text" id="contact_name" name="contact_name" placeholder="आपका नाम">
                                <label for="contact_phone" style="margin-top:10px;">फोन नंबर:</label>
                                <input type="tel" id="contact_phone" name="contact_phone" placeholder="+919876543210">
                                <label for="contact_email" style="margin-top:10px;">ईमेल:</label>
                                <input type="email" id="contact_email" name="contact_email" placeholder="example@gmail.com">
                                <label for="contact_company" style="margin-top:10px;">कंपनी (वैकल्पिक):</label>
                                <input type="text" id="contact_company" name="contact_company" placeholder="कंपनी का नाम">
                            </div>
                            
                            <input type="hidden" id="qr_type" name="qr_type" value="text">
                            
                            <div class="form-group">
                                <label>QR साइज़:</label>
                                <select name="size">
                                    <option value="200">200x200 (छोटा)</option>
                                    <option value="300" selected>300x300 (मध्यम)</option>
                                    <option value="400">400x400 (बड़ा)</option>
                                    <option value="500">500x500 (बहुत बड़ा)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>रंग चुनें:</label>
                                <div class="color-picker">
                                    <div class="color-box active" style="background:#000000;" data-color="000000"></div>
                                    <div class="color-box" style="background:#2196F3;" data-color="2196F3"></div>
                                    <div class="color-box" style="background:#4CAF50;" data-color="4CAF50"></div>
                                    <div class="color-box" style="background:#FF5722;" data-color="FF5722"></div>
                                    <div class="color-box" style="background:#9C27B0;" data-color="9C27B0"></div>
                                    <div class="color-box" style="background:#000000;" data-color="custom">कस्टम</div>
                                </div>
                                <input type="hidden" id="qr_color" name="qr_color" value="000000">
                            </div>
                            
                            <div class="form-group">
                                <label>लोगो जोड़ें (वैकल्पिक):</label>
                                <div class="logo-upload" onclick="document.getElementById(\'logo_file\').click()">
                                    <input type="file" id="logo_file" name="logo_file" accept="image/*" style="display:none;">
                                    <p>📁 लोगो अपलोड करने के लिए क्लिक करें</p>
                                    <small>PNG, JPG, SVG (मैक्स 2MB)</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>एरर करेक्शन:</label>
                                <select name="error_correction">
                                    <option value="low">कम (7%)</option>
                                    <option value="medium">मध्यम (15%)</option>
                                    <option value="quartile">क्वार्टाइल (25%)</option>
                                    <option value="high" selected>उच्च (30%)</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn">✅ QR कोड जेनरेट करें</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">🔄 रीसेट करें</button>
                        </form>
                    </div>
                    
                    <div class="preview-section">
                        <h3>QR कोड प्रिव्यू</h3>
                        <div class="qr-preview" id="qrPreview">
                            <p>फॉर्म भरें और QR कोड देखें</p>
                        </div>
                        <div id="downloadSection"></div>
                    </div>
                </div>
            </div>
            
            <script>
                // Tab switching
                document.querySelectorAll(".type-btn").forEach(btn => {
                    btn.addEventListener("click", function() {
                        // Remove active class from all buttons
                        document.querySelectorAll(".type-btn").forEach(b => b.classList.remove("active"));
                        
                        // Add active class to clicked button
                        this.classList.add("active");
                        
                        // Get data type
                        const type = this.getAttribute("data-type");
                        document.getElementById("qr_type").value = type;
                        
                        // Hide all sections
                        document.querySelectorAll("[id$=\'-section\']").forEach(section => {
                            section.style.display = "none";
                        });
                        
                        // Show selected section
                        document.getElementById(type + "-section").style.display = "block";
                    });
                });
                
                // Color picker
                document.querySelectorAll(".color-box").forEach(box => {
                    box.addEventListener("click", function() {
                        document.querySelectorAll(".color-box").forEach(b => b.classList.remove("active"));
                        this.classList.add("active");
                        
                        const color = this.getAttribute("data-color");
                        document.getElementById("qr_color").value = color;
                    });
                });
                
                // File upload preview
                document.getElementById("logo_file").addEventListener("change", function(e) {
                    if (this.files && this.files[0]) {
                        const fileName = this.files[0].name;
                        const uploadDiv = this.closest(".logo-upload");
                        uploadDiv.innerHTML = `<p>✅ ${fileName}</p><small>लोगो सेलेक्ट किया गया</small>`;
                    }
                });
                
                // Form reset
                window.resetForm = function() {
                    document.querySelector("form").reset();
                    document.querySelectorAll(".type-btn").forEach((btn, index) => {
                        btn.classList.remove("active");
                        if (index === 0) btn.classList.add("active");
                    });
                    document.querySelectorAll("[id$=\'-section\']").forEach(section => {
                        section.style.display = "none";
                    });
                    document.getElementById("text-section").style.display = "block";
                    document.getElementById("qrPreview").innerHTML = "<p>फॉर्म भरें और QR कोड देखें</p>";
                    document.getElementById("downloadSection").innerHTML = "";
                    
                    // Reset color picker
                    document.querySelectorAll(".color-box").forEach(box => {
                        box.classList.remove("active");
                        if (box.getAttribute("data-color") === "000000") {
                            box.classList.add("active");
                        }
                    });
                    document.getElementById("qr_color").value = "000000";
                    
                    // Reset logo upload
                    const uploadDiv = document.querySelector(".logo-upload");
                    uploadDiv.innerHTML = `
                        <input type="file" id="logo_file" name="logo_file" accept="image/*" style="display:none;">
                        <p>📁 लोगो अपलोड करने के लिए क्लिक करें</p>
                        <small>PNG, JPG, SVG (मैक्स 2MB)</small>
                    `;
                    document.getElementById("logo_file").addEventListener("change", arguments.callee);
                };
                
                // Form submission with AJAX
                document.querySelector("form").addEventListener("submit", async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    try {
                        const response = await fetch("", {
                            method: "POST",
                            body: formData
                        });
                        
                        const result = await response.text();
                        
                        // Extract QR code image from response
                        const tempDiv = document.createElement("div");
                        tempDiv.innerHTML = result;
                        
                        const qrImage = tempDiv.querySelector(".generated-qr");
                        if (qrImage) {
                            document.getElementById("qrPreview").innerHTML = qrImage.outerHTML;
                            
                            const downloadBtn = tempDiv.querySelector(".download-btn");
                            if (downloadBtn) {
                                document.getElementById("downloadSection").innerHTML = downloadBtn.outerHTML;
                            }
                        }
                    } catch (error) {
                        alert("Error: " + error.message);
                    }
                });
            </script>
        </body>
        </html>';
        
        return $html;
    }
    
    private static function processRequest() {
        try {
            $data = "";
            $type = $_POST['qr_type'] ?? 'text';
            
            switch ($type) {
                case 'text':
                    $data = $_POST['text'] ?? '';
                    break;
                case 'url':
                    $data = $_POST['url'] ?? '';
                    break;
                case 'whatsapp':
                    $number = $_POST['whatsapp_number'] ?? '';
                    $message = $_POST['whatsapp_message'] ?? '';
                    $data = "https://wa.me/" . preg_replace('/[^0-9+]/', '', $number);
                    if ($message) {
                        $data .= "?text=" . urlencode($message);
                    }
                    break;
                case 'email':
                    $email = $_POST['email_address'] ?? '';
                    $subject = $_POST['email_subject'] ?? '';
                    $body = $_POST['email_body'] ?? '';
                    $data = "mailto:$email";
                    if ($subject || $body) {
                        $data .= "?";
                        $params = [];
                        if ($subject) $params[] = "subject=" . urlencode($subject);
                        if ($body) $params[] = "body=" . urlencode($body);
                        $data .= implode("&", $params);
                    }
                    break;
                case 'wifi':
                    $ssid = $_POST['wifi_ssid'] ?? '';
                    $password = $_POST['wifi_password'] ?? '';
                    $encryption = $_POST['wifi_encryption'] ?? 'WPA';
                    $data = "WIFI:S:$ssid;T:$encryption;P:$password;;";
                    break;
                case 'contact':
                    $name = $_POST['contact_name'] ?? '';
                    $phone = $_POST['contact_phone'] ?? '';
                    $email = $_POST['contact_email'] ?? '';
                    $company = $_POST['contact_company'] ?? '';
                    $data = "BEGIN:VCARD\n";
                    $data .= "VERSION:3.0\n";
                    $data .= "FN:$name\n";
                    $data .= "TEL:$phone\n";
                    $data .= "EMAIL:$email\n";
                    if ($company) $data .= "ORG:$company\n";
                    $data .= "END:VCARD";
                    break;
            }
            
            if (empty($data)) {
                throw new Exception("कृपया डाटा डालें!");
            }
            
            // Generate QR code
            $generator = new AdvancedQRGenerator();
            
            // Set data
            $generator->setData($data);
            
            // Set size
            $size = $_POST['size'] ?? 300;
            $generator->setSize($size);
            
            // Set colors
            $colorHex = $_POST['qr_color'] ?? '000000';
            $color = self::hexToRgb($colorHex);
            $generator->setColors($color, [255, 255, 255]);
            
            // Set error correction
            $errorCorrection = $_POST['error_correction'] ?? 'high';
            $generator->setErrorCorrection($errorCorrection);
            
            // Add logo if uploaded
            if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] == UPLOAD_ERR_OK) {
                $logoPath = 'uploads/' . uniqid() . '_' . $_FILES['logo_file']['name'];
                move_uploaded_file($_FILES['logo_file']['tmp_name'], $logoPath);
                $generator->addLogo($logoPath, 60);
            }
            
            // Generate and save QR code
            $filename = 'qrcodes/qrcode_' . time() . '.png';
            $generator->saveToFile($filename);
            
            // Return HTML with QR code and download link
            $qrUrl = $filename;
            $base64 = $generator->getBase64();
            
            return '
            <div class="alert alert-success">
                ✅ QR कोड सफलतापूर्वक जेनरेट हो गया!
            </div>
            <div class="preview-section">
                <h3>आपका QR कोड</h3>
                <div class="qr-preview">
                    <img src="' . $base64 . '" alt="Generated QR Code" class="generated-qr">
                </div>
                <a href="' . $qrUrl . '" download="my_qrcode.png" class="download-btn">📥 QR कोड डाउनलोड करें</a>
            </div>';
            
        } catch (Exception $e) {
            return '
            <div class="alert alert-error">
                ❌ त्रुटि: ' . $e->getMessage() . '
            </div>';
        }
    }
    
    private static function hexToRgb($hex) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return [$r, $g, $b];
    }
}

// Create necessary directories
if (!is_dir('uploads')) mkdir('uploads', 0777, true);
if (!is_dir('qrcodes')) mkdir('qrcodes', 0777, true);

// Display the web interface
echo QRWebInterface::showForm();
