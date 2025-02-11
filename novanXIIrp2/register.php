<?php
require 'database.php';
$fullname = $_POST["fullname"];
$username = $_POST["username"];
$age = $_POST["age"];
$email = $_POST["email"];
$password = $_POST["password"];

$query_sql = "INSERT INTO tb_users (fullname, username, age, email, password) 
            VALUES ('$fullname', '$username', '$age', '$email', '$password')";

if (mysqli_query($conn, $query_sql)) {
    header("Location: index.html");
} else {
    echo "Pendaftaran Gagal : " . mysqli_error($conn);
}
