<?php
session_start();

// 1. Hapus semua variabel session (Data user yang sedang aktif)
$_SESSION = [];

// 2. Hapus Cookie Session (Tiket Masuk Utama)
// Ini PENTING agar browser benar-benar logout dari sisi keamanan
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// CATATAN: Kita TIDAK menghapus cookie 'user_login'
// Agar saat user kembali ke halaman login, username mereka masih tersimpan.

// 3. Hancurkan session di server
session_destroy();

// 4. Kembali ke halaman Login
header("Location: login.php");
exit;
?>