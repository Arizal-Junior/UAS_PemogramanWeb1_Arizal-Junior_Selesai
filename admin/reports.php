<?php
session_start();
include '../config/database.php';

// Atur Zona Waktu
date_default_timezone_set('Asia/Jakarta');

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$current_admin_id = $_SESSION['user_id'];

// Default Tanggal
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
$end_date   = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// --- PERBAIKAN LOGIKA DISINI (ANTI BOCOR) ---

// 1. Query Summary (Total Omset & Jumlah Transaksi)
// Kita JOIN ke tabel users untuk cek 'created_by'
$sql_summary = "SELECT SUM(t.total_amount) as omset, COUNT(t.transaction_id) as total_trx 
                FROM transactions t
                JOIN users u ON t.user_id = u.user_id
                WHERE (t.user_id = ? OR u.created_by = ?) 
                AND DATE(t.transaction_date) BETWEEN ? AND ?";

$stmt_sum = $conn->prepare($sql_summary);
$stmt_sum->bind_param("iiss", $current_admin_id, $current_admin_id, $start_date, $end_date);
$stmt_sum->execute();
$summary = $stmt_sum->get_result()->fetch_assoc();

// 2. Query Data Transaksi untuk Tabel Preview
// Sama, kita filter berdasarkan kepemilikan user
$sql_data = "SELECT t.*, u.username 
             FROM transactions t 
             JOIN users u ON t.user_id = u.user_id 
             WHERE (t.user_id = ? OR u.created_by = ?) 
             AND DATE(t.transaction_date) BETWEEN ? AND ? 
             ORDER BY t.transaction_date DESC";

$stmt_data = $conn->prepare($sql_data);
$stmt_data->bind_param("iiss", $current_admin_id, $current_admin_id, $start_date, $end_date);
$stmt_data->execute();
$result = $stmt_data->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Matchify</title>
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/reports.css">
</head>
<body>

    <div class="d-flex" id="wrapper">

        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <div class="brand-wrapper">
                    <div class="brand-icon">
                        <i class="bi bi-cup-hot-fill"></i>
                    </div>
                    <span>Matchify</span>
                </div>
                <button class="btn-sidebar-close sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item">
                    <i class="bi bi-grid"></i> Dashboard
                </a>
                <a href="categories.php" class="list-group-item">
                    <i class="bi bi-tags"></i> Kategori Menu
                </a>
                <a href="products.php" class="list-group-item">
                    <i class="bi bi-cup-straw"></i> Data Produk
                </a>
                <a href="users.php" class="list-group-item">
                    <i class="bi bi-people"></i> Manajemen User
                </a>
                <a href="reports.php" class="list-group-item active">
                    <i class="bi bi-file-earmark-bar-graph"></i> Laporan Penjualan
                </a>
                <a href="profile.php" class="list-group-item">
                    <i class="bi bi-person-circle"></i> Profile Saya
                </a>
            </div>

            <div class="sidebar-footer">
                <a href="../auth/logout.php" class="list-group-item text-danger fw-bold" style="border: 1px solid #ffebee; border-radius: 12px; justify-content: center;">
                    <i class="bi bi-box-arrow-left text-danger"></i> Logout
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            
            <div class="top-navbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light shadow-sm border-0 sidebarToggle" id="sidebarToggleTop">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <h2 class="page-title mb-0">Laporan Penjualan</h2>
                </div>

                <div class="user-profile">
                    <div class="avatar"><?php echo substr($_SESSION['username'], 0, 1); ?></div>
                    <span class="small fw-bold pe-2"><?php echo $_SESSION['username']; ?></span>
                </div>
            </div>

            <div class="content-box mb-4 filter-section">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small">DARI TANGGAL</label>
                        <input type="date" name="start" class="form-control bg-light" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small">SAMPAI TANGGAL</label>
                        <input type="date" name="end" class="form-control bg-light" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-search me-2"></i> Tampilkan</button>
                    </div>
                </form>
            </div>

            <div class="row mb-4 summary-section gx-3">
                <div class="col-md-8">
                    <div class="row gx-3">
                        <div class="col-md-6">
                            <div class="card-box bg-green-gradient h-100 d-flex flex-column justify-content-center">
                                <h6 class="opacity-75">Total Pendapatan</h6>
                                <h2 class="fw-bold mb-0">Rp <?php echo number_format($summary['omset'] ?? 0, 0, ',', '.'); ?></h2>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-box bg-blue-gradient h-100 d-flex flex-column justify-content-center">
                                <h6 class="opacity-75">Total Transaksi</h6>
                                <h2 class="fw-bold mb-0"><?php echo $summary['total_trx'] ?? 0; ?> <span class="fs-6 fw-normal">Transaksi</span></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="content-box h-100 d-flex flex-column justify-content-center">
                        <h6 class="fw-bold mb-3 text-muted">Export Laporan</h6>
                        <div class="d-flex gap-2">
                            <a href="report_print.php?start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>" class="btn btn-outline-danger w-100 fw-bold">
                                <i class="bi bi-file-pdf-fill me-2"></i> PDF
                            </a>
                            <a href="report_excel.php?start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>" class="btn btn-outline-success w-100 fw-bold">
                                <i class="bi bi-file-earmark-excel-fill me-2"></i> Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-box table-container-box">
                <h5 class="section-head mb-3 flex-shrink-0"><i class="bi bi-table me-2 text-primary"></i>Rincian Transaksi</h5>
                
                <div class="table-responsive table-scroll-area">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3">No</th>
                                <th>Tanggal & Jam</th>
                                <th>Kasir</th>
                                <th>Subtotal</th>
                                <th>Pajak</th>
                                <th class="text-end pe-3">Total Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0): ?>
                                <?php $no=1; while($row = $result->fetch_assoc()): 
                                    $subtotal = $row['total_amount'] - $row['tax'];
                                ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?php echo $no++; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo date('d M Y', strtotime($row['transaction_date'])); ?></div>
                                        <small class="text-muted"><?php echo date('H:i', strtotime($row['transaction_date'])); ?> WIB</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo ucfirst($row['username']); ?></span></td>
                                    <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                    <td>Rp <?php echo number_format($row['tax'], 0, ',', '.'); ?></td>
                                    <td class="text-end pe-3 fw-bold text-success">Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data transaksi pada periode ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <footer class="custom-footer">
                @Copyright by 23552011310_Arizal Junior_TIF 23 CNS B
            </footer>

        </div>
    </div>

    <script src="../bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <script>
    window.addEventListener('DOMContentLoaded', event => {
        const toggleButtons = document.querySelectorAll('.sidebarToggle');
        toggleButtons.forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();
                document.body.classList.toggle('sb-sidenav-toggled');
            });
        });
    });
    </script>
</body>
</html>