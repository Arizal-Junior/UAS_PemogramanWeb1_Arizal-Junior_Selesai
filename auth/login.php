<?php
session_start();
include '../config/database.php';

// --- LOGIC PHP ---
if (isset($_SESSION['user_id'])) {
    $redirect = ($_SESSION['role'] == 'admin') ? "../admin/index.php" : "../kasir/index.php";
    header("Location: $redirect");
    exit;
}

$error = "";

// Cek apakah ada cookie username tersimpan?
$saved_username = isset($_COOKIE['user_login']) ? $_COOKIE['user_login'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // SESUAI DATABASE: Mengubah 'id' menjadi 'user_id' dan menghapus 'owner_id' karena tidak ada di gambar skema
    $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // SESUAI DATABASE: user_id digunakan sebagai identifier session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            // Logic Owner ID dihapus atau disesuaikan karena kolom owner_id tidak ada di tabel users
            // Jika ingin tetap ada, admin dianggap owner dirinya sendiri
            if ($row['role'] == 'admin') {
                $_SESSION['owner_id'] = $row['user_id'];
            }

            // SIMPAN COOKIE USERNAME (Berlaku 30 Hari)
            setcookie('user_login', $row['username'], time() + (86400 * 30), "/");
            
            $redirect = ($row['role'] == 'admin') ? "../admin/index.php" : "../kasir/index.php";
            header("Location: $redirect");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Matchify</title>
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/login-style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            
            <div class="card-left">
                <div class="shape-blob-1"></div> 
                <div class="shape-blob-2"></div> 
                <div class="shape-blob-3"></div> 
            </div>

            <div class="card-right">
                <div class="login-header">
                    <h2>Login</h2>
                    <p class="text-muted small">Masuk untuk mengelola Matcha Cafe</p>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-danger py-2 text-center shadow-sm border-0" style="font-size: 0.9rem;">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php 
                if (isset($_GET['registered'])) {
                    echo '<div class="alert alert-success py-2 text-center shadow-sm border-0" style="font-size: 0.9rem;">
                            <i class="bi bi-check-circle-fill me-2"></i> Akun berhasil dibuat! Silakan login.
                          </div>';
                }
                ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <i class="bi bi-person-fill input-icon"></i>
                        <input type="text" class="form-control-custom" name="username" placeholder="Username" 
                               value="<?php echo htmlspecialchars($saved_username); ?>" required autofocus>
                    </div>

                    <div class="form-group">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" class="form-control-custom" name="password" placeholder="Password" required>
                    </div>

                    <button type="submit" class="btn-login shadow-sm">MASUK SISTEM</button>

                    <div class="create-account mt-4">
                        Belum punya akun? Registrasi sekarang<br>
                        <a href="register.php" class="text-success fw-bold text-decoration-none">
                            Registrasi disini
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="custom-footer" style="position: fixed; bottom: 0; left: 0; width: 100%; padding: 10px; background: transparent;">
        @Copyright by 23552011310_Arizal Junior_TIF 23 CNS B
    </footer>

    <script src="../bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>