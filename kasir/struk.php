<?php
session_start();
include '../config/database.php';

// Cek Login & ID Transaksi
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$trx_id = $_GET['id'];
$current_user_id = $_SESSION['user_id'];

// UPDATE QUERY: Menyesuaikan nama kolom sesuai database (user_id & transaction_id)
$sql_header = "SELECT t.*, u.username 
               FROM transactions t 
               JOIN users u ON t.user_id = u.user_id 
               WHERE t.transaction_id = ?";

$stmt = $conn->prepare($sql_header);
$stmt->bind_param("i", $trx_id);
$stmt->execute();
$result_header = $stmt->get_result();
$trx = $result_header->fetch_assoc();

// Jika data tidak ditemukan
if (!$trx) {
    echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
            <h3>Struk tidak ditemukan!</h3>
            <a href='index.php'>Kembali ke Kasir</a>
          </div>";
    exit;
}

// Ambil Data Detail (Produk) 
// SESUAI DATABASE: Mengubah td.product_id menjadi td.products_id dan p.id menjadi p.products_id
$sql_detail = "SELECT td.*, p.name 
               FROM transaction_details td 
               JOIN products p ON td.products_id = p.products_id 
               WHERE td.transaction_id = ?";
$stmt_d = $conn->prepare($sql_detail);
$stmt_d->bind_param("i", $trx_id);
$stmt_d->execute();
$details = $stmt_d->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #<?php echo $trx_id; ?></title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/struk-style.css">
</head>
<body>

    <div class="receipt-container">
        <div class="header">
            <div class="brand-name">
                <i class="bi bi-cup-hot-fill"></i> Matcha Cafe
            </div>
            <div class="meta-info">
                Jl. Code Kopi No. 123, Bandung<br>
                Kasir: <?php echo ucfirst($trx['username']); ?><br>
                <?php echo date('d/m/Y H:i', strtotime($trx['transaction_date'])); ?>
            </div>
        </div>

        <div class="item-list">
            <?php 
            $subtotal_calc = 0;
            while($item = $details->fetch_assoc()): 
                $subtotal_calc += $item['price'] * $item['qty'];
            ?>
            <div class="item-row">
                <div class="item-name"><?php echo $item['name']; ?></div>
                
                <div class="item-detail">
                    <span><?php echo $item['qty']; ?> x <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                    <span><?php echo number_format($item['price'] * $item['qty'], 0, ',', '.'); ?></span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="summary-section">
            <span>Subtotal</span>
            <span><?php echo number_format($trx['subtotal'], 0, ',', '.'); ?></span>
        </div>
        <div class="summary-section">
            <span>Pajak (10%)</span>
            <span><?php echo number_format($trx['tax'], 0, ',', '.'); ?></span>
        </div>
        
        <div class="summary-section total-row">
            <span>TOTAL</span>
            <span>Rp <?php echo number_format($trx['total_amount'], 0, ',', '.'); ?></span>
        </div>

        <div class="summary-section">
            <span>Tunai</span>
            <span>Rp <?php echo number_format($trx['cash'], 0, ',', '.'); ?></span>
        </div>
        <div class="summary-section">
            <span>Kembali</span>
            <span>Rp <?php echo number_format($trx['change_amount'], 0, ',', '.'); ?></span>
        </div>

        <div class="footer">
            Terima kasih atas kunjungan Anda!<br>
            <i>Password Wifi: matchalovers</i><br>
            <small class="mt-2 d-block">#<?php echo str_pad($trx['transaction_id'], 6, '0', STR_PAD_LEFT); ?></small>
        </div>

        <div class="actions">
            <a href="index.php" class="btn btn-back">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <i class="bi bi-printer"></i> Cetak
            </button>
        </div>
    </div>

    <script>
        // Auto print setelah 500ms
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>

</body>
</html>