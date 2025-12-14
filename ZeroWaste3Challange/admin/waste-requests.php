<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $requestId = intval($_POST['request_id'] ?? 0);
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("SELECT * FROM waste_exchanges WHERE id = ?");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        
        if ($request && $request['status'] === 'pending') {
            $points = ($request['unit'] === 'kg') ? $request['quantity'] * POINT_PER_KG : $request['quantity'];
            $points = intval($points);
            
            $stmt = $pdo->prepare("UPDATE waste_exchanges SET status = 'approved', points_earned = ?, processed_by = ?, processed_at = NOW() WHERE id = ?");
            $stmt->execute([$points, $_SESSION['user_id'], $requestId]);
            
            updateUserPoints($request['user_id'], $points);
            
            $stmt = $pdo->prepare("INSERT INTO inbox (user_id, title, message, type) VALUES (?, ?, ?, 'notification')");
            $stmt->execute([
                $request['user_id'],
                'Penukaran Sampah Disetujui',
                "Selamat! Penukaran sampah {$request['category']} sebanyak {$request['quantity']} {$request['unit']} Anda telah disetujui. {$points} poin telah ditambahkan ke akun Anda."
            ]);
            
            alert('Request berhasil disetujui! Poin telah ditambahkan.', 'success');
        }
    } elseif ($action === 'reject') {
        $reason = sanitize($_POST['reject_reason'] ?? 'Tidak memenuhi syarat');
        
        $stmt = $pdo->prepare("SELECT * FROM waste_exchanges WHERE id = ?");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        
        if ($request && $request['status'] === 'pending') {
            $stmt = $pdo->prepare("UPDATE waste_exchanges SET status = 'rejected', reject_reason = ?, processed_by = ?, processed_at = NOW() WHERE id = ?");
            $stmt->execute([$reason, $_SESSION['user_id'], $requestId]);
            
            $stmt = $pdo->prepare("INSERT INTO inbox (user_id, title, message, type) VALUES (?, ?, ?, 'notification')");
            $stmt->execute([
                $request['user_id'],
                'Penukaran Sampah Ditolak',
                "Mohon maaf, penukaran sampah {$request['category']} Anda ditolak. Alasan: {$reason}"
            ]);
            
            alert('Request berhasil ditolak.', 'success');
        }
    }
    
    redirect('waste-requests.php');
}

$status = $_GET['status'] ?? 'pending';
$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT we.*, u.full_name, u.username 
        FROM waste_exchanges we 
        JOIN users u ON we.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($status && $status !== 'all') {
    $sql .= " AND we.status = ?";
    $params[] = $status;
}

if ($search) {
    $sql .= " AND (u.full_name LIKE ? OR u.username LIKE ? OR we.category LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

$sql .= " ORDER BY we.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penukaran Sampah - Admin ZeroWaste</title>
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
                        <h4 class="fw-bold"><i class="bi bi-arrow-repeat me-2 text-success"></i>Penukaran Sampah</h4>
                        <p class="text-muted">Kelola request penukaran sampah dari user</p>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" name="search" placeholder="Cari user atau kategori..." value="<?= sanitize($search) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="status">
                                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Semua</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-filter me-2"></i>Filter
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
                                        <th>Tanggal</th>
                                        <th>User</th>
                                        <th>Kategori</th>
                                        <th>Jumlah</th>
                                        <th>Foto</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada request ditemukan</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($requests as $req): ?>
                                    <tr>
                                        <td><?= formatDateTime($req['created_at']) ?></td>
                                        <td>
                                            <div class="fw-bold"><?= sanitize($req['full_name']) ?></div>
                                            <small class="text-muted">@<?= sanitize($req['username']) ?></small>
                                        </td>
                                        <td><span class="text-capitalize badge bg-success-subtle text-success"><?= sanitize($req['category']) ?></span></td>
                                        <td><?= number_format($req['quantity'], 1) ?> <?= $req['unit'] ?></td>
                                        <td>
                                            <?php if ($req['photo']): ?>
                                            <a href="../uploads/sampah/<?= $req['photo'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-image"></i>
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $req['status'] ?>">
                                                <?= ucfirst($req['status']) ?>
                                            </span>
                                            <?php if ($req['status'] === 'approved'): ?>
                                            <br><small class="text-success">+<?= $req['points_earned'] ?> poin</small>
                                            <?php elseif ($req['status'] === 'rejected' && $req['reject_reason']): ?>
                                            <br><small class="text-muted"><?= sanitize($req['reject_reason']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($req['status'] === 'pending'): ?>
                                            <div class="btn-group">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui request ini?')">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $req['id'] ?>">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>

                                            <div class="modal fade" id="rejectModal<?= $req['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Tolak Request</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                                <input type="hidden" name="action" value="reject">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Alasan Penolakan</label>
                                                                    <textarea class="form-control" name="reject_reason" rows="3" required>Tidak memenuhi syarat</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">Tolak</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
