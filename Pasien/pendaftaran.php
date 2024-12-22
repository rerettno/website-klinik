<?php
include 'head.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();//ase
}

$user_id = $_SESSION['user_id'];
$message = "";

// Hapus otomatis pendaftaran yang sudah lewat sehari penuh
$stmt_hapus_otomatis = $conn->prepare("
    DELETE FROM daftar_poli 
    WHERE tgl_daftar < CURDATE() AND active = 0
");
$stmt_hapus_otomatis->execute();
$stmt_hapus_otomatis->close();

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
            jadwal_periksa.jam_selesai 
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

// Ambil waktu server saat inio
$current_date = new DateTime("now", new DateTimeZone("Asia/Jakarta"));
$current_day = strtolower($current_date->format('l')); // Nama hari dalam huruf kecil
$current_time = $current_date->format('H:i'); // Jam dalam format 24-jam (HH:MM)

// Array translasi nama hari
$hari_map = [
    'senin' => 'Monday',
    'selasa' => 'Tuesday',
    'rabu' => 'Wednesday',
    'kamis' => 'Thursday',
    'jumat' => 'Friday',
    'sabtu' => 'Saturday',
    'minggu' => 'Sunday',
];

// Periksa apakah pendaftaran sedang ditutup (Minggu 18:00–23:59)
if ($current_day === 'sunday' && $current_time >= '18:00' && $current_time <= '23:59') {
    $message = "Pendaftaran sedang ditutup, silakan kembali besok.";
} else {
    // Proses Form Pendaftaran
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keluhan = $_POST['keluhan'] ?? '';
        $jadwal = $_POST['jadwal'] ?? '';

        if (!empty($keluhan) && !empty($jadwal)) {
            // Ambil jadwal dokter untuk validasi
            $stmt_jadwal = $conn->prepare("
                SELECT hari, jam_mulai, jam_selesai 
                FROM jadwal_periksa 
                WHERE id = ? AND active = 1
            ");
            $stmt_jadwal->bind_param('i', $jadwal);
            $stmt_jadwal->execute();
            $stmt_jadwal->bind_result($jadwal_hari, $jadwal_jam_mulai, $jadwal_jam_selesai);
            $stmt_jadwal->fetch();
            $stmt_jadwal->close();

            // Konversi nama hari jadwal ke bahasa Inggris
            $hari_terpilih = strtolower($jadwal_hari);
            if (array_key_exists($hari_terpilih, $hari_map)) {
                $hari_terjemahan = $hari_map[$hari_terpilih];

                // Hitung tanggal jadwal berdasarkan minggu berjalan
                $start_monday = new DateTime("monday this week", new DateTimeZone("Asia/Jakarta"));
                $end_sunday = new DateTime("sunday this week", new DateTimeZone("Asia/Jakarta"));

                // Jika hari ini hari Minggu pukul 18:00–23:59, pindahkan ke minggu berikutnya
                if ($current_day === 'sunday' && $current_time >= '18:00' && $current_time <= '23:59') {
                    $start_monday->modify('+1 week');
                    $end_sunday->modify('+1 week');
                }

                // Tanggal jadwal yang dipilih
                $tanggal_jadwal = new DateTime(" $hari_terjemahan", new DateTimeZone("Asia/Jakarta"));
                if ($tanggal_jadwal < $start_monday) {
                    $tanggal_jadwal->modify('+1 week'); // Pastikan tanggal jadwal ada di minggu berjalan atau berikutnya
                }
                if (
                    $tanggal_jadwal->format('Y-m-d') === $current_date->format('Y-m-d') && 
                    $current_time > $jadwal_jam_selesai
                ) {
                    $message = "Pendaftaran tidak dapat dilakukan karena waktu jadwal sudah selesai untuk hari ini.";
                } elseif ($tanggal_jadwal > $end_sunday) {
                    $message = "Pendaftaran tidak dapat dilakukan karena jadwal sudah melewati batas waktu minggu ini.";
                } else {
                    // Cek apakah pasien sudah terdaftar pada jadwal tersebut
                    $stmt_cek = $conn->prepare("
                        SELECT active 
                        FROM daftar_poli 
                        WHERE id_pasien = ? AND id_jadwal = ? 
                        ORDER BY no_antrian DESC LIMIT 1
                    ");
                    $stmt_cek->bind_param('ii', $user_id, $jadwal);
                    $stmt_cek->execute();
                    $stmt_cek->bind_result($status);
                    $stmt_cek->fetch();
                    $stmt_cek->close();

                    if ($status === 0) {
                        $message = "Anda sudah memiliki pendaftaran yang sedang menunggu pemeriksaan.";
                    } else {
                        // Ambil nomor antrian terakhir untuk jadwal
                        $stmt_antrian = $conn->prepare("
                            SELECT MAX(no_antrian) 
                    FROM daftar_poli 
                    WHERE id_jadwal = ? AND tgl_daftar = ?
                        ");
                        $tgl_daftar = $current_date->format('Y-m-d'); // Tanggal hari ini
                $stmt_antrian->bind_param('is', $jadwal, $tgl_daftar);
                        $stmt_antrian->execute();
                        $stmt_antrian->bind_result($no_antrian_terakhir);
                        $stmt_antrian->fetch();
                        $stmt_antrian->close();

                        $no_antrian = $no_antrian_terakhir ? $no_antrian_terakhir + 1 : 1;

                        // Simpan pendaftaran baru
                        $tanggal_jadwal_formatted = $tanggal_jadwal->format('Y-m-d');
                        $stmt_daftar = $conn->prepare("
                    INSERT INTO daftar_poli (id_pasien, id_jadwal, keluhan, no_antrian, tgl_daftar, active)
                    VALUES (?, ?, ?, ?, ?, 0)
                ");
                $stmt_daftar->bind_param('iisds', $user_id, $jadwal, $keluhan, $no_antrian, $tgl_daftar);
                        $stmt_daftar->execute();
                        $stmt_daftar->close();

                        $message = "Pendaftaran berhasil untuk tanggal {$tanggal_jadwal_formatted}. Nomor antrian Anda: $no_antrian.";
                    }
                }
            } else {
                $message = "Hari pada jadwal tidak valid.";
            }
        } else {
            $message = "Harap lengkapi semua data sebelum mendaftar.";
        }
    }
}

// Ambil daftar jadwal dokter untuk dropdown pada form pendaftaran
$stmt_jadwal = $conn->prepare("
    SELECT jadwal_periksa.id, dokter.nama AS nama_dokter, jadwal_periksa.hari, jadwal_periksa.jam_mulai, jadwal_periksa.jam_selesai 
    FROM jadwal_periksa 
    JOIN dokter ON jadwal_periksa.id_dokter = dokter.id
    WHERE jadwal_periksa.active = 1
");
$stmt_jadwal->execute();
$result_jadwal = $stmt_jadwal->get_result();
$stmt_jadwal->close();

// Ambil daftar pendaftaran yang sedang menunggu pemeriksaan
$stmt_menunggu = $conn->prepare("
    SELECT 
        daftar_poli.no_antrian,
        dokter.nama AS nama_dokter,
        jadwal_periksa.hari,
        jadwal_periksa.jam_mulai,
        jadwal_periksa.jam_selesai,
        daftar_poli.keluhan,
        poli.nama_poli
    FROM daftar_poli
    JOIN jadwal_periksa ON daftar_poli.id_jadwal = jadwal_periksa.id
    JOIN dokter ON jadwal_periksa.id_dokter = dokter.id join poli on dokter.id_poli = poli.id
    WHERE daftar_poli.id_pasien = ? AND daftar_poli.active = 0
    ORDER BY daftar_poli.no_antrian ASC
");
$stmt_menunggu->bind_param('i', $user_id);
$stmt_menunggu->execute();
$result_menunggu = $stmt_menunggu->get_result();
$stmt_menunggu->close();

// Ambil riwayat pemeriksaan pasien
$stmt_riwayat = $conn->prepare("
    SELECT 
        periksa.tgl_periksa,
        dokter.nama AS nama_dokter,
        daftar_poli.keluhan,
        poli.nama_poli,
        periksa.catatan,
        COALESCE(
            (SELECT GROUP_CONCAT(obat.nama_obat SEPARATOR ', ') 
             FROM detail_periksa 
             JOIN obat ON detail_periksa.id_obat = obat.id 
             WHERE detail_periksa.id_periksa = periksa.id),
            'Tidak ada obat'
        ) AS obat
    FROM periksa
    JOIN daftar_poli ON periksa.id_daftar_poli = daftar_poli.id
    JOIN jadwal_periksa ON daftar_poli.id_jadwal = jadwal_periksa.id
    JOIN dokter ON jadwal_periksa.id_dokter = dokter.id JOIN poli on dokter.id_poli = poli.id
    WHERE daftar_poli.id_pasien = ?
    ORDER BY periksa.tgl_periksa DESC
");
$stmt_riwayat->bind_param('i', $user_id);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();
$stmt_riwayat->close();
?>



    <main class="container mx-auto mt-6">
        <!-- Form Pendaftaran -->
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Form Pendaftaran</h2>
            <?php if (!empty($message)): ?>
                <div id="flash-message" class="mt-4 bg-teal-100 border-t-4 border-teal-500 text-teal-900 px-4 py-3 rounded">
                    <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="mt-4">
                 <div class="mb-4">
                    <label for="poli" class="block text-gray-700 font-medium">Pilih Poli</label>
                    <select name="poli" id="poli" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200 focus:outline-none"
                        onchange="this.form.submit()">
                        <option value="" disabled selected>Pilih poli</option>
                        <?php while ($row = $result_poli->fetch_assoc()): ?>
                            <option value="<?= $row['id']; ?>" <?= $selected_poli == $row['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($row['nama_poli']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="jadwal" class="block text-gray-700 font-medium">Pilih Jadwal Dokter</label>
                    <select name="jadwal" id="jadwal" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200 focus:outline-none">
                        <option value="" disabled selected>Pilih jadwal</option>
                        <?php foreach ($jadwal_options as $jadwal): ?>
                            <option value="<?= $jadwal['id']; ?>">
                                <?= htmlspecialchars($jadwal['nama_dokter'] . " - " . $jadwal['hari'] . " (" . $jadwal['jam_mulai'] . " - " . $jadwal['jam_selesai'] . ")"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="keluhan" class="block text-gray-700 font-medium">Keluhan</label>
                    <textarea name="keluhan" id="keluhan" rows="3" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200 focus:outline-none"></textarea>
                </div>
                
                <button type="submit"
                    class="w-full bg-teal-500 text-white py-2 rounded-lg hover:bg-teal-400 transition">Daftar</button>
            </form>
        </section>

        <!-- Daftar Menunggu -->
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
            <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Sedang Menunggu Pemeriksaan</h2>
            <?php if ($result_menunggu->num_rows === 0): ?>
                <div class="mt-4 text-center text-gray-500 dark:text-gray-300">
                    Anda tidak memiliki pendaftaran yang sedang menunggu pemeriksaan.
                </div>
            <?php else: ?>
                <table class="mt-4 w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                        <tr>
                            <th class="px-6 py-3">Nomor Antrian</th>
                            <th class="px-6 py-3">Poli</th>
                            <th class="px-6 py-3">Dokter</th>
                            <th class="px-6 py-3">Jadwal</th>
                            <th class="px-6 py-3">Keluhan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_menunggu->fetch_assoc()): ?>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4"><?= htmlspecialchars($row['no_antrian']); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['nama_poli']); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['nama_dokter']); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars("{$row['hari']} ({$row['jam_mulai']} - {$row['jam_selesai']})"); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['keluhan']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <!-- Riwayat Pemeriksaan -->
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
            <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Riwayat Pemeriksaan</h2>
            <?php if ($result_riwayat->num_rows === 0): ?>
                <div class="mt-4 text-center text-gray-500 dark:text-gray-300">
                    Anda belum memiliki riwayat pemeriksaan.
                </div>
            <?php else: ?>
                <table class="mt-4 w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                        <tr>
                            <th class="px-6 py-3">Tanggal Pemeriksaan</th>
                            <th class="px-6 py-3">Poli</th>
                            <th class="px-6 py-3">Dokter</th>
                            <th class="px-6 py-3">Keluhan</th>
                            <th class="px-6 py-3">Catatan</th>
                            <th class="px-6 py-3">Obat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_riwayat->fetch_assoc()): ?>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4"><?= htmlspecialchars(date('d-m-Y', strtotime($row['tgl_periksa']))); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['nama_poli']); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['nama_dokter']); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['keluhan']); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['catatan']); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['obat']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
    <script src ="../admin/script.js"></script>
</body>

</html>
