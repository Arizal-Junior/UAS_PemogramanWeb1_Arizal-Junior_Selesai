<?php
session_start();
include '../config/database.php';

// Cek Login Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil ID Admin yang sedang login (Ini adalah "BOS"-nya)
$current_admin_id = $_SESSION['user_id'];

// --- LOGIKA PHP ---

// 1. Tambah User Baru (DENGAN LABEL PEMILIK)
if (isset($_POST['simpan_user'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = 'kasir'; 

    // Cek Username Kembar
    $stmt_cek = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt_cek->bind_param("s", $username);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();

    if ($result_cek->num_rows > 0) {
        $error = "Username '$username' sudah digunakan!";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // PERUBAHAN 1: Kita masukkan juga $current_admin_id ke kolom created_by
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $username, $password_hash, $role, $current_admin_id);
        
        if ($stmt->execute()) {
            header("Location: users.php");
            exit;
        } else {
            $error = "Gagal menyimpan user.";
        }
    }
}

// 2. Hapus User
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    
    // Proteksi tambahan: Pastikan yang dihapus adalah bawahan kita sendiri
    $stmt_del = $conn->prepare("DELETE FROM users WHERE user_id = ? AND created_by = ?");
    $stmt_del->bind_param("ii", $id_hapus, $current_admin_id);
    $stmt_del->execute();
    header("Location: users.php");
    exit;
}

// --- PERBAIKAN UTAMA DISINI ---
// Logika: "Tampilkan User yang DIBUAT OLEH SAYA (created_by = saya)"
// Ditambah: "ATAU Tampilkan SAYA SENDIRI (user_id = saya)"
$stmt_list = $conn->prepare("SELECT * FROM users WHERE created_by = ? OR user_id = ? ORDER BY user_id DESC");
$stmt_list->bind_param("ii", $current_admin_id, $current_admin_id);
$stmt_list->execute();
$result = $stmt_list->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Matchify</title>
    
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

    <div class="d-flex" id="wrapper">
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <div class="brand-wrapper">
                    <div class="brand-icon"><i class="bi bi-cup-hot-fill"></i></div>
                    <span>Matchify</span>
                </div>
                <button class="btn-sidebar-close sidebarToggle"><i class="bi bi-list"></i></button>
            </div>
            
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item"><i class="bi bi-grid"></i> Dashboard</a>
                <a href="categories.php" class="list-group-item"><i class="bi bi-tags"></i> Kategori Menu</a>
                <a href="products.php" class="list-group-item"><i class="bi bi-cup-straw"></i> Data Produk</a>
                <a href="users.php" class="list-group-item active"><i class="bi bi-people"></i> Manajemen User</a>
                <a href="reports.php" class="list-group-item"><i class="bi bi-file-earmark-bar-graph"></i> Laporan Penjualan</a>
                <a href="profile.php" class="list-group-item"><i class="bi bi-person-circle"></i> Profile Saya</a>
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
                    <h2 class="page-title mb-0">Manajemen User</h2>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="user-profile">
                        <div class="avatar"><?php echo substr($_SESSION['username'], 0, 1); ?></div>
                        <span class="small fw-bold pe-2"><?php echo $_SESSION['username']; ?></span>
                    </div>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger d-flex align-items-center mb-4 rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <div class="row">
                
                <div class="col-md-4 mb-4">
                    <div class="content-box h-100">
                        <h5 class="section-head mb-4"><i class="bi bi-person-plus-fill text-success me-2"></i>Buat Akun Kasir</h5>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">USERNAME KARYAWAN</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                    <input type="text" name="username" class="form-control bg-light border-start-0" placeholder="Username kasir" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">PASSWORD</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key"></i></span>
                                    <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="Minimal 6 karakter" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold">PERAN (ROLE)</label>
                                <input type="text" class="form-control bg-light" value="Kasir (Staff)" disabled>
                                <input type="hidden" name="role" value="kasir">
                            </div>

                            <button type="submit" name="simpan_user" class="btn btn-success w-100 py-2 rounded-3 fw-bold shadow-sm">
                                <i class="bi bi-check-circle me-2"></i> Simpan Kasir
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8 mb-4">
                    <div class="content-box h-100">
                        <h5 class="section-head mb-4"><i class="bi bi-people text-primary me-2"></i>Daftar Pengguna Aktif</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3 rounded-start">Username</th>
                                        <th>Role</th>
                                        <th class="text-center rounded-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle text-success fw-bold d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                                        <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                                    </div>
                                                    <span class="fw-bold text-dark"><?php echo $row['username']; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($row['role'] == 'admin'): ?>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 py-2 rounded-pill">Kasir</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($row['user_id'] != $_SESSION['user_id']): ?>
                                                    <a href="?hapus=<?php echo $row['user_id']; ?>" 
                                                       class="btn btn-sm btn-light text-danger shadow-sm rounded-circle" 
                                                       style="width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center;"
                                                       onclick="return confirm('Hapus user ini?')"
                                                       title="Hapus Akun">
                                                         <i class="bi bi-trash-fill"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border">Akun Utama</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada kasir yang Anda buat.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="custom-footer">@Copyright by 23552011310_Arizal Junior_TIF 23 CNS B</footer>
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