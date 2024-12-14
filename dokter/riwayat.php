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

// Validasi jika ID dokter tidak ditemukan
if (!$id_dokter) {
    die("Dokter tidak ditemukan. Pastikan data Anda valid.");
}

// Ambil daftar pasien dengan pemeriksaan terakhir
$stmt_pasien = $conn->prepare("
    SELECT DISTINCT pasien.id AS id_pasien, pasien.nama,
           MAX(periksa.tgl_periksa) AS tgl_terakhir
    FROM periksa
    JOIN daftar_poli ON periksa.id_daftar_poli = daftar_poli.id
    JOIN pasien ON daftar_poli.id_pasien = pasien.id
    JOIN jadwal_periksa ON daftar_poli.id_jadwal = jadwal_periksa.id
    WHERE jadwal_periksa.id_dokter = ?
    GROUP BY pasien.id
    ORDER BY tgl_terakhir DESC
");
$stmt_pasien->bind_param("i", $id_dokter);
$stmt_pasien->execute();
$result_pasien = $stmt_pasien->get_result();
$stmt_pasien->close();
?>

<h1 class="text-3xl font-bold dark:text-white text-center">Riwayat Pemeriksaan Pasien</h1>
<p class="mt-2 text-gray-600 dark:text-gray-100 text-center">
    Berikut adalah daftar pasien yang telah diperiksa oleh Anda.
</p>

<div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
    <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Riwayat Pasien</h2>

    <?php if ($result_pasien->num_rows === 0): ?>
        <div class="mt-4 text-center text-gray-500 dark:text-gray-300">
            Belum ada pasien yang diperiksa.
        </div>
    <?php else: ?>
        <table class="mt-4 w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama Pasien</th>
                    <th scope="col" class="px-6 py-3">Tanggal Pemeriksaan Terakhir</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_pasien->fetch_assoc()): ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4"><?= htmlspecialchars($row['nama']); ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars(date('d-m-Y', strtotime($row['tgl_terakhir']))); ?></td>
                        <td class="px-6 py-4">
                            <!-- Tombol Detail -->
                            <button 
                                class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600" 
                                onclick="showDetail(<?= $row['id_pasien']; ?>, '<?= htmlspecialchars($row['nama'], ENT_QUOTES); ?>')">
                                Detail
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<!-- Modal -->
<div id="detailModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
        <h3 class="text-xl font-bold text-gray-700 dark:text-gray-100 mb-4">Riwayat Pemeriksaan</h3>
        <p><strong>Nama Pasien:</strong> <span id="modal-nama"></span></p>
        <div class="mt-4">
            <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-100">Detail Pemeriksaan</h4>
            <ul id="modal-riwayat" class="list-disc list-inside text-gray-600 dark:text-gray-300 mt-2"></ul>
        </div>
        <div class="mt-6 flex justify-end space-x-2">
            <button class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600" onclick="hideDetail()">Tutup</button>
        </div>
    </div>
</div>

<script>
// Tampilkan modal dengan data riwayat
function showDetail(nama, riwayat) {
    document.getElementById('modal-nama').textContent = nama;

    const riwayatList = document.getElementById('modal-riwayat');
    riwayatList.innerHTML = ''; // Kosongkan sebelumnya

    // Loop melalui semua riwayat dan tambahkan ke modal
    if (riwayat.length > 0) {
        riwayat.forEach(item => {
            const li = document.createElement('li');
            li.textContent = `${new Date(item.tgl_periksa).toLocaleDateString('id-ID')}: ${item.keluhan} - Catatan: ${item.catatan} - Obat: ${item.obat}`;
            riwayatList.appendChild(li);
        });
    } else {
        const li = document.createElement('li');
        li.textContent = 'Belum ada riwayat pemeriksaan.';
        riwayatList.appendChild(li);
    }

    document.getElementById('detailModal').classList.remove('hidden');
}

// Tutup modal
function hideDetail() {
    document.getElementById('detailModal').classList.add('hidden');
}
</script>
