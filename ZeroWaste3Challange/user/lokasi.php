<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isUser()) {
    redirect('../login.php');
}

$search = sanitize($_GET['search'] ?? '');
$city = sanitize($_GET['city'] ?? '');

$sql = "SELECT * FROM recycling_locations WHERE is_active = 1";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE ? OR address LIKE ? OR waste_types LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

if ($city) {
    $sql .= " AND city = ?";
    $params[] = $city;
}

$sql .= " ORDER BY city, name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$locations = $stmt->fetchAll();

$stmt = $pdo->query("SELECT DISTINCT city FROM recycling_locations WHERE is_active = 1 ORDER BY city");
$cities = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokasi Cabang - ZeroWaste</title>
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
                        <h4 class="fw-bold"><i class="bi bi-geo-alt me-2 text-success"></i>Lokasi Cabang Daur Ulang</h4>
                        <p class="text-muted">Temukan lokasi drop point terdekat untuk menukarkan sampahmu</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text bg-success text-white">
                                                <i class="bi bi-search"></i>
                                            </span>
                                            <input type="text" class="form-control" name="search" placeholder="Cari nama tempat atau alamat..." value="<?= sanitize($search) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" name="city">
                                            <option value="">Semua Kota</option>
                                            <?php foreach ($cities as $c): ?>
                                            <option value="<?= sanitize($c) ?>" <?= $city === $c ? 'selected' : '' ?>><?= sanitize($c) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="bi bi-search me-2"></i>Cari
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <?php if (empty($locations)): ?>
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-geo-alt fs-1 text-muted mb-3"></i>
                                <h5>Tidak ada lokasi ditemukan</h5>
                                <p class="text-muted">Coba ubah kata kunci pencarian Anda</p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($locations as $loc): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                                        <i class="bi bi-geo-alt-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1"><?= sanitize($loc['name']) ?></h5>
                                        <span class="badge bg-secondary"><?= sanitize($loc['city']) ?></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="text-muted mb-2">
                                        <i class="bi bi-pin-map me-2"></i><?= sanitize($loc['address']) ?>
                                    </p>
                                    <?php if ($loc['phone']): ?>
                                    <p class="text-muted mb-2">
                                        <i class="bi bi-telephone me-2"></i><?= sanitize($loc['phone']) ?>
                                    </p>
                                    <?php endif; ?>
                                    <?php if ($loc['operating_hours']): ?>
                                    <p class="text-muted mb-2">
                                        <i class="bi bi-clock me-2"></i><?= sanitize($loc['operating_hours']) ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($loc['waste_types']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Jenis sampah yang diterima:</small>
                                    <div class="mt-1">
                                        <?php 
                                        $types = explode(',', $loc['waste_types']);
                                        foreach ($types as $type): 
                                        ?>
                                        <span class="badge bg-success-subtle text-success me-1 mb-1"><?= trim(sanitize($type)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($loc['maps_link']): ?>
                                <a href="<?= sanitize($loc['maps_link']) ?>" target="_blank" class="btn btn-success w-100">
                                    <i class="bi bi-map me-2"></i>Lihat di Google Maps
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
