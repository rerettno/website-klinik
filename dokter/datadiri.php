<?php
include 'head.php';
include 'sideMenu.php';

$message = ''; // Pesan untuk umpan balik pengguna

// Proses update data dokter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_dokter = trim($_POST['nama'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $no_hp = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($nama_dokter) || empty($alamat) || empty($no_hp)) {
        $message = "Semua kolom wajib diisi.";
    } else {
        try {
            $conn->begin_transaction();

            // Update nama, alamat, dan nomor telepon
            $stmt_update = $conn->prepare("UPDATE dokter SET nama = ?, alamat = ?, no_hp = ? WHERE nip = ?");
            $stmt_update->bind_param("ssss", $nama_dokter, $alamat, $no_hp, $_SESSION['nip']);
            $stmt_update->execute();
            $stmt_update->close();

            // Update password jika diisi
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_password = $conn->prepare("UPDATE dokter SET password = ? WHERE nip = ?");
                $stmt_password->bind_param("ss", $hashed_password, $_SESSION['nip']);
                $stmt_password->execute();
                $stmt_password->close();
            }

            $conn->commit();
            $message = "Data berhasil diperbarui.";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Gagal memperbarui data: " . $e->getMessage();
        }
    }
}

// Ambil data dokter untuk ditampilkan di profil
$stmt = $conn->prepare("SELECT dokter.*, poli.nama_poli FROM dokter 
                        LEFT JOIN poli ON dokter.id_poli = poli.id  
                        WHERE dokter.nip = ?");
$stmt->bind_param("s", $_SESSION['nip']);
$stmt->execute();
$result = $stmt->get_result();
$dokter = $result->fetch_assoc();
$stmt->close();

if (!$dokter) {
    die("Data dokter tidak ditemukan.");
}
?>

<h1 class="text-3xl font-bold dark:text-white text-center">Profil Dokter</h1>
<p class="mt-2 text-gray-600 dark:text-gray-100 text-center">Selamat datang, <?= htmlspecialchars($_SESSION['nama']); ?>!</p>

<div class="max-w-4xl mx-auto bg-white dark:bg-gray-300 p-6 rounded-lg shadow-md mt-6">
    <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-800 mb-4 border-b pb-2">Data Profil</h2>
    
    <?php if (!empty($message)): ?>
        <div id="flash-message" class="mt-4 p-4 <?= strpos($message, 'berhasil') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> border rounded">
            <?= $message; ?>
        </div>
    <?php endif; ?>
    <!-- Tampilan Profil -->
    <div id="profile-view" class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-gray-700">
        <p class="font-semibold">NIP (Nomor Induk Peengguna)</p>
        <p>: <?= htmlspecialchars($_SESSION['nip']); ?></p>

        <p class="font-semibold">Nama</p>
        <p>: <?= htmlspecialchars($dokter['nama']); ?></p>

        <p class="font-semibold">Alamat</p>
        <p>: <?= htmlspecialchars($dokter['alamat']); ?></p>

        <p class="font-semibold">Nomor Telepon</p>
        <p>: <?= htmlspecialchars($dokter['no_hp']); ?></p>

        <p class="font-semibold">Penempatan Saat Ini</p>
        <p>: <?= htmlspecialchars($dokter['nama_poli']); ?></p>
    </div>

    <!-- Tombol Edit Profil -->
    <button id="edit-btn" 
        class="mt-6 w-full sm:w-auto bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition shadow-md">
        Edit Profil
    </button>

    <!-- Form Edit Data -->
    <form id="edit-form" action="" method="POST" class="hidden mt-6 space-y-4 text-gray-700">
        <!-- Nama -->
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-600 dark:text-gray-800">Nama</label>
            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($dokter['nama']); ?>" required
                class="w-full px-4 py-2 mt-1 border rounded-lg shadow focus:ring focus:ring-indigo-200 focus:outline-none">
        </div>

        <!-- Alamat -->
        <div>
            <label for="alamat" class="block text-sm font-medium text-gray-600 dark:text-gray-800">Alamat</label>
            <textarea id="alamat" name="alamat" rows="4"
                class="w-full px-4 py-2 mt-1 border rounded-lg shadow focus:ring focus:ring-indigo-200 focus:outline-none"><?= htmlspecialchars($dokter['alamat']); ?></textarea>
        </div>

        <!-- Telepon -->
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-600 dark:text-gray-800">Nomor Telepon</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($dokter['no_hp']); ?>" required
                class="w-full px-4 py-2 mt-1 border rounded-lg shadow focus:ring focus:ring-indigo-200 focus:outline-none">
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-600 dark:text-gray-800">Password Baru</label>
            <input type="password" id="password" name="password" 
                class="w-full px-4 py-2 mt-1 border rounded-lg shadow focus:ring focus:ring-indigo-200 focus:outline-none"
                placeholder="Kosongkan jika tidak ingin mengubah password">
        </div>

        <!-- Tombol Aksi -->
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
            <button type="submit" 
                class="w-full sm:w-auto bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition shadow-md">
                Simpan Perubahan
            </button>
            <button type="button" id="cancel-btn" 
                class="w-full sm:w-auto bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition shadow-md">
                Batal
            </button>
        </div>
    </form>
</div>


<script>
// Tampilkan form edit saat tombol "Edit" diklik
document.getElementById('edit-btn').addEventListener('click', () => {
    document.getElementById('profile-view').classList.add('hidden');
    document.getElementById('edit-form').classList.remove('hidden');
    document.getElementById('edit-btn').classList.add('hidden');
});

// Kembalikan ke tampilan profil saat tombol "Batal" diklik
document.getElementById('cancel-btn').addEventListener('click', () => {
    document.getElementById('edit-form').classList.add('hidden');
    document.getElementById('profile-view').classList.remove('hidden');
});
</script>

<script src="../admin/script.js"></script>
</body>
</html>
