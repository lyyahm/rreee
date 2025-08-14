<?php
$inputFile  = "33.txt"; // file terenkripsi
$outputFile = "index.php";     // file hasil dekripsi

$password = "password_rahasia_kamu";
$key = hash('sha256', $password, true);

// Baca isi file terenkripsi
$encryptedContent = file_get_contents($inputFile);

// Ambil IV dari 16 byte pertama
$iv = substr($encryptedContent, 0, 16);

// Ambil data terenkripsi
$encryptedData = substr($encryptedContent, 16);

// Dekripsi
$decryptedData = openssl_decrypt($encryptedData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

// Simpan file hasil dekripsi
file_put_contents($outputFile, $decryptedData);

echo "File berhasil didekripsi ke: $outputFile\n";
?>
