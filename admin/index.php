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
    <h1 class="text-3xl font-bold">Dashboard</h1>
    <p class="mt-2 text-gray-600">Selamat datang di halaman admin.</p>

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

    <!-- Tabel Data Terbaru -->
    <div class="mt-8">
        <h2 class="text-2xl font-bold">Data Terbaru</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Tabel Pasien -->
            <div>
                <h3 class="text-xl font-bold mb-2">Pasien Terbaru</h3>
                <div class="overflow-x-auto shadow-md rounded-lg">
                    <table class="w-full text-sm text-gray-600">
                        <thead class="bg-gray-200 text-gray-900">
                            <tr>
                                <th class="px-4 py-2">Nama</th>
                                <th class="px-4 py-2">NIK</th>
                                <th class="px-4 py-2">No. RM</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_recent_pasien = "SELECT nama, nik, no_rm FROM pasien WHERE active = TRUE ORDER BY id DESC LIMIT 5";
                            $result_recent_pasien = $conn->query($sql_recent_pasien);

                            if ($result_recent_pasien->num_rows > 0):
                                while ($row = $result_recent_pasien->fetch_assoc()):
                            ?>
                            <tr class="bg-white border-b">
                                <td class="px-4 py-2"><?= htmlspecialchars($row['nama']); ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['nik']); ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['no_rm']); ?></td>
                            </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-center">Tidak ada data.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Tabel Dokter -->
            <div>
                <h3 class="text-xl font-bold mb-2">Dokter Terbaru</h3>
                <div class="overflow-x-auto shadow-md rounded-lg">
                    <table class="w-full text-sm text-gray-600">
                        <thead class="bg-gray-200 text-gray-900">
                            <tr>
                                <th class="px-4 py-2">Nama</th>
                                <th class="px-4 py-2">Spesialis</th>
                                <th class="px-4 py-2">No. HP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_recent_dokter = "SELECT dokter.*, poli.nama_poli FROM dokter 
                            JOIN poli ON dokter.id_poli = poli.id 
                            WHERE dokter.active = TRUE ORDER BY id DESC LIMIT 5";
                            $result_recent_dokter = $conn->query($sql_recent_dokter);

                            if ($result_recent_dokter->num_rows > 0):
                                while ($row = $result_recent_dokter->fetch_assoc()):
                            ?>
                            <tr class="bg-white border-b">
                                <td class="px-4 py-2"><?= htmlspecialchars($row['nama']); ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['nama_poli']); ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['no_hp']); ?></td>
                            </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-center">Tidak ada data.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
