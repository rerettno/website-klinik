<?php
include 'head.php';
include 'sideMenu.php';

// Hitung total statistik
$sql_poli = "SELECT COUNT(*) AS total_poli FROM poli WHERE active = TRUE";
$result_poli = $conn->query($sql_poli);
$total_poli = $result_poli->fetch_assoc()['total_poli'] ?? 0;

$sql_dokter = "SELECT COUNT(*) AS total_dokter FROM dokter 
               JOIN poli ON dokter.id_poli = poli.id 
               WHERE dokter.active = TRUE AND poli.active = TRUE";
$result_dokter = $conn->query($sql_dokter);
$total_dokter = $result_dokter->fetch_assoc()['total_dokter'] ?? 0;

$sql_obat = "SELECT COUNT(*) AS total_obat FROM obat WHERE active = TRUE";
$result_obat = $conn->query($sql_obat);
$total_obat = $result_obat->fetch_assoc()['total_obat'] ?? 0;

$sql_pasien = "SELECT COUNT(*) AS total_pasien FROM pasien WHERE active = TRUE";
$result_pasien = $conn->query($sql_pasien);
$total_pasien = $result_pasien->fetch_assoc()['total_pasien'] ?? 0;

// Ambil nama hari saat ini dalam format bahasa Indonesia
$days_in_indonesian = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu',
];
$current_day = $days_in_indonesian[(new DateTime("now", new DateTimeZone("Asia/Jakarta")))->format('l')];

$stmt_jadwal_hari_ini = $conn->prepare("
    SELECT 
        jadwal_periksa.id AS jadwal_id,
        poli.nama_poli,
        dokter.nama AS nama_dokter,
        jadwal_periksa.hari,
        jadwal_periksa.jam_mulai,
        jadwal_periksa.jam_selesai
    FROM jadwal_periksa
    JOIN dokter ON jadwal_periksa.id_dokter = dokter.id
    JOIN poli ON dokter.id_poli = poli.id
    WHERE jadwal_periksa.hari = ?
      AND jadwal_periksa.active = 1
      AND dokter.active = 1
      AND poli.active = 1
    ORDER BY jadwal_periksa.jam_mulai ASC
");

$stmt_jadwal_hari_ini->bind_param('s', $current_day);
$stmt_jadwal_hari_ini->execute();
$result_jadwal_hari_ini = $stmt_jadwal_hari_ini->get_result();
$stmt_jadwal_hari_ini->close();


// Ambil daftar pasien yang diperiksa hari ini
$current_date = (new DateTime("now", new DateTimeZone("Asia/Jakarta")))->format('Y-m-d');
$stmt_pasien_hari_ini = $conn->prepare("
    SELECT 
        daftar_poli.no_antrian,
        daftar_poli.keluhan,
        poli.nama_poli,
        dokter.nama AS nama_dokter,
        pasien.nama AS nama_pasien,
        daftar_poli.active
    FROM daftar_poli
    JOIN jadwal_periksa ON daftar_poli.id_jadwal = jadwal_periksa.id
    JOIN dokter ON jadwal_periksa.id_dokter = dokter.id
    JOIN poli ON dokter.id_poli = poli.id
    JOIN pasien ON daftar_poli.id_pasien = pasien.id
    WHERE daftar_poli.tgl_daftar = ?
    ORDER BY daftar_poli.no_antrian ASC
");
$stmt_pasien_hari_ini->bind_param('s', $current_date);
$stmt_pasien_hari_ini->execute();
$result_pasien_hari_ini = $stmt_pasien_hari_ini->get_result();
$total_pasien_hari_ini = $result_pasien_hari_ini->num_rows;
$stmt_pasien_hari_ini->close();
?>
<div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-extrabold text-gray-800 dark:text-white">Dashboard Admin</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-300">Selamat datang di halaman admin.</p>
    </div>

    <!-- Statistik Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-blue-100 text-blue-900 p-6 rounded-lg shadow-lg text-center">
            <h2 class="text-xl font-semibold">Total Poli</h2>
            <p class="text-5xl font-bold mt-3"><?= htmlspecialchars($total_poli); ?></p>
        </div>
        <div class="bg-green-100 text-green-900 p-6 rounded-lg shadow-lg text-center">
            <h2 class="text-xl font-semibold">Total Dokter</h2>
            <p class="text-5xl font-bold mt-3"><?= htmlspecialchars($total_dokter); ?></p>
        </div>
        <div class="bg-yellow-100 text-yellow-900 p-6 rounded-lg shadow-lg text-center">
            <h2 class="text-xl font-semibold">Total Obat</h2>
            <p class="text-5xl font-bold mt-3"><?= htmlspecialchars($total_obat); ?></p>
        </div>
        <div class="bg-red-100 text-red-900 p-6 rounded-lg shadow-lg text-center">
            <h2 class="text-xl font-semibold">Total Pasien</h2>
            <p class="text-5xl font-bold mt-3"><?= htmlspecialchars($total_pasien); ?></p>
        </div>
    </div>

    <!-- Jadwal Dokter Hari Ini -->
    <div class="mt-10">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Jadwal Dokter Aktif Hari Ini</h2>
        <?php if ($result_jadwal_hari_ini->num_rows > 0): ?>
            <div class="overflow-hidden rounded-lg shadow">
                <table class="min-w-full bg-white dark:bg-gray-800 text-sm">
                    <thead class="bg-gray-200 dark:bg-gray-700">
                        <tr>
                            <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">Nama Poli</th>
                            <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">Nama Dokter</th>
                            <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">Jadwal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_jadwal_hari_ini->fetch_assoc()): ?>
                            <tr class="border-b dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <td class="py-3 px-4"><?= htmlspecialchars($row['nama_poli']); ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($row['nama_dokter']); ?></td>
                                <td class="py-3 px-4">
                                    <?= htmlspecialchars($row['hari'] . " (" . $row['jam_mulai'] . " - " . $row['jam_selesai'] . ")"); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="mt-4 text-gray-600 dark:text-gray-300">Tidak ada jadwal dokter yang aktif hari ini.</p>
        <?php endif; ?>
    </div>

    <!-- Daftar Pasien Hari Ini -->
    <div class="mt-10">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Daftar Pasien Hari Ini (<?= $total_pasien_hari_ini; ?>)</h2>
        <?php if ($total_pasien_hari_ini > 0): ?>
            <div class="overflow-hidden rounded-lg shadow">
                <table class="min-w-full bg-white dark:bg-gray-800 text-sm">
                    <thead class="bg-gray-200 dark:bg-gray-700">
                        <tr>
                            <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">No Antrian</th>
                            <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">Nama Pasien</th>
                            <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">Poli</th>
                            <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">Dokter</th>
                            <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">Keluhan</th>
                            <th class="py-3 px-4 text-left font-medium text-gray-800 dark:text-gray-300">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_pasien_hari_ini->fetch_assoc()): ?>
                            <tr class="border-b dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <td class="py-3 px-4"><?= htmlspecialchars($row['no_antrian']); ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($row['nama_pasien']); ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($row['nama_poli']); ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($row['nama_dokter']); ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($row['keluhan']); ?></td>
                                <td class="py-3 px-4">
                                    <?= $row['active'] == 1 ? '<span class="text-green-500">Selesai</span>' : '<span class="text-yellow-500">Menunggu</span>'; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="mt-4 text-gray-600 dark:text-gray-300">Tidak ada pasien yang diperiksa hari ini.</p>
        <?php endif; ?>
    </div>
</div>


<script src="script.js"></script>
</body>
</html>
