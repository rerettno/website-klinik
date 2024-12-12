<?php include 'head.php'; ?>
<?php include 'sideMenu.php'; 

$message = ''; // Untuk menyimpan flash message

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'], $_GET['type'])) {
    $id = intval($_GET['id']);
    $type = $_GET['type'];

    if ($type === 'restore-poli') {
        $sql = "UPDATE poli SET active = TRUE WHERE id = ?";
    } elseif ($type === 'restore-dokter') {
        $sql = "UPDATE dokter SET active = TRUE WHERE id = ?";
    } elseif ($type === 'restore-obat') {
        $sql = "UPDATE obat SET active = TRUE WHERE id =?";
    }else {
        $sql = null;
    }

    if ($sql) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if ($type === 'restore-poli') {
                $message = "Data Poli berhasil dipulihkan.";
            } elseif ($type === 'restore-dokter') {
                $message = "Data Dokter berhasil dipulihkan.";
            } else {
                $message = "Data Obat berhasil dipulihkan.";
            }
        } else {
            $message = "Gagal memproses data: " . $stmt->error;
        }


        $stmt->close();
    } else {
        $message = "Aksi tidak valid.";
    }
}

?>

    <!-- Main Content -->
    <div class="p-4 sm:p-6">
         <?php if (!empty($message)): ?>
            <div id="flash-message" class="p-4 mb-4 text-sm text-white bg-green-500 rounded-lg">
                <?= htmlspecialchars($message); ?>
                
                <script>
                setTimeout(() => { window.location.href = 'sampah.php'; },2000); // Redirect setelah 2 detik
                </script>
            </div>
        <?php endif; ?>
        <!-- Table -->
        <div class="relative overflow-x-auto shadow-md rounded-md sm:rounded-lg mt-2">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-blue-100 dark:bg-gray-700 dark:text-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3">Poli</th>
                        <th scope="col" class="px-6 py-3">Keterangan</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM poli where active is false";
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
                        <td class="px-6 py-4 status">
                            <span class="text-red-500 font-semibold">Tidak Aktif</span>
                        </td>
                        <td class="px-6 py-4 flex space-x-2">
                            <button 
                                class="delete-btn text-red-500 hover:underline" 
                                data-url="?id=<?= htmlspecialchars($row['id']); ?>&type=restore-poli">
                                Pulihkan
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
                            WHERE dokter.active = FALSE";
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
                            <span class="text-red-500 font-semibold">Tidak Aktif</span>
                        </td>
                        <td class="px-6 py-4 flex space-x-2">
                            <button 
                                class="delete-btn text-red-500 hover:underline" 
                                data-url="?id=<?= htmlspecialchars($row['id']); ?>&type=restore-dokter">
                                Pulihkan
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
                    $sql = "SELECT * FROM obat where active = False";
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
                            <span class="text-red-500 font-semibold">Tidak Tersedia</span>
                        </td>
                        <td class="px-6 py-4 flex space-x-2">
                            <button 
                                class="delete-btn text-red-500 hover:underline" 
                                data-url="?id=<?= htmlspecialchars($row['id']); ?>&type=restore-obat">
                                Pulihkan
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

        <!-- Modal Hapus -->
        <div id="delete-modal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg">
                <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Pulihkan Data</h2>
                <p class="mb-6 text-gray-700  dark:text-white">Anda yakin ingin mempulihkan data ini?</p>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="delete-cancel-btn"class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-700 mr-2">Batal</button>
                    <button type="button" id="delete-confirm-btn" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Pulihkan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
