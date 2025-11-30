<?php
include 'db.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Aman!
    $role = 'user'; // Default role

    // Cek username duplikat
    $check = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($check->num_rows > 0) {
        $error = "Username sudah dipakai!";
    } else {
        $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location='login.php';</script>";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
include 'header.php';
?>

<div class="container">
    <h2>Daftar Akun Baru</h2>
    <?php if(isset($error)) echo "<div class='alert'>$error</div>"; ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit" name="register" class="btn">Daftar</button>
    </form>
</div>
</body></html>