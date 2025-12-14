<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($action === 'toggle_status') {
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND role = 'user'");
        $stmt->execute([$userId]);
        alert('Status user berhasil diubah!', 'success');
    } elseif ($action === 'reset_points') {
        $stmt = $pdo->prepare("UPDATE users SET points = 0 WHERE id = ? AND role = 'user'");
        $stmt->execute([$userId]);
        alert('Poin user berhasil direset!', 'success');
    } elseif ($action === 'toggle_forum_block') {
        $stmt = $pdo->prepare("UPDATE users SET is_forum_blocked = NOT is_forum_blocked WHERE id = ? AND role = 'user'");
        $stmt->execute([$userId]);
        alert('Status forum user berhasil diubah!', 'success');
    } elseif ($action === 'update_user') {
        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$fullName, $email, $phone, $userId]);
        alert('Data user berhasil diperbarui!', 'success');
    }
    
    redirect('users.php');
}

$search = sanitize($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM users WHERE role = 'user'";
$params = [];

if ($search) {
    $sql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

if ($status === 'active') {
    $sql .= " AND is_active = 1";
} elseif ($status === 'inactive') {
    $sql .= " AND is_active = 0";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin ZeroWaste</title>
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
                        <h4 class="fw-bold"><i class="bi bi-people me-2 text-success"></i>Manage Users</h4>
                        <p class="text-muted">Kelola data pengguna ZeroWaste</p>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" name="search" placeholder="Cari username, email, atau nama..." value="<?= sanitize($search) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-search me-2"></i>Cari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Telepon</th>
                                        <th>Poin</th>
                                        <th>Status</th>
                                        <th>Terdaftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada user ditemukan</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                    <?= strtoupper(substr($u['full_name'] ?? 'U', 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= sanitize($u['full_name'] ?? '') ?></div>
                                                    <small class="text-muted">@<?= sanitize($u['username'] ?? '') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= sanitize($u['email'] ?? '') ?></td>
                                        <td><?= sanitize($u['phone'] ?? '') ?: '-' ?></td>
                                        <td><span class="badge bg-success"><?= number_format($u['points'] ?? 0) ?></span></td>
                                        <td>
                                            <?php if ($u['is_active'] ?? false): ?>
                                            <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Nonaktif</span>
                                            <?php endif; ?>
                                            <?php if ($u['is_forum_blocked'] ?? false): ?>
                                            <span class="badge bg-warning">Forum Blocked</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDate($u['created_at'] ?? '') ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $u['id'] ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                            <input type="hidden" name="action" value="toggle_status">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="bi bi-toggle-<?= ($u['is_active'] ?? false) ? 'on' : 'off' ?> me-2"></i>
                                                                <?= ($u['is_active'] ?? false) ? 'Nonaktifkan' : 'Aktifkan' ?>
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Reset poin user ini?')">
                                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                            <input type="hidden" name="action" value="reset_points">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Poin
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                            <input type="hidden" name="action" value="toggle_forum_block">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="bi bi-chat-left-x me-2"></i>
                                                                <?= ($u['is_forum_blocked'] ?? false) ? 'Unblock Forum' : 'Block Forum' ?>
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php foreach ($users as $u): ?>
                <div class="modal fade" id="editModal<?= $u['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit User: <?= sanitize($u['username'] ?? '') ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="action" value="update_user">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="full_name" value="<?= sanitize($u['full_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?= sanitize($u['email'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Telepon</label>
                                        <input type="text" class="form-control" name="phone" value="<?= sanitize($u['phone'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
