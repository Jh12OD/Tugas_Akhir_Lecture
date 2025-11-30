<?php
// Pastikan session sudah dimulai jika belum (antisipasi jika db.php tidak ter-include di atas)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeroWaste Tracker</title>
    
    <!-- Link ke file CSS eksternal -->
    <link rel="stylesheet" href="style.css">
    
    <!-- Google Fonts (Opsional, agar font lebih bagus) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="logo">
        <!-- Ikon Daun Sederhana dengan CSS/SVG -->
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-leaf"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 1.45 9.66z"></path></svg>
        ZeroWaste Tracker
    </a>
    
    <div class="nav-links">
        <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Tampilkan nama user HANYA jika sudah login -->
            <!-- htmlspecialchars() digunakan untuk keamanan (mencegah XSS) -->
            <span>Halo, <b><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></b></span>
            
            <a href="index.php">Dashboard</a>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin.php">Admin Panel</a>
            <?php endif; ?>
            
            <a href="logout.php" class="btn-logout">Logout</a>
        <?php else: ?>
            <!-- Jika belum login, tampilkan tombol Login/Daftar -->
            <a href="login.php">Masuk</a>
            <a href="register.php" class="btn">Daftar Sekarang</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Mulai Container Utama (Ditutup di footer.php) -->
<div class="container fade-in">