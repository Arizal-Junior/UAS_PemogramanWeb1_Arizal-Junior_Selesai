<?php
session_start();
include '../config/database.php';

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kasir') {
    die("Akses Ditolak");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_data'])) {
    
    // 1. Ambil Data dari Frontend
    $user_id = $_SESSION['user_id'];
    $cash = $_POST['cash']; // Uang tunai dari inputan
    $cart = json_decode($_POST['cart_data'], true); // Ubah JSON jadi Array PHP
    
    // Hitung ulang total di backend (Demi keamanan, jangan percaya 100% data frontend)
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += ($item['price'] * $item['qty']);
    }
    
    $tax = $subtotal * 0.10; // Pajak 10%
    $total_amount = $subtotal + $tax;
    $kembalian = $cash - $total_amount;

    // Validasi Uang
    if ($cash < $total_amount) {
        echo "<script>alert('Uang tunai kurang!'); window.history.back();</script>";
        exit;
    }

    // 2. Simpan ke Tabel TRANSACTIONS (Header)
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, subtotal, tax, total_amount, cash, change_amount) VALUES (?, ?, ?, ?, ?, ?)");
    // Note: Kita perlu tambah kolom 'cash' dan 'change_amount' di tabel transactions agar struk lengkap
    // Tapi untuk sekarang kita simpan data dasar dulu, atau kita alter tabelnya nanti.
    // Asumsi tabel sesuai desain awal:
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, subtotal, tax, total_amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iddd", $user_id, $subtotal, $tax, $total_amount);
    
    if ($stmt->execute()) {
        $transaction_id = $conn->insert_id; // Ambil ID Transaksi yang baru saja dibuat

        // 3. Simpan Detail & Kurangi Stok
        $stmt_detail = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
        $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($cart as $item) {
            // Insert Detail
            $stmt_detail->bind_param("iiid", $transaction_id, $item['id'], $item['qty'], $item['price']);
            $stmt_detail->execute();

            // Update Stok (Kurangi)
            $stmt_stock->bind_param("ii", $item['qty'], $item['id']);
            $stmt_stock->execute();
        }

        // 4. Redirect ke Halaman Struk
        // Kita kirim nominal tunai lewat URL agar bisa dicetak di struk
        header("Location: struk.php?id=$transaction_id&cash=$cash");
        exit;

    } else {
        echo "Gagal menyimpan transaksi: " . $conn->error;
    }

} else {
    header("Location: index.php");
}
?>