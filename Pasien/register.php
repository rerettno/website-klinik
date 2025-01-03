<?php
    include '../db/db.php';

// Proses form jika ada data POST
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_GET['id'])) {
    // Proses tambah
    if (!empty($_POST['nik']) && !empty($_POST['name']) && !empty($_POST['alamat']) && !empty($_POST['phone'])) {
        $nik = $_POST['nik'];
        $nama = $_POST['name'];
        $alamat = $_POST['alamat'];
        $no_hp = $_POST['phone'];

        $conn->begin_transaction(); // Mulai transaksi untuk konsistensi
        try {
            // Validasi NIK
            $stmt_check = $conn->prepare("SELECT id FROM pasien WHERE nik = ?");
            $stmt_check->bind_param("s", $nik);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                throw new Exception("Pasien dengan NIK ini sudah terdaftar.");
            }
            $stmt_check->close();

            // Generate nomor rekam medis
            $tahun_bulan = date('Ym');
            $stmt_urut = $conn->prepare("SELECT MAX(CAST(SUBSTRING(no_rm, 8) AS UNSIGNED)) AS urut FROM pasien WHERE no_rm LIKE CONCAT(?, '-%')");
            $stmt_urut->bind_param('s', $tahun_bulan);
            $stmt_urut->execute();
            $stmt_urut->bind_result($urut_terakhir);
            $stmt_urut->fetch();
            $stmt_urut->close();

            $urut = ($urut_terakhir) ? $urut_terakhir + 1 : 1;
            $no_rm = $tahun_bulan . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

            // Tambahkan data
            $stmt_insert = $conn->prepare("INSERT INTO pasien (nik, nama, alamat, no_hp, no_rm, active) VALUES (?, ?, ?, ?, ?, TRUE)");
            $stmt_insert->bind_param("sssss", $nik, $nama, $alamat, $no_hp, $no_rm);
            $stmt_insert->execute();
            $stmt_insert->close();

            $conn->commit();
            $message = "Data berhasil ditambahkan dengan No RM: $no_rm.";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Gagal menambahkan data: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Pendaftaran Pasien</title>
</head>
<body class="bg-teal-50">
<header class="bg-teal-300">
    <div class="container mx-auto flex items-center justify-between px-6 py-4">
        <a href="#" class="flex items-center space-x-3">
            <img src="https://flowbite.com/docs/images/logo.svg" class="h-8" alt="Flowbite Logo" />
            <span class="self-center text-2xl font-semibold whitespace-nowrap text-white">BK HOSPITAL</span>
        </a>
    </div>
</header>

<section class="bg-white text-teal-500 py-16">
    <div class="container mx-auto max-w-xl">
        <h1 class="text-3xl font-bold text-center mb-6">Pendaftaran Pasien</h1>
        <?php if ($message): ?>
            <div id="flash-message" class="mb-4 p-4 text-white bg-teal-400 rounded-lg text-center">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST" class="bg-teal-50 rounded-lg shadow-lg p-6">
            <div class="mb-4">
                <label for="nik" class="block text-md font-medium text-teal-700">Nomor Induk Kependudukan</label>
                <input type="text" id="nik" name="nik" class="w-full mt-2 px-3 py-2 rounded-md border-gray-300 text-gray-700 shadow-sm"placeholder="Masukkan Nomor Induk Kependudukan" 
                            pattern="[0-9]{16}" 
                            maxlength="16" 
                            required
                            title="NIK harus terdiri dari 16 digit angka">
            </div>
            <div class="mb-4">
                <label for="name" class="block text-md font-medium text-teal-700">Nama Pasien</label>
                <input type="text" id="name" name="name" class="w-full mt-2 px-3 py-2 rounded-md border-gray-300 text-gray-700 shadow-sm" placeholder="Masukkan Nama" required>
            </div>
            <div class="mb-4">
                <label for="alamat" class="block text-md font-medium text-teal-700">Alamat</label>
                <textarea id="alamat" name="alamat" class="w-full mt-2 px-3 py-2 rounded-md border-gray-300 text-gray-700 shadow-sm" rows="4" placeholder="Masukkan Alamat" required></textarea>
            </div>
            <div class="mb-4">
                <label for="phone" class="block text-md font-medium text-teal-700">Nomor Telepon</label>
                <input type="tel" id="phone" name="phone" class="w-full mt-2 px-3 py-2 rounded-md border-gray-300 text-gray-700 shadow-sm" placeholder="08xx-xxxx-xxxx" required>
            </div>
            <div class="text-center">
                <button type="submit" class="bg-teal-500 text-white px-6 py-2 rounded-full font-medium hover:bg-teal-400 transition">Daftar</button>
            </div>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">
            sudah memiliki akun? <a href="login.php" class="text-teal-500 hover:underline">Login</a>.
        </p>
    </div>
</section>

<footer class="bg-teal-400 text-teal-100 py-6">
    <div class="container mx-auto text-center">
        <p>&copy; 2024 BK HOSPITAL. All rights reserved.</p>
    </div>
</footer>

<script src="../admin/script.js"></script>
</body>
</html>