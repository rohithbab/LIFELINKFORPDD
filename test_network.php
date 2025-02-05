<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Network Connectivity Test</h2>";

$host = 'smtp.gmail.com';
$ports = [587, 465, 25];

foreach ($ports as $port) {
    echo "<h3>Testing connection to {$host}:{$port}</h3>";
    
    $start = microtime(true);
    $connection = @fsockopen($host, $port, $errno, $errstr, 10);
    $end = microtime(true);
    
    if ($connection) {
        echo "<div style='color: green;'>✅ Successfully connected to {$host}:{$port}<br>";
        echo "Connection time: " . number_format($end - $start, 2) . " seconds</div>";
        fclose($connection);
    } else {
        echo "<div style='color: red;'>❌ Failed to connect to {$host}:{$port}<br>";
        echo "Error {$errno}: {$errstr}</div>";
    }
    echo "<br>";
}

// Test DNS resolution
echo "<h3>Testing DNS Resolution</h3>";
$ip = gethostbyname($host);
if ($ip != $host) {
    echo "<div style='color: green;'>✅ DNS Resolution successful<br>";
    echo "IP Address: {$ip}</div>";
} else {
    echo "<div style='color: red;'>❌ DNS Resolution failed</div>";
}

// Test SSL/TLS availability
echo "<h3>Testing SSL/TLS Support</h3>";
if (function_exists('openssl_get_cert_locations')) {
    echo "✅ OpenSSL is available<br>";
    echo "<pre>";
    print_r(openssl_get_cert_locations());
    echo "</pre>";
} else {
    echo "❌ OpenSSL is not available";
}

// Show PHP's SSL configuration
echo "<h3>PHP SSL Configuration</h3>";
echo "<pre>";
print_r(stream_get_transports());
echo "</pre>";
?>
