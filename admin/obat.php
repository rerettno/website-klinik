<?php
include 'head.php';
include 'sideMenu.php';

$message = ''; // Untuk menyimpan flash message

// Proses Tambah Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $nama_obat = $_POST['obat-name'] ?? '';
    $kemasan = $_POST['kemasan'] ?? '';
    $harga = $_POST['harga'] ?? '';

    if (!empty($nama_obat) ) {
        $sql = "INSERT INTO obat (nama_obat, kemasan, harga) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nama_obat, $kemasan, $harga);

        if ($stmt->execute()) {
            $message = "Data obat berhasil ditambahkan.";
        } else {
            $message = "Gagal menambahkan data: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Proses Edit Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $nama_obat = $_POST['edit-name'];
    $kemasan = $_POST['edit-kemasan'];
    $harga = $_POST['edit-harga'];

    $sql = "UPDATE obat SET nama_obat = ?, kemasan = ?, harga = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nama_obat, $kemasan, $harga, $id);

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
    $sql = "UPDATE obat SET active = FALSE WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Data obat berhasil dihapus.";
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
                setTimeout(() => { window.location.href = 'obat.php'; },2000); // Redirect setelah 2 detik
                </script>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex justify-between items-center p-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">DATA OBAT</h2>
            <button id="openModalBtn" type="button" 
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2.5 inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                <svg class="w-5 h-5 text-white mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/>
                </svg>
                Tambah obat
            </button>
        </div>

        <!-- Modal Tambah -->
        <div id="modal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
                <h2  class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Tambah Data Obat</h2>
                <form  action="#" method="POST"> 
                    <div class="mb-4">
                        <label for="obat-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Obat</label>
                        <input type="text" name="obat-name" id="obat-name" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="contoh: antangin" required>
                    </div>
                    <div class="mb-4">
                        <label for="kemasan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jenis Kemasan</label>
                        <select id="kemasan" name="kemasan" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            <option value="">Pilih Jenis Kemasan</option>
                            <option value="botol">Botol Kaca</option>
                            <option value="tablet">Tablet</option>
                            <option value="serbuk">Serbuk</option>
                            <option value="kapsul">kapsul</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="harga" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Harga (Rp)</label>
                        <input type="number" name="harga" id="harga" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="masukkan harga satuan obat"  required>
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
                <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Edit Data Obat</h2>
                <form id="edit-form" method="POST">
                    <div class="mb-4">
                        <label for="edit-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Obat</label>
                        <input type="text" id="edit-name" name="edit-name" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label for="edit-kemasan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jenis Kemasan</label>
                        <select id="edit-kemasan" name="edit-kemasan" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            <option value="">Pilih Jenis Kemasan</option>
                            <option value="">Pilih Jenis Kemasan</option>
                            <option value="botol">Botol Kaca</option>
                            <option value="tablet">Tablet</option>
                            <option value="serbuk">Serbuk</option>
                            <option value="kapsul">kapsul</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit-harga" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Harga</label>
                        <input type="number" id="edit-harga" name="edit-harga" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
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
                <p class="mb-6 text-gray-700  dark:text-white">Anda yakin ingin menghapus data Obat ini?</p>
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
                        <th scope="col" class="px-6 py-3">Nama Obat</th>
                        <th scope="col" class="px-6 py-3">Jenis Kemasan</th>
                        <th scope="col" class="px-6 py-3">Harga</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM obat where active = TRUE";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-800">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white obat-name">
                            <?= htmlspecialchars($row['nama_obat']); ?>
                        </td>
                        <td class="px-6 py-4 kemasan">
                            <?= htmlspecialchars($row['kemasan']); ?>
                        </td>
                        <td class="px-6 py-4 obat-harga">
                            <?= htmlspecialchars($row['harga']); ?>
                        </td>                        
                        <td class="px-6 py-4 status">
                            <span class="text-green-500 font-semibold">Tersedia</span>
                        </td>
                        <td class="px-6 py-4 flex space-x-2">
                            <button 
                                class="edit-btn text-blue-500 hover:underline" 
                                data-id="<?= htmlspecialchars($row['id']); ?>"
                                data-fields='<?= htmlspecialchars(json_encode([
                                    'name' => htmlspecialchars($row['nama_obat']),
                                    'kemasan' => htmlspecialchars($row['kemasan']),
                                    'harga' => htmlspecialchars($row['harga'])
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
                        <td colspan="5" class="px-4 py-2 text-center text-gray-300 dark:bg-gray-800 dark:border-gray-800">Belum ada data.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="script.js"></script>
    </body>
</html>
