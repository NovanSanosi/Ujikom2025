<?php
$host = "localhost"; // Sesuaikan dengan host database
$user = "xxmkrtoe_novan";      // Sesuaikan dengan username database
$pass = "Nopiepie21@#3";          // Sesuaikan dengan password database
$db   = "xxmkrtoe_todolist";  // Sesuaikan dengan nama database

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
