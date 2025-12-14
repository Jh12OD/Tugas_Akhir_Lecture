<?php
require_once 'config/database.php';
require_once 'config/functions.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('user/dashboard.php');
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'Semua field wajib harus diisi!';
    } elseif (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username atau email sudah terdaftar!';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?, 'user')");
            
            if ($stmt->execute([$username, $email, $hashedPassword, $full_name, $phone])) {
                $success = 'Pendaftaran berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
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
    <title>Daftar - ZeroWaste</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <a href="index.php" class="text-decoration-none">
                                <h2 class="text-success fw-bold">
                                    <i class="bi bi-recycle me-2"></i>ZeroWaste
                                </h2>
                            </a>
                            <p class="text-muted">Buat akun baru</p>
                        </div>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i><?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?= sanitize($_POST['full_name'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white">
                                        <i class="bi bi-at"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?= sanitize($_POST['username'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">No. Telepon</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white">
                                        <i class="bi bi-phone"></i>
                                    </span>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="<?= sanitize($_POST['phone'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="form-text">Minimal 6 karakter</div>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-2">
                                <i class="bi bi-person-plus me-2"></i>Daftar
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <p class="text-center text-muted mb-0">
                            Sudah punya akun? <a href="login.php" class="text-success fw-bold">Login</a>
                        </p>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="index.php" class="text-muted text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
