<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Tambah tugas dengan prioritas & deadline
if (isset($_POST['add_task'])) {
    $label = $_POST['task'];
    $prioritas = $_POST['prioritas'];
    $deadline = $_POST['deadline_task'];

    if (!empty($label)) {
        $stmt = $conn->prepare("INSERT INTO tugas (user_id, label, status, prioritas, deadline) VALUES (?, ?, 'active', ?, ?)");
        $stmt->bind_param("isss", $user_id, $label, $prioritas, $deadline);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit();
    }
}

// Tandai tugas sebagai selesai
if (isset($_GET['complete_task'])) {
    $id = $_GET['complete_task'];

    // Ubah status tugas menjadi 'completed'
    $stmt = $conn->prepare("UPDATE tugas SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

// Tandai subtask sebagai selesai
if (isset($_GET['complete_subtask'])) {
    $id = $_GET['complete_subtask'];

    // Ubah status subtask menjadi 'completed'
    $stmt = $conn->prepare("UPDATE subtask SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

// Tambah subtask dengan prioritas & deadline
if (isset($_POST['add_subtask'])) {
    $task_id = $_POST['task_id'];
    $label = $_POST['subtask'];
    $prioritas = $_POST['prioritas_subtask'];
    $deadline = $_POST['deadline_subtask'];

    if (!empty($label)) {
        $stmt = $conn->prepare("INSERT INTO subtask (task_id, label, status, prioritas, deadline) VALUES (?, ?, 'active', ?, ?)");
        $stmt->bind_param("isss", $task_id, $label, $prioritas, $deadline);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit();
    }
}

// Hapus tugas & simpan ke history
if (isset($_GET['delete_task'])) {
    $id = $_GET['delete_task'];

    // Simpan tugas ke history sebelum menghapus
    $stmt = $conn->prepare("INSERT INTO history (user_id, task_id, label, prioritas, deadline, deleted_at) 
                            SELECT user_id, id, label, prioritas, deadline, NOW() FROM tugas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Ubah status tugas menjadi 'deleted'
    $stmt = $conn->prepare("UPDATE tugas SET status = 'deleted' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Ubah status semua subtasks dari tugas yang dihapus
    $stmt = $conn->prepare("UPDATE subtask SET status = 'deleted' WHERE task_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}

if (isset($_GET['delete_subtask'])) {
    $id = $_GET['delete_subtask'];

    // Simpan subtask ke history sebelum menghapus
    $stmt = $conn->prepare("INSERT INTO history (user_id, task_id, label, prioritas, deadline, deleted_at) 
                            SELECT tugas.user_id, subtask.id, subtask.label, subtask.prioritas, subtask.deadline, NOW() 
                            FROM subtask JOIN tugas ON subtask.task_id = tugas.id WHERE subtask.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Ubah status subtask menjadi 'deleted'
    $stmt = $conn->prepare("UPDATE subtask SET status = 'deleted' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
}
// Update Task
if (isset($_POST['update_task'])) {
    $id = $_POST['edit_task_id'];
    $label = $_POST['edit_task_label'];
    $prioritas = $_POST['edit_task_prioritas'];
    $deadline = $_POST['edit_task_deadline'];

    if (!empty($label)) {
        $stmt = $conn->prepare("UPDATE tugas SET label = ?, prioritas = ?, deadline = ? WHERE id = ?");
        $stmt->bind_param("sssi", $label, $prioritas, $deadline, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php");
        exit();
    }
}


// Edit subtask
// Update Subtask
if (isset($_POST['update_subtask'])) {
    $id = $_POST['edit_subtask_id'];
    $label = $_POST['edit_subtask_label'];
    $prioritas = $_POST['edit_subtask_prioritas'];
    $deadline = $_POST['edit_subtask_deadline'];

    if (!empty($label)) {
        $stmt = $conn->prepare("UPDATE subtask SET label = ?, prioritas = ?, deadline = ? WHERE id = ?");
        $stmt->bind_param("sssi", $label, $prioritas, $deadline, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php");
        exit();
    }
}



// Ambil semua tugas yang statusnya 'active'
$stmt = $conn->prepare("SELECT * FROM tugas WHERE user_id = ? AND status = 'active' ORDER BY FIELD(prioritas, 'Penting', 'Tidak Penting'), createdat DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tugas = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil semua subtask untuk tugas yang belum dihapus
$subtasks = []; 
$stmt = $conn->prepare("SELECT * FROM subtask WHERE task_id IN (SELECT id FROM tugas WHERE user_id = ? AND status = 'active') AND status = 'active' ORDER BY FIELD(prioritas, 'Penting', 'Tidak Penting'), id ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_subtask = $stmt->get_result();
while ($row = $result_subtask->fetch_assoc()) {
    $subtasks[$row['task_id']][] = $row;    
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do List</title>
    <link rel="stylesheet" href="css/stylei.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<script>
function editSubtask(id, label, prioritas, deadline) {
    // Tampilkan form edit
    document.getElementById('editSubtaskForm').style.display = 'block';

    // Isi input form dengan data subtask yang ingin diedit
    document.getElementById('edit_subtask_id').value = id;
    document.getElementById('edit_subtask_label').value = label;
    document.getElementById('edit_subtask_prioritas').value = prioritas;
    document.getElementById('edit_subtask_deadline').value = deadline;
}
function editTask(id, label, prioritas, deadline) {
    // Tampilkan form edit
    document.getElementById('editTaskForm').style.display = 'block';

    // Isi input form dengan data task yang ingin diedit
    document.getElementById('edit_task_id').value = id;
    document.getElementById('edit_task_label').value = label;
    document.getElementById('edit_task_prioritas').value = prioritas;
    document.getElementById('edit_task_deadline').value = deadline;
}

</script>
<body>

<div class="dashboard">
    <aside class="sidebar">
        <h2>☰ MENU</h2>
        <ul>
            <li><a href="Home.php">🏠 Home</a></li>
            <li><a href="#">📋 To Do List</a></li>
            <li><a href="History.php">📜 History</a></li>
        </ul>
        <div class="logout">
            <a href="logout.php">🚪 Logout</a>
        </div>
    </aside>

    <main class="content">
        <div class="container">
            <h2>📋 To Do List</h2>
            <!-- Form Tambah Tugas -->
            <form method="post">
                <input type="text" class="input-control" name="task" placeholder="Tambahkan tugas..." required>
                <select name="prioritas" required>
                    <option value="Tidak Penting">Tidak Penting</option>
                    <option value="Penting" selected>Penting</option>
                </select>
                <input type="datetime-local"  name="deadline_task" required>
                <button type="submit" class="btn" name="add_task">Tambah</button>
            </form>
            <div class="task-list">
    <?php foreach ($tugas as $task): ?>
        <div class="task-container <?= ($task['status'] == 'completed') ? 'completed-task' : '' ?>">
            <!-- Form Edit Subtask (Tersembunyi Secara Default) -->
<div id="editSubtaskForm" style="display: none; background: #f9f9f9; padding: 15px; border-radius: 8px; max-width: 400px; margin: auto;">
    <h3>Edit Subtask</h3>
    <form method="post">
        <input type="hidden" name="edit_subtask_id" id="edit_subtask_id">

        <label>Nama Subtask:</label>
        <input type="text" class="input-control" name="edit_subtask_label" id="edit_subtask_label" required>

        <label>Prioritas:</label>
        <select name="edit_subtask_prioritas" id="edit_subtask_prioritas" required>
            <option value="Tidak Penting">Tidak Penting</option>
            <option value="Penting">Penting</option>
        </select>

        <label>Deadline:</label>
        <input type="datetime-local" name="edit_subtask_deadline" id="edit_subtask_deadline" required>

        <button type="submit" class="btn" name="update_subtask">Simpan Perubahan</button>
        <button type="button" class="btn" onclick="document.getElementById('editSubtaskForm').style.display = 'none'">Batal</button>
    </form>
</div>

            <span><?= $task['label'] ?> (<?= $task['prioritas'] ?>) - <?= $task['deadline'] ?></span>
            <div>
                <?php if ($task['status'] !== 'completed'): ?>
                    <a href="?complete_task=<?= $task['id'] ?>" title="Tandai Selesai">
                        <i class='bx bx-check-circle'></i>
                    </a>
                    <a href="#" onclick="editTask(<?= $task['id'] ?>, '<?= addslashes($task['label']) ?>', '<?= $task['prioritas'] ?>', '<?= $task['deadline'] ?>')" title="Edit">
                        <i class='bx bx-edit'></i>
                    </a>

                <?php endif; ?>
                <a href="?delete_task=<?= $task['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">
                    <i class='bx bx-trash'></i>
                </a>
            </div>
        </div>
        
        <!-- Daftar Subtask -->
<ul class="subtask-list">
    <!-- Form Edit Task (Tersembunyi Secara Default) -->
<div id="editTaskForm" style="display: none; background: #f9f9f9; padding: 15px; border-radius: 8px; max-width: 400px; margin: auto;">
    <h3>Edit Task</h3>
    <form method="post">
        <input type="hidden" name="edit_task_id" id="edit_task_id">

        <label>Nama Task:</label>
        <input type="text" class="input-control" name="edit_task_label" id="edit_task_label" required>

        <label>Prioritas:</label>
        <select name="edit_task_prioritas" id="edit_task_prioritas" required>
            <option value="Tidak Penting">Tidak Penting</option>
            <option value="Penting">Penting</option>
        </select>

        <label>Deadline:</label>
        <input type="datetime-local" name="edit_task_deadline" id="edit_task_deadline" required>

        <button type="submit" class="btn" name="update_task">Simpan Perubahan</button>
        <button type="button" class="btn" onclick="document.getElementById('editTaskForm').style.display = 'none'">Batal</button>
    </form>
</div>

    <?php if (!empty($subtasks[$task['id']])): ?>
        <?php foreach ($subtasks[$task['id']] as $subtask): ?>
            <li class="<?= ($subtask['status'] == 'completed') ? 'completed-task' : '' ?>">
                <span><?= $subtask['label'] ?> (<?= $subtask['prioritas'] ?>) - <?= $subtask['deadline'] ?></span>
                
                <!-- Tombol Tandai Selesai -->
                <a href="?complete_subtask=<?= $subtask['id'] ?>" title="Tandai Selesai">
                    <i class='bx bx-check-circle'></i>
                </a>

                <!-- Tombol Edit -->
                <a href="#" onclick="editSubtask(<?= $subtask['id'] ?>, '<?= $subtask['label'] ?>', '<?= $subtask['prioritas'] ?>', '<?= $subtask['deadline'] ?>')" title="Edit">
                    <i class='bx bx-edit'></i>
                </a>

                <!-- Tombol Hapus -->
                <a href="?delete_subtask=<?= $subtask['id'] ?>" onclick="return confirm('Hapus subtask ini?')" title="Hapus">
                    <i class='bx bx-trash'></i>
                </a>
            </li>
        <?php endforeach; ?>
    <?php else: ?>
        <li><i>Belum ada subtask</i></li>
    <?php endif; ?>
</ul>


        <!-- Form Tambah Subtask -->
        <form method="post" class="subtask-form">
            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
            <input type="text" class="input-control" name="subtask" placeholder="Tambahkan subtask..." required>
            <select name="prioritas_subtask" required>
                <option value="Tidak Penting">Tidak Penting</option>
                <option value="Penting">Penting</option>
            </select>
            <input type="datetime-local" name="deadline_subtask" required>
            <button type="submit" class="btn" name="add_subtask">Tambah Subtask</button>
        </form>
    <?php endforeach; ?>
</div>

</body>
</html>
            
