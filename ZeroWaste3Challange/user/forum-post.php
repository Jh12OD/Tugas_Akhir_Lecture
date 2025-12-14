<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isUser()) {
    redirect('../login.php');
}

$postId = intval($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

if (!$user) {
    session_destroy();
    redirect('../login.php');
}

$stmt = $pdo->prepare("
    SELECT p.*, u.full_name, u.username
    FROM forum_posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ? AND p.is_deleted = 0
");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    alert('Postingan tidak ditemukan.', 'danger');
    redirect('forum.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !($user['is_forum_blocked'] ?? false)) {
    $content = sanitize($_POST['comment'] ?? '');
    
    if (empty($content)) {
        alert('Komentar tidak boleh kosong!', 'danger');
    } else {
        $stmt = $pdo->prepare("INSERT INTO forum_comments (post_id, user_id, content) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$postId, $userId, $content])) {
            alert('Komentar berhasil ditambahkan!', 'success');
            redirect('forum-post.php?id=' . $postId);
        }
    }
}

$stmt = $pdo->prepare("
    SELECT c.*, u.full_name, u.username
    FROM forum_comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ? AND c.is_deleted = 0
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
    <title><?= sanitize($post['title']) ?> - Forum ZeroWaste</title>
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
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Forum
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <div class="d-flex mb-3">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <?= strtoupper(substr($post['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= sanitize($post['full_name']) ?></h6>
                                        <small class="text-muted">@<?= sanitize($post['username']) ?> - <?= formatDateTime($post['created_at']) ?></small>
                                    </div>
                                </div>
                                
                                <h3 class="fw-bold mb-3"><?= sanitize($post['title']) ?></h3>
                                <div class="mb-4">
                                    <?= nl2br(sanitize($post['content'])) ?>
                                </div>
                                
                                <?php if ($post['photo']): ?>
                                <div class="mb-4">
                                    <img src="../uploads/forum/<?= $post['photo'] ?>" class="img-fluid rounded">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-chat-dots me-2 text-success"></i>Komentar (<?= count($comments) ?>)</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!$user['is_forum_blocked']): ?>
                                <form method="POST" class="mb-4">
                                    <div class="mb-3">
                                        <textarea class="form-control" name="comment" rows="3" placeholder="Tulis komentar Anda..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-send me-2"></i>Kirim Komentar
                                    </button>
                                </form>
                                <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>Anda tidak dapat berkomentar karena akun Anda diblokir dari forum.
                                </div>
                                <?php endif; ?>

                                <?php if (empty($comments)): ?>
                                <p class="text-muted text-center py-3">Belum ada komentar. Jadilah yang pertama!</p>
                                <?php else: ?>
                                <hr>
                                <?php foreach ($comments as $comment): ?>
                                <div class="d-flex mb-4">
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <?= strtoupper(substr($comment['full_name'], 0, 1)) ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="bg-light rounded p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0 fw-bold"><?= sanitize($comment['full_name']) ?></h6>
                                                <small class="text-muted"><?= formatDateTime($comment['created_at']) ?></small>
                                            </div>
                                            <p class="mb-0"><?= nl2br(sanitize($comment['content'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
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
