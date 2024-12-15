<?php
include 'head.php';

// Pastikan pasien sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$message = "";

// Proses Form Pendaftaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keluhan = $_POST['keluhan'] ?? '';
    $jadwal = $_POST['jadwal'] ?? '';

    if (!empty($keluhan) && !empty($jadwal)) {
        // Cek jika pasien sudah terdaftar pada jadwal tersebut dan belum diperiksa
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
                WHERE id_jadwal = ?
            ");
            $stmt_antrian->bind_param('i', $jadwal);
            $stmt_antrian->execute();
            $stmt_antrian->bind_result($no_antrian_terakhir);
            $stmt_antrian->fetch();
            $stmt_antrian->close();

            $no_antrian = $no_antrian_terakhir ? $no_antrian_terakhir + 1 : 1;

            // Tambahkan pendaftaran baru
            $stmt_daftar = $conn->prepare("
                INSERT INTO daftar_poli (id_pasien, id_jadwal, keluhan, no_antrian, active)
                VALUES (?, ?, ?, ?, 0)
            ");
            $stmt_daftar->bind_param('iisi', $user_id, $jadwal, $keluhan, $no_antrian);
            $stmt_daftar->execute();
            $stmt_daftar->close();

            $message = "Pendaftaran berhasil. Nomor antrian Anda: $no_antrian.";
        }
    } else {
        $message = "Harap lengkapi semua data sebelum mendaftar.";
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
        daftar_poli.keluhan
    FROM daftar_poli
    JOIN jadwal_periksa ON daftar_poli.id_jadwal = jadwal_periksa.id
    JOIN dokter ON jadwal_periksa.id_dokter = dokter.id
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
    JOIN dokter ON jadwal_periksa.id_dokter = dokter.id
    WHERE daftar_poli.id_pasien = ?
    ORDER BY periksa.tgl_periksa DESC
");
$stmt_riwayat->bind_param('i', $user_id);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();
$stmt_riwayat->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Dashboard Pasien</title>
</head>

<body class="bg-gray-50">
    <header class="bg-teal-300 px-6 py-4">
        <h1 class="text-2xl font-bold text-white text-center">Dashboard Pasien</h1>
    </header>

    <main class="container mx-auto mt-6">
        <!-- Form Pendaftaran -->
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Form Pendaftaran</h2>
            <?php if (!empty($message)): ?>
                <div class="mt-4 bg-teal-100 border-t-4 border-teal-500 text-teal-900 px-4 py-3 rounded">
                    <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="mt-4">
                <div class="mb-4">
                    <label for="keluhan" class="block text-gray-700 font-medium">Keluhan</label>
                    <textarea name="keluhan" id="keluhan" rows="3" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200 focus:outline-none"></textarea>
                </div>
                <div class="mb-4">
                    <label for="jadwal" class="block text-gray-700 font-medium">Pilih Jadwal Dokter</label>
                    <select name="jadwal" id="jadwal" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-200 focus:outline-none">
                        <option value="" disabled selected>Pilih jadwal</option>
                        <?php while ($row = $result_jadwal->fetch_assoc()): ?>
                            <option value="<?= $row['id']; ?>">
                                <?= htmlspecialchars($row['nama_dokter'] . " - " . $row['hari'] . " (" . $row['jam_mulai'] . " - " . $row['jam_selesai'] . ")"); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
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
                            <th class="px-6 py-3">Dokter</th>
                            <th class="px-6 py-3">Jadwal</th>
                            <th class="px-6 py-3">Keluhan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_menunggu->fetch_assoc()): ?>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4"><?= htmlspecialchars($row['no_antrian']); ?></td>
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
</body>

</html>
