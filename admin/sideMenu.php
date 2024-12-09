<!-- Overlay -->
<div id="drawer-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-30"></div>

<!-- Drawer -->
<div id="drawer-navigation"
    class="fixed top-0 left-0 z-40 w-72 h-screen p-6 mt-2 overflow-y-auto bg-white dark:bg-gray-800 transition-transform -translate-x-full"
    tabindex="-1" aria-labelledby="drawer-navigation-label" aria-hidden="true">
    <h2 id="drawer-navigation-label" class="sr-only">Navigation Drawer</h2>
    <ul class="space-y-2 font-medium">
        <!-- User Info -->
        <li class="mb-4">
            <h1 class="text-xl font-bold text-gray-700 dark:text-white">Selamat datang, <?= htmlspecialchars($username) ?>!</h1>
            <p class="text-sm text-gray-500 dark:text-white">Status: <span class="font-bold text-green-600"><?= htmlspecialchars($status) ?></span></p>
        </li>
        <!-- Menu Items -->
        <li>
            <a href="index.php"
                class="block px-4 py-2 text-gray-900 rounded-lg hover:bg-blue-100 dark:text-white dark:hover:bg-gray-700 flex flex-row">
                <svg class="w-6 h-6 text-gray-800 dark:text-white mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.143 4H4.857A.857.857 0 0 0 4 4.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 10 9.143V4.857A.857.857 0 0 0 9.143 4Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 20 9.143V4.857A.857.857 0 0 0 19.143 4Zm-10 10H4.857a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286A.857.857 0 0 0 9.143 14Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286a.857.857 0 0 0-.857-.857Z"/>
                </svg>

                Beranda
            </a>
        </li>
        <li>
            <a href="poli.php"
                class="block px-4 py-2 text-gray-900 rounded-lg hover:bg-blue-100 dark:text-white dark:hover:bg-gray-700 flex flex-row">
                <svg class="w-6 h-6 text-gray-800 dark:text-white mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M3 21h18M4 18h16M6 10v8m4-8v8m4-8v8m4-8v8M4 9.5v-.955a1 1 0 0 1 .458-.84l7-4.52a1 1 0 0 1 1.084 0l7 4.52a1 1 0 0 1 .458.84V9.5a.5.5 0 0 1-.5.5h-15a.5.5 0 0 1-.5-.5Z"/>
                </svg>

                Data Poli
            </a>
        </li>
        <li>
            <a href="dokter.php"
                class="block px-4 py-2 text-gray-900 rounded-lg hover:bg-blue-100 dark:text-white dark:hover:bg-gray-700 flex flex-row">
                <svg class="w-6 h-6 text-gray-800 dark:text-white mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h3m-3 3h3m-3 3h3m-6 1c-.306-.613-.933-1-1.618-1H7.618c-.685 0-1.312.387-1.618 1M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm7 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z"/>
                </svg>

                Data Dokter
            </a>
        </li>
        <li>
            <a href="pasien.php"
                class="block px-4 py-2 text-gray-900 rounded-lg hover:bg-blue-100 dark:text-white dark:hover:bg-gray-700 flex flex-row">
                <svg class="w-6 h-6 text-gray-800 dark:text-white mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.5 17H4a1 1 0 0 1-1-1 3 3 0 0 1 3-3h1m0-3.05A2.5 2.5 0 1 1 9 5.5M19.5 17h.5a1 1 0 0 0 1-1 3 3 0 0 0-3-3h-1m0-3.05a2.5 2.5 0 1 0-2-4.45m.5 13.5h-7a1 1 0 0 1-1-1 3 3 0 0 1 3-3h3a3 3 0 0 1 3 3 1 1 0 0 1-1 1Zm-1-9.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/>
                </svg>

                Data Pasien
            </a>

        </li>
        <li>
            <a href="obat.php"
                class="block px-4 py-2 text-gray-900 rounded-lg hover:bg-blue-100 dark:text-white dark:hover:bg-gray-700 flex flex-row">
                <svg class="w-6 h-6 text-gray-800 dark:text-white mr-2 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-6 4h6m-6 4h6M6 3v18l2-2 2 2 2-2 2 2 2-2 2 2V3l-2 2-2-2-2 2-2-2-2 2-2-2Z"/>
</svg>

                Data Obat
            </a>
        </li>
        <li>
            <a href="sampah.php"
                class="block px-4 py-2 text-gray-900 rounded-lg hover:bg-blue-100 dark:text-white dark:hover:bg-gray-700 flex flex-row">
                <svg class="w-6 h-6 text-gray-900 dark:text-white mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/>
                </svg>

                Sampah
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
