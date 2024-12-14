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

// Ambil semua pasien dan riwayat mereka, termasuk obat yang diberikan
$stmt_pasien = $conn->prepare("
    SELECT 
        pasien.id AS id_pasien, 
        pasien.nama,
        MAX(periksa.tgl_periksa) AS tgl_terakhir,
        GROUP_CONCAT(
            CONCAT(
                periksa.tgl_periksa, '|', 
                daftar_poli.keluhan, '|', 
                periksa.catatan, '|', 
                COALESCE(
                    (SELECT GROUP_CONCAT(obat.nama_obat SEPARATOR ', ') 
                     FROM detail_periksa 
                     JOIN obat ON detail_periksa.id_obat = obat.id 
                     WHERE detail_periksa.id_periksa = periksa.id),
                    'Tidak ada obat'
                )
            ) ORDER BY periksa.tgl_periksa DESC SEPARATOR '||'
        ) AS riwayat
    FROM periksa
    JOIN daftar_poli ON periksa.id_daftar_poli = daftar_poli.id
    JOIN pasien ON daftar_poli.id_pasien = pasien.id
    JOIN jadwal_periksa ON daftar_poli.id_jadwal = jadwal_periksa.id
    WHERE jadwal_periksa.id_dokter = ?
    GROUP BY pasien.id, pasien.nama
    ORDER BY tgl_terakhir DESC
");
$stmt_pasien->bind_param("i", $id_dokter);
$stmt_pasien->execute();
$result_pasien = $stmt_pasien->get_result();
$stmt_pasien->close();
?>

<h1 class="text-3xl font-bold dark:text-white text-center">Riwayat Pemeriksaan Pasien</h1>
<p class="mt-2 text-gray-600 dark:text-gray-100 text-center">
    Berikut adalah daftar pasien dengan tanggal pemeriksaan terakhir mereka.
</p>

<div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
    <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Daftar Pasien</h2>

    <?php if ($result_pasien->num_rows === 0): ?>
        <div class="mt-4 text-center text-gray-500 dark:text-gray-300">
            Tidak ada data pasien.
        </div>
    <?php else: ?>
        <table class="mt-4 w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama Pasien</th>
                    <th scope="col" class="px-6 py-3">Tanggal Terakhir Periksa</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_pasien->fetch_assoc()): ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4"><?= htmlspecialchars($row['nama']); ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars(date('d-m-Y', strtotime($row['tgl_terakhir']))); ?></td>
                        <td class="px-6 py-4">
                            <button 
                                class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600" 
                                onclick="showRiwayat(
                                    '<?= htmlspecialchars($row['nama'], ENT_QUOTES); ?>', 
                                    '<?= htmlspecialchars($row['riwayat'], ENT_QUOTES); ?>'
                                )">
                                Lihat Riwayat
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal Detail Riwayat -->
<div id="riwayatModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
        <h3 class="text-xl font-bold text-gray-700 dark:text-gray-100 mb-4">Riwayat Pemeriksaan</h3>
        <p><strong>Nama Pasien:</strong> <span id="modal-nama"></span></p>
        <table class="mt-4 w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-3">Tanggal Pemeriksaan</th>
                    <th scope="col" class="px-6 py-3">Keluhan Pasien</th>
                    <th scope="col" class="px-6 py-3">Catatan Pemeriksaan</th>
                    <th scope="col" class="px-6 py-3">Obat</th>
                </tr>
            </thead>
            <tbody id="modal-riwayat"></tbody>
        </table>
        <div class="mt-6 flex justify-end space-x-2">
            <button class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600" onclick="hideRiwayat()">Tutup</button>
        </div>
    </div>
</div>

<script src="../admin/script.js"></script>
<script>
// Tampilkan modal dengan data riwayat
function showRiwayat(nama, riwayat) {
    document.getElementById('modal-nama').textContent = nama;

    const tbody = document.getElementById('modal-riwayat');
    tbody.innerHTML = ''; // Kosongkan isi tabel

    // Proses riwayat (format: tgl_periksa|keluhan|catatan|obat)
    const records = riwayat.split('||');
    records.forEach(record => {
        const [tgl_periksa, keluhan, catatan, obat] = record.split('|');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4">${tgl_periksa}</td>
            <td class="px-6 py-4">${keluhan}</td>
            <td class="px-6 py-4">${catatan}</td>
            <td class="px-6 py-4">${obat}</td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('riwayatModal').classList.remove('hidden');
}

// Sembunyikan modal
function hideRiwayat() {
    document.getElementById('riwayatModal').classList.add('hidden');
}
</script>
</body>
</html>