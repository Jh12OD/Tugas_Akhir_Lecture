<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$totalUsers = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM waste_exchanges WHERE status = 'pending'");
$pendingExchanges = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT SUM(quantity) as total FROM waste_exchanges WHERE status = 'approved'");
$totalWaste = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as count FROM forum_posts WHERE is_deleted = 0");
$totalPosts = $stmt->fetch()['count'];

$interval30 = dateInterval(30);
$stmt = $pdo->query("
    SELECT category, SUM(quantity) as total 
    FROM waste_exchanges 
    WHERE status = 'approved' AND created_at >= {$interval30}
    GROUP BY category
    ORDER BY total DESC
");
$wasteByCategory = $stmt->fetchAll();

$interval6m = dateInterval(180);
$monthCol = toYearMonth('created_at');
$stmt = $pdo->query("
    SELECT {$monthCol} as month, SUM(quantity) as total 
    FROM waste_exchanges 
    WHERE status = 'approved' AND created_at >= {$interval6m}
    GROUP BY {$monthCol}
    ORDER BY month ASC
");
$monthlyData = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT u.id, u.full_name, u.username, u.points,
           SUM(we.quantity) as total_waste
    FROM users u
    LEFT JOIN waste_exchanges we ON u.id = we.user_id AND we.status = 'approved'
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY total_waste DESC
    LIMIT 5
");
$topUsers = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT we.*, u.full_name 
    FROM waste_exchanges we
    JOIN users u ON we.user_id = u.id
    WHERE we.status = 'pending'
    ORDER BY we.created_at DESC
    LIMIT 5
");
$recentPending = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT 
        rl.name AS location_name,
        rl.city AS location_city,
        COUNT(we.id) AS total_transactions,
        SUM(CASE WHEN we.unit = 'kg' THEN we.quantity ELSE we.quantity * 0.1 END) AS total_waste_kg,
        AVG(CASE WHEN we.unit = 'kg' THEN we.quantity ELSE we.quantity * 0.1 END) AS avg_waste_per_transaction,
        (
            -- Subquery untuk mencari kategori yang paling banyak ditransaksikan di lokasi ini
            SELECT we2.category
            FROM waste_exchanges we2
            WHERE we2.location_id = rl.id AND we2.status = 'approved'
            GROUP BY we2.category
            ORDER BY COUNT(we2.id) DESC, we2.category ASC
            LIMIT 1
        ) AS top_category
    FROM waste_exchanges we
    JOIN recycling_locations rl ON we.location_id = rl.id
    WHERE we.status = 'approved'
    GROUP BY rl.id, rl.name, rl.city
    ORDER BY total_waste_kg DESC
");
$locationStats = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ZeroWaste</title>
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
                        <h4 class="fw-bold">Admin Dashboard</h4>
                        <p class="text-muted">Selamat datang, <?= sanitize($_SESSION['full_name']) ?></p>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card stats-card shadow-sm h-100 border-start border-4 border-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="bi bi-people fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?= number_format($totalUsers) ?></h3>
                                        <small class="text-muted">Total User</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stats-card shadow-sm h-100 border-start border-4 border-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                                        <i class="bi bi-hourglass-split fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?= number_format($pendingExchanges) ?></h3>
                                        <small class="text-muted">Pending Request</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stats-card shadow-sm h-100 border-start border-4 border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                                        <i class="bi bi-recycle fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?= number_format($totalWaste, 1) ?> kg</h3>
                                        <small class="text-muted">Total Sampah</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stats-card shadow-sm h-100 border-start border-4 border-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                                        <i class="bi bi-chat-dots fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?= number_format($totalPosts) ?></h3>
                                        <small class="text-muted">Forum Posts</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-bank me-2 text-primary"></i>Statistik Penukaran Per Bank Sampah</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($locationStats)): ?>
                                <div class="p-4 text-center text-muted">
                                    <p class="mb-0">Tidak ada data penukaran yang disetujui untuk ditampilkan.</p>
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Bank Sampah (Lokasi)</th>
                                                <th>Total Transaksi</th>
                                                <th>Total Sampah (Kg)</th>
                                                <th>Rata-rata Sampah / Transaksi (Kg)</th>
                                                <th>Kategori Terbanyak</th> </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($locationStats as $stat): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= sanitize($stat['location_name']) ?></strong> 
                                                    <small class="text-muted">(<?= sanitize($stat['location_city']) ?>)</small>
                                                </td>
                                                <td><?= number_format($stat['total_transactions']) ?></td>
                                                <td><?= number_format($stat['total_waste_kg'] ?? 0, 2) ?></td>
                                                <td><?= number_format($stat['avg_waste_per_transaction'] ?? 0, 2) ?></td>
                                                <td>
                                                    <span class="text-capitalize fw-bold">
                                                        <?= sanitize($stat['top_category'] ?? '-') ?>
                                                    </span>
                                                </td> </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-graph-up me-2 text-success"></i>Sampah Terkumpul (6 Bulan Terakhir)</h5>
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
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-hourglass me-2 text-warning"></i>Request Pending</h5>
                                <a href="waste-requests.php" class="btn btn-sm btn-outline-success">Lihat Semua</a>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($recentPending)): ?>
                                <div class="p-4 text-center text-muted">
                                    <p class="mb-0">Tidak ada request pending</p>
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>User</th>
                                                <th>Kategori</th>
                                                <th>Jumlah</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentPending as $req): ?>
                                            <tr>
                                                <td><?= sanitize($req['full_name']) ?></td>
                                                <td><span class="text-capitalize"><?= sanitize($req['category']) ?></span></td>
                                                <td><?= number_format($req['quantity'], 1) ?> <?= $req['unit'] ?></td>
                                                <td>
                                                    <a href="waste-requests.php?action=view&id=<?= $req['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
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
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top Users</h5>
                                <a href="users.php" class="btn btn-sm btn-outline-success">Lihat Semua</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Nama</th>
                                                <th>Sampah (kg)</th>
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
                                                <td><?= sanitize($u['full_name']) ?></td>
                                                <td><?= number_format($u['total_waste'] ?? 0, 1) ?></td>
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
        const monthlyLabels = <?= json_encode(array_map(function($d) { return date('M Y', strtotime($d['month'] . '-01')); }, $monthlyData)) ?>;
        const monthlyValues = <?= json_encode(array_map(function($d) { return floatval($d['total']); }, $monthlyData)) ?>;
        
        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: monthlyLabels.length ? monthlyLabels : ['Tidak ada data'],
                datasets: [{
                    label: 'Total Sampah (kg)',
                    data: monthlyValues.length ? monthlyValues : [0],
                    backgroundColor: 'rgba(25, 135, 84, 0.7)',
                    borderColor: '#198754',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        const categoryLabels = <?= json_encode(array_column($wasteByCategory, 'category')) ?>;
        const categoryValues = <?= json_encode(array_map(function($d) { return floatval($d['total']); }, $wasteByCategory)) ?>;
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