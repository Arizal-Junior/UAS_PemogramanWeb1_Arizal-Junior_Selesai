<?php
// config/database.php

$host = "localhost";
$user = "root";     // Default user XAMPP
$pass = "";         // Default password XAMPP (kosong)
$db   = "db_pos_cafe";

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}
?>