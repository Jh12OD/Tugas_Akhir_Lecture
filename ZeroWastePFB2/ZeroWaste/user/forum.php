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

if ($user['is_forum_blocked'] ?? false) {
    alert('Akun Anda diblokir dari forum. Hubungi admin untuk informasi lebih lanjut.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !($user['is_forum_blocked'] ?? false)) {
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['photo'], 'forum');
        if ($upload['success']) {
            $photo = $upload['filename'];
        }
    }
    
    if (empty($title) || empty($content)) {
        alert('Judul dan konten harus diisi!', 'danger');
    } else {
        $stmt = $pdo->prepare("INSERT INTO forum_posts (user_id, title, content, photo) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$userId, $title, $content, $photo])) {
            alert('Postingan berhasil dibuat!', 'success');
            redirect('forum.php');
        } else {
            alert('Terjadi kesalahan. Silakan coba lagi.', 'danger');
        }
    }
}

$stmt = $pdo->query("
    SELECT p.*, u.full_name, u.username,
           (SELECT COUNT(*) FROM forum_comments WHERE post_id = p.id AND is_deleted = 0) as comment_count
    FROM forum_posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.is_deleted = 0
    ORDER BY p.created_at DESC
    LIMIT 50
");
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - ZeroWaste</title>
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
                            <h4 class="fw-bold"><i class="bi bi-chat-dots me-2 text-success"></i>Forum Komunitas</h4>
                            <p class="text-muted mb-0">Berbagi tips dan pengalaman dengan sesama pengguna</p>
                        </div>
                        <?php if (!$user['is_forum_blocked']): ?>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newPostModal">
                            <i class="bi bi-plus-lg me-2"></i>Buat Postingan
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <?php if (empty($posts)): ?>
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-chat-square-text fs-1 text-muted mb-3"></i>
                                <h5>Belum ada postingan</h5>
                                <p class="text-muted">Jadilah yang pertama membuat postingan!</p>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                        <div class="card shadow-sm mb-3 forum-post">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                            <?= strtoupper(substr($post['full_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold"><?= sanitize($post['full_name']) ?></h6>
                                            <small class="text-muted">@<?= sanitize($post['username']) ?> - <?= formatDateTime($post['created_at']) ?></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <a href="forum-post.php?id=<?= $post['id'] ?>" class="text-decoration-none">
                                    <h5 class="text-dark fw-bold mb-2"><?= sanitize($post['title']) ?></h5>
                                </a>
                                <p class="text-muted mb-3"><?= nl2br(sanitize(substr($post['content'], 0, 300))) ?><?= strlen($post['content']) > 300 ? '...' : '' ?></p>
                                
                                <?php if ($post['photo']): ?>
                                <div class="mb-3">
                                    <img src="../uploads/forum/<?= $post['photo'] ?>" class="img-fluid rounded" style="max-height: 300px;">
                                </div>
                                <?php endif; ?>
                                
                                <div class="d-flex align-items-center">
                                    <a href="forum-post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-success me-2">
                                        <i class="bi bi-chat-dots me-1"></i><?= $post['comment_count'] ?> Komentar
                                    </a>
                                    <a href="forum-post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        Baca Selengkapnya
                                    </a>
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

    <div class="modal fade" id="newPostModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-success"></i>Buat Postingan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Konten <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="6" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Foto (Opsional)</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-send me-2"></i>Posting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
