<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $eventTime = $_POST['event_time'] ?? null;
        $location = sanitize($_POST['location'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $poster = null;
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['poster'], 'events');
            if ($upload['success']) {
                $poster = $upload['filename'];
            }
        }
        
        if ($action === 'create') {
            $sql = "INSERT INTO events (title, description, event_date, event_time, location, poster, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$title, $description, $eventDate, $eventTime, $location, $poster, $isActive, $_SESSION['user_id']];
            $pdo->prepare($sql)->execute($params);
            alert('Event berhasil dibuat!', 'success');
        } else {
            $eventId = intval($_POST['event_id'] ?? 0);
            if ($poster) {
                $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, poster = ?, is_active = ? WHERE id = ?";
                $params = [$title, $description, $eventDate, $eventTime, $location, $poster, $isActive, $eventId];
            } else {
                $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, is_active = ? WHERE id = ?";
                $params = [$title, $description, $eventDate, $eventTime, $location, $isActive, $eventId];
            }
            $pdo->prepare($sql)->execute($params);
            alert('Event berhasil diperbarui!', 'success');
        }
    } elseif ($action === 'delete') {
        $eventId = intval($_POST['event_id'] ?? 0);
        $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$eventId]);
        alert('Event berhasil dihapus!', 'success');
    } elseif ($action === 'broadcast') {
        $title = sanitize($_POST['broadcast_title'] ?? '');
        $message = sanitize($_POST['broadcast_message'] ?? '');
        $type = sanitize($_POST['broadcast_type'] ?? 'notification');
        
        $stmt = $pdo->prepare("INSERT INTO inbox (title, message, type, is_global, created_by) VALUES (?, ?, ?, 1, ?)");
        $stmt->execute([$title, $message, $type, $_SESSION['user_id']]);
        alert('Broadcast berhasil dikirim ke semua user!', 'success');
    }
    
    redirect('events.php');
}

$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Event - Admin ZeroWaste</title>
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
                            <h4 class="fw-bold"><i class="bi bi-calendar-event me-2 text-success"></i>Kelola Event & Banner</h4>
                            <p class="text-muted mb-0">Buat dan kelola event untuk user</p>
                        </div>
                        <div>
                            <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#broadcastModal">
                                <i class="bi bi-megaphone me-2"></i>Broadcast
                            </button>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
                                <i class="bi bi-plus-lg me-2"></i>Buat Event
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <?php if (empty($events)): ?>
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-calendar-x fs-1 text-muted mb-3"></i>
                                <h5>Belum ada event</h5>
                                <p class="text-muted">Buat event pertama Anda</p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($events as $event): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm h-100">
                            <?php if ($event['poster']): ?>
                            <img src="../uploads/events/<?= $event['poster'] ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                            <div class="card-img-top bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-calendar-event text-success" style="font-size: 4rem;"></i>
                            </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title fw-bold mb-0"><?= sanitize($event['title']) ?></h5>
                                    <?php if ($event['is_active']): ?>
                                    <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text text-muted small"><?= sanitize(substr($event['description'], 0, 100)) ?>...</p>
                                <div class="mb-3">
                                    <small class="text-muted d-block">
                                        <i class="bi bi-calendar me-1"></i><?= formatDate($event['event_date']) ?>
                                        <?php if ($event['event_time']): ?>
                                        <i class="bi bi-clock ms-2 me-1"></i><?= date('H:i', strtotime($event['event_time'])) ?>
                                        <?php endif; ?>
                                    </small>
                                    <?php if ($event['location']): ?>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-geo-alt me-1"></i><?= sanitize($event['location']) ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <div class="btn-group w-100">
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $event['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Hapus event ini?')">
                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="editModal<?= $event['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Event</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Judul Event</label>
                                                <input type="text" class="form-control" name="title" value="<?= sanitize($event['title']) ?>" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Deskripsi</label>
                                                <textarea class="form-control" name="description" rows="4" required><?= sanitize($event['description']) ?></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Tanggal</label>
                                                <input type="date" class="form-control" name="event_date" value="<?= $event['event_date'] ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Waktu</label>
                                                <input type="time" class="form-control" name="event_time" value="<?= $event['event_time'] ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Lokasi</label>
                                                <input type="text" class="form-control" name="location" value="<?= sanitize($event['location']) ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Poster Baru (Opsional)</label>
                                                <input type="file" class="form-control" name="poster" accept="image/*">
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active_<?= $event['id'] ?>" <?= $event['is_active'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="is_active_<?= $event['id'] ?>">Event Aktif</label>
                                                </div>
                                            </div>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-success"></i>Buat Event Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Judul Event</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="description" rows="4" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="event_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Waktu</label>
                                <input type="time" class="form-control" name="event_time">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Lokasi</label>
                                <input type="text" class="form-control" name="location">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Poster</label>
                                <input type="file" class="form-control" name="poster" accept="image/*">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active_new" checked>
                                    <label class="form-check-label" for="is_active_new">Event Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Buat Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="broadcastModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-megaphone me-2 text-primary"></i>Broadcast ke Semua User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="broadcast">
                        <div class="mb-3">
                            <label class="form-label">Tipe</label>
                            <select class="form-select" name="broadcast_type">
                                <option value="notification">Notifikasi</option>
                                <option value="event">Event</option>
                                <option value="news">Berita</option>
                                <option value="system">Sistem</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" class="form-control" name="broadcast_title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pesan</label>
                            <textarea class="form-control" name="broadcast_message" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Kirim Broadcast</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
