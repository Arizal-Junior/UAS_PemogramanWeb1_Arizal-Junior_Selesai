<?php
session_start();
include '../config/database.php';

// Pastikan ada ID transaksi
if (!isset($_GET['id'])) {
    die("ID Transaksi tidak ditemukan");
}

$id_trx = $_GET['id'];
$cash_in = isset($_GET['cash']) ? $_GET['cash'] : 0; // Uang Tunai dari URL

// Ambil Data Transaksi + Nama Kasir
$query_header = "SELECT t.*, u.username 
                 FROM transactions t 
                 JOIN users u ON t.user_id = u.id 
                 WHERE t.id = $id_trx";
$result_header = $conn->query($query_header);
$trx = $result_header->fetch_assoc();

if (!$trx) {
    die("Transaksi tidak ditemukan");
}

// Ambil Detail Barang
$query_detail = "SELECT td.*, p.name 
                 FROM transaction_details td 
                 JOIN products p ON td.product_id = p.id 
                 WHERE td.transaction_id = $id_trx";
$result_detail = $conn->query($query_detail);

// Hitung Kembalian
$kembalian = $cash_in - $trx['total_amount'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk #<?php echo $id_trx; ?></title>
    <style>
        /* CSS Khusus Cetak Struk */
        body {
            font-family: 'Courier New', Courier, monospace; /* Font mirip mesin kasir */
            font-size: 12px;
            width: 300px; /* Lebar kertas thermal 80mm standar */
            margin: 0 auto;
            padding: 10px;
            color: #000;
        }
        .header, .footer { text-align: center; margin-bottom: 10px; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .flex { display: flex; justify-content: space-between; }
        .bold { font-weight: bold; }
        
        /* Sembunyikan tombol saat diprint */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()"> <div class="header">
        <h3 style="margin:0;">🍵 CAFE POS</h3>
        <p style="margin:0;">Jl. Kenangan Indah No. 24</p>
        <p style="margin:0;">Telp: 0812-3456-7890</p>
    </div>

    <div class="divider"></div>

    <div>
        No Transaksi : #<?php echo $trx['id']; ?><br>
        Tanggal      : <?php echo $trx['transaction_date']; ?><br>
        Kasir        : <?php echo strtoupper($trx['username']); ?>
    </div>

    <div class="divider"></div>

    <table style="width: 100%; text-align: left;">
        <?php while($item = $result_detail->fetch_assoc()): ?>
        <tr>
            <td colspan="2"><?php echo $item['name']; ?></td>
        </tr>
        <tr>
            <td><?php echo $item['qty']; ?> x <?php echo number_format($item['price']); ?></td>
            <td style="text-align: right;"><?php echo number_format($item['qty'] * $item['price']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="divider"></div>

    <div class="flex">
        <span>Subtotal</span>
        <span>Rp <?php echo number_format($trx['subtotal']); ?></span>
    </div>
    <div class="flex">
        <span>Pajak (10%)</span>
        <span>Rp <?php echo number_format($trx['tax']); ?></span>
    </div>
    <div class="flex bold" style="font-size: 14px; margin-top: 5px;">
        <span>TOTAL</span>
        <span>Rp <?php echo number_format($trx['total_amount']); ?></span>
    </div>

    <div class="divider"></div>

    <div class="flex">
        <span>Tunai</span>
        <span>Rp <?php echo number_format($cash_in); ?></span>
    </div>
    <div class="flex">
        <span>Kembali</span>
        <span>Rp <?php echo number_format($kembalian); ?></span>
    </div>

    <div class="footer" style="margin-top: 20px;">
        <p>Terima Kasih atas Kunjungan Anda<br>Simpan struk ini sebagai bukti pembayaran</p>
        <p>-- Layanan Konsumen --<br>IG: @cafepos.matcha</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <a href="index.php" style="background: #000; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px;">Transaksi Baru</a>
    </div>

</body>
</html>