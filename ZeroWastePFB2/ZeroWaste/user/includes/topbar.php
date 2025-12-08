<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid px-4">
        <button class="btn btn-link text-dark d-lg-none" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>
        
        <div class="ms-auto d-flex align-items-center">
            <a href="inbox.php" class="btn btn-link text-dark position-relative me-3">
                <i class="bi bi-bell fs-5"></i>
                <?php
                $unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM inbox WHERE (user_id = ? OR is_global = 1) AND is_read = 0");
                $unreadStmt->execute([$_SESSION['user_id']]);
                $unreadCount = $unreadStmt->fetchColumn();
                if ($unreadCount > 0):
                ?>
                <span class="notification-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                <?php endif; ?>
            </a>
            
            <div class="dropdown">
                <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                        <?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?>
                    </div>
                    <div class="d-none d-md-block">
                        <span class="text-dark"><?= sanitize($_SESSION['full_name']) ?></span>
                        <small class="d-block text-success"><?= number_format($_SESSION['points'] ?? 0) ?> Poin</small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        let overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });
        
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }
});
</script>
