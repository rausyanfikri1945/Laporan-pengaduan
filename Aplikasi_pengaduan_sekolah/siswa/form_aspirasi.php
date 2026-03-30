<?php
require_once '../includes/auth.php';
checkAuth('siswa');

$database = new Database();
$conn = $database->getConnection();

// Ambil data kategori
$query = "SELECT * FROM kategori ORDER BY nama_kategori";
$stmt = $conn->prepare($query);
$stmt->execute();
$kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = cleanInput($_POST['judul']);
    $kategori_id = cleanInput($_POST['kategori_id']);
    $deskripsi = cleanInput($_POST['deskripsi']);
    $prioritas = cleanInput($_POST['prioritas']);
    
    // Validasi
    $rules = [
        'judul' => 'required|min:5|max:200',
        'kategori_id' => 'required',
        'deskripsi' => 'required|min:10'
    ];
    
    $errors = validateInput($_POST, $rules);
    
    if (empty($errors)) {
        // Upload lampiran jika ada
        $lampiran = null;
        if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == 0) {
            $upload = uploadFile($_FILES['lampiran'], '../assets/uploads/lampiran/');
            if ($upload['success']) {
                $lampiran = $upload['file_name'];
            }
        }
        
        // Simpan ke database
        $query = "INSERT INTO aspirasi (user_id, kategori_id, judul, deskripsi, tanggal_lapor, prioritas, lampiran) 
                  VALUES (:user_id, :kategori_id, :judul, :deskripsi, CURDATE(), :prioritas, :lampiran)";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':kategori_id' => $kategori_id,
            ':judul' => $judul,
            ':deskripsi' => $deskripsi,
            ':prioritas' => $prioritas,
            ':lampiran' => $lampiran
        ]);
        
        if ($result) {
            $aspirasi_id = $conn->lastInsertId();
            saveLog($_SESSION['user_id'], 'Tambah Aspirasi', "Menambahkan aspirasi ID: $aspirasi_id");
            $success = "Aspirasi berhasil dikirim! Terima kasih atas partisipasi Anda.";
        } else {
            $error = "Gagal mengirim aspirasi. Silakan coba lagi.";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Aspirasi - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-brand {
            font-size: 20px;
            font-weight: 600;
        }
        
        .nav-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
        }
        
        .nav-menu a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .form-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .form-header p {
            opacity: 0.9;
        }
        
        .form-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            color: #555;
            font-weight: 500;
            font-size: 15px;
        }
        
        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .file-input {
            border: 2px dashed #e0e0e0;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-input:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
        
        .file-input input {
            display: none;
        }
        
        .file-label {
            cursor: pointer;
        }
        
        .file-icon {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .file-text {
            color: #666;
        }
        
        .file-info {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        .radio-option {
            flex: 1;
        }
        
        .radio-option input[type="radio"] {
            display: none;
        }
        
        .radio-option label {
            display: block;
            padding: 15px;
            text-align: center;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            margin: 0;
        }
        
        .radio-option input[type="radio"]:checked + label {
            border-color: #667eea;
            background: #f0f3ff;
            color: #667eea;
        }
        
        .radio-option label:hover {
            border-color: #667eea;
        }
        
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102,126,234,0.3);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 15px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .char-counter {
            text-align: right;
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><?php echo APP_NAME; ?></div>
        <div class="nav-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="form_aspirasi.php" style="background: rgba(255,255,255,0.2);">Form Aspirasi</a>
            <a href="histori.php">Histori</a>
            <a href="profil.php">Profil</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
        
        <div class="form-card">
            <div class="form-header">
                <h1>Form Aspirasi Siswa</h1>
                <p>Sampaikan aspirasi, kritik, dan saran Anda untuk kemajuan sekolah</p>
            </div>
            
            <div class="form-body">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Judul Aspirasi</label>
                        <input type="text" name="judul" required 
                               placeholder="Contoh: Kerusakan Meja di Kelas X IPA 1"
                               maxlength="200"
                               onkeyup="document.getElementById('judul-counter').textContent = this.value.length + '/200'">
                        <div class="char-counter" id="judul-counter">0/200</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori_id" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategori as $k): ?>
                                <option value="<?php echo $k['id']; ?>">
                                    <?php echo htmlspecialchars($k['nama_kategori']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Prioritas</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" name="prioritas" id="rendah" value="rendah" checked>
                                <label for="rendah">🟢 Rendah</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="prioritas" id="sedang" value="sedang">
                                <label for="sedang">🟡 Sedang</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="prioritas" id="tinggi" value="tinggi">
                                <label for="tinggi">🔴 Tinggi</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi Lengkap</label>
                        <textarea name="deskripsi" required 
                                  placeholder="Jelaskan secara detail masalah atau masukan Anda..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Lampiran (Opsional)</label>
                        <div class="file-input" onclick="document.getElementById('lampiran').click()">
                            <input type="file" name="lampiran" id="lampiran" 
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <div class="file-label">
                                <div class="file-icon">📎</div>
                                <div class="file-text">Klik untuk upload lampiran</div>
                                <div class="file-info">Format: JPG, PNG, PDF (Max 5MB)</div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        Kirim Aspirasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>