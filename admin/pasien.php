<?php
ob_start();
include 'head.php';
include 'sideMenu.php';

$message = ''; // Untuk menyimpan pesan notifikasi

// Proses Tambah Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_GET['id'])) {
    // Proses tambah
    if (!empty($_POST['nik']) && !empty($_POST['name']) && !empty($_POST['alamat']) && !empty($_POST['phone'])) {
        $nik = $_POST['nik'];
        $nama = $_POST['name'];
        $alamat = $_POST['alamat'];
        $no_hp = $_POST['phone'];

        $conn->begin_transaction(); // Mulai transaksi untuk konsistensi
        try {
            // Validasi NIK
            $stmt_check = $conn->prepare("SELECT id FROM pasien WHERE nik = ?");
            $stmt_check->bind_param("s", $nik);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                throw new Exception("Pasien dengan NIK ini sudah terdaftar.");
            }
            $stmt_check->close();

            // Generate nomor rekam medis
            $tahun_bulan = date('Ym');
            $stmt_urut = $conn->prepare("SELECT MAX(CAST(SUBSTRING(no_rm, 8) AS UNSIGNED)) AS urut FROM pasien WHERE no_rm LIKE CONCAT(?, '-%')");
            $stmt_urut->bind_param('s', $tahun_bulan);
            $stmt_urut->execute();
            $stmt_urut->bind_result($urut_terakhir);
            $stmt_urut->fetch();
            $stmt_urut->close();

            $urut = ($urut_terakhir) ? $urut_terakhir + 1 : 1;
            $no_rm = $tahun_bulan . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

            // Tambahkan data
            $stmt_insert = $conn->prepare("INSERT INTO pasien (nik, nama, alamat, no_hp, no_rm, active) VALUES (?, ?, ?, ?, ?, TRUE)");
            $stmt_insert->bind_param("sssss", $nik, $nama, $alamat, $no_hp, $no_rm);
            $stmt_insert->execute();
            $stmt_insert->close();

            $conn->commit();
            $message = "Data berhasil ditambahkan dengan No RM: $no_rm.";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Gagal menambahkan data: " . $e->getMessage();
        }
    }
}

// Proses Edit Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $nik = $_POST['edit-nik'] ?? '';
    $nama = $_POST['edit-name'] ?? '';
    $alamat = $_POST['edit-alamat'] ?? '';
    $no_hp = $_POST['edit-phone'] ?? '';

    try {
        $stmt_check = $conn->prepare("SELECT id FROM pasien WHERE nik = ? AND id != ?");
        $stmt_check->bind_param("si", $nik, $id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            throw new Exception("Pasien dengan NIK ini sudah terdaftar.");
        }
        $stmt_check->close();

        $stmt_update = $conn->prepare("UPDATE pasien SET nik = ?, nama = ?, alamat = ?, no_hp = ? WHERE id = ?");
        $stmt_update->bind_param("ssssi", $nik, $nama, $alamat, $no_hp, $id);
        $stmt_update->execute();
        $stmt_update->close();
        // Output pesan ke halaman dengan JavaScript
        $message = "Data berhasil diperbarui.";
    } catch (Exception $e) {
        $message = "Gagal memperbarui data: " . $e->getMessage();
    }

}


// **Proses Hapus Data**
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "UPDATE pasien SET active = FALSE WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Data berhasil dihapus.";
    } else {
        $message = "Gagal menghapus data: " . $stmt->error;
    }
    $stmt->close();
}

