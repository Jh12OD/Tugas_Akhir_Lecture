<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=laporan-sampah-' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['Tanggal', 'User', 'Kategori', 'Jumlah', 'Satuan', 'Status', 'Poin']);
    
    $stmt = $pdo->query("
        SELECT we.*, u.full_name 
        FROM waste_exchanges we 
        JOIN users u ON we.user_id = u.id 
        ORDER BY we.created_at DESC
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            date('Y-m-d H:i', strtotime($row['created_at'])),
            $row['full_name'],
            $row['category'],
            $row['quantity'],
            $row['unit'],
            $row['status'],
            $row['points_earned']
        ]);
    }
    
    fclose($output);
    exit;
}

$stmt = $pdo->query("
    SELECT category, 
           COUNT(*) as count, 
           SUM(quantity) as total_quantity,
           SUM(points_earned) as total_points
    FROM waste_exchanges 
    WHERE status = 'approved'
    GROUP BY category
    ORDER BY total_quantity DESC
");
$categoryStats = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT u.id, u.full_name, u.username, u.points,
           COUNT(we.id) as exchange_count,
           COALESCE(SUM(we.quantity), 0) as total_waste
    FROM users u
    LEFT JOIN waste_exchanges we ON u.id = we.user_id AND we.status = 'approved'
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY total_waste DESC
    LIMIT 10
");
$topUsers = $stmt->fetchAll();

$interval12m = dateInterval(365);
$monthCol = toYearMonth('created_at');
$stmt = $pdo->query("
    SELECT {$monthCol} as month,
           COUNT(*) as count,
           SUM(quantity) as total_quantity
    FROM waste_exchanges
    WHERE status = 'approved' AND created_at >= {$interval12m}
    GROUP BY {$monthCol}
    ORDER BY month ASC
");
$monthlyStats = $stmt->fetchAll();

$stmt = $pdo->query("SELECT SUM(quantity) as total FROM waste_exchanges WHERE status = 'approved'");
$totalWaste = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as count FROM waste_exchanges WHERE status = 'approved'");
$totalTransactions = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT SUM(points_earned) as total FROM waste_exchanges WHERE status = 'approved'");
$totalPoints = $stmt->fetch()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin ZeroWaste</title>
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
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold"><i class="bi bi-bar-chart me-2 text-success"></i>Laporan Tracking Sampah</h4>
                            <p class="text-muted mb-0">Analisis data penukaran sampah</p>
                        </div>
                        <a href="?export=csv" class="btn btn-success">
                            <i class="bi bi-download me-2"></i>Export CSV
                        </a>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card stats-card shadow-sm h-100 border-start border-4 border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                                        <i class="bi bi-recycle fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?= number_format($totalWaste, 1) ?> kg</h3>
                                        <small class="text-muted">Total Sampah Terkumpul</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card shadow-sm h-100 border-start border-4 border-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="bi bi-arrow-left-right fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?= number_format($totalTransactions) ?></h3>
                                        <small class="text-muted">Total Transaksi</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card shadow-sm h-100 border-start border-4 border-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                                        <i class="bi bi-coin fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?= number_format($totalPoints) ?></h3>
                                        <small class="text-muted">Total Poin Diberikan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-graph-up me-2 text-success"></i>Tren Bulanan (12 Bulan)</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="monthlyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-pie-chart me-2 text-success"></i>Per Kategori</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-list-check me-2 text-success"></i>Sampah per Kategori</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Kategori</th>
                                                <th>Jumlah Transaksi</th>
                                                <th>Total (kg)</th>
                                                <th>Poin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categoryStats as $cat): ?>
                                            <tr>
                                                <td><span class="text-capitalize badge bg-success-subtle text-success"><?= sanitize($cat['category']) ?></span></td>
                                                <td><?= number_format($cat['count']) ?></td>
                                                <td><?= number_format($cat['total_quantity'], 1) ?></td>
                                                <td><?= number_format($cat['total_points']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-trophy me-2 text-warning"></i>User Paling Aktif</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>User</th>
                                                <th>Transaksi</th>
                                                <th>Total (kg)</th>
                                                <th>Poin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topUsers as $i => $u): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($i < 3): ?>
                                                    <span class="badge bg-<?= $i == 0 ? 'warning' : ($i == 1 ? 'secondary' : 'danger') ?>">
                                                        <?= $i + 1 ?>
                                                    </span>
                                                    <?php else: ?>
                                                    <?= $i + 1 ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?= sanitize($u['full_name']) ?></div>
                                                    <small class="text-muted">@<?= sanitize($u['username']) ?></small>
                                                </td>
                                                <td><?= number_format($u['exchange_count']) ?></td>
                                                <td><?= number_format($u['total_waste'], 1) ?></td>
                                                <td><span class="badge bg-success"><?= number_format($u['points']) ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const monthlyLabels = <?= json_encode(array_map(function($d) { return date('M Y', strtotime($d['month'] . '-01')); }, $monthlyStats)) ?>;
        const monthlyValues = <?= json_encode(array_map(function($d) { return floatval($d['total_quantity']); }, $monthlyStats)) ?>;
        
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: monthlyLabels.length ? monthlyLabels : ['Tidak ada data'],
                datasets: [{
                    label: 'Total Sampah (kg)',
                    data: monthlyValues.length ? monthlyValues : [0],
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

        const categoryLabels = <?= json_encode(array_column($categoryStats, 'category')) ?>;
        const categoryValues = <?= json_encode(array_map(function($d) { return floatval($d['total_quantity']); }, $categoryStats)) ?>;
        const colors = ['#198754', '#20c997', '#0dcaf0', '#ffc107', '#fd7e14', '#dc3545', '#6f42c1', '#6c757d'];
        
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryLabels.length ? categoryLabels : ['Belum ada data'],
                datasets: [{
                    data: categoryValues.length ? categoryValues : [1],
                    backgroundColor: categoryLabels.length ? colors.slice(0, categoryLabels.length) : ['#dee2e6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>
</html>
