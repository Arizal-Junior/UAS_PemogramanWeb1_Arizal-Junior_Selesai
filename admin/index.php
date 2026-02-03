<?php
session_start();
include '../config/database.php';

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil ID User (Admin) yang sedang login
$current_user_id = $_SESSION['user_id'];

// --- HITUNG STATISTIK REAL-TIME ---

// 1. Hitung Kategori
$stmt_cat = $conn->prepare("SELECT COUNT(*) as total FROM categories WHERE user_id = ?");
$stmt_cat->bind_param("i", $current_user_id);
$stmt_cat->execute();
$countKategori = $stmt_cat->get_result()->fetch_assoc()['total'];

// 2. Hitung Produk
$stmt_prod = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE user_id = ? AND is_active = 1");
$stmt_prod->bind_param("i", $current_user_id);
$stmt_prod->execute();
$countProduk = $stmt_prod->get_result()->fetch_assoc()['total'];

// 3. Hitung User (Kasir + Admin) - PERBAIKAN DISINI
// Logika Baru: "Hitung user yang DIBUAT OLEH SAYA (created_by = saya) ATAU SAYA SENDIRI (user_id = saya)"
$stmt_user = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE created_by = ? OR user_id = ?");
$stmt_user->bind_param("ii", $current_user_id, $current_user_id);
$stmt_user->execute();
$countUser = $stmt_user->get_result()->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Matchify</title>
    
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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
                <a href="index.php" class="list-group-item active">
                    <i class="bi bi-grid-fill"></i> Dashboard
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
                <a href="reports.php" class="list-group-item">
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
                    <h2 class="page-title mb-0">Dashboard</h2>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <div class="user-profile">
                        <div class="avatar">
                            <?php echo substr($_SESSION['username'], 0, 1); ?>
                        </div>
                        <span class="small fw-bold pe-2"><?php echo $_SESSION['username']; ?></span>
                    </div>
                </div>
            </div>

            <div class="dashboard-cards">
                <div class="card-box bg-green-gradient">
                    <div>
                        <h4 class="fw-bold">Kategori</h4>
                        <p class="mb-0 opacity-75"><?php echo $countKategori; ?> Kategori</p>
                    </div>
                    <i class="bi bi-tags-fill card-icon-large"></i>
                    <a href="categories.php" class="btn-circle-arrow"><i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="card-box bg-pink-gradient">
                    <div>
                        <h4 class="fw-bold">Produk / Menu</h4>
                        <p class="mb-0 opacity-75"><?php echo $countProduk; ?> Menu Tersedia</p>
                    </div>
                    <i class="bi bi-cup-straw card-icon-large"></i>
                    <a href="products.php" class="btn-circle-arrow"><i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="card-box bg-blue-gradient">
                    <div>
                        <h4 class="fw-bold">Kasir / User</h4>
                        <p class="mb-0 opacity-75"><?php echo $countUser; ?> Akun Aktif</p>
                    </div>
                    <i class="bi bi-people-fill card-icon-large"></i>
                    <a href="users.php" class="btn-circle-arrow"><i class="bi bi-arrow-right"></i></a>
                </div>
            </div>

            <div class="bottom-section">
                <div class="content-box">
                    <h5 class="section-head">Aktivitas Sistem</h5>
                    
                    <div class="activity-item">
                        <div class="d-flex align-items-center">
                            <div class="icon-box ic-green"><i class="bi bi-check-lg"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Sistem Online</h6>
                                <small class="text-muted">Database terhubung dengan baik.</small>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Normal</span>
                    </div>

                    <div class="activity-item">
                        <div class="d-flex align-items-center">
                            <div class="icon-box ic-pink"><i class="bi bi-box-seam"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Cek Stok</h6>
                                <small class="text-muted">Jangan lupa update stok barang.</small>
                            </div>
                        </div>
                        <a href="products.php" class="btn btn-sm btn-light rounded-pill px-3">Lihat</a>
                    </div>
                </div>

                <div class="content-box text-center d-flex flex-column justify-content-center align-items-center">
                    <h6 class="text-muted mb-3"><?php echo date('F Y'); ?></h6>
                    <h1 class="display-4 fw-bold text-success mb-0"><?php echo date('d'); ?></h1>
                    <p class="text-muted fw-bold"><?php echo date('l'); ?></p>
                    <div class="mt-3 text-muted small">
                        "Semangat kerja, <?php echo ucfirst($_SESSION['username']); ?>!"
                    </div>
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