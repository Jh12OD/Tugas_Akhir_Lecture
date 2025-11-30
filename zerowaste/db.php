<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_zerowaste";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Memulai sesi di setiap halaman yang include file ini
session_start();
?>
