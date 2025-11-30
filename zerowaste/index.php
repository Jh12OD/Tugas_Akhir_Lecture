<?php
include 'db.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data logs milik user ini
$sql = "SELECT logs.*, categories.name as category_name 
        FROM logs 
        JOIN categories ON logs.category_id = categories.id 
        WHERE logs.user_id = $user_id 
        ORDER BY logs.log_date DESC";
$result = $conn->query($sql);
?>

<?php include 'header.php'; ?>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Dashboard Saya</h2>
        <?php if($_SESSION['role'] == 'user'): ?>
            <a href="tambah_log.php" class="btn">+ Catat Sampah</a>
        <?php endif; ?>
    </div>

    <!-- Info Box Edukasi (SDG 12) -->
    <div style="background: #e0f2fe; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #0369a1;">
        <strong>Info SDG 12:</strong> Kurangi penggunaan plastik sekali pakai. Membawa tas belanja sendiri dapat mengurangi ratusan kantong plastik per tahun!
    </div>

    <h3>Riwayat Konsumsi</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Barang</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['log_date'] ?></td>
                    <td><?= $row['category_name'] ?></td>
                    <td><?= $row['quantity'] ?> pcs</td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3">Belum ada data. Mulai mencatat sekarang!</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body></html>
