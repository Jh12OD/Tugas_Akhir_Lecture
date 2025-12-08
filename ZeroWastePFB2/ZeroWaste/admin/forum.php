<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete_post') {
        $postId = intval($_POST['post_id'] ?? 0);
        $pdo->prepare("UPDATE forum_posts SET is_deleted = 1 WHERE id = ?")->execute([$postId]);
        alert('Postingan berhasil dihapus!', 'success');
    } elseif ($action === 'delete_comment') {
        $commentId = intval($_POST['comment_id'] ?? 0);
        $pdo->prepare("UPDATE forum_comments SET is_deleted = 1 WHERE id = ?")->execute([$commentId]);
        alert('Komentar berhasil dihapus!', 'success');
    } elseif ($action === 'block_user') {
        $userId = intval($_POST['user_id'] ?? 0);
        $pdo->prepare("UPDATE users SET is_forum_blocked = 1 WHERE id = ?")->execute([$userId]);
        alert('User berhasil diblokir dari forum!', 'success');
    } elseif ($action === 'unblock_user') {
        $userId = intval($_POST['user_id'] ?? 0);
        $pdo->prepare("UPDATE users SET is_forum_blocked = 0 WHERE id = ?")->execute([$userId]);
        alert('User berhasil di-unblock dari forum!', 'success');
    }
    
    redirect('forum.php');
}

$stmt = $pdo->query("
    SELECT p.*, u.full_name, u.username, u.is_forum_blocked,
           (SELECT COUNT(*) FROM forum_comments WHERE post_id = p.id AND is_deleted = 0) as comment_count
    FROM forum_posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.is_deleted = 0
    ORDER BY p.created_at DESC
    LIMIT 50
");
$posts = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' AND is_forum_blocked = 1 ORDER BY full_name");
$blockedUsers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderasi Forum - Admin ZeroWaste</title>
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
                        <h4 class="fw-bold"><i class="bi bi-chat-dots me-2 text-success"></i>Moderasi Forum</h4>
                        <p class="text-muted">Kelola postingan dan user forum</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-file-text me-2 text-success"></i>Postingan Forum</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($posts)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-chat-square-text fs-1 mb-3"></i>
                                    <p class="mb-0">Tidak ada postingan</p>
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>User</th>
                                                <th>Judul</th>
                                                <th>Komentar</th>
                                                <th>Tanggal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                            <?= strtoupper(substr($post['full_name'], 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold small"><?= sanitize($post['full_name']) ?></div>
                                                            <?php if ($post['is_forum_blocked']): ?>
                                                            <span class="badge bg-danger" style="font-size: 0.6rem;">Blocked</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?= sanitize(substr($post['title'], 0, 40)) ?><?= strlen($post['title']) > 40 ? '...' : '' ?></div>
                                                    <small class="text-muted"><?= sanitize(substr($post['content'], 0, 50)) ?>...</small>
                                                </td>
                                                <td><span class="badge bg-secondary"><?= $post['comment_count'] ?></span></td>
                                                <td><small><?= formatDateTime($post['created_at']) ?></small></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="forum-detail.php?id=<?= $post['id'] ?>" class="btn btn-outline-primary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus postingan ini?')">
                                                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                            <input type="hidden" name="action" value="delete_post">
                                                            <button type="submit" class="btn btn-outline-danger">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                        <?php if (!$post['is_forum_blocked']): ?>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Block user ini dari forum?')">
                                                            <input type="hidden" name="user_id" value="<?= $post['user_id'] ?>">
                                                            <input type="hidden" name="action" value="block_user">
                                                            <button type="submit" class="btn btn-outline-warning" title="Block User">
                                                                <i class="bi bi-person-x"></i>
                                                            </button>
                                                        </form>
                                                        <?php endif; ?>
                                                    </div>
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

                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-person-x me-2 text-danger"></i>User Diblokir</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($blockedUsers)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-check-circle fs-1 mb-3"></i>
                                    <p class="mb-0">Tidak ada user yang diblokir</p>
                                </div>
                                <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($blockedUsers as $user): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold"><?= sanitize($user['full_name']) ?></div>
                                            <small class="text-muted">@<?= sanitize($user['username']) ?></small>
                                        </div>
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="action" value="unblock_user">
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-unlock"></i> Unblock
                                            </button>
                                        </form>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-info"></i>Panduan Moderasi</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="bi bi-eye text-primary me-2"></i>
                                        <strong>View:</strong> Lihat detail postingan
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-trash text-danger me-2"></i>
                                        <strong>Delete:</strong> Hapus postingan yang melanggar
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-person-x text-warning me-2"></i>
                                        <strong>Block:</strong> Blokir user dari forum
                                    </li>
                                    <li>
                                        <i class="bi bi-unlock text-success me-2"></i>
                                        <strong>Unblock:</strong> Buka blokir user
                                    </li>
                                </ul>
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
