<?php
session_start();

// Hardcoded username dan password
$admin_username = 'admin';
$admin_password = 'password123';

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

    if ($username === $admin_username && $password === $admin_password) {
        // Login berhasil, simpan sesi
        $_SESSION['logged_in'] = true;
        header('Location: admin/index.php');
        exit();
    } else {
        // Login gagal, tampilkan pesan error
        $error = "Username atau password salah!";
    }
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

<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-sm">
        <h2 class="text-2xl font-bold text-center mb-4 text-gray-700">Login</h2>
        <?php if (!empty($error)) : ?>
            <p class="text-red-500 text-sm text-center mb-4"><?= $error ?></p>
        <?php endif; ?>
        <form action="" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-600">Username</label>
                <input type="text" name="username" id="username" placeholder="Masukkan username"
                    class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring focus:ring-indigo-200 focus:outline-none"
                    required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-600">Password</label>
                <input type="password" name="password" id="password" placeholder="Masukkan password"
                    class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring focus:ring-indigo-200 focus:outline-none"
                    required>
            </div>
            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition">
                Login
            </button>
        </form>
    </div>
</body>

</html>
