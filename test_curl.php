<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Gmail Connection using cURL</h2>";

$host = 'smtp.gmail.com';
$ports = [587, 465];

foreach ($ports as $port) {
    echo "<h3>Testing HTTPS connection to {$host}:{$port}</h3>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://{$host}:{$port}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    
    if ($error) {
        echo "<div style='color: red;'>❌ Error: " . $error . "</div>";
    } else {
        echo "<div style='color: green;'>✅ Connection successful</div>";
    }
    
    echo "<pre>";
    print_r($info);
    echo "</pre>";
    
    curl_close($ch);
}

// Also test general internet connectivity
echo "<h3>Testing general internet connectivity</h3>";
$ch = curl_init("https://www.google.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$result = curl_exec($ch);

if (curl_error($ch)) {
    echo "<div style='color: red;'>❌ Cannot connect to Google: " . curl_error($ch) . "</div>";
} else {
    echo "<div style='color: green;'>✅ Successfully connected to Google</div>";
}
curl_close($ch);
?>
