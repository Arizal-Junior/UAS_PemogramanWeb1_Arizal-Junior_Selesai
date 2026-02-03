<?php
session_start();
include '../config/database.php';

// Cek Login Kasir
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kasir') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil ID Kasir yang sedang login
$current_kasir_id = $_SESSION['user_id'];

// --- LOGIKA MENCARI "SIAPA BOS SAYA?" ---
// Kita cek kolom 'created_by' di tabel users
$stmt_bos = $conn->prepare("SELECT created_by FROM users WHERE user_id = ?");
$stmt_bos->bind_param("i", $current_kasir_id);
$stmt_bos->execute();
$result_bos = $stmt_bos->get_result();
$data_bos = $result_bos->fetch_assoc();

// Jika created_by ada isinya, itulah ID Bos kita.
// Jika NULL (kosong), berarti error/akun lama, kita anggap ID bos = ID kasir (fallback)
$id_bos_toko = $data_bos['created_by'] ? $data_bos['created_by'] : $current_kasir_id;


// --- 1. Ambil Kategori (Hanya milik Bos) ---
$stmt_cat = $conn->prepare("SELECT * FROM categories WHERE user_id = ?");
$stmt_cat->bind_param("i", $id_bos_toko);
$stmt_cat->execute();
$categories_result = $stmt_cat->get_result();

