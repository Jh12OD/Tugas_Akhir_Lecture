<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $name = sanitize($_POST['name'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $mapsLink = sanitize($_POST['maps_link'] ?? '');
        $operatingHours = sanitize($_POST['operating_hours'] ?? '');
        $wasteTypes = sanitize($_POST['waste_types'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if ($action === 'create') {
            $sql = "INSERT INTO recycling_locations (name, address, city, phone, maps_link, operating_hours, waste_types, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$name, $address, $city, $phone, $mapsLink, $operatingHours, $wasteTypes, $isActive]);
            alert('Lokasi berhasil ditambahkan!', 'success');
        } else {
            $locationId = intval($_POST['location_id'] ?? 0);
            $sql = "UPDATE recycling_locations SET name = ?, address = ?, city = ?, phone = ?, maps_link = ?, operating_hours = ?, waste_types = ?, is_active = ? WHERE id = ?";
            $pdo->prepare($sql)->execute([$name, $address, $city, $phone, $mapsLink, $operatingHours, $wasteTypes, $isActive, $locationId]);
            alert('Lokasi berhasil diperbarui!', 'success');
        }
    } elseif ($action === 'delete') {
        $locationId = intval($_POST['location_id'] ?? 0);
        $pdo->prepare("DELETE FROM recycling_locations WHERE id = ?")->execute([$locationId]);
        alert('Lokasi berhasil dihapus!', 'success');
    }
    
    redirect('locations.php');
}

$stmt = $pdo->query("SELECT * FROM recycling_locations ORDER BY city, name");
$locations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokasi Cabang - Admin ZeroWaste</title>
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
                            <h4 class="fw-bold"><i class="bi bi-geo-alt me-2 text-success"></i>Kelola Lokasi Cabang</h4>
                            <p class="text-muted mb-0">Kelola data lokasi drop point</p>
                        </div>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
                            <i class="bi bi-plus-lg me-2"></i>Tambah Lokasi
                        </button>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>Kota</th>
                                        <th>Telepon</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($locations)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Tidak ada lokasi</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($locations as $loc): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= sanitize($loc['name']) ?></div>
                                            <small class="text-muted"><?= sanitize($loc['operating_hours']) ?></small>
                                        </td>
                                        <td><?= sanitize($loc['address']) ?></td>
                                        <td><span class="badge bg-secondary"><?= sanitize($loc['city']) ?></span></td>
                                        <td><?= sanitize($loc['phone']) ?: '-' ?></td>
                                        <td>
                                            <?php if ($loc['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $loc['id'] ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($loc['maps_link']): ?>
                                                <a href="<?= sanitize($loc['maps_link']) ?>" target="_blank" class="btn btn-outline-success">
                                                    <i class="bi bi-map"></i>
                                                </a>
                                                <?php endif; ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Hapus lokasi ini?')">
                                                    <input type="hidden" name="location_id" value="<?= $loc['id'] ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="btn btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editModal<?= $loc['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Lokasi</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="hidden" name="location_id" value="<?= $loc['id'] ?>">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Nama Lokasi</label>
                                                                <input type="text" class="form-control" name="name" value="<?= sanitize($loc['name']) ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Kota</label>
                                                                <input type="text" class="form-control" name="city" value="<?= sanitize($loc['city']) ?>" required>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Alamat</label>
                                                                <textarea class="form-control" name="address" rows="2" required><?= sanitize($loc['address']) ?></textarea>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Telepon</label>
                                                                <input type="text" class="form-control" name="phone" value="<?= sanitize($loc['phone']) ?>">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Jam Operasional</label>
                                                                <input type="text" class="form-control" name="operating_hours" value="<?= sanitize($loc['operating_hours']) ?>">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Link Google Maps</label>
                                                                <input type="url" class="form-control" name="maps_link" value="<?= sanitize($loc['maps_link']) ?>">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Jenis Sampah yang Diterima</label>
                                                                <input type="text" class="form-control" name="waste_types" value="<?= sanitize($loc['waste_types']) ?>" placeholder="Pisahkan dengan koma">
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active_<?= $loc['id'] ?>" <?= $loc['is_active'] ? 'checked' : '' ?>>
                                                                    <label class="form-check-label" for="is_active_<?= $loc['id'] ?>">Lokasi Aktif</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-success">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-success"></i>Tambah Lokasi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lokasi</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kota</label>
                                <input type="text" class="form-control" name="city" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="address" rows="2" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telepon</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jam Operasional</label>
                                <input type="text" class="form-control" name="operating_hours" placeholder="Contoh: Senin - Sabtu, 08:00 - 17:00">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Link Google Maps</label>
                                <input type="url" class="form-control" name="maps_link">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Jenis Sampah yang Diterima</label>
                                <input type="text" class="form-control" name="waste_types" placeholder="Contoh: Plastik, Kertas, Logam, Kaca">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active_new" checked>
                                    <label class="form-check-label" for="is_active_new">Lokasi Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Tambah Lokasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
