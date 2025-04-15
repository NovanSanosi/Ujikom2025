<?php
session_start();
require 'koneksi.php'; // Pastikan koneksi ke database sudah benar

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status']; // Status akan berupa 1 (checked) atau 0 (unchecked)

    // Siapkan statement untuk memperbarui status tugas
    $stmt = $conn->prepare("UPDATE tugas SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $id);

    // Eksekusi statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}

$conn->close();
?>