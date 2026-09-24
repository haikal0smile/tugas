<?php
/**
 * Koneksi Database (PDO)
 * Ubah kredensial di bawah sesuai environment hosting/XAMPP/Laragon Anda.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'umkm_ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}
