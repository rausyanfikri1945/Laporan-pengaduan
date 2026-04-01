<?php include 'koneksi.php';

if (isset($_POST['simpan'])) {
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $ket = mysqli_real_escape_string($koneksi, $_POST['ket']);
    
    if(!empty($id_kategori)) {
        $res_k = mysqli_query($koneksi, "SELECT ket_kategori FROM kategori WHERE id_kategori='$id_kategori'");
        $dt_k = mysqli_fetch_assoc($res_k);
        $lokasi = $dt_k['ket_kategori'];
        
        $cek_s = mysqli_query($koneksi, "SELECT * FROM siswa WHERE nis='$nis'");
        if (mysqli_num_rows($cek_s) == 0) { mysqli_query($koneksi, "INSERT INTO siswa (nis, kelas) VALUES ('$nis', '$kelas')"); }
        
        $q_input = "INSERT INTO input_aspirasi (nis, id_kategori, lokasi, ket) VALUES ('$nis', '$id_kategori', '$lokasi', '$ket')";
        if (mysqli_query($koneksi, $q_input)) {
            $id_p = mysqli_insert_id($koneksi);
            mysqli_query($koneksi, "INSERT INTO aspirasi (id_pelaporan, status, feedback) VALUES ('$id_p', 'Menunggu', '-')");
            echo "<script>alert('Aspirasi Terkirim!'); window.location='index.php';</script>";
        }
    }
}
$kategori = mysqli_query($koneksi, "SELECT * FROM kategori");
$aspirasi = mysqli_query($koneksi, "SELECT i.*, s.kelas, k.ket_kategori, a.status, a.feedback FROM input_aspirasi i LEFT JOIN siswa s ON i.nis = s.nis LEFT JOIN kategori k ON i.id_kategori = k.id_kategori LEFT JOIN aspirasi a ON i.id_pelaporan = a.id_pelaporan ORDER BY i.id_pelaporan DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Layanan Aspirasi Siswa</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 20px; margin: 0; }
        .container { max-width: 1100px; margin: auto; display: flex; flex-wrap: wrap; gap: 30px; }
        .card { background: rgba(255, 255, 255, 0.95); padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); flex: 1; min-width: 320px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-Menunggu { background: #ffeeba; color: #856404; }
        .status-Proses { background: #b8daff; color: #004085; }
        .status-Selesai { background: #c3e6cb; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h3>📝 Kirim Aspirasi</h3>
            <form method="POST">
                <input type="number" name="nis" placeholder="NIS Anda" style="width:100%; padding:10px; margin:5px 0;" required>
                <input type="text" name="kelas" placeholder="Kelas (Contoh: XII RPL 1)" style="width:100%; padding:10px; margin:5px 0;" required>
                <select name="id_kategori" style="width:100%; padding:10px; margin:5px 0;" required>
                    <option value="">-- Pilih Lokasi --</option>
                    <?php while($k = mysqli_fetch_assoc($kategori)) : ?>
                        <option value="<?= $k['id_kategori'] ?>"><?= $k['ket_kategori'] ?></option>
                    <?php endwhile; ?>
                </select>
                <textarea name="ket" rows="4" placeholder="Detail laporan..." style="width:100%; padding:10px; margin:5px 0;" required></textarea>
                <button type="submit" name="simpan" style="width:100%; padding:12px; background:#28a745; color:white; border:none; cursor:pointer;">Kirim</button>
            </form>
        </div>

        <div class="card" style="flex: 2;">
            <h3>📋 Riwayat & Feedback</h3>
            <div style="overflow-x: auto;">
                <table>
                    <tr>
                        <th>NIS</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Feedback Admin</th>
                    </tr>
                    <?php while($row = mysqli_fetch_assoc($aspirasi)) : ?>
                    <tr>
                        <td><?= $row['nis'] ?></td>
                        <td><?= $row['ket_kategori'] ?></td>
                        <td><span class="badge status-<?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                        <td style="color: #555; font-style: italic;">
                            <?= ($row['feedback'] == '-' ? 'Belum dibalas' : $row['feedback']) ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <a href="admin.php" style="display:block; margin-top:20px; color:#667eea; text-decoration:none;">🔒 Login Admin</a>
        </div>
    </div>
</body>
</html>