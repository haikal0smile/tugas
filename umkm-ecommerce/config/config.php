<?php
/**
 * Konfigurasi umum aplikasi
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SITE_NAME', 'Bebek Goreng Hitam Madura');
// BASE_URL dipakai untuk link & asset. Sesuaikan jika folder project berbeda.
define('BASE_URL', '/umkm-ecommerce');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