$categories = [];
while($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

// --- 2. Ambil Produk (Hanya milik Bos) ---
// Syarat: Stok > 0, Aktif, dan Milik Bos Toko
$where_clause = "stock > 0 AND is_active = 1 AND user_id = ?"; 
$params = [$id_bos_toko];
$types = "i"; // Tipe data integer untuk user_id

// Filter tambahan jika ada parameter kategori di URL
if (isset($_GET['kategori']) && $_GET['kategori'] != '') {
    $where_clause .= " AND category_id = ?";
    $params[] = $_GET['kategori'];
    $types .= "i";
}

$sql_products = "SELECT * FROM products WHERE $where_clause ORDER BY name ASC";
$stmt_prod = $conn->prepare($sql_products);

if (!empty($params)) {
    $stmt_prod->bind_param($types, ...$params);
}

$stmt_prod->execute();
$products = $stmt_prod->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - Matchify</title>
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/pos-style.css">
</head>
<body>

    <nav class="pos-navbar d-flex justify-content-between align-items-center">
        <div class="brand-text">
            <div class="brand-icon-box">
                <i class="bi bi-cup-hot-fill"></i>
            </div>
            <span>Matcha Cafe POS</span>
        </div>
        
        <div class="dropdown">
            <button class="cashier-profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar">
                    <?php echo substr($_SESSION['username'], 0, 1); ?>
                </div>
                <span class="username-text"><?php echo $_SESSION['username']; ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2">
                <li><h6 class="dropdown-header">Halo, <?php echo $_SESSION['username']; ?>!</h6></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger fw-bold py-2" href="../auth/logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main-content container-fluid">
        <div class="row h-100">
            
            <div class="col-md-8 h-100">
                <div class="product-scroll-area">
                    <div class="category-scroll">
                        <a href="index.php" class="cat-pill <?php echo !isset($_GET['kategori']) ? 'active' : ''; ?>">
                            <i class="bi bi-grid-fill me-1"></i> Semua
                        </a>
                        <?php foreach($categories as $cat): ?>
                            <a href="?kategori=<?php echo $cat['category_id']; ?>" class="cat-pill <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == $cat['category_id']) ? 'active' : ''; ?>">
                                <?php echo $cat['name']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                        <?php if($products->num_rows > 0): ?>
                            <?php while($prod = $products->fetch_assoc()): ?>
                            <div class="col">
                                <div class="product-card" onclick="addToCart(<?php echo $prod['products_id']; ?>, '<?php echo addslashes($prod['name']); ?>', <?php echo $prod['price']; ?>)">
                                    <div class="product-img-wrapper">
                                        <div class="stock-badge">Stok: <?php echo $prod['stock']; ?></div>
                                        <img src="../assets/images/<?php echo $prod['image']; ?>" class="product-img" alt="<?php echo $prod['name']; ?>" 
                                             onerror="this.src='https://via.placeholder.com/150?text=No+Image'">
                                    </div>
                                    <div class="card-content">
                                        <h6 class="product-title"><?php echo $prod['name']; ?></h6>
                                        <div class="product-price">Rp <?php echo number_format($prod['price'], 0, ',', '.'); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <i class="bi bi-search display-1 text-muted opacity-25"></i>
                                <h4 class="text-muted mt-3">Produk tidak ditemukan.</h4>
                                <small>Pastikan Admin Anda sudah menambahkan produk.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4 h-100 pb-2">
                <div class="cart-panel">
                    <div class="cart-header d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-cart3 me-2 text-success"></i>Pesanan</h5>
                        <button class="btn btn-sm btn-light text-danger fw-bold shadow-sm" onclick="clearCart()">
                            <i class="bi bi-trash"></i> Reset
                        </button>
                    </div>

                    <div class="cart-body" id="cartBody">
                        <div class="text-center text-muted mt-5 pt-5">
                            <i class="bi bi-basket display-4 opacity-25"></i>
                            <p class="mt-2">Belum ada item dipilih.</p>
                        </div>
                    </div>

                    <div class="cart-footer">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="labelSubtotal" class="fw-bold">Rp 0</span>
                        </div>
                        <div class="summary-row">
                            <span>Pajak (10%)</span>
                            <span id="labelTax" class="fw-bold text-danger">Rp 0</span>
                        </div>
                        <div class="summary-total">
                            <span>Total Bayar</span>
                            <span id="labelTotal" class="text-success">Rp 0</span>
                        </div>

                        <form action="process_trx.php" method="POST" onsubmit="return validateCheckout()">
                            <input type="hidden" name="cart_data" id="cartInput">
                            <input type="hidden" name="total_amount" id="totalAmountInput">
                            
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-light border-0 fw-bold small text-muted">TUNAI</span>
                                <input type="number" name="cash" id="inputCash" class="form-control border-0 bg-light fw-bold text-end" placeholder="0" required style="font-size: 1.1rem;">
                            </div>
                            
                            <button type="submit" class="btn-pay shadow-sm">
                                <i class="bi bi-printer-fill me-2"></i> PROSES BAYAR
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer class="custom-footer">
        @Copyright by 23552011310_Arizal Junior_TIF 23 CNS B
    </footer>

    <script>
        let cart = [];

        function addToCart(id, name, price) {
            let existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.qty++;
            } else {
                cart.push({ id: id, name: name, price: price, qty: 1 });
            }
            updateCartUI();
        }

        function updateQty(index, change) {
            if (cart[index].qty + change > 0) {
                cart[index].qty += change;
            } else {
                removeFromCart(index);
                return;
            }
            updateCartUI();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartUI();
        }

        function clearCart() {
            if(confirm('Kosongkan keranjang?')) {
                cart = [];
                updateCartUI();
            }
        }

        function updateCartUI() {
            let container = document.getElementById('cartBody');
            container.innerHTML = '';

            if(cart.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted mt-5 pt-5">
                        <i class="bi bi-basket display-4 opacity-25"></i>
                        <p class="mt-2">Belum ada item dipilih.</p>
                    </div>`;
            }

            let subtotal = 0;

            cart.forEach((item, index) => {
                let itemTotal = item.price * item.qty;
                subtotal += itemTotal;

                let html = `
                <div class="cart-item">
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">${item.name}</div>
                        <div class="text-muted" style="font-size: 0.8rem;">@ Rp ${item.price.toLocaleString('id-ID')}</div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="qty-control">
                            <button type="button" class="btn-qty text-danger" onclick="updateQty(${index}, -1)">-</button>
                            <span class="fw-bold" style="font-size: 0.9rem; min-width: 20px; text-align: center;">${item.qty}</span>
                            <button type="button" class="btn-qty text-success" onclick="updateQty(${index}, 1)">+</button>
                        </div>
                        <div class="fw-bold text-dark text-end" style="min-width: 60px; font-size: 0.95rem;">
                            ${(itemTotal / 1000).toFixed(0)}k
                        </div>
                    </div>
                </div>`;
                container.innerHTML += html;
            });

            let tax = subtotal * 0.10;
            let total = subtotal + tax;

            document.getElementById('labelSubtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('labelTax').innerText = 'Rp ' + tax.toLocaleString('id-ID');
            document.getElementById('labelTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');

            document.getElementById('cartInput').value = JSON.stringify(cart);
            document.getElementById('totalAmountInput').value = total;
        }

        function validateCheckout() {
            if (cart.length === 0) {
                alert("Keranjang belanja masih kosong!");
                return false;
            }
            
            let cash = parseFloat(document.getElementById('inputCash').value);
            let total = parseFloat(document.getElementById('totalAmountInput').value);

            if (isNaN(cash) || cash < total) {
                alert("Uang pembayaran kurang! Total: Rp " + total.toLocaleString('id-ID'));
                return false;
            }
            
            return confirm("Proses pembayaran dan cetak struk?");
        }
    </script>
    
    <script src="../bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>