<?php
include 'head.php';
include 'sideMenu.php';

// Cek apakah user sudah login
if (!isset($_SESSION['nip'])) {
    header('Location: login.php');
    exit();
}

// Ambil ID dokter berdasarkan NIP dari sesi
$stmt_dokter = $conn->prepare("SELECT id FROM dokter WHERE nip = ?");
$stmt_dokter->bind_param("s", $_SESSION['nip']);
$stmt_dokter->execute();
$stmt_dokter->bind_result($id_dokter);
$stmt_dokter->fetch();
$stmt_dokter->close();

if (!$id_dokter) {
    die("Dokter tidak valid.");
}

$message = ''; // Pesan untuk umpan balik pengguna

// Ambil ID daftar_poli dari parameter URL
$id_daftar = intval($_GET['id_daftar'] ?? 0);
if ($id_daftar === 0) {
    die("ID pasien tidak valid.");
}

// Ambil informasi pasien dan keluhan
$stmt = $conn->prepare("
    SELECT daftar_poli.id AS id_daftar, pasien.nama, daftar_poli.keluhan, daftar_poli.active 
    FROM daftar_poli
    JOIN pasien ON daftar_poli.id_pasien = pasien.id
    WHERE daftar_poli.id = ?
");
$stmt->bind_param("i", $id_daftar);
$stmt->execute();
$result = $stmt->get_result();
$pasien = $result->fetch_assoc();
$stmt->close();

if (!$pasien) {
    die("Data pasien tidak ditemukan.");
}

// Cegah pemeriksaan ulang jika pasien sudah diperiksa
$is_already_checked = $pasien['active'];

// Ambil riwayat pemeriksaan pasien oleh dokter yang login
$stmt_riwayat = $conn->prepare("
    SELECT 
        periksa.tgl_periksa AS tanggal, 
        periksa.catatan, 
        daftar_poli.keluhan AS keluhan_saat_pemeriksaan, 
        periksa.biaya_periksa AS biaya,
        GROUP_CONCAT(obat.nama_obat SEPARATOR ', ') AS obat
    FROM periksa
    JOIN daftar_poli ON periksa.id_daftar_poli = daftar_poli.id
    JOIN jadwal_periksa ON daftar_poli.id_jadwal = jadwal_periksa.id
    LEFT JOIN detail_periksa ON periksa.id = detail_periksa.id_periksa
    LEFT JOIN obat ON detail_periksa.id_obat = obat.id
    WHERE 
        daftar_poli.id_pasien = (
            SELECT id_pasien FROM daftar_poli WHERE id = ?
        )
        AND jadwal_periksa.id_dokter = ? -- Filter berdasarkan dokter login
    GROUP BY periksa.id
    ORDER BY periksa.tgl_periksa DESC
");
$stmt_riwayat->bind_param("ii", $id_daftar, $id_dokter);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();
$stmt_riwayat->close();

// Proses simpan data pemeriksaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_already_checked) {
    $catatan = $_POST['catatan'] ?? '';
    $biaya_jasa = 150000; // Biaya jasa dokter tetap
    $obat_ids = $_POST['obat'] ?? [];

    try {
        $conn->begin_transaction();

        // Hitung biaya obat
        $biaya_obat = 0;
        if (!empty($obat_ids)) {
            $placeholder = implode(',', array_fill(0, count($obat_ids), '?'));
            $query_obat = "SELECT harga FROM obat WHERE id IN ($placeholder) AND active = 1";
            $stmt_obat = $conn->prepare($query_obat);
            $stmt_obat->bind_param(str_repeat('i', count($obat_ids)), ...$obat_ids);
            $stmt_obat->execute();
            $result_obat = $stmt_obat->get_result();
            while ($row = $result_obat->fetch_assoc()) {
                $biaya_obat += $row['harga'];
            }
            $stmt_obat->close();
        }

        // Total biaya periksa
        $total_biaya = $biaya_jasa + $biaya_obat;

        // Simpan data pemeriksaan
        $stmt_periksa = $conn->prepare("
            INSERT INTO periksa (id_daftar_poli, tgl_periksa, catatan, biaya_periksa) 
            VALUES (?, NOW(), ?, ?)
        ");
        $stmt_periksa->bind_param("isd", $id_daftar, $catatan, $total_biaya);
        $stmt_periksa->execute();
        $id_periksa = $stmt_periksa->insert_id;
        $stmt_periksa->close();

        // Simpan data obat yang digunakan
        if (!empty($obat_ids)) {
            $stmt_obat_periksa = $conn->prepare("
                INSERT INTO detail_periksa (id_periksa, id_obat) 
                VALUES (?, ?)
            ");
            foreach ($obat_ids as $obat_id) {
                $stmt_obat_periksa->bind_param("ii", $id_periksa, $obat_id);
                $stmt_obat_periksa->execute();
            }
            $stmt_obat_periksa->close();
        }

        // Update status daftar_poli menjadi selesai diperiksa
        $stmt_update = $conn->prepare("UPDATE daftar_poli SET active = 1 WHERE id = ?");
        $stmt_update->bind_param("i", $id_daftar);
        $stmt_update->execute();
        $stmt_update->close();

        $conn->commit();
        $message = "Pemeriksaan berhasil disimpan.";
        $is_already_checked = true;
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Gagal menyimpan pemeriksaan: " . $e->getMessage();
    }
}

// Ambil daftar obat aktif
$stmt_obat = $conn->prepare("SELECT id, nama_obat, kemasan, harga FROM obat WHERE active = 1");
$stmt_obat->execute();
$result_obat = $stmt_obat->get_result();
$stmt_obat->close();
?>

<!-- Tampilan -->
<h1 class="text-3xl font-bold dark:text-white text-center">Pemeriksaan Pasien</h1>
<p class="mt-2 text-gray-600 dark:text-gray-100 text-center">Pasien: <?= htmlspecialchars($pasien['nama']); ?></p>

<div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
    <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Pemeriksaan Baru</h2>

    <?php if (!empty($message)): ?>
        <div id="flash-message" class="mt-4 p-4 <?= strpos($message, 'berhasil') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> border rounded">
            <?= $message; ?>
        </div>
    <?php endif; ?>

    <!-- Form Pemeriksaan Baru -->
    <?php if (!$is_already_checked): ?>
        <form action="" method="POST" class="space-y-4 mt-4">
            <!-- Keluhan Pasien -->
            <div>
                <label for="keluhan" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Keluhan Pasien</label>
                <p class="text-sm text-gray-600 dark:text-gray-300"> <?= htmlspecialchars($pasien['keluhan']); ?></p>
            </div>
            <!-- Catatan -->
            <div>
                <label for="catatan" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Catatan Pemeriksaan</label>
                <textarea id="catatan" name="catatan" rows="4" 
                    class="w-full px-4 py-2 mt-1 border rounded-lg focus:ring focus:ring-indigo-200 focus:outline-none"
                    placeholder="Tambahkan catatan pemeriksaan..." required></textarea>
            </div>

            <!-- Obat -->
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Obat yang Digunakan</label>
                <?php while ($row = $result_obat->fetch_assoc()): ?>
                    <div class="flex items-center space-x-2 mt-2">
                        <input type="checkbox" name="obat[]" value="<?= $row['id']; ?>" id="obat-<?= $row['id']; ?>" 
                            data-harga="<?= $row['harga']; ?>" class="obat-checkbox" />
                        <label for="obat-<?= $row['id']; ?>" class="text-gray-600 dark:text-gray-300">
                            <?= htmlspecialchars($row['nama_obat']); ?> (<?= htmlspecialchars($row['kemasan']); ?>) - Rp. <?= number_format($row['harga'], 0, ',', '.'); ?>
                        </label>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Prediksi Biaya -->
            <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-700 rounded border text-gray-800 dark:text-gray-300">
                <p class="font-medium">Prediksi Total Biaya:</p>
                <p id="prediksi-biaya" class="text-xl font-bold">Rp. 150.000</p>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                Simpan Pemeriksaan
            </button>
        </form>
    <?php else: ?>
        <p class="mt-4 text-center text-gray-500 dark:text-gray-300">Pemeriksaan sudah selesai. Tidak dapat diperbarui lagi.</p>
    <?php endif; ?>

    <!-- Riwayat Pemeriksaan -->
    <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100 mt-6">Riwayat Pemeriksaan</h2>
    <table class="mt-4 w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
            <tr>
                <th scope="col" class="px-6 py-3">Tanggal Periksa </th>
                <th scope="col" class="px-6 py-3">Keluhan Pasien</th>
                <th scope="col" class="px-6 py-3">Catatan Dokter</th>
                <th scope="col" class="px-6 py-3">Biaya Pengobatan</th>
                <th scope="col" class="px-6 py-3">Obat</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_riwayat->fetch_assoc()): ?>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="px-6 py-4"><?= htmlspecialchars(date('l, d-m-Y', strtotime($row['tanggal']))); ?></td>                 
                    <td class="px-6 py-4"><?= htmlspecialchars($row['keluhan_saat_pemeriksaan']); ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row['catatan']); ?></td>
                    <td class="px-6 py-4">Rp. <?= number_format($row['biaya'], 0, ',', '.'); ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row['obat']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
// JavaScript untuk menghitung total biaya secara dinamis
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.obat-checkbox');
    const prediksiBiayaElem = document.getElementById('prediksi-biaya');
    const biayaJasaDokter = 150000; // Biaya jasa dokter tetap

    function updateTotalBiaya() {
        let totalBiaya = biayaJasaDokter;

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                totalBiaya += parseInt(checkbox.getAttribute('data-harga'));
            }
        });

        prediksiBiayaElem.textContent = 'Rp. ' + totalBiaya.toLocaleString('id-ID');
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotalBiaya);
    });

    // Perbarui total biaya saat halaman dimuat
    updateTotalBiaya();
});
</script>
<script src="../admin/script.js"></script>
</body>
</html>
