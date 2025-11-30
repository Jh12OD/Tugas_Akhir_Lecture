<?php
include 'db.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verifikasi password (gunakan password_verify jika password di database ter-hash)
        // Untuk simpel di tugas awal, kita anggap plain text dulu, tapi SANGAT DISARANKAN pakai hash.
        if (password_verify($password, $row['password'])) { 
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            header("Location: index.php"); // Redirect ke dashboard
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
include 'header.php';
?>

<div class="container">
    <h2>Login Pengguna</h2>
    <?php if(isset($error)) echo "<div class='alert'>$error</div>"; ?>
    
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>
        
        <label>Password</label>
        <input type="password" name="password" required>
        
        <button type="submit" name="login" class="btn">Masuk</button>
        <p>Belum punya akun? <a href="register.php">Daftar disini</a></p>
    </form>
</div>
</body></html>