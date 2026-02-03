<?php
session_start();
include '../config/database.php';

// Jika sudah login, lempar ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    $redirect = ($_SESSION['role'] == 'admin') ? "../admin/index.php" : "../kasir/index.php";
    header("Location: $redirect");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // ATURAN: Pendaftaran publik hanya untuk ADMIN (Pemilik Toko)
    $role = 'admin';

    // Validasi Password
    if ($password !== $confirm_password) {
        $message = "<div class='alert alert-danger py-2 text-center'>Password konfirmasi tidak cocok!</div>";
    } else {
        // SESUAI DATABASE: Kolom id diubah menjadi user_id
        $cek = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $cek->bind_param("s", $username);
        $cek->execute();
        
        if ($cek->get_result()->num_rows > 0) {
            $message = "<div class='alert alert-warning py-2 text-center'>Username sudah digunakan!</div>";
        } else {
            // Insert User Baru (Sebagai Admin)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // SESUAI DATABASE: Nama kolom adalah (username, password, role) 
            // created_at akan terisi otomatis oleh database jika tipenya timestamp
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $role);
            
            if ($stmt->execute()) {
                // Sukses - Redirect ke login dengan pesan
                header("Location: login.php?registered=true");
                exit;
            } else {
                $message = "<div class='alert alert-danger py-2 text-center'>Gagal mendaftar. Coba lagi.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin - Matchify</title>
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/login-style.css">
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
                    <h2>Registrasi</h2>
                    <p class="text-muted small">Buat akun Toko baru (Administrator)</p>
                </div>

                <?php echo $message; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <i class="bi bi-person-fill input-icon"></i>
                        <input type="text" class="form-control-custom" name="username" placeholder="Username Baru" required autofocus>
                    </div>

                    <div class="form-group">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" class="form-control-custom" name="password" placeholder="Password" required>
                    </div>

                    <div class="form-group">
                        <i class="bi bi-check-lg input-icon"></i>
                        <input type="password" class="form-control-custom" name="confirm_password" placeholder="Ulangi Password" required>
                    </div>

                    <button type="submit" class="btn-login shadow-sm">DAFTAR</button>

                    <div class="create-account mt-3">
                        Sudah punya akun? <br>
                        <a href="login.php" class="text-success fw-bold text-decoration-none">Login disini</a>
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