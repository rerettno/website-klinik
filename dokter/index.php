<?php
include 'head.php';
include 'sideMenu.php';

// Ambil ID dokter berdasarkan nip dari session
$stmt_id = $conn->prepare("SELECT id FROM dokter WHERE nip = ?");
$stmt_id->bind_param("s", $_SESSION['nip']);
$stmt_id->execute();
$stmt_id->bind_result($id_dokter);
$stmt_id->fetch();
$stmt_id->close();

if (!$id_dokter) {
    die("Dokter tidak ditemukan. Pastikan data Anda valid.");
}

// Ambil pasien yang belum diperiksa (active = 0)
$stmt_antrian = $conn->prepare("
    SELECT 
        daftar_poli.id AS id_daftar,
        daftar_poli.no_antrian,
        pasien.nama, pasien.no_rm,
        daftar_poli.keluhan,
        jadwal_periksa.*
    FROM daftar_poli
    JOIN pasien ON daftar_poli.id_pasien = pasien.id
    JOIN jadwal_periksa ON daftar_poli.id_jadwal = jadwal_periksa.id
    WHERE jadwal_periksa.id_dokter = ? 
      AND daftar_poli.active = 0
    ORDER BY daftar_poli.no_antrian ASC
");
$stmt_antrian->bind_param("i", $id_dokter);
$stmt_antrian->execute();
$result_antrian = $stmt_antrian->get_result();
$jumlah_pasien = $result_antrian->num_rows; // Hitung jumlah pasien
$stmt_antrian->close();

// Ambil jadwal dokter pribadi yang aktif
$stmt_jadwal_dokter = $conn->prepare("
    SELECT jadwal_periksa.*, poli.nama_poli
    FROM jadwal_periksa 
    JOIN dokter ON jadwal_periksa.id_dokter = dokter.id 
    JOIN poli ON dokter.id_poli = poli.id
    WHERE id_dokter = ? AND jadwal_periksa.active = 1
");
$stmt_jadwal_dokter->bind_param('i', $id_dokter);
$stmt_jadwal_dokter->execute();
$result_jadwal_dokter = $stmt_jadwal_dokter->get_result();
$stmt_jadwal_dokter->close();

?>
<div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Pesan jika akun tidak aktif -->
    <?php if (isset($_GET['message']) && $_GET['message'] === 'inactive'): ?>
        <div class="p-4 mb-4 text-sm text-white bg-red-600 rounded-lg">
            Akun Anda saat ini tidak aktif. Silakan hubungi administrator untuk aktivasi.
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-extrabold text-gray-800 dark:text-white">Dashboard Dokter</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-300">Selamat beraktivitas kembali!</p>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Statistik Pasien Hari Ini -->
        <div class="bg-blue-500 text-white p-6 rounded-lg shadow-lg flex flex-col items-center">
            <h2 class="text-2xl font-semibold">Jumlah Pasien Menunggu Diperiksa </h2>
            <p class="text-6xl font-bold mt-4"><?= $jumlah_pasien; ?></p>
        </div>

        <!-- Jadwal Pribadi Dokter -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Jadwal Pribadi Anda</h2>
            <?php if ($result_jadwal_dokter->num_rows > 0): ?>
                <div class="overflow-hidden rounded-lg shadow">
                    <table class="min-w-full bg-gray-50 dark:bg-gray-700 text-sm">
                        <thead class="bg-gray-200 dark:bg-gray-600">
                            <tr>
                                <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">Poli</th>
                                <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">Hari</th>
                                <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">Jam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result_jadwal_dokter->fetch_assoc()): ?>
                                <tr class="border-b dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <td class="py-3 px-4"><?= htmlspecialchars($row['nama_poli']); ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($row['hari']); ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($row['jam_mulai'] . ' - ' . $row['jam_selesai']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="mt-4 text-gray-600 dark:text-gray-300">Tidak ada jadwal yang aktif.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../admin/script.js"></script>
</body>
</html>
