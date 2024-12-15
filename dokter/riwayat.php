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

$stmt_pasien = $conn->prepare("
    SELECT 
        pasien.id AS id_pasien, 
        pasien.nama, 
        MAX(periksa.tgl_periksa) AS tgl_terakhir,
        GROUP_CONCAT(
            CONCAT(
                periksa.tgl_periksa, '|', 
                jadwal_periksa.hari, ' ',
                jadwal_periksa.jam_mulai, '-', jadwal_periksa.jam_selesai, '|',  
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
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-4xl relative">
        <button 
            class="absolute top-3 right-3 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100"
            onclick="hideRiwayat()"
        >
            ✖
        </button>
        <h3 class="text-xl font-bold text-gray-700 dark:text-gray-100 mb-4">Riwayat Pemeriksaan</h3>
        <p class="mb-4">
            <strong>Nama Pasien:</strong> <span id="modal-nama" class="text-gray-800 dark:text-gray-200"></span>
        </p>
        <div class="overflow-x-auto">
            <table class="table-auto min-w-full text-sm text-left text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 min-w-[150px] border border-gray-200 dark:border-gray-700">Tanggal Periksa</th>
                        <th scope="col" class="px-6 py-3 min-w-[150px] border border-gray-200 dark:border-gray-700">Jadwal Periksa</th>
                        <th scope="col" class="px-6 py-3 min-w-[200px] border border-gray-200 dark:border-gray-700">Keluhan Pasien</th>
                        <th scope="col" class="px-6 py-3 min-w-[200px] border border-gray-200 dark:border-gray-700">Catatan Dokter</th>
                        <th scope="col" class="px-6 py-3 min-w-[150px] border border-gray-200 dark:border-gray-700">Obat</th>
                    </tr>
                </thead>
                <tbody id="modal-riwayat" class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Data akan diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>
        <div class="mt-6 flex justify-end space-x-2">
            <button 
                class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 focus:outline-none"
                onclick="hideRiwayat()"
            >
                Tutup
            </button>
        </div>
    </div>
</div>



<script src="../admin/script.js"></script>
<script>
function showRiwayat(nama, riwayat) {
    document.getElementById('modal-nama').textContent = nama;

    const tbody = document.getElementById('modal-riwayat');
    tbody.innerHTML = ''; // Kosongkan isi tabel

    const records = riwayat.split('||');
    records.forEach(record => {
        const [tgl_periksa,hari, keluhan, catatan, obat] = record.split('|');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">${tgl_periksa}</td>
            <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">${hari}</td>
            <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">${keluhan}</td>
            <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">${catatan}</td>
            <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">${obat}</td>
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
