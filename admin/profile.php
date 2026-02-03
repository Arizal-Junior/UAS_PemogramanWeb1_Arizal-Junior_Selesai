<?php
session_start();
include '../config/database.php';

// Cek Login & Role Admin (Agar Kasir tidak bisa masuk sini)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$msg = "";

// Logic Ganti Password
if (isset($_POST['update_password'])) {
    $user_id = $_SESSION['user_id'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if(strlen($new_pass) < 6) {
        $msg = "<div class='alert alert-warning'>Password minimal 6 karakter.</div>";
    } elseif ($new_pass !== $confirm_pass) {
        $msg = "<div class='alert alert-danger'>Konfirmasi password tidak cocok!</div>";
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        // DISESUAIKAN: Menggunakan user_id sesuai struktur database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hash, $user_id);
        
        if($stmt->execute()) {
            $msg = "<div class='alert alert-success'>Password berhasil diperbarui!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Gagal memperbarui password.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Saya - Matchify</title>
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
                <a href="reports.php" class="list-group-item">
                    <i class="bi bi-file-earmark-bar-graph"></i> Laporan Penjualan
                </a>
                <a href="profile.php" class="list-group-item active">
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
                    <h2 class="page-title mb-0">Profile Saya</h2>
                </div>

                <div class="user-profile">
                    <div class="avatar"><?php echo substr($_SESSION['username'], 0, 1); ?></div>
                    <span class="small fw-bold pe-2"><?php echo $_SESSION['username']; ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="content-box text-center h-100">
                        <div class="mb-4 mt-3">
                            <div style="width: 100px; height: 100px; background: #e8f5e9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #2e7d32; font-size: 3rem; font-weight: bold;">
                                <?php echo substr($_SESSION['username'], 0, 1); ?>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-1"><?php echo $_SESSION['username']; ?></h4>
                        <span class="badge bg-success rounded-pill px-4 py-2 mb-4">
                            <?php echo ucfirst($_SESSION['role']); ?>
                        </span>
                        
                        <div class="border-top pt-4 text-start">
                            <small class="text-muted fw-bold">INFO AKUN</small>
                            <div class="mt-3">
                                <label class="d-block text-muted small">Status</label>
                                <div class="fw-bold text-success"><i class="bi bi-circle-fill" style="font-size: 8px; vertical-align: middle;"></i> Aktif</div>
                            </div>
                            <div class="mt-3">
                                <label class="d-block text-muted small">Terakhir Login</label>
                                <div class="fw-bold"><?php echo date('d M Y'); ?> (Sesi Ini)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8 mb-4">
                    <div class="content-box h-100">
                        <h5 class="section-head mb-4"><i class="bi bi-shield-lock text-primary me-2"></i>Keamanan Akun</h5>
                        
                        <?php echo $msg; ?>

                        <form method="POST">
                            <div class="alert alert-light border mb-4">
                                <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Disarankan untuk mengganti password secara berkala demi keamanan sistem.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">USERNAME</label>
                                <input type="text" class="form-control bg-light" value="<?php echo $_SESSION['username']; ?>" disabled>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-muted">PASSWORD BARU</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                                        <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold small text-muted">ULANGI PASSWORD</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-check-lg"></i></span>
                                        <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi password" required>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" name="update_password" class="btn btn-primary px-4 fw-bold rounded-3">
                                    <i class="bi bi-save me-2"></i> Simpan Password Baru
                                </button>
                            </div>
                        </form>
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