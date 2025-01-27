<?php
echo "<h2>Setting up PHPMailer</h2>";

// Directory to extract PHPMailer
$phpmailer_dir = __DIR__ . '/backend/php/PHPMailer';

// Create directory if it doesn't exist
if (!file_exists($phpmailer_dir)) {
    mkdir($phpmailer_dir, 0777, true);
}

// Download PHPMailer
$zip_url = 'https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.8.1.zip';
$zip_file = __DIR__ . '/phpmailer.zip';

echo "Downloading PHPMailer...<br>";
if (file_put_contents($zip_file, file_get_contents($zip_url))) {
    echo "Download complete.<br>";
    
    // Extract ZIP file
    $zip = new ZipArchive;
    if ($zip->open($zip_file) === TRUE) {
        echo "Extracting files...<br>";
        $zip->extractTo(__DIR__ . '/temp_phpmailer/');
        $zip->close();
        
        // Copy required files
        $source_dir = __DIR__ . '/temp_phpmailer/PHPMailer-6.8.1/src/';
        if (file_exists($source_dir)) {
            // Copy files
            copy($source_dir . 'PHPMailer.php', $phpmailer_dir . '/PHPMailer.php');
            copy($source_dir . 'SMTP.php', $phpmailer_dir . '/SMTP.php');
            copy($source_dir . 'Exception.php', $phpmailer_dir . '/Exception.php');
            
            echo "Files installed successfully!<br>";
            
            // Clean up
            array_map('unlink', glob(__DIR__ . '/temp_phpmailer/*'));
            rmdir(__DIR__ . '/temp_phpmailer');
            unlink($zip_file);
            
            echo "<p style='color: green'>✅ PHPMailer setup complete!</p>";
            echo "<p>Now you can try sending emails again.</p>";
        } else {
            echo "<p style='color: red'>❌ Error: Source directory not found!</p>";
        }
    } else {
        echo "<p style='color: red'>❌ Error: Could not extract ZIP file!</p>";
    }
} else {
    echo "<p style='color: red'>❌ Error: Could not download PHPMailer!</p>";
}
?>
