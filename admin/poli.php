<?php
include 'head.php';
include 'sideMenu.php';

$message = ''; // Untuk menyimpan flash message

// Proses Tambah Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['id'])) {
    $nama_poli = $_POST['name'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';

    if (!empty($nama_poli)) {
        $sql = "INSERT INTO poli (nama_poli, keterangan) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nama_poli, $keterangan);

        if ($stmt->execute()) {
            $message = "Data berhasil ditambahkan.";
        } else {
            $message = "Gagal menambahkan data: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Nama poli tidak boleh kosong.";
    }
}

// Proses Edit Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $nama_poli = $_POST['edit-name'] ?? '';
    $keterangan = $_POST['edit-description'] ?? '';

    if (!empty($nama_poli)) {
        $sql = "UPDATE poli SET nama_poli = ?, keterangan = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nama_poli, $keterangan, $id);

        if ($stmt->execute()) {
            $message = "Data berhasil diperbarui.";
        } else {
            $message = "Gagal memperbarui data: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Nama poli tidak boleh kosong.";
    }
}
?>

<div class="p-4 sm:p-6">
    <?php if (!empty($message)): ?>
        <div id="flash-message" class="p-4 mb-4 text-sm text-white bg-green-500 rounded-lg">
            <?= htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex justify-between items-center p-4">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">DATA POLI</h2>
        <button id="openModalBtn" type="button" 
            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-2.5 inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            <svg class="w-5 h-5 text-white mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/>
            </svg>
            Tambah
        </button>
    </div> 

    <!-- Modal Tambah -->
    <div id="modal-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40">
        <div id="modal" class="fixed inset-0 flex items-center justify-center bg-gray bg-opacity-100 hidden z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
                <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Masukkan Data Poli Baru</h2>
                <form action="#" method="POST">
                    <div class="mb-4">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Poli</label>
                        <input type="text" name="name" id="name" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Masukkan Nama Poli" required>
                    </div>
                    <div class="mb-4">
                        <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keterangan</label>
                        <textarea id="keterangan" name="keterangan" rows="4" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Tambahkan Keterangan Poli"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button id="closeModalBtn" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-700 mr-2">Tutup</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">Tambah Poli</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="edit-modal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
            <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Edit Data Poli</h2>
            <form id="edit-form" method="POST">
                <div class="mb-4">
                    <label for="edit-name" class="block text-sm font-medium text-gray-700 dark:text-white">Nama Poli</label>
                    <input type="text" id="edit-name" name="edit-name" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Edit Nama Poli" required>
                </div>
                <div class="mb-4">
                    <label for="edit-description" class="block text-sm font-medium text-gray-700 dark:text-white">Keterangan</label>
                    <textarea id="edit-description" name="edit-description" rows="4" class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Edit Keterangan"></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="edit-close-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-700">Tutup</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="relative overflow-x-auto shadow-md rounded-md sm:rounded-lg mt-2">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-3">Poli</th>
                    <th scope="col" class="px-6 py-3">Keterangan</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM poli";
                $result = $conn->query($sql);

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-800">
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white poli-name">
                        <?= htmlspecialchars($row['nama_poli']); ?>
                    </td>
                    <td class="px-6 py-4 poli-keterangan">
                        <?= htmlspecialchars($row['keterangan']); ?>
                    </td>
                    <td class="px-6 py-4 flex space-x-2">
                        <button 
                            class="edit-btn text-blue-500 hover:underline" 
                            data-id="<?= htmlspecialchars($row['id']); ?>">
                            Edit
                        </button>
                        <button 
                            class="delete-btn text-red-500 hover:underline" 
                            data-id="<?= htmlspecialchars($row['id']); ?>">
                            Hapus
                        </button>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="3" class="px-4 py-2 text-center text-gray-300 dark:bg-gray-800 dark:border-gray-800">Belum ada data.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>

