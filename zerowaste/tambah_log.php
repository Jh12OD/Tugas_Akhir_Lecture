<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil daftar kategori untuk dropdown
$categories = $conn->query("SELECT * FROM categories");

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $category_id = $_POST['category_id'];
    $qty = $_POST['quantity'];
    $date = $_POST['log_date'];

    $sql = "INSERT INTO logs (user_id, category_id, quantity, log_date) 
            VALUES ('$user_id', '$category_id', '$qty', '$date')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
    } else {
        $error = "Gagal menyimpan: " . $conn->error;
    }
}
include 'header.php';
?>

<div class="container">
    <h2>Catat Barang Sekali Pakai</h2>
    <form method="POST">
        <label>Jenis Barang</label>
        <select name="category_id" required>
            <?php while($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Jumlah (pcs)</label>
        <input type="number" name="quantity" min="1" required>

        <label>Tanggal</label>
        <input type="date" name="log_date" value="<?= date('Y-m-d') ?>" required>

        <button type="submit" name="submit" class="btn">Simpan</button>
        <a href="index.php" style="margin-left: 10px; color: #666;">Batal</a>
    </form>
</div>
</body></html>