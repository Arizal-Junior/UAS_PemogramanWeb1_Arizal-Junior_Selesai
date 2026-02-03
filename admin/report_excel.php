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

// Nama File
$filename = "Laporan_Penjualan_$start-sd-$end.xls";

// Header Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache"); 
header("Expires: 0");

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
<html>
<head>
    <meta charset="utf-8">
    <style>
        <?php include '../assets/css/report_excel.css'; ?>
    </style>
</head>
<body>

    <table border="0" width="100%">
        <tr>
            <td colspan="7" class="header">MATCHIFY CAFE SYSTEM</td>
        </tr>
        <tr>
            <td colspan="7" class="sub-header">LAPORAN PENJUALAN</td>
        </tr>
        <tr>
            <td colspan="7" class="periode-text">Periode: <?php echo date('d F Y', strtotime($start)); ?> s/d <?php echo date('d F Y', strtotime($end)); ?></td>
        </tr>
        <tr><td colspan="7"></td></tr>
    </table>

    <table border="1" width="100%">
        <thead>
            <tr class="table-head">
                <th>No</th>
                <th>No. Transaksi</th>
                <th>Tanggal Waktu</th>
                <th>Kasir</th>
                <th>Subtotal</th>
                <th>Pajak (10%)</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            $grand_total = 0;
            
            if($result->num_rows > 0):
                while($row = $result->fetch_assoc()): 
                    // Logika Hitungan
                    $row_total = $row['total_amount'];
                    $row_tax   = $row['tax'];
                    $row_sub   = $row_total - $row_tax; 

                    $grand_total += $row_total;
                    
                    // FORMAT TANGGAL
                    $formatted_date = date('d/m/Y H:i', strtotime($row['transaction_date']));
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td class="text-left" style='mso-number-format:"\@"'>#<?php echo str_pad($row['transaction_id'], 6, '0', STR_PAD_LEFT); ?></td>
                
                <td class="text-center" style='mso-number-format:"\@"'>
                    <?php echo $formatted_date; ?>
                </td>
                
                <td class="text-left"><?php echo $row['username']; ?></td>
                <td class="text-right"><?php echo $row_sub; ?></td>
                <td class="text-right"><?php echo $row_tax; ?></td>
                <td class="text-right fw-bold"><?php echo $row_total; ?></td>
            </tr>
            <?php endwhile; ?>
            
            <tr class="total-row">
                <td colspan="6">GRAND TOTAL PENDAPATAN</td>
                <td><?php echo $grand_total; ?></td>
            </tr>

            <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">Tidak ada data transaksi pada periode ini.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    
    <table border="0">
        <tr><td colspan="7"></td></tr>
        <tr>
            <td colspan="5"></td>
            <td colspan="2" class="footer-text">Bandung, <?php echo date('d F Y'); ?></td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td colspan="2" class="footer-spacer"></td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td colspan="2" class="footer-text fw-bold">( <?php echo $_SESSION['username']; ?> )</td>
        </tr>
    </table>

</body>
</html>