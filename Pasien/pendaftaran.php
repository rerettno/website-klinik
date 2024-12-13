<!-- Sebagai seorang Pasien, saya ingin mendaftar ke poli sesuai
 keluhan / gejala yang saya rasakan. Saat mendaftar poli saya
 ingin memilih dokter dan melihat jadwal prakteknya. Setelah
 saya mendaftar poli, saya ingin mendapatkan nomer antrian. 
Sehingga saya dapat mengatur waktu saya untuk berangkat
 ke poli 
 
 tambahan
 1. kalau pasien blm login maka halaman iniakan diarahan ke hal login
 2. kalau sudah daftar g bisa dihapus atau dibatalkan
 2. kaau sudah selesai nanti statusnya jadi selesai. dmn nanti ada rincian obat dan harga semua pemeriksaannamun jika hari h pasien tidak datang maka tulisnannyagagal. disini doketer
 yang mengatur satus
 3. pokoknya kalau hariudah lewat, mau berhasil atau gagal akan masuk ke riwaayt. kalau sedang diperiksa masuk ketabel onprogress-->

<?php
include 'head.php';

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit(); // Hentikan eksekusi skrip setelah redirect
}


$user_id = $_SESSION['user_id']; // ID pasien untuk foreign key

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keluhan = $_POST['keluhan'] ?? '';
    $jadwal = $_POST['jadwal'] ?? '';

    if (!empty($keluhan) && !empty($jadwal)) {
        // Cek apakah pasien sudah terdaftar untuk jadwal tertentu
        $query_cek = "
            SELECT id 
            FROM daftar_poli 
            WHERE id_pasien = ? AND id_jadwal = ?
        ";
        $stmt_cek = $conn->prepare($query_cek);
        $stmt_cek->bind_param('ii', $user_id, $jadwal);
        $stmt_cek->execute();
        $stmt_cek->store_result();

        if ($stmt_cek->num_rows > 0) {
            $message = "Anda sudah terdaftar untuk jadwal ini.";
            $stmt_cek->close();
        } else {
            $stmt_cek->close();

            // Cari Nomor Antrian
            $query_antrian = "
                SELECT MAX(no_antrian) AS antrian_terakhir 
                FROM daftar_poli 
                WHERE id_jadwal = ?
            ";
            $stmt_antrian = $conn->prepare($query_antrian);
            $stmt_antrian->bind_param('i', $jadwal);
            $stmt_antrian->execute();
            $stmt_antrian->bind_result($antrian_terakhir);
            $stmt_antrian->fetch();
            $stmt_antrian->close();

            $no_antrian = $antrian_terakhir ? $antrian_terakhir + 1 : 1;

            // Simpan Data Pendaftaran
            $query_insert = "
                INSERT INTO daftar_poli (id_pasien, id_jadwal, keluhan, no_antrian) 
                VALUES (?, ?, ?, ?)
            ";
            $stmt_insert = $conn->prepare($query_insert);
            $stmt_insert->bind_param('iisi', $user_id, $jadwal, $keluhan, $no_antrian);
            $stmt_insert->execute();
            $stmt_insert->close();

            $message = "Pendaftaran berhasil. Nomor antrian Anda: $no_antrian.";
        }
    } else {
        $message = "Silakan lengkapi data pendaftaran.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Pendaftaran Poli</title>
</head>

<body class="bg-gray-50">
    <header class="bg-teal-300 px-6 py-4">
        <h1 class="text-2xl font-bold text-white text-center">Pendaftaran Poli</h1>
    </header>

    <main class="container mx-auto mt-6">
        <?php if (isset($message)): ?>
            <div class="bg-teal-100 border-t-4 border-teal-500 text-teal-900 px-4 py-3 rounded mb-4">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="bg-white p-6 shadow-md rounded-lg">
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
                    <?php
                    // Ambil jadwal dokter aktif
                    $query_jadwal = "SELECT id, id_dokter, hari, jam_mulai, jam_selesai FROM jadwal_periksa WHERE active = 1";
                    $result_jadwal = $conn->query($query_jadwal);
                    while ($row = $result_jadwal->fetch_assoc()) {
                        $dokter_id = $row['id_dokter'];
                        $dokter_query = $conn->prepare("SELECT nama FROM dokter WHERE id = ?");
                        $dokter_query->bind_param('i', $dokter_id);
                        $dokter_query->execute();
                        $dokter_query->bind_result($dokter_nama);
                        $dokter_query->fetch();
                        $dokter_query->close();

                        echo "<option value='{$row['id']}'>Dr. $dokter_nama - {$row['hari']} ({$row['jam_mulai']} - {$row['jam_selesai']})</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit"
                class="w-full bg-teal-500 text-white py-2 rounded-lg hover:bg-teal-400 transition">Daftar</button>
        </form>
    </main>
</body>

</html>
