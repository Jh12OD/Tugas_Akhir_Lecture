<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isUser()) {
    redirect('../login.php');
}

$userId = $_SESSION['user_id'];

if (isset($_GET['read'])) {
    $msgId = intval($_GET['read']);
    $stmt = $pdo->prepare("UPDATE inbox SET is_read = 1 WHERE id = ? AND (user_id = ? OR is_global = 1)");
    $stmt->execute([$msgId, $userId]);
}

if (isset($_GET['read_all'])) {
    $stmt = $pdo->prepare("UPDATE inbox SET is_read = 1 WHERE user_id = ? OR is_global = 1");
    $stmt->execute([$userId]);
    alert('Semua pesan ditandai sudah dibaca.', 'success');
    redirect('inbox.php');
}

$stmt = $pdo->prepare("
    SELECT * FROM inbox 
    WHERE user_id = ? OR is_global = 1
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$messages = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM events WHERE is_active = 1 AND event_date >= CURRENT_DATE ORDER BY event_date ASC");
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox - ZeroWaste</title>
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
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold"><i class="bi bi-inbox me-2 text-success"></i>Inbox</h4>
                            <p class="text-muted mb-0">Notifikasi, event, dan berita terbaru</p>
                        </div>
                        <a href="?read_all=1" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-check-all me-1"></i>Tandai Semua Dibaca
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-envelope me-2 text-success"></i>Pesan</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($messages)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-inbox fs-1 mb-2"></i>
                                    <p class="mb-0">Tidak ada pesan</p>
                                </div>
                                <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($messages as $msg): ?>
                                    <a href="?read=<?= $msg['id'] ?>" class="list-group-item list-group-item-action inbox-item <?= !$msg['is_read'] ? 'unread' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="me-3">
                                                <?php
                                                $icon = 'bell';
                                                $color = 'secondary';
                                                switch ($msg['type']) {
                                                    case 'event': $icon = 'calendar-event'; $color = 'primary'; break;
                                                    case 'news': $icon = 'newspaper'; $color = 'info'; break;
                                                    case 'system': $icon = 'gear'; $color = 'warning'; break;
                                                    case 'notification': $icon = 'bell'; $color = 'success'; break;
                                                }
                                                ?>
                                                <div class="bg-<?= $color ?> bg-opacity-10 text-<?= $color ?> rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                    <i class="bi bi-<?= $icon ?>"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="mb-1 fw-bold"><?= sanitize($msg['title']) ?></h6>
                                                    <small class="text-muted"><?= formatDateTime($msg['created_at']) ?></small>
                                                </div>
                                                <p class="mb-1 text-muted"><?= sanitize(substr($msg['message'], 0, 150)) ?><?= strlen($msg['message']) > 150 ? '...' : '' ?></p>
                                                <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?>"><?= ucfirst($msg['type']) ?></span>
                                                <?php if ($msg['is_global']): ?>
                                                <span class="badge bg-secondary-subtle text-secondary">Broadcast</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-calendar-event me-2 text-success"></i>Event Mendatang</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($events)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-calendar-x fs-1 mb-2"></i>
                                    <p class="mb-0">Tidak ada event mendatang</p>
                                </div>
                                <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($events as $event): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex">
                                            <div class="bg-success text-white rounded text-center me-3 p-2" style="min-width: 50px;">
                                                <div class="fw-bold"><?= date('d', strtotime($event['event_date'])) ?></div>
                                                <small><?= date('M', strtotime($event['event_date'])) ?></small>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-bold"><?= sanitize($event['title']) ?></h6>
                                                <small class="text-muted">
                                                    <?php if ($event['event_time']): ?>
                                                    <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($event['event_time'])) ?>
                                                    <?php endif; ?>
                                                    <?php if ($event['location']): ?>
                                                    <br><i class="bi bi-geo-alt me-1"></i><?= sanitize($event['location']) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
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
