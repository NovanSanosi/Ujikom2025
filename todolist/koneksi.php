<?php
$host = "localhost"; // Sesuaikan dengan host database
$user = "root";      // Sesuaikan dengan username database
$pass = "";          // Sesuaikan dengan password database
$db   = "todolist";  // Sesuaikan dengan nama database

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
