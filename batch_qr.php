<?php
// File: batch_qr.php
// Generate multiple QR codes at once

require_once 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class BatchQRGenerator {
    
    public static function generateFromCSV($csvFile, $outputDir = 'batch_qrcodes/') {
        if (!file_exists($csvFile)) {
            die("CSV file not found!");
        }
        
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }
        
        $handle = fopen($csvFile, 'r');
        $writer = new PngWriter();
        $count = 0;
        
        echo "<h2>Generating QR Codes...</h2>";
        echo "<ul>";
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 2) {
                $name = $data[0];
                $text = $data[1];
                
                $qrCode = QrCode::create($text)
                    ->setSize(300)
                    ->setMargin(10);
                
                $result = $writer->write($qrCode);
                $filename = $outputDir . preg_replace('/[^a-zA-Z0-9]/', '_', $name) . '.png';
                $result->saveToFile($filename);
                
                echo "<li>Generated: $name <img src='$filename' height='50'></li>";
                $count++;
            }
        }
        
        fclose($handle);
        
        echo "</ul>";
        echo "<h3>Total $count QR codes generated in '$outputDir' directory</h3>";
        echo '<a href="' . $outputDir . '" download="qrcodes.zip">📦 Download All QR Codes</a>';
    }
    
    public static function generateFromArray($dataArray, $outputDir = 'array_qrcodes/') {
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }
        
        $writer = new PngWriter();
        $count = 0;
        
        foreach ($dataArray as $name => $text) {
            $qrCode = QrCode::create($text)
                ->setSize(300)
                ->setMargin(10);
            
            $result = $writer->write($qrCode);
            $filename = $outputDir . preg_replace('/[^a-zA-Z0-9]/', '_', $name) . '.png';
            $result->saveToFile($filename);
            $count++;
        }
        
        return $count;
    }
}

// Example Usage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir);
    
    $csvFile = $uploadDir . basename($_FILES['csv_file']['name']);
    move_uploaded_file($_FILES['csv_file']['tmp_name'], $csvFile);
    
    BatchQRGenerator::generateFromCSV($csvFile);
    exit;
}

// HTML Interface
?>
<!DOCTYPE html>
<html>
<head>
    <title>Batch QR Generator</title>
</head>
<body>
    <h1>Batch QR Code Generator</h1>
    
    <h2>Method 1: Upload CSV File</h2>
    <p>CSV format: Name,Data</p>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv">
        <button type="submit">Generate QR Codes from CSV</button>
    </form>
    
    <h2>Method 2: Manual Entry</h2>
    <div id="entries">
        <div class="entry">
            <input type="text" name="names[]" placeholder="Name">
            <input type="text" name="data[]" placeholder="Data" size="40">
            <button type="button" onclick="addEntry()">+</button>
        </div>
    </div>
    <button onclick="generateManual()">Generate All</button>
    
    <script>
        function addEntry() {
            const div = document.createElement('div');
            div.className = 'entry';
            div.innerHTML = `
                <input type="text" name="names[]" placeholder="Name">
                <input type="text" name="data[]" placeholder="Data" size="40">
                <button type="button" onclick="this.parentElement.remove()">-</button>
            `;
            document.getElementById('entries').appendChild(div);
        }
        
        function generateManual() {
            const entries = document.querySelectorAll('.entry');
            const data = {};
            
            entries.forEach(entry => {
                const nameInput = entry.querySelector('input[name="names[]"]');
                const dataInput = entry.querySelector('input[name="data[]"]');
                if (nameInput.value && dataInput.value) {
                    data[nameInput.value] = dataInput.value;
                }
            });
            
            // Send to server via AJAX
            fetch('batch_generate.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            })
            .then(response => response.text())
            .then(result => {
                document.body.innerHTML += result;
            });
        }
    </script>
    
    <h3>Sample CSV Content:</h3>
    <pre>
Name,Data
Google,https://google.com
Phone,+919876543210
WiFi,WIFI:S:MyNetwork;T:WPA;P:mypassword;;
Email,mailto:example@gmail.com
    </pre>
</body>
</html>
