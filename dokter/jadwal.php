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

// Fungsi untuk mengecek waktu valid untuk aktifkan/nonaktifkan jadwal
function isValidTimeForAction() {
    $now = new DateTime("now", new DateTimeZone("Asia/Jakarta"));
    $dayOfWeek = $now->format("l"); // "Sunday", "Monday", ...
    $currentTime = $now->format("H:i");

    // Waktu valid: Minggu antara 18:00 - 23:59
    return $dayOfWeek === "Sunday" && $currentTime >= "18:00" && $currentTime <= "23:59";
}

// Fungsi untuk mengecek waktu valid untuk menambah/mengubah jadwal
function isValidTimeForSchedule() {
    return isValidTimeForAction(); // Syarat sama seperti aktifkan/nonaktifkan
}

// Fungsi untuk mengecek apakah hari ini adalah hari H jadwal aktif
function isTodayActiveSchedule($conn, $id_dokter) {
    $today = (new DateTime("now", new DateTimeZone("Asia/Jakarta")))->format("l");
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM jadwal_periksa 
        WHERE id_dokter = ? AND active = 1 AND hari = ?
    ");
    $stmt->bind_param("is", $id_dokter, $today);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

// Proses tambah jadwal periksa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    if (!isValidTimeForSchedule()) {
        $message = "Anda hanya dapat menambah/mengubah jadwal pada hari Minggu antara 18.00 dan 23.59.";
    } elseif (isTodayActiveSchedule($conn, $id_dokter)) {
        $message = "Anda tidak dapat mengubah jadwal pada hari H jadwal aktif.";
    } else {
        $hari = $_POST['hari'] ?? '';
        $jam_mulai = $_POST['jam_mulai'] ?? '';
        $jam_selesai = $_POST['jam_selesai'] ?? '';

        try {
            $conn->begin_transaction();

            // Validasi: Pastikan jadwal tidak bertabrakan dengan jadwal lain
            $stmt_check = $conn->prepare("
                SELECT COUNT(*) AS jumlah FROM jadwal_periksa 
                WHERE id_dokter = ? AND hari = ? 
                AND (
                    (jam_mulai < ? AND jam_selesai > ?) OR
                    (jam_mulai < ? AND jam_selesai > ?) OR
                    (jam_mulai >= ? AND jam_selesai <= ?)
                )
            ");
            $stmt_check->bind_param(
                "isssssss",
                $id_dokter, $hari,
                $jam_selesai, $jam_mulai,
                $jam_mulai, $jam_selesai,
                $jam_mulai, $jam_selesai
            );
            $stmt_check->execute();
            $stmt_check->bind_result($jumlah);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($jumlah > 0) {
                throw new Exception("Jadwal periksa bertabrakan dengan jadwal yang sudah ada.");
            }

            // Tambahkan jadwal periksa baru
            $stmt_insert = $conn->prepare("
                INSERT INTO jadwal_periksa (id_dokter, hari, jam_mulai, jam_selesai, active) 
                VALUES (?, ?, ?, ?, 0)
            ");
            $stmt_insert->bind_param("isss", $id_dokter, $hari, $jam_mulai, $jam_selesai);
            $stmt_insert->execute();
            $stmt_insert->close();

            $conn->commit();
            $message = "Jadwal periksa berhasil ditambahkan.";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Gagal menambahkan jadwal: " . $e->getMessage();
        }
    }
}

// Proses aktifkan jadwal
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['activate'])) {
    if (!isValidTimeForAction()) {
        $message = "Anda hanya dapat mengaktifkan jadwal pada hari Minggu antara 18.00 dan 23.59.";
    } elseif (isTodayActiveSchedule($conn, $id_dokter)) {
        $message = "Anda tidak dapat mengaktifkan jadwal pada hari H jadwal aktif.";
    } else {
        $id_jadwal = intval($_GET['activate']);

        try {
            $conn->begin_transaction();

            // Nonaktifkan semua jadwal lain
            $stmt_deactivate = $conn->prepare("UPDATE jadwal_periksa SET active = 0 WHERE id_dokter = ?");
            $stmt_deactivate->bind_param("i", $id_dokter);
            $stmt_deactivate->execute();
            $stmt_deactivate->close();

            // Aktifkan jadwal yang dipilih
            $stmt_activate = $conn->prepare("UPDATE jadwal_periksa SET active = 1 WHERE id = ? AND id_dokter = ?");
            $stmt_activate->bind_param("ii", $id_jadwal, $id_dokter);
            $stmt_activate->execute();
            $stmt_activate->close();

            $conn->commit();
            $message = "Jadwal berhasil diaktifkan.";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Gagal mengaktifkan jadwal: " . $e->getMessage();
        }
    }
}

// Proses nonaktifkan jadwal
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['deactivate'])) {
    if (!isValidTimeForAction()) {
        $message = "Anda hanya dapat menonaktifkan jadwal pada hari Minggu antara 18.00 dan 23.59.";
    } elseif (isTodayActiveSchedule($conn, $id_dokter)) {
        $message = "Anda tidak dapat menonaktifkan jadwal pada hari H jadwal aktif.";
    } else {
        $id_jadwal = intval($_GET['deactivate']);

        try {
            $conn->begin_transaction();

            // Nonaktifkan jadwal yang dipilih
            $stmt_deactivate = $conn->prepare("UPDATE jadwal_periksa SET active = 0 WHERE id = ? AND id_dokter = ?");
            $stmt_deactivate->bind_param("ii", $id_jadwal, $id_dokter);
            $stmt_deactivate->execute();
            $stmt_deactivate->close();

            $conn->commit();
            $message = "Jadwal berhasil dinonaktifkan.";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Gagal menonaktifkan jadwal: " . $e->getMessage();
        }
    }
}

