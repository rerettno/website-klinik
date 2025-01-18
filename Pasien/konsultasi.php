<?php
include 'head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Ambil no_rm pasien berdasarkan user_id
$stmt_no_rm = $conn->prepare("SELECT no_rm FROM pasien WHERE id = ?");
$stmt_no_rm->bind_param("i", $user_id);
$stmt_no_rm->execute();
$stmt_no_rm->bind_result($no_rm);
$stmt_no_rm->fetch();
$stmt_no_rm->close();

// Ambil daftar poli untuk dropdown
$stmt_poli = $conn->prepare("SELECT id, nama_poli FROM poli WHERE active = 1");
$stmt_poli->execute();
$result_poli = $stmt_poli->get_result();
$stmt_poli->close();

// Default nilai poli dan jadwal
$selected_poli = $_POST['poli'] ?? null;
$jadwal_options = [];

// Ambil jadwal dokter berdasarkan poli yang dipilih
if (!empty($selected_poli)) {
    $stmt_jadwal = $conn->prepare("
        SELECT 
            jadwal_periksa.id, 
            dokter.nama AS nama_dokter, 
            jadwal_periksa.hari, 
            jadwal_periksa.jam_mulai, 
            jadwal_periksa.jam_selesai, 
            dokter.id AS id_dokter
        FROM jadwal_periksa
        JOIN dokter ON jadwal_periksa.id_dokter = dokter.id
        JOIN poli ON dokter.id_poli = poli.id
        WHERE poli.id = ? AND jadwal_periksa.active = 1
    ");
    $stmt_jadwal->bind_param('i', $selected_poli);
    $stmt_jadwal->execute();
    $result_jadwal = $stmt_jadwal->get_result();
    while ($row = $result_jadwal->fetch_assoc()) {
        $jadwal_options[] = $row;
    }
    $stmt_jadwal->close();
}

// Proses formulir ketika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pertanyaan = $_POST['pertanyaan'] ?? '';
    $jadwal = $_POST['jadwal'] ?? '';
    $subject = $_POST['subject'] ?? '';

    // Validasi input
    if ($jadwal && $subject && $pertanyaan) {
        // Set zona waktu ke Jakarta
        date_default_timezone_set('Asia/Jakarta');

        // Ambil waktu server saat ini
        $current_date = new DateTime();
        $tgl_konsultasi = $current_date->format('Y-m-d H:i:s'); // Format tanggal dan waktu

        // Cek apakah dokter valid
        $stmt_check_dokter = $conn->prepare("SELECT id_dokter FROM jadwal_periksa WHERE id = ?");
        $stmt_check_dokter->bind_param('i', $jadwal);
        $stmt_check_dokter->execute();
        $stmt_check_dokter->bind_result($id_dokter);
        $stmt_check_dokter->fetch();
        $stmt_check_dokter->close();

        if ($id_dokter) {
            // Simpan data ke tabel konsultasi
            $stmt_daftar = $conn->prepare("
                INSERT INTO konsulasi (subject, pertanyaan, tgl_konsultasi, id_pasien, id_dokter)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt_daftar->bind_param('ssdii', $subject, $pertanyaan, $tgl_konsultasi, $user_id, $id_dokter);
            if ($stmt_daftar->execute()) {
                $message = "Pendaftaran konsultasi berhasil!";
            } else {
                $message = "Gagal mendaftar konsultasi.";
            }
            $stmt_daftar->close();
        } else {
            $message = "Dokter yang dipilih tidak valid.";
        }
    } else {
        $message = "Pastikan semua field terisi dengan benar.";
    }
}

// Ambil riwayat konsultasi pasien
$stmt_konsultasi = $conn->prepare("
    SELECT 
        konsulasi.tgl_konsultasi,
        konsulasi.subject,
        konsulasi.pertanyaan,
        konsulasi.jawaban
    FROM konsulasi
    WHERE konsulasi.id_pasien = ?
    ORDER BY konsulasi.tgl_konsultasi DESC
");
$stmt_konsultasi->bind_param('i', $user_id);
$stmt_konsultasi->execute();
$result_konsultasi = $stmt_konsultasi->get_result();
$stmt_konsultasi->close();
?>

<main class="container mx-auto mt-6">
    <!-- Formulir Konsultasi -->
    <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Ruang Konsultasi</h2>
        <?php if ($message): ?>
            <div class="text-red-500 mt-4"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="" method="POST" class="mt-4">
            <div class="mb-4">
                <label for="no_rm" class="block text-gray-700 font-medium">Nomor Rekam Medis</label>
                <input type="text" id="no_rm" name="no_rm" value="<?= htmlspecialchars($no_rm); ?>" 
                    class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200 focus:outline-none bg-gray-100"
                    readonly>
            </div>

            <div class="mb-4">
                <label for="poli" class="block text-gray-700 font-medium">Pilih Poli</label>
                <select name="poli" id="poli" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200 focus:outline-none"
                    onchange="this.form.submit()">
                    <option value="" disabled selected>Pilih Poli</option>
                    <?php while ($row = $result_poli->fetch_assoc()): ?>
                        <option value="<?= $row['id']; ?>" <?= $selected_poli == $row['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($row['nama_poli']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="jadwal" class="block text-gray-700 font-medium">Pilih Dokter</label>
                <select name="jadwal" id="jadwal" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200 focus:outline-none">
                    <option value="" disabled selected>Pilih Dokter</option>
                    <?php foreach ($jadwal_options as $jadwal): ?>
                        <option value="<?= $jadwal['id']; ?>">
                            <?= htmlspecialchars($jadwal['nama_dokter']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="subject" class="block text-gray-700 font-medium">Pilih Subject Pertanyaan</label>
                <select name="subject" id="subject" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200 focus:outline-none">
                    <option value="" disabled selected>Pilih Subject Pertanyaan</option>
                    <option value="Luka-Luka">Luka-Luka</option>
                    <option value="Pusing">Pusing</option>
                    <option value="Diare">Diare</option>
                    <option value="Lemas">Lemas</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="pertanyaan" class="block text-gray-700 font-medium">Pertanyaan</label>
                <textarea name="pertanyaan" id="pertanyaan" rows="3" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200 focus:outline-none"></textarea>
            </div>

            <button type="submit" class="w-full bg-teal-500 text-white py-2 rounded-lg hover:bg-teal-400 transition">
                Konsultasi Sekarang
            </button>
        </form>
    </section>

    <!-- Riwayat Konsultasi -->
    <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
        <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Riwayat Konsultasi</h2>
        <?php if ($result_konsultasi->num_rows === 0): ?>
            <div class="mt-4 text-center text-gray-500 dark:text-gray-300">
                Anda tidak memiliki riwayat Konsultasi.
            </div>
        <?php else: ?>
            <table class="mt-4 w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                    <tr>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">Subject</th>
                        <th class="px-4 py-2">Pertanyaan</th>
                        <th class="px-4 py-2">Jawaban</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_konsultasi->fetch_assoc()): ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-4 py-2"><?= htmlspecialchars($row['tgl_konsultasi']); ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($row['subject']); ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($row['pertanyaan']); ?></td>
                            <td class="px-4 py-2">
                                <?= $row['jawaban'] ? htmlspecialchars($row['jawaban']) : '<span class="text-yellow-500">Belum Dijawab</span>'; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>

<script src="../admin/script.js"></script>
</body>
</html>  