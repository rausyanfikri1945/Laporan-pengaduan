<?php
require_once '../includes/auth.php';
checkAuth('siswa');

$database = new Database();
$conn = $database->getConnection();

// Ambil data statistik siswa
$user_id = $_SESSION['user_id'];

// Total aspirasi
$query = "SELECT COUNT(*) as total FROM aspirasi WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Aspirasi per status
$status_query = "SELECT status, COUNT(*) as jumlah 
                 FROM aspirasi 
                 WHERE user_id = :user_id 
                 GROUP BY status";
$stmt = $conn->prepare($status_query);
$stmt->execute([':user_id' => $user_id]);
$status_counts = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $status_counts[$row['status']] = $row['jumlah'];
}

// Aspirasi terbaru
$recent_query = "SELECT a.*, k.nama_kategori 
                 FROM aspirasi a
                 JOIN kategori k ON a.kategori_id = k.id
                 WHERE a.user_id = :user_id
                 ORDER BY a.created_at DESC
                 LIMIT 5";
$stmt = $conn->prepare($recent_query);
$stmt->execute([':user_id' => $user_id]);
$recent_aspirasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - <?php echo APP_NAME; ?></title>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            transition: background 0.3s;
        }
        
        .nav-menu a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .nav-menu a.active {
            background: rgba(255,255,255,0.2);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102,126,234,0.3);
        }
        
        .welcome-card h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .welcome-card p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-icon.total { background: #e3f2fd; color: #1976d2; }
        .stat-icon.menunggu { background: #fff3e0; color: #f57c00; }
        .stat-icon.diproses { background: #e8f5e8; color: #388e3c; }
        .stat-icon.selesai { background: #e8eaf6; color: #3f51b5; }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .section-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: transform 0.3s;
            display: inline-block;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #666;
        }
        
        tr:hover td {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-warning { background: #fff3e0; color: #f57c00; }
        .badge-info { background: #e3f2fd; color: #1976d2; }
        .badge-success { background: #e8f5e8; color: #388e3c; }
        .badge-danger { background: #ffebee; color: #c33; }
        
        .action-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .action-link:hover {
            text-decoration: underline;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><?php echo APP_NAME; ?></div>
        <div class="nav-menu">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="form_aspirasi.php">Form Aspirasi</a>
            <a href="histori.php">Histori</a>
            <a href="profil.php">Profil</a>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                </div>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="welcome-card">
            <h1>Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?>!</h1>
            <p>Terima kasih telah menggunakan aplikasi pengaduan sarana sekolah. Sampaikan aspirasi Anda untuk kemajuan sekolah kita bersama.</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">📋</div>
                <div class="stat-value"><?php echo $total; ?></div>
                <div class="stat-label">Total Aspirasi</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon menunggu">⏳</div>
                <div class="stat-value"><?php echo isset($status_counts['menunggu']) ? $status_counts['menunggu'] : 0; ?></div>
                <div class="stat-label">Menunggu</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon diproses">⚙️</div>
                <div class="stat-value"><?php echo isset($status_counts['diproses']) ? $status_counts['diproses'] : 0; ?></div>
                <div class="stat-label">Diproses</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon selesai">✅</div>
                <div class="stat-value"><?php echo isset($status_counts['selesai']) ? $status_counts['selesai'] : 0; ?></div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>
        
        <div class="section-title">
            <h2>Aspirasi Terbaru</h2>
            <a href="form_aspirasi.php" class="btn-primary">+ Aspirasi Baru</a>
        </div>
        
        <div class="table-container">
            <?php if (count($recent_aspirasi) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Judul</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_aspirasi as $aspirasi): ?>
                        <tr>
                            <td><?php echo formatTanggal($aspirasi['tanggal_lapor'], 'd/m/Y'); ?></td>
                            <td><?php echo htmlspecialchars($aspirasi['nama_kategori']); ?></td>
                            <td><?php echo htmlspecialchars($aspirasi['judul']); ?></td>
                            <td><?php echo getStatusBadge($aspirasi['status']); ?></td>
                            <td>
                                <a href="detail_aspirasi.php?id=<?php echo $aspirasi['id']; ?>" class="action-link">Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>Belum ada aspirasi yang Anda kirimkan.</p>
                    <a href="form_aspirasi.php" class="btn-primary" style="margin-top: 15px;">Kirim Aspirasi Sekarang</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>