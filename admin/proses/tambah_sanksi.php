<?php
require '../../koneksi.php';
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_sanksi']));
    $batas = (int)$_POST['batas_poin'];
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));

    $insert = mysqli_query($conn, "INSERT INTO master_sanksi (nama_sanksi, batas_poin, deskripsi) VALUES ('$nama', $batas, '$deskripsi')");
    header("Location: ../sanksi.php?msg=" . ($insert ? 'added' : 'error'));
}
?>