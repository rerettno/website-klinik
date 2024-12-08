<!-- Overlay -->
<div id="drawer-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-30"></div>

<!-- Drawer -->
<div id="drawer-navigation"
    class="fixed top-0 left-0 z-40 w-72 h-screen p-6 mt-2 overflow-y-auto bg-white dark:bg-gray-800 transition-transform -translate-x-full"
    tabindex="-1" aria-labelledby="drawer-navigation-label" aria-hidden="true">
    <h2 id="drawer-navigation-label" class="sr-only">Navigation Drawer</h2>
    <ul class="space-y-4 font-medium">
        <!-- User Info -->
        <li class="mb-4">
            <h1 class="text-xl font-bold text-gray-700 dark:text-white">Selamat datang, <?= htmlspecialchars($username) ?>!</h1>
            <p class="text-sm text-gray-500 dark:text-white">Status: <span class="font-bold text-green-600"><?= htmlspecialchars($status) ?></span></p>
        </li>
        <!-- Menu Items -->
        <li>
            <a href="index.php"
                class="block px-4 py-2 text-gray-900 rounded-lg hover:bg-blue-100 dark:text-white dark:hover:bg-gray-700">
                Beranda
            </a>
        </li>
        <li>
            <a href="poli.php"
                class="block px-4 py-2 text-gray-900 rounded-lg hover:bg-blue-100 dark:text-white dark:hover:bg-gray-700">
                Data Poli
            </a>
        </li>
        <li>
            <a href="dokter.php"
                class="block px-4 py-2 text-gray-900 rounded-lg hover:bg-blue-100 dark:text-white dark:hover:bg-gray-700">
                Data Dokter
            </a>
        </li>
        <li>
            <a href="pasien.php"
                class="block px-4 py-2 text-gray-900 rounded-lg hover:bg-blue-100 dark:text-white dark:hover:bg-gray-700">
                Data Pasien
            </a>
        </li>
        <li>
            <a href="obat.php"
                class="block px-4 py-2 text-gray-900 rounded-lg hover:bg-blue-100 dark:text-white dark:hover:bg-gray-700">
                
                Data Obat
            </a>
        </li>
    </ul>
    <!-- Logout -->
    <ul class="mt-auto space-y-2 font-medium">
        <li>
            <a href="../index.php?logout=true"
                class="block px-4 py-2 text-blue-900 rounded-lg bg-blue-100 hover:bg-blue-200 dark:text-white dark:bg-gray-700 dark:hover:bg-gray-800 flex flex-row">
                <svg class="w-6 h-6 text-blue-900 dark:text-white mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M18 18V6h-5v12h5Zm0 0h2M4 18h2.5m3.5-5.5V12M6 6l7-2v16l-7-2V6Z"/>
</svg>

               Keluar
            </a>
        </li>
    </ul>
</div>
