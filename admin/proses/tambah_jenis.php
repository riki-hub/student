<?php
require '../../koneksi.php';
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_pelanggaran']));
    $poin = (int)$_POST['poin'];

    $insert = mysqli_query($conn, "INSERT INTO jenis_pelanggaran (nama_pelanggaran, poin) VALUES ('$nama', $poin)");
    header("Location: ../jenis_pelanggaran.php?msg=" . ($insert ? 'added' : 'error'));
}
?>