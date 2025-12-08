<?php
require_once 'config/database.php';
require_once 'config/functions.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('user/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeroWaste - Ubah Sampah Jadi Berkah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-recycle me-2"></i>ZeroWaste
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#locations">Lokasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light ms-2" href="login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section bg-gradient-green text-white py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Ubah Sampah Jadi Berkah</h1>
                    <p class="lead mb-4">Bergabunglah dengan komunitas ZeroWaste dan mulai tukarkan sampah bekasmu menjadi poin yang bisa ditukar dengan berbagai hadiah menarik!</p>
                    <div class="d-flex gap-3">
                        <a href="register.php" class="btn btn-light btn-lg">
                            <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                        </a>
                        <a href="#about" class="btn btn-outline-light btn-lg">Pelajari Lebih</a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="bi bi-recycle display-1" style="font-size: 15rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="fw-bold mb-4">Apa itu ZeroWaste?</h2>
                    <p class="lead text-muted">ZeroWaste adalah platform yang menghubungkan masyarakat Indonesia dengan program daur ulang. Kami percaya bahwa setiap sampah memiliki nilai dan dapat diubah menjadi sesuatu yang bermanfaat.</p>
                </div>
            </div>
            <div class="row mt-5 g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="card-body">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-trash3 text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="fw-bold">Kumpulkan Sampah</h5>
                            <p class="text-muted">Kumpulkan sampah daur ulang dari rumahmu seperti plastik, kertas, logam, dan lainnya.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="card-body">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-arrow-left-right text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="fw-bold">Tukarkan di Cabang</h5>
                            <p class="text-muted">Bawa sampahmu ke cabang ZeroWaste terdekat dan tukarkan dengan poin.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="card-body">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-gift text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="fw-bold">Dapatkan Hadiah</h5>
                            <p class="text-muted">Tukarkan poinmu dengan berbagai produk ramah lingkungan atau voucher belanja.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-5 bg-light">
        <div class="container">
            <h2 class="fw-bold text-center mb-5">Fitur Unggulan</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-graph-up-arrow text-success fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">Tracking Sampah</h5>
                            <p class="text-muted mb-0">Pantau statistik sampah harianmu dengan grafik yang informatif.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-trophy text-success fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">Gamification</h5>
                            <p class="text-muted mb-0">Dapatkan badge dan level sesuai kontribusimu!</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-people text-success fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">Forum Komunitas</h5>
                            <p class="text-muted mb-0">Berbagi tips dan pengalaman dengan komunitas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-calendar-event text-success fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">Event & Lomba</h5>
                            <p class="text-muted mb-0">Ikuti berbagai event dan lomba daur ulang.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-geo-alt text-success fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">Lokasi Cabang</h5>
                            <p class="text-muted mb-0">Temukan lokasi drop point terdekat.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-bell text-success fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">Notifikasi</h5>
                            <p class="text-muted mb-0">Dapatkan update terbaru langsung di inbox.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="locations" class="py-5">
        <div class="container">
            <h2 class="fw-bold text-center mb-5">Lokasi Cabang Kami</h2>
            <div class="row g-4">
                <?php
                $stmt = $pdo->query("SELECT * FROM recycling_locations WHERE is_active = 1 LIMIT 6");
                while ($loc = $stmt->fetch()):
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold text-success">
                                <i class="bi bi-geo-alt-fill me-2"></i><?= sanitize($loc['name']) ?>
                            </h5>
                            <p class="text-muted small mb-2"><?= sanitize($loc['address']) ?>, <?= sanitize($loc['city']) ?></p>
                            <p class="mb-2"><i class="bi bi-clock me-2"></i><?= sanitize($loc['operating_hours']) ?></p>
                            <p class="mb-3"><i class="bi bi-telephone me-2"></i><?= sanitize($loc['phone']) ?></p>
                            <span class="badge bg-success-subtle text-success"><?= sanitize($loc['waste_types']) ?></span>
                            <?php if ($loc['maps_link']): ?>
                            <a href="<?= sanitize($loc['maps_link']) ?>" target="_blank" class="btn btn-sm btn-outline-success mt-3 w-100">
                                <i class="bi bi-map me-1"></i>Lihat di Maps
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <section class="py-5 bg-success text-white">
        <div class="container text-center">
            <h2 class="fw-bold mb-4">Siap Memulai Perjalanan Zero Waste-mu?</h2>
            <p class="lead mb-4">Bergabunglah dengan ribuan pengguna lainnya dan mulai berkontribusi untuk lingkungan yang lebih bersih!</p>
            <a href="register.php" class="btn btn-light btn-lg">
                <i class="bi bi-person-plus me-2"></i>Daftar Gratis Sekarang
            </a>
        </div>
    </section>

    <footer class="py-4 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="fw-bold"><i class="bi bi-recycle me-2"></i>ZeroWaste</h5>
                    <p class="text-muted mb-0">Ubah Sampah Jadi Berkah</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">&copy; 2024 ZeroWaste. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
