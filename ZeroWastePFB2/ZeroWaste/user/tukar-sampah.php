<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isUser()) {
    redirect('../login.php');
}

$userId = $_SESSION['user_id'];
$categories = getWasteCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = sanitize($_POST['category'] ?? '');
    $quantity = floatval($_POST['quantity'] ?? 0);
    $unit = sanitize($_POST['unit'] ?? 'kg');
    $description = sanitize($_POST['description'] ?? '');
    
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['photo'], 'sampah');
        if ($upload['success']) {
            $photo = $upload['filename'];
        } else {
            alert($upload['message'], 'danger');
            redirect('tukar-sampah.php');
        }
    }
    
    if (empty($category) || $quantity <= 0) {
        alert('Kategori dan jumlah harus diisi dengan benar!', 'danger');
    } else {
        $stmt = $pdo->prepare("INSERT INTO waste_exchanges (user_id, category, quantity, unit, photo, description, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        
        if ($stmt->execute([$userId, $category, $quantity, $unit, $photo, $description])) {
            alert('Pengajuan penukaran sampah berhasil! Mohon tunggu persetujuan admin.', 'success');
            redirect('tukar-sampah.php');
        } else {
            alert('Terjadi kesalahan. Silakan coba lagi.', 'danger');
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM waste_exchanges WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$exchanges = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tukar Sampah - ZeroWaste</title>
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
                        <h4 class="fw-bold"><i class="bi bi-arrow-repeat me-2 text-success"></i>Tukar Sampah</h4>
                        <p class="text-muted">Ajukan penukaran sampah untuk mendapatkan poin</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-plus-circle me-2 text-success"></i>Form Pengajuan</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Kategori Sampah <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php foreach ($categories as $key => $value): ?>
                                            <option value="<?= $key ?>"><?= $value ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-8">
                                            <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="quantity" name="quantity" step="0.1" min="0.1" required>
                                        </div>
                                        <div class="col-4">
                                            <label for="unit" class="form-label">Satuan</label>
                                            <select class="form-select" id="unit" name="unit">
                                                <option value="kg">Kg</option>
                                                <option value="pcs">Pcs</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Deskripsi</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Jelaskan jenis sampah yang akan ditukar"></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <label for="photo" class="form-label">Foto Sampah</label>
                                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                        <div class="form-text">Format: JPG, PNG, GIF. Maks 5MB</div>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-send me-2"></i>Ajukan Penukaran
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card shadow-sm mt-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-info"></i>Informasi Poin</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>1 Kg = <?= POINT_PER_KG ?> Poin</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>1 Pcs = 1 Poin</li>
                                    <li><i class="bi bi-clock text-warning me-2"></i>Proses verifikasi 1-2 hari kerja</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-list-check me-2 text-success"></i>Riwayat Pengajuan</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($exchanges)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-inbox fs-1 mb-2"></i>
                                    <p class="mb-0">Belum ada riwayat pengajuan</p>
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Kategori</th>
                                                <th>Jumlah</th>
                                                <th>Status</th>
                                                <th>Poin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($exchanges as $ex): ?>
                                            <tr>
                                                <td><?= formatDate($ex['created_at']) ?></td>
                                                <td>
                                                    <span class="text-capitalize"><?= sanitize($ex['category']) ?></span>
                                                    <?php if ($ex['photo']): ?>
                                                    <a href="../uploads/sampah/<?= $ex['photo'] ?>" target="_blank" class="ms-1">
                                                        <i class="bi bi-image text-success"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= number_format($ex['quantity'], 1) ?> <?= $ex['unit'] ?></td>
                                                <td>
                                                    <span class="status-badge status-<?= $ex['status'] ?>">
                                                        <?= ucfirst($ex['status']) ?>
                                                    </span>
                                                    <?php if ($ex['status'] === 'rejected' && $ex['reject_reason']): ?>
                                                    <br><small class="text-muted"><?= sanitize($ex['reject_reason']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($ex['status'] === 'approved'): ?>
                                                    <span class="text-success fw-bold">+<?= $ex['points_earned'] ?></span>
                                                    <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
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
