<?php
session_start();
include '../config/database.php';

// --- LOGIC PHP (TETAP SAMA) ---
if (isset($_SESSION['user_id'])) {
    $redirect = ($_SESSION['role'] == 'admin') ? "../admin/index.php" : "../kasir/index.php";
    header("Location: $redirect");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            setcookie('user_login', $row['username'], time() + 86400, "/");
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
    <title>Login - POS Cafe</title>
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/login-style.css">
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            
            <div class="card-left">
                <div class="shape-blob-1"></div> <div class="shape-blob-2"></div> <div class="shape-blob-3"></div> </div>

            <div class="card-right">
                <div class="login-header">
                    <h2>User Login</h2>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-danger py-1 text-center" style="font-size: 0.9rem;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <i class="bi bi-person-fill input-icon"></i>
                        <input type="text" class="form-control-custom" name="username" placeholder="Username" required autofocus>
                    </div>

                    <div class="form-group">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" class="form-control-custom" name="password" placeholder="Password" required>
                    </div>

                    <button type="submit" class="btn-login">LOGIN</button>

                    <div class="forgot-pass">
                        Forgot Username / Password?
                    </div>

                    <div class="create-account">
                       Create Your Account &rarr;
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>