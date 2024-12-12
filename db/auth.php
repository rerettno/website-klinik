<?php
/**
 * Validasi akses berdasarkan role
 *
 * @param string $role Role yang diperbolehkan (e.g., 'admin' atau 'dokter')
 */
function validateAccess($role) {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== $role) {
        header('Location: ../index.php'); // Redirect ke login jika role tidak sesuai
        exit();
    }
}
?>
