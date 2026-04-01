<?php
$koneksi = mysqli_connect('localhost', 'root', '', 'sarana_pengaduan');
if (!$koneksi) die("Koneksi gagal: " . mysqli_connect_error());

// Inisialisasi Kategori secara otomatis jika belum lengkap
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 0");
$check = mysqli_query($koneksi, "SELECT * FROM kategori");
if (mysqli_num_rows($check) != 7) {
    mysqli_query($koneksi, "TRUNCATE TABLE kategori");
    mysqli_query($koneksi, "INSERT INTO kategori (id_kategori, ket_kategori) VALUES 
        (1, 'Kelas'), (2, 'Musholla'), (3, 'Kamar Mandi'), 
        (4, 'Lapangan Upacara'), (5, 'Lapangan Olah Raga'), 
        (6, 'Perpustakaan'), (7, 'Parkiran')");
}
// Membuat akun admin default jika belum ada
mysqli_query($koneksi, "INSERT IGNORE INTO admin (username, password) VALUES ('admin', 'admin123')");
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 1");
?>