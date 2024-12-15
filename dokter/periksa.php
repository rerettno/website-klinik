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
        pasien.nama,
        daftar_poli.keluhan,jadwal_periksa.*
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

?>

<!-- Tampilan -->
<h1 class="text-3xl font-bold dark:text-white text-center">Periksa Pasien</h1>
<p class="mt-2 text-gray-600 dark:text-gray-100 text-center">
    Jumlah pasien menunggu: <?= $jumlah_pasien; ?>
</p>

<div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">

    <?php if ($result_antrian->num_rows === 0): ?>
        <div class="mt-4 text-center text-gray-500 dark:text-gray-300">
            Tidak ada pasien yang menunggu saat ini.
        </div>
    <?php else: ?>
        <table class="mt-4 w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-3">Nomor Antrian</th>
                    <th scope="col" class="px-6 py-3">Nama Pasien</th>
                    <th scope="col" class="px-6 py-3">Jadwal Periksa</th>
                    <th scope="col" class="px-6 py-3">Keluhan Pasien</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_antrian->fetch_assoc()): ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4"><?= htmlspecialchars($row['no_antrian']); ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['nama']); ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['hari'] . ', ' . $row['jam_mulai'] . ' - ' . $row['jam_selesai']); ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['keluhan']); ?></td>
                        <td class="px-6 py-4 flex space-x-2">

                            <!-- Tombol Periksa -->
                            <a href="pasien.php?id_daftar=<?= $row['id_daftar']; ?>" 
                                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                                Periksa
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>



<script src="../admin/script.js"></script>
</body>
</html>
