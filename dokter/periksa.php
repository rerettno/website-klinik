<?php
include 'head.php';
include 'sideMenu.php';

$message = ''; // Pesan untuk umpan balik pengguna

// Ambil ID dokter berdasarkan nip dari session
$stmt_id = $conn->prepare("SELECT id FROM dokter WHERE nip = ?");
$stmt_id->bind_param("s", $_SESSION['nip']);
$stmt_id->execute();
$stmt_id->bind_result($id_dokter);
$stmt_id->fetch();
$stmt_id->close();

// Validasi jika ID dokter tidak ditemukan
if (!$id_dokter) {
    die("Dokter tidak ditemukan. Pastikan data Anda valid.");
}

// Ambil daftar pasien mengantri
$stmt_antrian = $conn->prepare("
    SELECT daftar_poli.id AS id_daftar, daftar_poli.no_antrian, pasien.nama, daftar_poli.keluhan 
    FROM daftar_poli
    JOIN pasien ON daftar_poli.id_pasien = pasien.id
    JOIN jadwal_periksa ON daftar_poli.id_jadwal = jadwal_periksa.id
    WHERE jadwal_periksa.id_dokter = ? 
      AND jadwal_periksa.active = 1
      AND daftar_poli.active = 0
    ORDER BY daftar_poli.no_antrian ASC
");
$stmt_antrian->bind_param("i", $id_dokter);
$stmt_antrian->execute();
$result_antrian = $stmt_antrian->get_result();
$stmt_antrian->close();

// Hitung jumlah pasien mengantri
$jumlah_antri = $result_antrian->num_rows;
?>

<h1 class="text-3xl font-bold dark:text-white text-center">Daftar Pasien Mengantri</h1>
<p class="mt-2 text-gray-600 dark:text-gray-100 text-center">
    Saat ini ada <strong><?= $jumlah_antri; ?></strong> pasien yang mengantri untuk diperiksa.
</p>

<div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
    <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Pasien Mengantri</h2>

    <?php if ($jumlah_antri === 0): ?>
        <div class="mt-4 text-center text-gray-500 dark:text-gray-300">
            Tidak ada pasien yang mengantri saat ini.
        </div>
    <?php else: ?>
        <table class="mt-4 w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-3">Nomor Antrian</th>
                    <th scope="col" class="px-6 py-3">Nama Pasien</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_antrian->fetch_assoc()): ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4"><?= htmlspecialchars($row['no_antrian']); ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['nama']); ?></td>
                        <td class="px-6 py-4">
                            <span class="text-yellow-500 font-semibold">Menunggu diperiksa</span>
                        </td>
                        <td class="px-6 py-4 flex space-x-2">
                            <!-- Tombol Detail -->
                            <button 
                                class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600" 
                                onclick="showDetail('<?= htmlspecialchars($row['nama'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['keluhan'], ENT_QUOTES); ?>', <?= $row['id_daftar']; ?>)">
                                Detail
                            </button>
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

<!-- Modal Detail -->
<div id="detailModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
        <h3 class="text-xl font-bold text-gray-700 dark:text-gray-100 mb-4">Detail Pasien</h3>
        <p><strong>Nama Pasien:</strong> <span id="modal-nama"></span></p>
        <p class="mt-2"><strong>Keluhan:</strong></p>
        <p id="modal-keluhan" class="text-gray-600 dark:text-gray-300"></p>
        <p class="mt-4"><strong>Riwayat Pemeriksaan Sebelumnya:</strong></p>
        <ul id="modal-riwayat" class="list-disc list-inside text-gray-600 dark:text-gray-300"></ul>
        <div class="mt-6 flex justify-end space-x-2">
            <button class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600" onclick="hideDetail()">Tutup</button>
        </div>
    </div>
</div>

<script>
// Function untuk menampilkan modal detail
function showDetail(nama, keluhan, id_daftar) {
    document.getElementById('modal-nama').textContent = nama;
    document.getElementById('modal-keluhan').textContent = keluhan;

    // Fetch riwayat pemeriksaan pasien
    fetchRiwayat(id_daftar);
    document.getElementById('detailModal').classList.remove('hidden');
}

// Function untuk menyembunyikan modal
function hideDetail() {
    document.getElementById('detailModal').classList.add('hidden');
}

// Fetch riwayat pemeriksaan dari PHP
function fetchRiwayat(id_daftar) {
    const riwayatList = document.getElementById('modal-riwayat');
    riwayatList.innerHTML = '';

    <?php
    $stmt_riwayat = $conn->prepare("
        SELECT periksa.tgl_periksa, periksa.catatan, GROUP_CONCAT(obat.nama_obat SEPARATOR ', ') AS obat
        FROM periksa
        JOIN detail_periksa ON periksa.id = detail_periksa.id_periksa
        JOIN obat ON detail_periksa.id_obat = obat.id
        WHERE periksa.id_daftar_poli = ?
        GROUP BY periksa.id
    ");
    ?>
    
    <?php $stmt_riwayat->bind_param("i", $row['id_daftar']); ?>
    <?php $stmt_riwayat->execute(); ?>
}

</script>
