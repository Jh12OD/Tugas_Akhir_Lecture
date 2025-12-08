<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isUser()) {
    redirect('../login.php');
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);

if (!$user) {
    session_destroy();
    redirect('../login.php');
}

$badge = getBadge($user['points'] ?? 0);
$motivation = getMotivationalMessage($user['full_name'] ?? 'User');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (!empty($newPassword)) {
        if (!password_verify($currentPassword, $user['password'])) {
            alert('Password saat ini salah!', 'danger');
        } elseif (strlen($newPassword) < 6) {
            alert('Password baru minimal 6 karakter!', 'danger');
        } elseif ($newPassword !== $confirmPassword) {
            alert('Konfirmasi password tidak cocok!', 'danger');
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, password = ? WHERE id = ?");
            $stmt->execute([$fullName, $phone, $address, $hashedPassword, $userId]);
            alert('Profile dan password berhasil diperbarui!', 'success');
            $_SESSION['full_name'] = $fullName;
            redirect('profile.php');
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$fullName, $phone, $address, $userId]);
        alert('Profile berhasil diperbarui!', 'success');
        $_SESSION['full_name'] = $fullName;
        redirect('profile.php');
    }
}

$stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(quantity) as total FROM waste_exchanges WHERE user_id = ? AND status = 'approved'");
$stmt->execute([$userId]);
$wasteStats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM forum_posts WHERE user_id = ? AND is_deleted = 0");
$stmt->execute([$userId]);
$forumStats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ZeroWaste</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1">
            <?php include 'includes/topbar.php'; ?>
            
            <div class="container-fluid p-4">
                <?= showAlert() ?>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="fw-bold"><i class="bi bi-person me-2 text-success"></i>Profile Saya</h4>
                        <p class="text-muted">Lihat dan edit informasi akun Anda</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="card shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="avatar-circle bg-success mx-auto mb-3">
                                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                </div>
                                <h4 class="fw-bold mb-1"><?= sanitize($user['full_name']) ?></h4>
                                <p class="text-muted mb-3">@<?= sanitize($user['username']) ?></p>
                                
                                <div class="mb-3">
                                    <span class="badge-level badge-<?= $badge['color'] ?>">
                                        <i class="bi bi-<?= $badge['icon'] ?> me-2"></i>
                                        <?= $badge['name'] ?>
                                    </span>
                                </div>
                                
                                <div class="bg-light rounded p-3 mb-3">
                                    <h2 class="fw-bold text-success mb-0"><?= number_format($user['points']) ?></h2>
                                    <small class="text-muted">Total Poin</small>
                                </div>
                                
                                <p class="text-muted fst-italic"><?= $motivation ?></p>
                            </div>
                        </div>

                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-bar-chart me-2 text-success"></i>Statistik</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 text-center">
                                    <div class="col-4">
                                        <h4 class="fw-bold text-success mb-0"><?= $wasteStats['count'] ?? 0 ?></h4>
                                        <small class="text-muted">Transaksi</small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="fw-bold text-info mb-0"><?= number_format($wasteStats['total'] ?? 0, 1) ?></h4>
                                        <small class="text-muted">Kg Sampah</small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="fw-bold text-warning mb-0"><?= $forumStats['count'] ?? 0 ?></h4>
                                        <small class="text-muted">Post Forum</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-trophy me-2 text-success"></i>Level Progress</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $levels = [
                                    ['name' => 'Newcomer', 'min' => 0, 'color' => 'gray'],
                                    ['name' => 'Eco Starter', 'min' => 50, 'color' => 'green'],
                                    ['name' => 'Eco Enthusiast', 'min' => 200, 'color' => 'bronze'],
                                    ['name' => 'Green Warrior', 'min' => 500, 'color' => 'silver'],
                                    ['name' => 'Eco Champion', 'min' => 1000, 'color' => 'gold'],
                                ];
                                foreach ($levels as $level):
                                    $achieved = $user['points'] >= $level['min'];
                                ?>
                                <div class="d-flex align-items-center mb-2 <?= !$achieved ? 'opacity-50' : '' ?>">
                                    <i class="bi bi-<?= $achieved ? 'check-circle-fill text-success' : 'circle' ?> me-2"></i>
                                    <span class="<?= $achieved ? 'fw-bold' : '' ?>"><?= $level['name'] ?></span>
                                    <small class="ms-auto text-muted"><?= $level['min'] ?> poin</small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-pencil me-2 text-success"></i>Edit Profile</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" value="<?= sanitize($user['username']) ?>" disabled>
                                            <div class="form-text">Username tidak dapat diubah</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" value="<?= sanitize($user['email']) ?>" disabled>
                                            <div class="form-text">Email tidak dapat diubah</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="full_name" class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?= sanitize($user['full_name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">No. Telepon</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?= sanitize($user['phone']) ?>">
                                        </div>
                                        <div class="col-12">
                                            <label for="address" class="form-label">Alamat</label>
                                            <textarea class="form-control" id="address" name="address" rows="2"><?= sanitize($user['address']) ?></textarea>
                                        </div>
                                    </div>

                                    <hr class="my-4">
                                    
                                    <h6 class="fw-bold mb-3">Ubah Password (Opsional)</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label for="current_password" class="form-label">Password Saat Ini</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="new_password" class="form-label">Password Baru</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-success"></i>Informasi Akun</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Bergabung sejak:</strong></p>
                                        <p class="text-muted"><?= formatDateTime($user['created_at']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Status Akun:</strong></p>
                                        <p><span class="badge bg-success">Aktif</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
