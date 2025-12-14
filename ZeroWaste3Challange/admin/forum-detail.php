<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$postId = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete_comment') {
        $commentId = intval($_POST['comment_id'] ?? 0);
        $pdo->prepare("UPDATE forum_comments SET is_deleted = 1 WHERE id = ?")->execute([$commentId]);
        alert('Komentar berhasil dihapus!', 'success');
    } elseif ($action === 'delete_post') {
        $pdo->prepare("UPDATE forum_posts SET is_deleted = 1 WHERE id = ?")->execute([$postId]);
        alert('Postingan berhasil dihapus!', 'success');
        redirect('forum.php');
    }
    
    redirect('forum-detail.php?id=' . $postId);
}

$stmt = $pdo->prepare("
    SELECT p.*, u.full_name, u.username, u.is_forum_blocked
    FROM forum_posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    alert('Postingan tidak ditemukan.', 'danger');
    redirect('forum.php');
}

$stmt = $pdo->prepare("
    SELECT c.*, u.full_name, u.username
    FROM forum_comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
");
$stmt->execute([$postId]);
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Forum - Admin ZeroWaste</title>
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
                        <a href="forum.php" class="btn btn-outline-secondary mb-3">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Moderasi Forum
                        </a>
                        <h4 class="fw-bold"><i class="bi bi-chat-square-text me-2 text-success"></i>Detail Postingan</h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                        <?= strtoupper(substr($post['full_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= sanitize($post['full_name'] ?? '') ?></h6>
                                        <small class="text-muted">@<?= sanitize($post['username'] ?? '') ?> &bull; <?= formatDateTime($post['created_at'] ?? '') ?></small>
                                        <?php if ($post['is_forum_blocked'] ?? false): ?>
                                        <span class="badge bg-danger ms-2">Blocked</span>
                                        <?php endif; ?>
                                        <?php if ($post['is_deleted'] ?? false): ?>
                                        <span class="badge bg-secondary ms-2">Deleted</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <form method="POST" onsubmit="return confirm('Hapus postingan ini?')">
                                    <input type="hidden" name="action" value="delete_post">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash me-1"></i>Hapus Post
                                    </button>
                                </form>
                            </div>
                            <div class="card-body">
                                <h4 class="fw-bold mb-3"><?= sanitize($post['title'] ?? '') ?></h4>
                                
                                <?php if (!empty($post['photo'])): ?>
                                <div class="mb-3">
                                    <img src="../uploads/forum/<?= $post['photo'] ?>" alt="Post Image" class="img-fluid rounded" style="max-height: 400px;">
                                </div>
                                <?php endif; ?>
                                
                                <div class="post-content">
                                    <?= nl2br(sanitize($post['content'] ?? '')) ?>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-chat-dots me-2 text-success"></i>
                                    Komentar (<?= count($comments) ?>)
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($comments)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-chat fs-1 mb-3"></i>
                                    <p class="mb-0">Belum ada komentar</p>
                                </div>
                                <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($comments as $comment): ?>
                                    <div class="list-group-item <?= ($comment['is_deleted'] ?? false) ? 'bg-light' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="d-flex">
                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                                    <?= strtoupper(substr($comment['full_name'] ?? 'U', 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold small">
                                                        <?= sanitize($comment['full_name'] ?? '') ?>
                                                        <span class="text-muted fw-normal">@<?= sanitize($comment['username'] ?? '') ?></span>
                                                        <?php if ($comment['is_deleted'] ?? false): ?>
                                                        <span class="badge bg-secondary">Deleted</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted"><?= formatDateTime($comment['created_at'] ?? '') ?></small>
                                                    <p class="mb-0 mt-2"><?= nl2br(sanitize($comment['content'] ?? '')) ?></p>
                                                </div>
                                            </div>
                                            <?php if (!($comment['is_deleted'] ?? false)): ?>
                                            <form method="POST" onsubmit="return confirm('Hapus komentar ini?')">
                                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                <input type="hidden" name="action" value="delete_comment">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-info"></i>Info Postingan</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted">Post ID</td>
                                        <td class="fw-bold">#<?= $post['id'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">User ID</td>
                                        <td class="fw-bold">#<?= $post['user_id'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Dibuat</td>
                                        <td class="fw-bold"><?= formatDateTime($post['created_at'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Komentar</td>
                                        <td class="fw-bold"><?= count($comments) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Foto</td>
                                        <td class="fw-bold"><?= !empty($post['photo']) ? 'Ya' : 'Tidak' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Status</td>
                                        <td>
                                            <?php if ($post['is_deleted'] ?? false): ?>
                                            <span class="badge bg-secondary">Deleted</span>
                                            <?php else: ?>
                                            <span class="badge bg-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
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
