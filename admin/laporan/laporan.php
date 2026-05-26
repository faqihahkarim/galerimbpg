<?php 
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit();
}

include '../../db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../css/style.css">
   
</head>

<body>
<div class="overlay"></div>

<div class="admin-layout">

    <?php include '../sidebar.php'; ?>

    <main class="main">

        <header class="topbar">
            <button id="menu-toggle" class="menu-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div>
                <h1>Laporan</h1>
                <p>Penjanaan Laporan</p>
            </div>
        </header>

      
<script src="/web/galeriseramikmbpg/admin/js/sidebar.js"></script>


    
</body>
</html>