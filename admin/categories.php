<?php
session_start();
include '../config/database.php';

// Cek apakah user sudah login dan role-nya ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// --- LOGIKA PHP (CRUD) - TETAP SAMA ---

// 1. Handle Tambah Kategori
if (isset($_POST['simpan_kategori'])) {
    $nama = $_POST['nama_kategori'];
    if(!empty($nama)){
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        header("Location: categories.php"); 
        exit;
    }
}

// 2. Handle Hapus Kategori
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: categories.php");
    exit;
}

// 3. Handle Edit Kategori (Update)
if (isset($_POST['update_kategori'])) {
    $id = $_POST['id_kategori'];
    $nama = $_POST['nama_kategori'];
    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $nama, $id);
    $stmt->execute();
    header("Location: categories.php");
    exit;
}

// Ambil semua data kategori
$result = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Matcha Cafe</title>
    
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

    <div class="d-flex" id="wrapper">

        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <div class="brand-icon">
                    <i class="bi bi-cup-hot-fill"></i>
                </div>
                <span>Matcha Cafe</span>
            </div>
            
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>
                <a href="categories.php" class="list-group-item active">
                    <i class="bi bi-tags-fill"></i> Kategori Menu
                </a>
                <a href="products.php" class="list-group-item">
                    <i class="bi bi-cup-straw"></i> Data Produk
                </a>
                <a href="users.php" class="list-group-item">
                    <i class="bi bi-people"></i> Manajemen User
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
                <h2 class="page-title">Kategori Menu</h2>
                <div class="d-flex align-items-center gap-3">
                    <div class="user-profile">
                        <div class="avatar">
                            <?php echo substr($_SESSION['username'], 0, 1); ?>
                        </div>
                        <span class="small fw-bold pe-2"><?php echo $_SESSION['username']; ?></span>
                    </div>
                </div>
            </div>

            <div class="row">
                
                <div class="col-md-4 mb-4">
                    <div class="content-box h-100">
                        <h5 class="section-head mb-4"><i class="bi bi-plus-circle text-success me-2"></i>Tambah Baru</h5>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">NAMA KATEGORI</label>
                                <input type="text" name="nama_kategori" class="form-control py-2" 
                                       placeholder="Contoh: Coffee, Non-Coffee" required 
                                       style="background: #f8f9fa; border: 1px solid #ebedf2;">
                            </div>
                            <button type="submit" name="simpan_kategori" class="btn btn-success w-100 py-2 rounded-3 fw-bold">
                                <i class="bi bi-save me-2"></i> Simpan
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8 mb-4">
                    <div class="content-box h-100">
                        <h5 class="section-head mb-4"><i class="bi bi-list-ul text-primary me-2"></i>Daftar Kategori</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50" class="text-center rounded-start">No</th>
                                        <th>Nama Kategori</th>
                                        <th width="100" class="text-center rounded-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($result->num_rows > 0): ?>
                                        <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="text-center fw-bold text-muted"><?php echo $no++; ?></td>
                                            <td>
                                                <form method="POST" class="d-flex gap-2">
                                                    <input type="hidden" name="id_kategori" value="<?php echo $row['id']; ?>">
                                                    <input type="text" name="nama_kategori" 
                                                           class="form-control form-control-sm border-0 bg-transparent fw-bold text-dark" 
                                                           value="<?php echo $row['name']; ?>"
                                                           onfocus="this.style.background='#fff'; this.style.border='1px solid #ced4da';"
                                                           onblur="this.style.background='transparent'; this.style.border='none';">
                                                    
                                                    <button type="submit" name="update_kategori" class="btn btn-sm btn-light text-success shadow-sm" title="Update">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                <a href="?hapus=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-light text-danger shadow-sm rounded-circle" 
                                                   style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"
                                                   onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                                                   title="Hapus">
                                                    <i class="bi bi-trash-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                                Belum ada kategori data.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block mt-3 fst-italic">* Klik pada nama kategori untuk mengedit, lalu tekan tombol centang.</small>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script src="../bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>