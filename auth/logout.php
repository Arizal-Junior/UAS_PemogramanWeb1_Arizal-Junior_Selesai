<?php
session_start();

// 1. Hapus semua variabel session
$_SESSION = [];

// 2. Hancurkan session di server
session_destroy();

// 3. Hapus Cookie 'user_login' (Caranya dengan set waktu ke masa lalu)
if (isset($_COOKIE['user_login'])) {
    setcookie('user_login', '', time() - 3600, "/");
}

// 4. Kembali ke halaman Login
// Karena file ini ada di folder 'auth', kita redirect ke file di sebelahnya (login.php)
header("Location: login.php");
exit;
?>