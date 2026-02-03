<?php
session_start();
include '../config/database.php';

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// AMBIL ID ADMIN UNTUK FILTER
$current_admin_id = $_SESSION['user_id'];

// Ambil parameter tanggal
$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
$end   = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// LOGIKA BARU (ANTI BOCOR)
$query = "SELECT t.*, u.username 
          FROM transactions t 
          JOIN users u ON t.user_id = u.user_id 
          WHERE (t.user_id = ? OR u.created_by = ?) 
          AND DATE(t.transaction_date) BETWEEN ? AND ?
          ORDER BY t.transaction_date ASC";

$stmt = $conn->prepare($query);
// Bind: i (id admin), i (id admin/creator), s (tanggal start), s (tanggal end)
$stmt->bind_param("iiss", $current_admin_id, $current_admin_id, $start, $end);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Matchify</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="../assets/css/report_print.css">
</head>
<body>

    <div class="action-bar">
        <button onclick="window.history.back()" class="btn btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </button>
        <button onclick="window.print()" class="btn btn-print">
            <i class="bi bi-printer-fill"></i> Cetak Laporan
        </button>
    </div>
    
    <div class="report-content">
        <div class="header">
            <h1>MATCHIFY CAFE SYSTEM</h1>
            <p>Jl. Code Kopi No. 123, Bandung | Telp: (022) 12345678</p>
            <span class="sub-title">LAPORAN PENJUALAN</span>
            <p>Periode: <strong><?php echo date('d F Y', strtotime($start)); ?></strong> s/d <strong><?php echo date('d F Y', strtotime($end)); ?></strong></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Pajak (10%)</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                $grand_total = 0;
                
                if($result->num_rows > 0):
                    while($row = $result->fetch_assoc()): 
                        // Hitung Subtotal Manual
                        $row_total = $row['total_amount'];
                        $row_tax   = $row['tax'];
                        $row_sub   = $row_total - $row_tax;

                        $grand_total += $row_total;
                ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td>#<?php echo str_pad($row['transaction_id'], 6, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['transaction_date'])); ?></td>
                    <td><?php echo ucfirst($row['username']); ?></td>
                    
                    <td class="text-end">Rp <?php echo number_format($row_sub, 0, ',', '.'); ?></td>
                    <td class="text-end">Rp <?php echo number_format($row_tax, 0, ',', '.'); ?></td>
                    <td class="text-end fw-bold">Rp <?php echo number_format($row_total, 0, ',', '.'); ?></td>
                </tr>
                <?php endwhile; 
                else: ?>
                    <tr><td colspan="7" class="text-center" style="padding: 20px;">Data tidak ditemukan pada periode ini.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" class="text-end">GRAND TOTAL PENDAPATAN</td>
                    <td class="text-end">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer-sign">
            <p>Bandung, <?php echo date('d F Y'); ?></p>
            <br>
            <span class="sign-name">( <?php echo $_SESSION['username']; ?> )</span>
        </div>
    </div>

</body>
</html>