?>

    <div class="p-4 sm:p-6">
        <?php if (!empty($message)): ?>
            <div id="flash-message" class="p-4 mb-4 text-sm text-white bg-green-500 rounded-lg">
                <?= htmlspecialchars($message); 
                ?>
                <script>
                setTimeout(() => { window.location.href = 'pasien.php'; },2000); // Redirect setelah 2 detik
                </script>
            </div>
                
        <?php endif; ?>

        <!-- Header -->
        <div class="flex justify-between items-center p-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">DATA PASIEN</h2>
            <button id="openModalBtn" type="button" 
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2.5 inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                <svg class="w-5 h-5 text-white mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/>
                </svg>
                Tambah
            </button>
        </div> 

        <!-- Modal Tambah -->
        <div id="modal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
                <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Masukkan Data Pasien Baru</h2>
                <form action="#" method="POST">
                    <div class="mb-4">
                        <label for="nik" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">NIK</label>
                        <input 
                            type="text" 
                            name="nik" 
                            id="nik" 
                            class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                            placeholder="Masukkan Nomor Induk Kependudukan" 
                            pattern="[0-9]{16}" 
                            maxlength="16" 
                            required
                            title="NIK harus terdiri dari 16 digit angka">
                    </div>
                    <div class="mb-4">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Pasien</label>
                        <input type="text" name="name" id="name" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Masukkan Nama Pasien" required>
                    </div>
                    <div class="mb-4">
                        <label for="alamat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="4" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Masukkan Alamat"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor Telepon</label>
                        <input type="number" name="phone" id="phone" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="08xx-xxxx-xxxx" required>
                    </div>
                    <div class="flex justify-end">
                        <button id="closeModalBtn" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-700 mr-2">Tutup</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">Tambah Pasien</button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Modal Edit -->
        <div id="edit-modal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
                <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Edit Data Pasien</h2>
                <form id="edit-form" method="POST">
                    <div class="mb-4">
                        <label for="edit-nik" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">NIK</label>
                        <input type="text" id="edit-nik" pattern="[0-9]{16}" maxlength="16" name="edit-nik" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Pasien</label>
                        <input type="text" id="edit-name" name="edit-name" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit-alamat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat</label>
                        <textarea id="edit-alamat" name="edit-alamat" rows="4" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="edit-phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor Telepon</label>
                        <input type="number" id="edit-phone" name="edit-phone" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" id="edit-close-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-700">Tutup</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">Simpan</button>
                    </div>
                </form>
            </div>
        </div>


        <!-- Modal Hapus -->
        <div id="delete-modal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
                <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Konfirmasi Hapus</h2>
                <p class="mb-6 text-gray-700  dark:text-white">Anda yakin ingin menghapus data ini?</p>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="delete-cancel-btn"class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-700 mr-2">Batal</button>
                    <button type="button" id="delete-confirm-btn" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Hapus</button>
                </div>
            </div>
        </div>   

        <!-- Table -->
        <div class="relative overflow-x-auto shadow-md rounded-md sm:rounded-lg mt-2">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-blue-100 dark:bg-gray-700 dark:text-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3">NIK</th>
                        <th scope="col" class="px-6 py-3">Nama Pasien</th>
                        <th scope="col" class="px-6 py-3">Alamat</th>
                        <th scope="col" class="px-6 py-3">Nomor Telepon</th>
                        <th scope="col" class="px-6 py-3">Rekam Medis</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM pasien where active is TRUE";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-800">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white pasien-nik">
                            <?= htmlspecialchars($row['nik']); ?>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white pasien-nama">
                            <?= htmlspecialchars($row['nama']); ?>
                        </td>
                        <td class="px-6 py-4 pasien-alamat">
                            <?= htmlspecialchars($row['alamat']); ?>
                        </td>
                        <td class="px-6 py-4 pasien-handphone">
                            <?= htmlspecialchars($row['no_hp']); ?>
                        </td>    
                        <td class="px-6 py-4 pasien-rekam_medis">
                            <?= htmlspecialchars($row['no_rm']); ?>
                        </td>
                    <td class="px-6 py-4 flex space-x-2">
                        <button 
                            class="edit-btn text-blue-500 hover:underline" 
                            data-id="<?= htmlspecialchars($row['id']); ?>"
                            data-fields='{
                                "nik": "<?= htmlspecialchars($row['nik']); ?>", 
                                "name": "<?= htmlspecialchars($row['nama']); ?>", 
                                "alamat": "<?= htmlspecialchars($row['alamat']); ?>", 
                                "phone": "<?= htmlspecialchars($row['no_hp']); ?>"
                            }'>
                            Edit
                        </button>

                        <button 
                            class="delete-btn text-red-500 hover:underline" 
                            data-url="?id=<?= htmlspecialchars($row['id']);?>">
                            Hapus
                        </button>
                    </td>

                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="8" class="px-4 py-2 text-center text-gray-300 dark:bg-gray-800 dark:border-gray-800">Belum ada data.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="script.js"></script>
    <?php
    ob_end_flush();
    ?>
</body>
</html>

