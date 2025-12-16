<?php
include '../koneksi.php';

$keyword = $_POST['keyword'];
$kelas   = $_POST['kelas'];

if ($kelas != "") {
    // Kalau kelas dipilih
    $query = mysqli_query($conn, "
        SELECT * FROM siswa 
        WHERE id_kelas = '$kelas'
        AND nama_siswa LIKE '%$keyword%'
        ORDER BY nama_siswa
    ");
} else {
    // Kalau kelas belum dipilih
    $query = mysqli_query($conn, "
        SELECT * FROM siswa 
        WHERE nama_siswa LIKE '%$keyword%'
        ORDER BY nama_siswa
    ");
}
while ($row = mysqli_fetch_assoc($query)) {
    echo "<div onclick=\"pilihSiswa('{$row['id_siswa']}', '{$row['nama_siswa']}')\">
            {$row['nama_siswa']}
          </div>";
}
