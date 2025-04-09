<?php
session_start();
require 'koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil jumlah tugas yang belum selesai berdasarkan user_id
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM tugas WHERE status = 'active' AND user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tugasBelumSelesai = $result->fetch_assoc()['total'];
$stmt->close();

// Ambil jumlah tugas yang sudah selesai berdasarkan user_id
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM tugas WHERE status = 'completed' AND user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tugasSelesai = $result->fetch_assoc()['total'];
$stmt->close();

// Ambil daftar 5 tugas terbaru yang masih aktif
$stmt = $conn->prepare("SELECT * FROM tugas WHERE user_id = ? AND status = 'active' ORDER BY createdat DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tugasTerbaru = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - To Do List</title>
    <link rel="stylesheet" href="css/styleH.css">
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
            <h2>🏠 Selamat Datang, <?= $_SESSION['username']; ?>!</h2>
            <?php if ($tugasSelesai > 0): ?>
                <div class="notification">
                    🎉 Selamat! Anda telah menyelesaikan <?= $tugasSelesai ?> tugas! Tetap semangat!
                </div>
            <?php endif; ?>
            
            <h3>Tugas Terbaru</h3>
            <ul class="task-list">
                <?php while ($task = $tugasTerbaru->fetch_assoc()): ?>
                    <li class="task-item">
                        <span>
                            <strong><?= htmlspecialchars($task['label']) ?></strong> 
                            (<?= htmlspecialchars($task['prioritas']) ?>) - <?= htmlspecialchars($task['deadline']) ?>
                        </span>
                        
                        <?php
                        $task_id = $task['id'];
                        $stmt = $conn->prepare("SELECT * FROM subtask WHERE task_id = ? AND status = 'active'");
                        $stmt->bind_param("i", $task_id);
                        $stmt->execute();
                        $subtasks = $stmt->get_result();
                        ?>

                        <?php if ($subtasks->num_rows > 0): ?>
                            <ul class="subtask-list">
                                <?php while ($subtask = $subtasks->fetch_assoc()): ?>
                                    <li class="subtask-item">
                                        <?= htmlspecialchars($subtask['label']) ?> 
                                        (<?= htmlspecialchars($subtask['prioritas']) ?>)
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php endif; ?>

                        <?php $stmt->close(); ?>
                    </li>
                <?php endwhile; ?>
                <?php if ($tugasTerbaru->num_rows === 0): ?>
                    <p class="empty">Belum ada tugas.</p>
                <?php endif; ?>
            </ul>
            
            <a href="index.php" class="btn">Kelola Tugas</a>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (isset($_SESSION['notification'])): ?>
    <script>
        Swal.fire({
            title: "Sukses!",
            text: "<?= $_SESSION['notification']; ?>",
            icon: "success",
            confirmButtonText: "OK"
        });
    </script>
    <?php unset($_SESSION['notification']); ?>
<?php endif; ?>

</body>
</html>
