<?php
include 'head.php';
include 'sideMenu.php';

$message = ''; // Untuk menyimpan flash message

// Proses Tambah Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $nama_dokter = $_POST['dokter-name'] ?? '';
    $nip = $_POST['nip'] ?? '';
    $id_poli = $_POST['penempatan'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $no_hp = $_POST['phone'] ?? '';

    if (!empty($nama_dokter) && !empty($id_poli)) {
        $sql = "INSERT INTO dokter (nama,nip, id_poli, alamat, no_hp) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nama_dokter, $nip, $id_poli, $alamat, $no_hp);

        if ($stmt->execute()) {
            $message = "Data dokter berhasil ditambahkan.";
        } else {
            $message = "Gagal menambahkan data: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Proses Edit Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $nip = $_POST['edit-nip'];
    $name = $_POST['edit-name'];
    $penempatan = $_POST['edit-penempatan'];
    $alamat = $_POST['edit-alamat'];
    $phone = $_POST['edit-phone'];

    $sql = "UPDATE dokter SET nip = ?, nama = ?, id_poli = ?, alamat = ?, no_hp = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nip, $name, $penempatan, $alamat, $phone, $id);

    if ($stmt->execute()) {
        $message = "Data berhasil diperbarui.";
    } else {
        $message = "Gagal memperbarui data: " . $stmt->error;
    }

    $stmt->close();
}


// Proses Hapus Data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "UPDATE dokter SET active = FALSE WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Data dokter berhasil dihapus.";
    } else {
        $message = "Gagal menghapus data: " . $stmt->error;
    }

    $stmt->close();
}
?>

    <div class="p-4 sm:p-6">
        <?php if (!empty($message)): ?>
            <div id="flash-message" class="p-4 mb-4 text-sm text-white bg-green-500 rounded-lg">
                <?= htmlspecialchars($message); ?>
                <script>
                setTimeout(() => { window.location.href = 'dokter.php'; },2000); // Redirect setelah 2 detik
                </script>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex justify-between items-center p-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">DATA DOKTER</h2>
            <button id="openModalBtn" type="button" 
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2.5 inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                <svg class="w-5 h-5 text-white mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/>
                </svg>
                Tambah Dokter
            </button>
        </div>

        <!-- Modal Tambah -->
        <div id="modal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
                <h2  class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Tambah Data Dokter</h2>
                <form  action="#" method="POST">                    
                    <div class="mb-4">
                        <label for="nip" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">NIP</label>
                        <input type="text" name="nip" id="nip" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="contoh: 1234567890" required>
                    </div>
                    <div class="mb-4">
                        <label for="dokter-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Dokter</label>
                        <input type="text" name="dokter-name" id="dokter-name" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="contoh: dr. Nama" required>
                    </div>
                    <div class="mb-4">
                        <label for="penempatan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Penempatan Poli</label>
                        <select id="penempatan" name="penempatan" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            <option value="">Pilih Poli</option>
                            <?php
                            $poliQuery = "SELECT id, nama_poli FROM poli WHERE active = TRUE";
                            $poliResult = $conn->query($poliQuery);
                            while ($poliRow = $poliResult->fetch_assoc()):
                            ?>
                            <option value="<?= htmlspecialchars($poliRow['id']); ?>"><?= htmlspecialchars($poliRow['nama_poli']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="alamat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="4" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Tambahkan Alamat"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor Telepon</label>
                        <input type="number" name="phone" id="phone" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="081234567890"  required>
                    </div>
                    <div class="flex justify-end">
                        <button id="closeModalBtn" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-700 mr-2">Tutup</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit -->
        <div id="edit-modal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
                <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Edit Data Dokter</h2>
                <form id="edit-form" method="POST">
                    <!-- NIP -->
                    <div class="mb-4">
                        <label for="edit-nip" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">NIP</label>
                        <input type="text" id="edit-nip" name="edit-nip" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <!-- Nama Dokter -->
                    <div class="mb-4">
                        <label for="edit-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Dokter</label>
                        <input type="text" id="edit-name" name="edit-name" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <!-- Penempatan Poli -->
                    <div class="mb-4">
                        <label for="edit-penempatan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Penempatan Poli</label>
                        <select id="edit-penempatan" name="edit-penempatan" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            <option value="">Pilih Poli</option>
                            <?php
                            $poliQuery = "SELECT id, nama_poli FROM poli WHERE active = TRUE";
                            $poliResult = $conn->query($poliQuery);
                            while ($poliRow = $poliResult->fetch_assoc()):
                            ?>
                            <option 
                                value="<?= htmlspecialchars($poliRow['id']); ?>" 
                                <?= isset($fields['penempatan']) && $poliRow['id'] == $fields['penempatan'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($poliRow['nama_poli']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <!-- Alamat -->
                    <div class="mb-4">
                        <label for="edit-alamat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat</label>
                        <input type="text" id="edit-alamat" name="edit-alamat" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <!-- Telepon -->
                    <div class="mb-4">
                        <label for="edit-phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Telepon</label>
                        <input type="number" id="edit-phone" name="edit-phone" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <!-- Actions -->
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
                <p class="mb-6 text-gray-700  dark:text-white">Anda yakin ingin menghapus data dokter ini?</p>
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
                        <th scope="col" class="px-6 py-3">NIP</th>
                        <th scope="col" class="px-6 py-3">Nama Dokter</th>
                        <th scope="col" class="px-6 py-3">Poli</th>
                        <th scope="col" class="px-6 py-3">Alamat</th>
                        <th scope="col" class="px-6 py-3">No. Telepon</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT dokter.*, poli.nama_poli FROM dokter 
                            JOIN poli ON dokter.id_poli = poli.id 
                            WHERE dokter.active = TRUE";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-800">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white dokter-nip">
                            <?= htmlspecialchars($row['nip']); ?>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white dokter-name">
                            <?= htmlspecialchars($row['nama']); ?>
                        </td>
                        <td class="px-6 py-4 id-poli">
                            <?= htmlspecialchars($row['nama_poli']); ?>
                        </td>
                        <td class="px-6 py-4 dokter-alamat">
                            <?= htmlspecialchars($row['alamat']); ?>
                        </td>
                        <td class="px-6 py-4 dokter-handphone">
                            <?= htmlspecialchars($row['no_hp']); ?>
                        </td>
                        <td class="px-6 py-4 status">
                            <span class="text-green-500 font-semibold">Aktif</span>
                        </td>
                        <td class="px-6 py-4 flex space-x-2">
                            <button 
                                class="edit-btn text-blue-500 hover:underline" 
                                data-id="<?= htmlspecialchars($row['id']); ?>"
                                data-fields='<?= htmlspecialchars(json_encode([
                                    "nip" => $row['nip'],
                                    "name" => $row['nama'],
                                    "penempatan" => $row['nama_poli'],
                                    "alamat" => $row['alamat'],
                                    "phone" => $row['no_hp']
                                ])); ?>'>
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
                        <td colspan="6" class="px-4 py-2 text-center text-gray-300 dark:bg-gray-800 dark:border-gray-800">Belum ada data.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="script.js"></script>
    </body>
</html>
