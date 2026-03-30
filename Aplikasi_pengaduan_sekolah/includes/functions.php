<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app_config.php';

/**
 * Fungsi untuk membersihkan input
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validasi input form
 */
function validateInput($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = isset($data[$field]) ? $data[$field] : '';
        
        if (strpos($rule, 'required') !== false && empty($value)) {
            $errors[$field] = ucfirst($field) . ' harus diisi';
        }
        
        if (strpos($rule, 'email') !== false && !empty($value)) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'Format email tidak valid';
            }
        }
        
        if (preg_match('/min:(\d+)/', $rule, $matches)) {
            if (strlen($value) < $matches[1]) {
                $errors[$field] = ucfirst($field) . ' minimal ' . $matches[1] . ' karakter';
            }
        }
        
        if (preg_match('/max:(\d+)/', $rule, $matches)) {
            if (strlen($value) > $matches[1]) {
                $errors[$field] = ucfirst($field) . ' maksimal ' . $matches[1] . ' karakter';
            }
        }
    }
    
    return $errors;
}

/**
 * Format tanggal Indonesia
 */
function formatTanggal($tanggal, $format = 'l, d F Y') {
    $hari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    
    $bulan = [
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember'
    ];
    
    $date = date_create($tanggal);
    $formatted = date_format($date, $format);
    
    $formatted = str_replace(array_keys($hari), array_values($hari), $formatted);
    $formatted = str_replace(array_keys($bulan), array_values($bulan), $formatted);
    
    return $formatted;
}

/**
 * Mendapatkan badge status
 */
function getStatusBadge($status) {
    $badges = [
        'menunggu' => '<span class="badge bg-warning text-dark">⏳ Menunggu</span>',
        'diproses' => '<span class="badge bg-info text-white">⚙️ Diproses</span>',
        'selesai' => '<span class="badge bg-success text-white">✅ Selesai</span>',
        'ditolak' => '<span class="badge bg-danger text-white">❌ Ditolak</span>'
    ];
    
    return isset($badges[$status]) ? $badges[$status] : '<span class="badge bg-secondary">Tidak Diketahui</span>';
}

/**
 * Mendapatkan badge prioritas
 */
function getPrioritasBadge($prioritas) {
    $badges = [
        'rendah' => '<span class="badge bg-success">Rendah</span>',
        'sedang' => '<span class="badge bg-warning text-dark">Sedang</span>',
        'tinggi' => '<span class="badge bg-danger">Tinggi</span>'
    ];
    
    return isset($badges[$prioritas]) ? $badges[$prioritas] : '<span class="badge bg-secondary">Normal</span>';
}

/**
 * Simpan log aktivitas
 */
function saveLog($user_id, $aktivitas, $detail = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $query = "INSERT INTO log_aktivitas (user_id, aktivitas, detail, ip_address, user_agent) 
              VALUES (:user_id, :aktivitas, :detail, :ip, :user_agent)";
    
    $stmt = $db->prepare($query);
    return $stmt->execute([
        ':user_id' => $user_id,
        ':aktivitas' => $aktivitas,
        ':detail' => $detail,
        ':ip' => $ip,
        ':user_agent' => $user_agent
    ]);
}

/**
 * Upload file
 */
function uploadFile($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'pdf']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File tidak valid'];
    }
    
    $file_name = $file['name'];
    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validasi tipe file
    if (!in_array($file_ext, $allowed_types)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    // Validasi ukuran file (max 5MB)
    if ($file_size > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 5MB'];
    }
    
    // Generate nama file unik
    $new_file_name = uniqid() . '_' . date('Ymd') . '.' . $file_ext;
    $target_file = $target_dir . $new_file_name;
    
    if (move_uploaded_file($file_tmp, $target_file)) {
        return ['success' => true, 'file_name' => $new_file_name];
    } else {
        return ['success' => false, 'message' => 'Gagal mengupload file'];
    }
}

/**
 * Kirim notifikasi email (sederhana)
 */
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@aplikasi-pengaduan.sch.id" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Generate random password
 */
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * Hitung umur pengaduan dalam hari
 */
function hitungUmurPengaduan($tanggal_lapor) {
    $lapor = new DateTime($tanggal_lapor);
    $sekarang = new DateTime();
    $interval = $lapor->diff($sekarang);
    return $interval->days;
}

/**
 * Format ukuran file
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Cek session login
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Redirect dengan pesan
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'text' => $message
    ];
    header("Location: $url");
    exit();
}
?>