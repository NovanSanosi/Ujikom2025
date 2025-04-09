<?php 
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Ambil ID user yang login

// Ambil data history tugas yang sudah dihapus
$stmt = $conn->prepare("SELECT label, prioritas, deadline, deleted_at FROM history WHERE user_id = ? ORDER BY deleted_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$history = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History</title>
    <link rel="stylesheet" href="css/styleHistory.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<div class="dashboard">
    <aside class="sidebar">
        <h2>☰ MENU</h2>
        <ul>
        <li><a href="home.php">🏠 Home</a></li>
            <li><a href="index.php">📋 To Do List</a></li>
            <li><a href="History.php">📜 History</a></li>
        </ul>
        <div class="logout">
            <a href="logout.php">🚪 Logout</a>
        </div>
    </aside>

    <main class="content">
        <div class="container">
            <h2>📜 History</h2>
            <p>Daftar tugas yang telah dihapus.</p>

            <table class="history-table">
                <thead>
                    <tr>
                        <th>Tugas</th>
                        <th>Prioritas</th>
                        <th>Deadline</th>
                        <th>Dihapus Pada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $task): ?>
                        <tr>
                            <td><?= htmlspecialchars($task['label']) ?></td>
                            <td><?= htmlspecialchars($task['prioritas']) ?></td>
                            <td><?= htmlspecialchars($task['deadline']) ?></td>
                            <td><?= htmlspecialchars($task['deleted_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($history)): ?>
                        <tr><td colspan="4" class="empty">Belum ada history.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>