<?php
    include '../db/db.php';
    session_start();
    // Fungsi untuk validasi akses
    require '../db/auth.php';
    validateAccess('dokter'); // Validasi akses untuk admin

    // Cek apakah user sudah login
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: ../index.php');
        exit();
    }

    // Ambil data dari sesi
    $username = $_SESSION['nama'] ?? 'Guest';
    $status = $_SESSION['role']; // Status tetap hardcoded


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokter Section</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite/dist/flowbite.min.js"></script>
</head>
<body class="bg-blue-50 dark:bg-gray-600 ">

    <!-- Navbar -->
    <nav class="border-blue-200 bg-blue-100 dark:bg-gray-800 dark:border-gray-700">
        <div class="max-w-screen-2xl flex flex-wrap items-center justify-between mx-auto p-4 sm:p-6">

            <!-- Hamburger Button -->
            <button data-drawer-target="drawer-navigation" type="button"
                class="inline-flex items-center justify-center p-2 w-10 h-10 text-sm text-gray-500 rounded-lg hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                <span class="sr-only">Open main menu</span>
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M1 1h15M1 7h15M1 13h15" />
                </svg>
            </button>

            <!-- Logo -->
            <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
                <img src="https://flowbite.com/docs/images/logo.svg" class="h-8" alt="Flowbite Logo" />
                <span class="self-center text-2xl font-semibold whitespace-nowrap text-gray-500 dark:text-white">BK HOSPITAL</span>
            </a>
        </div>
    </nav>
