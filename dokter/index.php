<?php include 'head.php'; ?>
<?php include 'sideMenu.php';

//jadi gini?>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'inactive'): ?>
        <div class="p-4 mb-4 text-sm text-white bg-red-500 rounded-lg">
            Akun Anda saat ini tidak aktif. Silakan hubungi administrator untuk aktivasi.
        </div>
    <?php endif; ?>

    
    <h1 class="text-3xl font-bold dark:text-white text-center">Selamat Datang</h1>
    <p class="mt-2 text-gray-600 dark:text-gray-100 text-center">Selamat Beraktivitas Kembali</p>

    <script src="../admin/script.js"></script>
</body>
</html>