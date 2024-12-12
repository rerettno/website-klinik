<?php
session_start();
require 'db/db.php'; // Pastikan file koneksi sudah benar

// Hardcoded admin username dan password
$admin_username = 'adm001';
$admin_password = '12345';

// Proses logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Proses login
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check jika admin
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['role'] = 'admin';
        header('Location: admin/index.php');
        exit();
    }

    // Jika bukan admin, cek di tabel dokter
    $query = "SELECT nip, nama, password FROM dokter WHERE nip = ? OR id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $dokter = $result->fetch_assoc();
        // Verifikasi password
        if (password_verify($password, $dokter['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['role'] = 'dokter';
            $_SESSION['nama'] = $dokter['nama'];
            $_SESSION['nip'] = $dokter['nip'];
            header('Location: dokter/index.php');
            exit();
        } else {
            $error = "Password salah.";
        }
    } else {
        $error = "Username atau NIP tidak ditemukan.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-blue-500 to-indigo-700 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-xl rounded-lg p-8 w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Login</h2>
        <?php if (!empty($error)) : ?>
            <div class="bg-red-100 text-red-700 border border-red-400 rounded-lg p-4 mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-600">ID Pengguna</label>
                <input type="text" name="username" id="username" placeholder="Masukkan Nomor Identitas Pengguna"
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:ring focus:ring-indigo-200 focus:outline-none"
                    required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-600">Kata Sandi</label>
                <input type="password" name="password" id="password" placeholder="Masukkan password"
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:ring focus:ring-indigo-200 focus:outline-none"
                    required>
            </div>
            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition font-semibold">
                Login
            </button>
        </form>
    </div>
</body>

</html>
