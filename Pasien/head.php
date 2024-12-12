<?php
include '../db/db.php'; // Koneksi database
session_start();

// Fungsi Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Hospital Homepage</title>
</head>
<body class="bg-teal-50">

<header class="bg-teal-300">
    <div class="container mx-auto flex items-center justify-between px-6 py-4">
        <a href="#" class="flex items-center space-x-3">
            <img src="https://flowbite.com/docs/images/logo.svg" class="h-8" alt="Flowbite Logo" />
            <span class="self-center text-2xl font-semibold whitespace-nowrap text-white">BK HOSPITAL</span>
        </a>
        <nav class="flex items-center">
            <?php if (isset($_SESSION['user'])): ?>
                <!-- Jika sudah login -->
                <div class="flex items-center space-x-4">
                    <span class="text-white">Selamat datang, <?= htmlspecialchars($_SESSION['user']); ?></span>
                    <a href="?action=logout" class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-400 transition">
                        Logout
                    </a>
                </div>
            <?php else: ?>
                <!-- Jika belum login -->
                <div class="flex items-center space-x-4 ml-6">
                    <a href="login.php" class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-400 transition">
                        Login
                    </a>
                    <a href="register.php" class="border border-white text-white px-4 py-2 rounded hover:bg-teal-400 hover:border-teal-400 transition">
                        Register
                    </a>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>