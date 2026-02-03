<?php
session_start();
include '../config/database.php';

// Cek Login Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$current_user_id = $_SESSION['user_id'];

// --- LOGIKA PHP ---

// 1. Tambah Produk
if (isset($_POST['simpan_produk'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $gambar = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];
    
    if (!empty($gambar)) {
        $nama_baru = time() . "_" . $gambar;
        $path_upload = "../assets/images/" . $nama_baru;
        move_uploaded_file($tmp_name, $path_upload);
    } else {
        $nama_baru = "default.png";
    }

    $stmt = $conn->prepare("INSERT INTO products (category_id, name, price, stock, image, user_id, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("isdisi", $category_id, $name, $price, $stock, $nama_baru, $current_user_id);
    
    if ($stmt->execute()) {
        header("Location: products.php?status=success");
        exit();
    }
}

// 2. UPDATE PRODUK LENGKAP (FITUR BARU)
if (isset($_POST['update_produk_lengkap'])) {
    $id = $_POST['products_id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Kita update Nama, Kategori, Harga, dan Stok
    $stmt_upd = $conn->prepare("UPDATE products SET name=?, category_id=?, price=?, stock=? WHERE products_id=? AND user_id=?");
    $stmt_upd->bind_param("sidiii", $name, $category_id, $price, $stock, $id, $current_user_id);
    
    if ($stmt_upd->execute()) {
        header("Location: products.php?status=updated");
        exit();
    }
}

// 3. Soft Delete Produk (Aman untuk Database)
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt_del = $conn->prepare("UPDATE products SET is_active = 0 WHERE products_id = ? AND user_id = ?");
    $stmt_del->bind_param("ii", $id, $current_user_id);
    $stmt_del->execute();
    header("Location: products.php?status=deleted");
    exit();
}

// Ambil Data Produk Aktif
$stmt_list = $conn->prepare("SELECT p.*, c.name as category_name 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.category_id 
                             WHERE p.user_id = ? AND p.is_active = 1 
                             ORDER BY p.products_id DESC");
$stmt_list->bind_param("i", $current_user_id);
$stmt_list->execute();
$result = $stmt_list->get_result();

// Ambil Kategori untuk Dropdown
$stmt_cat = $conn->prepare("SELECT * FROM categories WHERE user_id = ?");
$stmt_cat->bind_param("i", $current_user_id);
$stmt_cat->execute();
$categories = $stmt_cat->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Matchify</title>
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
                <a href="products.php" class="list-group-item active"><i class="bi bi-cup-straw"></i> Data Produk</a>
                <a href="users.php" class="list-group-item"><i class="bi bi-people"></i> Manajemen User</a>
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
                    <button class="btn btn-light shadow-sm border-0 sidebarToggle" id="sidebarToggleTop"><i class="bi bi-list fs-4"></i></button>
                    <h2 class="page-title mb-0">Data Produk</h2>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-2"></i> Tambah Menu
                    </button>
                    <div class="user-profile ms-3">
                        <div class="avatar"><?php echo substr($_SESSION['username'], 0, 1); ?></div>
                        <span class="small fw-bold pe-2"><?php echo $_SESSION['username']; ?></span>
                    </div>
                </div>
            </div>

            <div class="content-box">
                <h5 class="section-head mb-4"><i class="bi bi-menu-button-wide text-primary me-2"></i>Daftar Menu Tersedia</h5>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center rounded-start">Gambar</th>
                                <th>Nama Menu</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th class="text-center">Stok</th>
                                <th class="text-center rounded-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center">
                                        <img src="../assets/images/<?php echo $row['image']; ?>" class="rounded-3 shadow-sm border" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='../assets/images/default.png'">
                                    </td>
                                    <td><span class="fw-bold text-dark"><?php echo $row['name']; ?></span></td>
                                    <td><span class="badge bg-light text-secondary border border-secondary-subtle"><?php echo htmlspecialchars($row['category_name'] ?? 'Tanpa Kategori'); ?></span></td>
                                    <td class="fw-bold text-success">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <?php if($row['stock'] <= 5): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><?php echo $row['stock']; ?> (Low)</span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success"><?php echo $row['stock']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-light text-primary shadow-sm rounded-circle me-1" 
                                                style="width: 35px; height: 35px;"
                                                data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $row['products_id']; ?>"
                                                title="Edit Menu Lengkap">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <a href="?hapus=<?php echo $row['products_id']; ?>" 
                                           class="btn btn-sm btn-light text-danger shadow-sm rounded-circle" 
                                           style="width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center;"
                                           onclick="return confirm('Hapus menu ini? (Data transaksi tetap aman)')"
                                           title="Hapus Produk">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>

                                        <div class="modal fade" id="modalEdit<?php echo $row['products_id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h6 class="modal-title fw-bold">Edit Menu: <?php echo $row['name']; ?></h6>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="products_id" value="<?php echo $row['products_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-muted">NAMA MENU</label>
                                                                <input type="text" name="name" class="form-control" value="<?php echo $row['name']; ?>" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-muted">KATEGORI</label>
                                                                <select name="category_id" class="form-select">
                                                                    <option value="">-- Pilih Kategori --</option>
                                                                    <?php 
                                                                    // Reset pointer kategori agar bisa diloop ulang untuk setiap modal
                                                                    if($categories) {
                                                                        $categories->data_seek(0);
                                                                        while($cat = $categories->fetch_assoc()): 
                                                                            $selected = ($cat['category_id'] == $row['category_id']) ? 'selected' : '';
                                                                    ?>
                                                                        <option value="<?php echo $cat['category_id']; ?>" <?php echo $selected; ?>>
                                                                            <?php echo htmlspecialchars($cat['name']); ?>
                                                                        </option>
                                                                    <?php endwhile; } ?>
                                                                </select>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6 mb-3">
                                                                    <label class="form-label small fw-bold text-muted">HARGA (RP)</label>
                                                                    <input type="number" name="price" class="form-control" value="<?php echo $row['price']; ?>" required>
                                                                </div>
                                                                <div class="col-6 mb-3">
                                                                    <label class="form-label small fw-bold text-muted">STOK</label>
                                                                    <input type="number" name="stock" class="form-control" value="<?php echo $row['stock']; ?>" required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0">
                                                            <button type="submit" name="update_produk_lengkap" class="btn btn-primary w-100 rounded-pill fw-bold">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada menu.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <footer class="custom-footer">@Copyright by 23552011310_Arizal Junior_TIF 23 CNS B</footer>
        </div>
    </div>

    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-cup-straw me-2"></i>Tambah Menu Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">NAMA MENU</label>
                            <input type="text" name="name" class="form-control bg-light" placeholder="Misal: Matcha Latte" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">KATEGORI</label>
                            <select name="category_id" class="form-select bg-light" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php 
                                if($categories) {
                                    $categories->data_seek(0);
                                    while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endwhile; 
                                } ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">HARGA (RP)</label>
                                <input type="number" name="price" class="form-control bg-light" placeholder="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">STOK AWAL</label>
                                <input type="number" name="stock" class="form-control bg-light" value="10" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">FOTO MENU</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="simpan_produk" class="btn btn-success rounded-pill px-4 fw-bold">Simpan Menu</button>
                    </div>
                </form>
            </div>
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