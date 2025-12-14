<div class="sidebar d-flex flex-column" style="width: 250px;">
    <div class="p-4 text-white">
        <a href="dashboard.php" class="text-decoration-none text-white">
            <h4 class="fw-bold mb-0">
                <i class="bi bi-recycle me-2"></i>ZeroWaste
            </h4>
            <small class="opacity-75">Admin Panel</small>
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
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>" href="users.php">
                    <i class="bi bi-people"></i> Manage Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'waste-requests.php' ? 'active' : '' ?>" href="waste-requests.php">
                    <i class="bi bi-arrow-repeat"></i> Penukaran Sampah
                    <?php
                    $pendingCount = $pdo->query("SELECT COUNT(*) FROM waste_exchanges WHERE status = 'pending'")->fetchColumn();
                    if ($pendingCount > 0):
                    ?>
                    <span class="badge bg-danger ms-1"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>" href="reports.php">
                    <i class="bi bi-bar-chart"></i> Laporan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : '' ?>" href="events.php">
                    <i class="bi bi-calendar-event"></i> Kelola Event
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'forum.php' ? 'active' : '' ?>" href="forum.php">
                    <i class="bi bi-chat-dots"></i> Moderasi Forum
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'locations.php' ? 'active' : '' ?>" href="locations.php">
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
