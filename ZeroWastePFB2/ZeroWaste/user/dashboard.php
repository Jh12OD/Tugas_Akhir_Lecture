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

$stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM waste_exchanges WHERE user_id = ? AND status = 'approved'");
$stmt->execute([$userId]);
$totalWaste = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM waste_exchanges WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$userId]);
$pendingCount = $stmt->fetch()['count'];

$interval30 = dateInterval(30);
$stmt = $pdo->prepare("
    SELECT category, SUM(quantity) as total 
    FROM daily_waste_logs 
    WHERE user_id = ? AND log_date >= {$interval30}
    GROUP BY category
    ORDER BY total DESC
");
$stmt->execute([$userId]);
$wasteByCategory = $stmt->fetchAll();

$interval7 = dateInterval(7);
$dateCol = toDate('log_date');
$stmt = $pdo->prepare("
    SELECT {$dateCol} as date, SUM(quantity) as total 
    FROM daily_waste_logs 
    WHERE user_id = ? AND log_date >= {$interval7}
    GROUP BY {$dateCol}
    ORDER BY date ASC
");
$stmt->execute([$userId]);
$weeklyData = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM events WHERE is_active = 1 AND event_date >= CURRENT_DATE ORDER BY event_date ASC LIMIT 5");
$events = $stmt->fetchAll();

$badge = getBadge($user['points'] ?? 0);
$motivation = getMotivationalMessage($user['full_name'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ZeroWaste</title>
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
                        <div class="card border-0 bg-gradient-green text-white">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h4 class="mb-2">Halo, <?= sanitize($user['full_name']) ?>!</h4>
                                        <p class="mb-0 opacity-75"><?= $motivation ?></p>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <span class="badge-level badge-<?= $badge['color'] ?>">
                                            <i class="bi bi-<?= $badge['icon'] ?> me-2"></i>
                                            <?= $badge['name'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card stats-card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                                        <i class="bi bi-coin fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?= number_format($user['points']) ?></h3>
                                        <small class="text-muted">Total Poin</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stats-card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                                        <i class="bi bi-recycle fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?= number_format($totalWaste, 1) ?> kg</h3>
                                        <small class="text-muted">Total Sampah Ditukar</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stats-card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                                        <i class="bi bi-hourglass-split fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?= $pendingCount ?></h3>
                                        <small class="text-muted">Menunggu Persetujuan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stats-card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="bi bi-trophy fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?= $badge['name'] ?></h3>
                                        <small class="text-muted">Level Anda</small>
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
                                <h5 class="mb-0"><i class="bi bi-graph-up me-2 text-success"></i>Statistik Sampah 7 Hari Terakhir</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="weeklyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-pie-chart me-2 text-success"></i>Jenis Sampah</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-star me-2 text-success"></i>Eco Score Progress</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <div class="position-relative d-inline-block">
                                        <canvas id="ecoScoreRing" width="150" height="150"></canvas>
                                        <div class="eco-score-value">
                                            <h2 class="mb-0 fw-bold text-success"><?= $user['points'] ?></h2>
                                            <small class="text-muted">Poin</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Progress ke level berikutnya</span>
                                        <?php
                                        $nextLevel = 50;
                                        if ($user['points'] >= 50) $nextLevel = 200;
                                        if ($user['points'] >= 200) $nextLevel = 500;
                                        if ($user['points'] >= 500) $nextLevel = 1000;
                                        if ($user['points'] >= 1000) $nextLevel = $user['points'];
                                        $progress = min(100, ($user['points'] / $nextLevel) * 100);
                                        ?>
                                        <span class="text-success"><?= round($progress) ?>%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                                    </div>
                                    <small class="text-muted">Target: <?= number_format($nextLevel) ?> poin</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-calendar-event me-2 text-success"></i>Event Mendatang</h5>
                                <a href="inbox.php" class="btn btn-sm btn-outline-success">Lihat Semua</a>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($events)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-calendar-x fs-1 mb-2"></i>
                                    <p class="mb-0">Belum ada event mendatang</p>
                                </div>
                                <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($events as $event): ?>
                                    <div class="list-group-item px-4 py-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 fw-bold"><?= sanitize($event['title']) ?></h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar me-1"></i><?= formatDate($event['event_date']) ?>
                                                    <?php if ($event['location']): ?>
                                                    <i class="bi bi-geo-alt ms-2 me-1"></i><?= sanitize($event['location']) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-success-subtle text-success">Upcoming</span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-lightning me-2 text-success"></i>Aksi Cepat</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <a href="tukar-sampah.php" class="btn btn-outline-success w-100 py-3">
                                            <i class="bi bi-arrow-repeat fs-4 d-block mb-2"></i>
                                            Tukar Sampah
                                        </a>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <a href="catatan-harian.php" class="btn btn-outline-success w-100 py-3">
                                            <i class="bi bi-journal-plus fs-4 d-block mb-2"></i>
                                            Catat Sampah
                                        </a>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <a href="forum.php" class="btn btn-outline-success w-100 py-3">
                                            <i class="bi bi-chat-dots fs-4 d-block mb-2"></i>
                                            Forum
                                        </a>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <a href="lokasi.php" class="btn btn-outline-success w-100 py-3">
                                            <i class="bi bi-geo-alt fs-4 d-block mb-2"></i>
                                            Lokasi Cabang
                                        </a>
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
    <script>
        const weeklyLabels = <?= json_encode(array_map(function($d) { return date('d M', strtotime($d['date'])); }, $weeklyData)) ?>;
        const weeklyValues = <?= json_encode(array_map(function($d) { return floatval($d['total']); }, $weeklyData)) ?>;
        
        new Chart(document.getElementById('weeklyChart'), {
            type: 'line',
            data: {
                labels: weeklyLabels.length ? weeklyLabels : ['Tidak ada data'],
                datasets: [{
                    label: 'Sampah (kg)',
                    data: weeklyValues.length ? weeklyValues : [0],
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        const categoryLabels = <?= json_encode(array_column($wasteByCategory, 'category')) ?>;
        const categoryValues = <?= json_encode(array_map(function($d) { return floatval($d['total']); }, $wasteByCategory)) ?>;
        const categoryColors = ['#198754', '#20c997', '#0dcaf0', '#ffc107', '#fd7e14', '#dc3545', '#6f42c1', '#6c757d'];
        
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryLabels.length ? categoryLabels : ['Belum ada data'],
                datasets: [{
                    data: categoryValues.length ? categoryValues : [1],
                    backgroundColor: categoryLabels.length ? categoryColors.slice(0, categoryLabels.length) : ['#dee2e6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        const ctx = document.getElementById('ecoScoreRing').getContext('2d');
        const progress = <?= $progress ?>;
        const startAngle = -0.5 * Math.PI;
        const endAngle = startAngle + (2 * Math.PI * progress / 100);
        
        ctx.beginPath();
        ctx.arc(75, 75, 65, 0, 2 * Math.PI);
        ctx.strokeStyle = '#e9ecef';
        ctx.lineWidth = 15;
        ctx.stroke();
        
        ctx.beginPath();
        ctx.arc(75, 75, 65, startAngle, endAngle);
        ctx.strokeStyle = '#198754';
        ctx.lineWidth = 15;
        ctx.lineCap = 'round';
        ctx.stroke();
    </script>
</body>
</html>
