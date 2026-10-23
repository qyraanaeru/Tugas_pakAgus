<?php
$host = 'localhost';
$db   = 'data_mahasiswa'; 
$user = 'root';
$pass = '';

try {
    // Membuat koneksi PDO ke MySQL
    $koneksi = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    print "Koneksi database gagal: " . $e->getMessage() . "<br/>";
    die();
}
?>