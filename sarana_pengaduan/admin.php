<?php
session_start();
include 'koneksi.php';

// Logika Login
if (isset($_POST['login'])) {
    $u = mysqli_real_escape_string($koneksi, $_POST['username']);
    $p = mysqli_real_escape_string($koneksi, $_POST['password']);
    $q = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$u' AND password='$p'");
    if (mysqli_num_rows($q) > 0) { $_SESSION['admin'] = $u; header("Location: admin.php"); exit(); }
}

if (isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit(); }

// Logika Update Status dan Feedback
if (isset($_POST['update'])) {
    $id = $_POST['id_aspirasi'];
    $st = $_POST['status'];
    $fb = mysqli_real_escape_string($koneksi, $_POST['feedback']); // Menangkap input feedback
    mysqli_query($koneksi, "UPDATE aspirasi SET status='$st', feedback='$fb' WHERE id_aspirasi='$id'");
}

// Logika Hapus
if (isset($_GET['hapus'])) {
    $id_p = $_GET['hapus'];
    mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 0");
    mysqli_query($koneksi, "DELETE FROM aspirasi WHERE id_pelaporan='$id_p'");
    mysqli_query($koneksi, "DELETE FROM input_aspirasi WHERE id_pelaporan='$id_p'");
    mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 1");
    header("Location: admin.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #343a40; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        .btn-update { background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
        textarea { width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd; margin-bottom: 5px; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['admin'])) : ?>
    <div style="max-width:400px; margin:100px auto; background:white; padding:40px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.1); text-align:center;">
        <h2>🔒 Admin Login</h2>
        <form method="POST">
            <input name="username" placeholder="Username" style="width:100%; padding:12px; margin:10px 0; border:1px solid #ddd;" required>
            <input name="password" type="password" placeholder="Password" style="width:100%; padding:12px; margin:10px 0; border:1px solid #ddd;" required>
            <button type="submit" name="login" style="width:100%; padding:12px; background:#4e73df; color:white; border:none; border-radius:5px; cursor:pointer;">Login</button>
        </form>
    </div>
<?php else: 
    $aspirasi = mysqli_query($koneksi, "SELECT a.*, i.nis, i.ket, k.ket_kategori FROM aspirasi a JOIN input_aspirasi i ON a.id_pelaporan = i.id_pelaporan LEFT JOIN kategori k ON i.id_kategori = k.id_kategori ORDER BY a.id_aspirasi DESC");
?>
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #eee; padding-bottom:20px;">
            <h2>Dashboard Pengaduan</h2>
            <div>
                <a href="index.php">🏠 Lihat Web</a> | 
                <a href="?logout=1" style="color:red; font-weight:bold;">Logout 👋</a>
            </div>
        </div>
        <table>
            <tr>
                <th>NIS & Detail</th>
                <th width="40%">Status & Feedback Admin</th>
                <th>Aksi</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($aspirasi)) : ?>
            <tr>
                <td>
                    <b><?= $row['nis'] ?></b><br>
                    <small style="color:#888;"><?= $row['ket_kategori'] ?></small><br>
                    <?= $row['ket'] ?>
                </td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="id_aspirasi" value="<?= $row['id_aspirasi'] ?>">
                        <textarea name="feedback" placeholder="Tulis feedback..."><?= $row['feedback'] ?></textarea>
                        <div style="display:flex; gap:5px;">
                            <select name="status" style="flex:1; padding:5px;">
                                <option value="Menunggu" <?= $row['status'] == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                <option value="Proses" <?= $row['status'] == 'Proses' ? 'selected' : '' ?>>Proses</option>
                                <option value="Selesai" <?= $row['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                            <button name="update" class="btn-update">Simpan</button>
                        </div>
                    </form>
                </td>
                <td>
                    <a href="?hapus=<?= $row['id_pelaporan'] ?>" style="color:red;" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
<?php endif; ?>

</body>
</html>