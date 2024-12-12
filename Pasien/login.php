<?php
include '../db/db.php'; // Pastikan file ini berisi koneksi database

session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = $_POST['nik'];
    $name = $_POST['name'];

    // Cek apakah pasien sudah terdaftar
    $stmt = $conn->prepare("SELECT id, nama FROM pasien WHERE nik = ? AND nama = ?");
    $stmt->bind_param("ss", $nik, $name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Login berhasil
        $_SESSION['user'] = $name;
        header("Location: index.php");
        exit;
    } else {
        // Jika pasien belum terdaftar, arahkan ke halaman pendaftaran
        $message = "Pasien tidak ditemukan. Harap mendaftar terlebih dahulu.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login Pasien</title>
</head>
<body class="bg-teal-50">
<header class="bg-teal-300">
    <div class="container mx-auto flex items-center justify-between px-6 py-4">
        <a href="index.php" class="flex items-center space-x-3">
            <img src="https://flowbite.com/docs/images/logo.svg" class="h-8" alt="Flowbite Logo" />
            <span class="self-center text-2xl font-semibold whitespace-nowrap text-white">BK HOSPITAL</span>
        </a>
    </div>
</header>

<section class="bg-white text-teal-500 py-16">
    <div class="container mx-auto max-w-xl">
        <h1 class="text-3xl font-bold text-center mb-6">Selamat Datang !</h1>
        <?php if ($message): ?>
            <div class="mb-4 p-4 text-white bg-red-400 rounded-lg text-center">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST" class="bg-teal-50 rounded-lg shadow-lg p-6">
            <div class="mb-4">
                <label for="nik" class="block text-md font-medium text-teal-700">Nomor Induk Kependudukan</label>
                <input type="text" id="nik" name="nik" class="w-full mt-2 px-3 py-2 rounded-md border-gray-300 text-gray-700 shadow-sm" 
                       placeholder="Masukkan NIK" required maxlength="16">
            </div>
            <div class="mb-4">
                <label for="name" class="block text-md font-medium text-teal-700">Nama Pasien</label>
                <input type="text" id="name" name="name" class="w-full mt-2 px-3 py-2 rounded-md border-gray-300 text-gray-700 shadow-sm" 
                       placeholder="Masukkan Nama" required>
            </div>
            <div class="text-center">
                <button type="submit" class="bg-teal-500 text-white px-6 py-2 rounded-full font-medium hover:bg-teal-400 transition">Login</button>
            </div>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">
            Belum memiliki akun? <a href="register.php" class="text-teal-500 hover:underline">Daftar di sini</a>.
        </p>
    </div>
</section>

<footer class="bg-teal-400 text-teal-100 py-6">
    <div class="container mx-auto text-center">
        <p>&copy; 2024 BK HOSPITAL. All rights reserved.</p>
    </div>
</footer>
<script src="script.js"></script>
</body>
</html>
