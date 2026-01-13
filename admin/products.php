<?php
session_start();
include '../config/database.php';

// Cek Login Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// --- LOGIKA PHP ---

// 1. Tambah Produk (Upload Gambar)
if (isset($_POST['simpan_produk'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Proses Upload Gambar
    $gambar = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];
    
    // Rename gambar agar tidak bentrok (pakai timestamp)
    $nama_baru = time() . "_" . $gambar;
    $path_upload = "../assets/images/" . $nama_baru;

    // Cek apakah user upload gambar?
    if (!empty($gambar)) {
        move_uploaded_file($tmp_name, $path_upload);
    } else {
        $nama_baru = "default.png"; // Gambar default jika tidak upload
    }

    $stmt = $conn->prepare("INSERT INTO products (category_id, name, price, stock, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdis", $category_id, $name, $price, $stock, $nama_baru);
    
    if ($stmt->execute()) {
        header("Location: products.php");
    } else {
        echo "Gagal: " . $conn->error;
    }
}

// 2. Hapus Produk
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Ambil nama file gambar dulu untuk dihapus dari folder
    $sql = "SELECT image FROM products WHERE id = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    // Hapus file gambar dari folder (kecuali default.png)
    if ($row['image'] != 'default.png' && file_exists("../assets/images/" . $row['image'])) {
        unlink("../assets/images/" . $row['image']);
    }

    // Hapus data dari database
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: products.php");
}

// Ambil Data Produk (JOIN dengan Kategori biar muncul nama kategorinya)
$query = "SELECT products.*, categories.name as category_name 
          FROM products 
          LEFT JOIN categories ON products.category_id = categories.id 
          ORDER BY products.id DESC";
$result = $conn->query($query);

// Ambil Data Kategori untuk Dropdown
$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Matcha Cafe</title>
    
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
                <a href="categories.php" class="list-group-item">
                    <i class="bi bi-tags"></i> Kategori Menu
                </a>
                <a href="products.php" class="list-group-item active">
                    <i class="bi bi-cup-straw-fill"></i> Data Produk
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
                <h2 class="page-title">Data Produk</h2>
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-2"></i> Tambah Menu
                    </button>

                    <div class="user-profile ms-3">
                        <div class="avatar">
                            <?php echo substr($_SESSION['username'], 0, 1); ?>
                        </div>
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
                            <?php if($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center">
                                        <img src="../assets/images/<?php echo $row['image']; ?>" 
                                             class="rounded-3 shadow-sm border" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark"><?php echo $row['name']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border border-secondary-subtle">
                                            <?php echo $row['category_name']; ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success">
                                        Rp <?php echo number_format($row['price'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($row['stock'] <= 5): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                                <?php echo $row['stock']; ?> (Low)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                <?php echo $row['stock']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="?hapus=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-light text-danger shadow-sm rounded-circle" 
                                           style="width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center;"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?')"
                                           title="Hapus Produk">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-cup-hot display-4 d-block mb-3 opacity-25"></i>
                                        Belum ada menu yang ditambahkan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

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
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                <?php endforeach; ?>
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
                            <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">* Format JPG, PNG (Max 2MB)</small>
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
</body>
</html>