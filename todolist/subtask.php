<?php
session_start();
require 'koneksi.php';

// Tambah subtask
if (isset($_POST['add_subtask'])) {
    $task_id = $_POST['task_id'];
    $label = $_POST['subtask'];
    if (!empty($label)) {
        $stmt = $conn->prepare("INSERT INTO subtask (task_id, label, status) VALUES (?, ?, 0)");
        $stmt->bind_param("is", $task_id, $label);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php");
        exit();
    }
}

// Edit subtask
if (isset($_POST['update_subtask'])) {
    $id = $_POST['subtask_id'];
    $label = $_POST['subtask'];
    $stmt = $conn->prepare("UPDATE subtask SET label = ? WHERE id = ?");
    $stmt->bind_param("si", $label, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

// Hapus subtask
if (isset($_GET['delete_subtask'])) {
    $id = $_GET['delete_subtask'];
    $stmt = $conn->prepare("DELETE FROM subtask WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}
?>
