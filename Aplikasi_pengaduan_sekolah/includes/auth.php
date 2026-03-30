<?php
require_once __DIR__ . '/functions.php';

/**
 * Cek autentikasi
 */
function checkAuth($required_role = null) {
    if (!isLoggedIn()) {
        redirectWithMessage('../login.php', 'Silakan login terlebih dahulu', 'warning');
    }
    
    if ($required_role && $_SESSION['role'] !== $required_role) {
        redirectWithMessage('../index.php', 'Anda tidak memiliki akses ke halaman ini', 'error');
    }
}

/**
 * Cek role admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Cek role siswa
 */
function isSiswa() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'siswa';
}

/**
 * Login user
 */
function loginUser($username, $password) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM users WHERE username = :username OR nis = :username";
    $stmt = $db->prepare($query);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        
        saveLog($user['id'], 'Login', 'User berhasil login');
        return ['success' => true, 'role' => $user['role']];
    }
    
    return ['success' => false, 'message' => 'Username/NIS atau password salah'];
}

/**
 * Logout user
 */
function logoutUser() {
    if (isset($_SESSION['user_id'])) {
        saveLog($_SESSION['user_id'], 'Logout', 'User logout dari sistem');
    }
    
    session_destroy();
    session_start();
    redirectWithMessage('../login.php', 'Anda telah logout', 'info');
}

/**
 * Register user baru
 */
function registerUser($data) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Validasi NIS unik
    $check = "SELECT id FROM users WHERE nis = :nis";
    $stmt = $db->prepare($check);
    $stmt->execute([':nis' => $data['nis']]);
    
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'NIS sudah terdaftar'];
    }
    
    // Validasi username unik
    $check = "SELECT id FROM users WHERE username = :username";
    $stmt = $db->prepare($check);
    $stmt->execute([':username' => $data['username']]);
    
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Username sudah digunakan'];
    }
    
    // Hash password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Insert user baru
    $query = "INSERT INTO users (username, password, nama_lengkap, kelas, nis, role) 
              VALUES (:username, :password, :nama_lengkap, :kelas, :nis, 'siswa')";
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        ':username' => $data['username'],
        ':password' => $hashed_password,
        ':nama_lengkap' => $data['nama_lengkap'],
        ':kelas' => $data['kelas'],
        ':nis' => $data['nis']
    ]);
    
    if ($result) {
        return ['success' => true, 'message' => 'Registrasi berhasil'];
    } else {
        return ['success' => false, 'message' => 'Registrasi gagal'];
    }
}
?>