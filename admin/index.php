<?php include 'head.php'; ?>
<?php include 'sideMenu.php'; ?>

<?php
// Contoh query untuk mendapatkan ringkasan data
// Pastikan variabel $conn sudah tersedia dari koneksi database

// Hitung jumlah poli
$sql_poli = "SELECT COUNT(*) AS total_poli FROM poli where active = TRUE";
$result_poli = $conn->query($sql_poli);
$total_poli = $result_poli->fetch_assoc()['total_poli'] ?? 0;

// Hitung jumlah dokter
$sql_dokter = "SELECt COUNT(*) AS total_dokter FROM dokter 
                            JOIN poli ON dokter.id_poli = poli.id 
                            WHERE dokter.active = TRUE and poli.active  = TRUE";
$result_dokter = $conn->query($sql_dokter);
$total_dokter = $result_dokter->fetch_assoc()['total_dokter'] ?? 0;

// Hitung jumlah obat
$sql_obat = "SELECT COUNT(*) AS total_obat FROM obat where active = TRUE";
$result_obat = $conn->query($sql_obat);
$total_obat = $result_obat->fetch_assoc()['total_obat'] ?? 0;

// Hitung jumlah pasien
$sql_pasien = "SELECT COUNT(*) AS total_pasien FROM pasien WHERE active = TRUE";
$result_pasien = $conn->query($sql_pasien);
$total_pasien = $result_pasien->fetch_assoc()['total_pasien'] ?? 0;
?>

<!-- Main Content -->
<div class="p-4">
    <h1 class="text-3xl font-bold dark:text-white text-center">Dashboard</h1>
    <p class="mt-2 text-gray-600 dark:text-gray-100 text-center">Selamat datang di halaman admin.</p>

    <!-- Statistik Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
        <!-- Ringkasan Poli -->
        <div class="bg-blue-100 text-blue-900 p-4 rounded-lg shadow">
            <h2 class="text-xl font-bold">Total Poli</h2>
            <p class="text-3xl mt-2"><?= htmlspecialchars($total_poli); ?></p>
        </div>
        <!-- Ringkasan Dokter -->
        <div class="bg-green-100 text-green-900 p-4 rounded-lg shadow">
            <h2 class="text-xl font-bold">Total Dokter</h2>
            <p class="text-3xl mt-2"><?= htmlspecialchars($total_dokter); ?></p>
        </div>
        <!-- Ringkasan Obat -->
        <div class="bg-yellow-100 text-yellow-900 p-4 rounded-lg shadow">
            <h2 class="text-xl font-bold">Total Obat</h2>
            <p class="text-3xl mt-2"><?= htmlspecialchars($total_obat); ?></p>
        </div>
        <!-- Ringkasan Pasien -->
        <div class="bg-red-100 text-red-900 p-4 rounded-lg shadow">
            <h2 class="text-xl font-bold">Total Pasien</h2>
            <p class="text-3xl mt-2"><?= htmlspecialchars($total_pasien); ?></p>
        </div>
    </div>


</div>

<script src="script.js"></script>
</body>
</html>
