<?php
session_start();
require 'koneksi.php';

// Tambah tugas ke database
if (isset($_POST['add'])) {
    $label = $_POST['task'];
    if (!empty($label)) {
        $stmt = $conn->prepare("INSERT INTO tugas (label, status) VALUES (?, 0)");
        $stmt->bind_param("s", $label);
        $stmt->execute();
        $stmt->close();

        // Redirect untuk mencegah duplikasi saat refresh
        header("Location: index.php");
        exit();
    }
}

// Edit tugas di database
if (isset($_POST['update'])) {
    $id = $_POST['task_id'];
    $label = $_POST['task'];
    $stmt = $conn->prepare("UPDATE tugas SET label = ? WHERE id = ?");
    $stmt->bind_param("si", $label, $id);
    $stmt->execute();
    $stmt->close();

    // Redirect untuk mencegah duplikasi saat refresh
    header("Location: index.php");
    exit();
}

// Hapus tugas dari database
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tugas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Redirect untuk mencegah duplikasi saat refresh
    header("Location: index.php");
    exit();
}

// Ambil tugas untuk diedit
$editTask = "";
$editId = "";
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM tugas WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        $editId = $row['id'];
        $editTask = $row['label'];
    }
	if (isset($_POST['add'])) {
		$label = $_POST['task'];
		if (!empty($label)) {
			$stmt = $conn->prepare("INSERT INTO tugas (label, status) VALUES (?, 0)");
			$stmt->bind_param("s", $label);
			$stmt->execute();
			$stmt->close();
	
			// Tambahkan ke history
			$stmt = $conn->prepare("INSERT INTO history (task_label, action) VALUES (?, 'Ditambahkan')");
			$stmt->bind_param("s", $label);
			$stmt->execute();
			$stmt->close();
	
			header("Location: index.php");
			exit();
		}
	}
	
}

// Ambil semua tugas dari database
$tugas = $conn->query("SELECT * FROM tugas ORDER BY createdat DESC");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do List</title>
    <link rel="stylesheet" href="styleR.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<div class="dashboard">
    <!-- Sidebar -->
	<aside class="sidebar">
    <h2>☰ MENU</h2>
    <ul>
        <li><a href="#"><i class="bx bx-home"></i> Profile</a></li>
        <li><a href="#"><i class="bx bx-list-check"></i> To Do List</a></li>
        <li><a href="#"><i class="bx bx-cog"></i> Settings</a></li>
    </ul>
    <div class="logout">
        <a href="logout.php"><i class="bx bx-log-out"></i> Logout</a>
    </div>
</aside>


    <!-- Konten Utama -->
    <main class="content">
        <div class="container">
            <h2>📋 To Do List</h2>

            <!-- Form Tambah & Edit -->
            <form method="post">
                <input type="hidden" name="task_id" value="<?= $editId ?>">
                <input type="text" class="input-control" name="task" value="<?= $editTask ?>" placeholder="Tambahkan tugas..." required>
                <button type="submit" class="btn" name="<?= $editTask ? 'update' : 'add' ?>">
                    <?= $editTask ? 'Update' : 'Tambah' ?>
                </button>
            </form>

            <!-- Daftar Tugas -->
            <div class="task-list">
                <?php while ($task = $tugas->fetch_assoc()): ?>
                    <div class="task-item">
                        <span><?= $task['label'] ?></span>
                        <div>
                            <a href="?edit=<?= $task['id'] ?>"><i class='bx bx-edit'></i></a>
                            <a href="?delete=<?= $task['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')"><i class='bx bx-trash'></i></a>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php if ($tugas->num_rows === 0): ?>
                    <p class="empty">Belum ada tugas</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>