// Ambil semua jadwal dokter untuk ditampilkan
$stmt_jadwal = $conn->prepare("
    SELECT id, hari, jam_mulai, jam_selesai, active 
    FROM jadwal_periksa 
    WHERE id_dokter = ? 
    ORDER BY active DESC, hari, jam_mulai
");
$stmt_jadwal->bind_param("i", $id_dokter);
$stmt_jadwal->execute();
$result_jadwal = $stmt_jadwal->get_result();
$stmt_jadwal->close();
?>


<!-- Tampilan HTML -->
<h1 class="text-3xl font-bold dark:text-white text-center">Jadwal Periksa Dokter</h1>
<p class="mt-2 text-gray-600 dark:text-gray-100 text-center">Kelola waktu periksa Anda di sini</p>

<div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
    <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Jadwal Periksa Anda</h2>
    <?php if (!empty($message)): ?>
        <div id="flash-message" class="mt-4 p-4 <?= strpos($message, 'berhasil') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> border rounded">
            <?= $message; ?>
        </div>
    <?php endif; ?>

    <!-- Tabel Jadwal -->
    <table class="mt-4 w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
            <tr>
                <th scope="col" class="px-6 py-3">Hari</th>
                <th scope="col" class="px-6 py-3">Jam Mulai</th>
                <th scope="col" class="px-6 py-3">Jam Selesai</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_jadwal->fetch_assoc()): ?>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="px-6 py-4"><?= htmlspecialchars($row['hari']); ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row['jam_mulai']); ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row['jam_selesai']); ?></td>
                    <td class="px-6 py-4">
                        <?= $row['active'] ? '<span class="text-green-500 font-bold">Aktif</span>' : '<span class="text-gray-500">Nonaktif</span>'; ?>
                    </td>
                    <td class="px-6 py-4">
                        <?php if (!$row['active']): ?>
                            <a href="?activate=<?= $row['id']; ?>" class="text-blue-500 hover:underline">Aktifkan</a>
                        <?php else: ?>
                            <a href="?deactivate=<?= $row['id']; ?>" class="text-gray-500 hover:underline">Nonaktifkan</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Tombol Tambah Jadwal -->
    <button id="openModalBtn" 
        class="mt-6 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
        Tambah Jadwal
    </button>

    <!-- Modal Tambah Jadwal -->
    <div id="addScheduleModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
            <h3 class="text-xl font-bold text-gray-700 dark:text-gray-100 mb-4">Tambah Jadwal Baru</h3>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="add_schedule" value="1">
                <!-- Hari -->
                <div>
                    <label for="hari" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Hari</label>
                    <select id="hari" name="hari" required 
                        class="w-full px-4 py-2 mt-1 border rounded-lg focus:ring focus:ring-indigo-200 focus:outline-none">
                        <option value="">Pilih Hari</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                </div>
                <!-- Jam Mulai -->
                <div>
                    <label for="jam_mulai" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Jam Mulai</label>
                    <input type="time" id="jam_mulai" name="jam_mulai" required 
                        class="w-full px-4 py-2 mt-1 border rounded-lg focus:ring focus:ring-indigo-200 focus:outline-none">
                </div>
                <!-- Jam Selesai -->
                <div>
                    <label for="jam_selesai" class="block text-sm font-medium text-gray-600 dark:text-gray-300">Jam Selesai</label>
                    <input type="time" id="jam_selesai" name="jam_selesai" required 
                        class="w-full px-4 py-2 mt-1 border rounded-lg focus:ring focus:ring-indigo-200 focus:outline-none">
                </div>
                <!-- Tombol Submit -->
                <div class="flex justify-end space-x-2">
                    <button type="button" id="closeModalBtn" class="bg-gray-500 text-white py
                    px-4 rounded-lg hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" 
                        class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                        Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal Tambah Jadwal
const openModalBtn = document.getElementById('openModalBtn');
const closeModalBtn = document.getElementById('closeModalBtn');
const addScheduleModal = document.getElementById('addScheduleModal');

// Buka Modal
openModalBtn.addEventListener('click', () => {
    addScheduleModal.classList.remove('hidden');
});

// Tutup Modal
closeModalBtn.addEventListener('click', () => {
    addScheduleModal.classList.add('hidden');
});

    // Fungsi untuk memperbaiki menit pada waktu yang dipilih
    function adjustMinutes(input) {
        const timeValue = input.value; // Format: "HH:MM"
        if (timeValue) {
            const [hours, minutes] = timeValue.split(':');
            let newMinutes = "00"; // Default menit
            if (minutes >= 15 && minutes < 45) {
                newMinutes = "30";
            }
            const adjustedTime = `${hours}:${newMinutes}`;
            input.value = adjustedTime; // Atur kembali nilainya
        }
    }

    // Menambahkan event listener ke semua input time
    document.querySelectorAll('input[type="time"]').forEach(input => {
        input.addEventListener('blur', function () {
            adjustMinutes(this); // Atur menit saat input kehilangan fokus
        });

        input.addEventListener('change', function () {
            adjustMinutes(this); // Atur menit saat nilai input berubah
        });
    });

</script>

<script src="../admin/script.js"></script>
</body>
</html>
