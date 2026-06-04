<?php
session_start();
include "../db.php";
$base="/web/galeriseramikmbpg/";

// 🔐 ONLY IT OFFICER
if (!isset($_SESSION['admin_login']) || $_SESSION['role'] !== 'it_officer') {
    header("Location: ../login.php");
    exit;
}

// 📋 FETCH ADMINS (exclude IT itself)
$result = mysqli_query($conn, "SELECT * FROM admins WHERE role != 'it_officer' ORDER BY admin_id ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="<?= $base ?>assets/images/logogaleri.png" type="image/png">

    <title>IT Dashboard</title>

    <style>
        body {
            margin: 0;
            font-family: Amiri;
            background: #ffffff;
            display: flex;
        }

        /* SIDEBAR */
        .sidebar {
            width: 200px;
            background: #321c12;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px; /* space between logo and text */
            padding: 15px;
        }

        .sidebar-logo {
            width: 50px;   /* adjust size */
            height: 50px;
            object-fit: contain;
        }

        .sidebar-text h2 {
            margin: 0;
            font-size: 18px;
            color: white;
        }

        .sidebar-text p {
            margin: 0;
            font-size: 12px;
            color: #ccc;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 12px;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
        }

        .active {
            background: #8c6f4e;
        }

        /* MAIN */
        .main {
            margin-left: 240px;
            padding: 20px;
            width: 100%;
        }

        .header {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .sub {
            color: gray;
            margin-bottom: 20px;
        }

        /* CARD */
        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .add-btn {
            background: #c62828;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            height: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px;
            font-size: 13px;
            color: #555;
            border-bottom: 1px solid #eee;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        .btn {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            font-size: 12px;
        }

        .edit { background: #1976d2; }
        .delete { background: #d32f2f; }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            width: 320px;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
        }

        input, select {
            width: 300px;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .modal-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .save { background: #2e7d32; color: white; }
        .cancel { background: #777; color: white; }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="sidebar-header">
        <img src="../assets/images/logombpg.png" alt="Logo" class="sidebar-logo">

        <div class="sidebar-text">
            <h2>GALERI SERAMIK</h2>
            <p>PASIR GUDANG</p>
        </div>
    </div>

    <a class="active" href="it_dashboard.php">IT Dashboard</a>
    <a href="../logout.php">Logout</a>
</div>

<!-- MAIN -->
<div class="main">

    <div class="header">Pengurusan Admin</div>
    <div class="sub">IT Officer Control Panel</div>

    <div class="card">

        <div class="top-bar">
            <h3>Senarai Admin</h3>
            <button class="add-btn" onclick="openAddModal()">+ Tambah Admin</button>
        </div>

        <!-- TABLE -->
        <table>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Name</th>
                <th>Role</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['admin_id'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['admin_name'] ?></td>
                <td><?= $row['role'] ?></td>
                <td>

                    <button class="btn edit"
                        onclick="openEditModal(
                            '<?= $row['admin_id'] ?>',
                            '<?= $row['email'] ?>',
                            '<?= $row['admin_name'] ?>',
                            '<?= $row['role'] ?>'
                        )">
                        Edit
                    </button>
                </td>

                <td>

                    <button class="btn delete"
                        onclick="deleteAdmin(<?= $row['admin_id'] ?>)">
                        Delete
                    </button>

                </td>
            </tr>
            <?php } ?>

        </table>
    </div>
</div>

<!-- ================= ADD MODAL ================= -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <h3>Tambah Admin</h3>

        <form method="POST" action="admin_register.php">

            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="admin_name" placeholder="Name" required>

            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="it">IT Officer</option>
            </select>

            <button class="modal-btn save" type="submit">Create</button>
            <button class="modal-btn cancel" type="button" onclick="closeAddModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- ================= EDIT MODAL ================= -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Edit Admin</h3>

        <form method="POST" action="admin_update.php">

            <input type="hidden" id="edit_id" name="admin_id">

            <input type="email" id="edit_email" name="email" required>

            <input type="text" id="edit_name" name="admin_name" placeholder="Name" required>

            <select id="edit_role" name="role" required>
                <option value="admin">Admin</option>
                <option value="it">IT Officer</option>
            </select>

            <button class="modal-btn save" type="submit">Update</button>
            <button class="modal-btn cancel" type="button" onclick="closeEditModal()">Cancel</button>
        </form>
    </div>
</div>

<script>

function openAddModal() {
    document.getElementById("addModal").style.display = "block";
}

function closeAddModal() {
    document.getElementById("addModal").style.display = "none";
}

/* ✅ FIXED ORDER */
function openEditModal(id, email, admin_name, role) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_email").value = email;
    document.getElementById("edit_name").value = admin_name;
    document.getElementById("edit_role").value = role;

    document.getElementById("editModal").style.display = "block";
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}

function deleteAdmin(id) {
    if (confirm("Delete this admin?")) {
        window.location = "admin_delete.php?id=" + id;
    }
}

/* Close modal when clicking outside */
window.onclick = function(event) {
    if (event.target === document.getElementById("addModal")) closeAddModal();
    if (event.target === document.getElementById("editModal")) closeEditModal();
}

</script>

</body>