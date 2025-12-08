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
    $notes = sanitize($_POST['notes'] ?? '');
    $logDate = $_POST['log_date'] ?? date('Y-m-d');
    
    if (empty($category) || $quantity <= 0) {
        alert('Kategori dan jumlah harus diisi dengan benar!', 'danger');
    } else {
        $stmt = $pdo->prepare("INSERT INTO daily_waste_logs (user_id, category, quantity, unit, notes, log_date) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$userId, $category, $quantity, $unit, $notes, $logDate])) {
            alert('Catatan sampah berhasil disimpan!', 'success');
            redirect('catatan-harian.php');
        } else {
            alert('Terjadi kesalahan. Silakan coba lagi.', 'danger');
        }
    }
}

if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM daily_waste_logs WHERE id = ? AND user_id = ?");
    $stmt->execute([$deleteId, $userId]);
    alert('Catatan berhasil dihapus!', 'success');
    redirect('catatan-harian.php');
}

$stmt = $pdo->prepare("SELECT * FROM daily_waste_logs WHERE user_id = ? ORDER BY log_date DESC, created_at DESC LIMIT 50");
$stmt->execute([$userId]);
$logs = $stmt->fetchAll();

$interval30 = dateInterval(30);
$stmt = $pdo->prepare("
    SELECT category, SUM(quantity) as total 
    FROM daily_waste_logs 
    WHERE user_id = ? AND log_date >= {$interval30}
    GROUP BY category
");
$stmt->execute([$userId]);
$categoryStats = $stmt->fetchAll();

$interval14 = dateInterval(14);
$dateCol = toDate('log_date');
$stmt = $pdo->prepare("
    SELECT {$dateCol} as date, SUM(quantity) as total 
    FROM daily_waste_logs 
    WHERE user_id = ? AND log_date >= {$interval14}
    GROUP BY {$dateCol}
    ORDER BY date ASC
");
$stmt->execute([$userId]);
$dailyStats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Sampah Harian - ZeroWaste</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <h4 class="fw-bold"><i class="bi bi-journal-text me-2 text-success"></i>Catatan Sampah Harian</h4>
                        <p class="text-muted">Catat dan pantau sampah yang kamu hasilkan setiap hari</p>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-plus-circle me-2 text-success"></i>Tambah Catatan</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="log_date" class="form-label">Tanggal</label>
                                        <input type="date" class="form-control" id="log_date" name="log_date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Jenis Sampah <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Pilih Jenis</option>
                                            <?php foreach ($categories as $key => $value): ?>
                                            <option value="<?= $key ?>"><?= $value ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-8">
                                            <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="quantity" name="quantity" step="0.01" min="0.01" required>
                                        </div>
                                        <div class="col-4">
                                            <label for="unit" class="form-label">Satuan</label>
                                            <select class="form-select" id="unit" name="unit">
                                                <option value="kg">Kg</option>
                                                <option value="gram">Gram</option>
                                                <option value="pcs">Pcs</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="notes" class="form-label">Catatan</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Contoh: Sisa makanan siang"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-save me-2"></i>Simpan Catatan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-white py-3">
                                        <h5 class="mb-0"><i class="bi bi-graph-up me-2 text-success"></i>Tren Sampah 14 Hari</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container" style="height: 250px;">
                                            <canvas id="trendChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-white py-3">
                                        <h5 class="mb-0"><i class="bi bi-pie-chart me-2 text-success"></i>Distribusi Sampah</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container" style="height: 200px;">
                                            <canvas id="pieChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-white py-3">
                                        <h5 class="mb-0"><i class="bi bi-bar-chart me-2 text-success"></i>Per Kategori</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container" style="height: 200px;">
                                            <canvas id="barChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-list-ul me-2 text-success"></i>Riwayat Catatan</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($logs)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-journal-x fs-1 mb-2"></i>
                                    <p class="mb-0">Belum ada catatan sampah</p>
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Jenis Sampah</th>
                                                <th>Jumlah</th>
                                                <th>Catatan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?= formatDate($log['log_date']) ?></td>
                                                <td><span class="text-capitalize badge bg-success-subtle text-success"><?= sanitize($log['category']) ?></span></td>
                                                <td><?= number_format($log['quantity'], 2) ?> <?= $log['unit'] ?></td>
                                                <td><?= sanitize($log['notes']) ?: '-' ?></td>
                                                <td>
                                                    <a href="?delete=<?= $log['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus catatan ini?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
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
    <script>
        const trendLabels = <?= json_encode(array_map(function($d) { return date('d M', strtotime($d['date'])); }, $dailyStats)) ?>;
        const trendData = <?= json_encode(array_map(function($d) { return floatval($d['total']); }, $dailyStats)) ?>;
        
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendLabels.length ? trendLabels : ['Tidak ada data'],
                datasets: [{
                    label: 'Total Sampah (kg)',
                    data: trendData.length ? trendData : [0],
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        const pieLabels = <?= json_encode(array_column($categoryStats, 'category')) ?>;
        const pieData = <?= json_encode(array_map(function($d) { return floatval($d['total']); }, $categoryStats)) ?>;
        const colors = ['#198754', '#20c997', '#0dcaf0', '#ffc107', '#fd7e14', '#dc3545', '#6f42c1', '#6c757d'];
        
        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: pieLabels.length ? pieLabels : ['Belum ada data'],
                datasets: [{
                    data: pieData.length ? pieData : [1],
                    backgroundColor: pieLabels.length ? colors.slice(0, pieLabels.length) : ['#dee2e6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12 } } }
            }
        });

        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: pieLabels.length ? pieLabels : ['Belum ada data'],
                datasets: [{
                    label: 'Total (kg)',
                    data: pieData.length ? pieData : [0],
                    backgroundColor: colors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>
