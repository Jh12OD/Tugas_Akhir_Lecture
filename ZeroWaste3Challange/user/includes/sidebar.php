<div class="sidebar d-flex flex-column" style="width: 250px;">
    <div class="p-4 text-white">
        <a href="dashboard.php" class="text-decoration-none text-white">
            <h4 class="fw-bold mb-0">
                <i class="bi bi-recycle me-2"></i>ZeroWaste
            </h4>
        </a>
    </div>
    
    <nav class="flex-grow-1">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'tukar-sampah.php' ? 'active' : '' ?>" href="tukar-sampah.php">
                    <i class="bi bi-arrow-repeat"></i> Tukar Sampah
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'catatan-harian.php' ? 'active' : '' ?>" href="catatan-harian.php">
                    <i class="bi bi-journal-text"></i> Catatan Harian
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>" href="profile.php">
                    <i class="bi bi-person"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'forum.php' || basename($_SERVER['PHP_SELF']) == 'forum-post.php' ? 'active' : '' ?>" href="forum.php">
                    <i class="bi bi-chat-dots"></i> Forum
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'inbox.php' ? 'active' : '' ?>" href="inbox.php">
                    <i class="bi bi-inbox"></i> Inbox
                    <?php
                    $unreadCount = $pdo->prepare("SELECT COUNT(*) FROM inbox WHERE (user_id = ? OR is_global = 1) AND is_read = 0");
                    $unreadCount->execute([$_SESSION['user_id']]);
                    $count = $unreadCount->fetchColumn();
                    if ($count > 0):
                    ?>
                    <span class="badge bg-danger ms-1"><?= $count ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'lokasi.php' ? 'active' : '' ?>" href="lokasi.php">
                    <i class="bi bi-geo-alt"></i> Lokasi Cabang
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="p-3 border-top border-white border-opacity-25">
        <a href="../logout.php" class="nav-link text-white-50">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>
</div>
