<?php
// Konfigurasi Aplikasi
define('APP_NAME', 'Aplikasi Pengaduan Sarana Sekolah');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/aplikasi-pengaduan-sekolah');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/aplikasi-pengaduan-sekolah/assets/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session
session_start();

// Error Reporting (mati saat production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>