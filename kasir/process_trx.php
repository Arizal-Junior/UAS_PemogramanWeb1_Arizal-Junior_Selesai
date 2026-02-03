<?php
session_start();
include '../config/database.php';

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kasir') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil Data dari Form & Session
    $user_id = $_SESSION['user_id'];
    
    $cart_json = $_POST['cart_data'];
    $total_amount = $_POST['total_amount']; // Total sudah termasuk pajak
    $cash = $_POST['cash'];
    $change_amount = $cash - $total_amount;
    
    // Decode JSON Cart
    $cart_items = json_decode($cart_json, true);

    if (empty($cart_items)) {
        header("Location: index.php");
        exit;
    }

    // 2. Hitung Pajak & Subtotal (Validasi Backend)
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }
    $tax = $subtotal * 0.10;
    
    // 3. Database Transaction
    $conn->begin_transaction();

    try {
        // A. Simpan ke Tabel Transactions
        // Sesuai gambar DB: user_id, transaction_date, subtotal, tax, total_amount, cash, change_amount
        // transaction_date diisi otomatis oleh NOW()
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, transaction_date, subtotal, tax, total_amount, cash, change_amount) VALUES (?, NOW(), ?, ?, ?, ?, ?)");
        
        // Perbaikan: Bind 6 variabel sesuai jumlah '?' (NOW() tidak di-bind)
        // String tipe data: "iddddd" (1 Integer, 5 Double)
        $stmt->bind_param("iddddd", $user_id, $subtotal, $tax, $total_amount, $cash, $change_amount);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan transaksi: " . $stmt->error);
        }
        
        // Ambil ID transaksi yang baru masuk
        $transaction_id = $conn->insert_id; 

        // B. Simpan Detail & Kurangi Stok
        // Kolom di transaction_details: transaction_id, products_id, qty, price
        $stmt_detail = $conn->prepare("INSERT INTO transaction_details (transaction_id, products_id, qty, price) VALUES (?, ?, ?, ?)");
        
        // Kolom PK di products: products_id
        $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE products_id = ?");

        foreach ($cart_items as $item) {
            // Simpan detail (iiid: int, int, int, double)
            $stmt_detail->bind_param("iiid", $transaction_id, $item['id'], $item['qty'], $item['price']);
            $stmt_detail->execute();

            // Kurangi stok (ii: int, int)
            $stmt_stock->bind_param("ii", $item['qty'], $item['id']);
            $stmt_stock->execute();
        }

        // C. Commit jika semua berhasil
        $conn->commit();

        // 4. Redirect ke Halaman Struk
        header("Location: struk.php?id=" . $transaction_id);
        exit;

    } catch (Exception $e) {
        // Batalkan perubahan jika ada error
        $conn->rollback();
        echo "Terjadi Kesalahan: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
?>