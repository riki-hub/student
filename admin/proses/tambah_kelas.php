<?php
require '../../koneksi.php';

if (isset($_POST['tambah'])) {

    if (empty($_POST['nama_kelas'])) {
        header("Location: ../kelas.php?msg=empty");
        exit;
    }

    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_kelas']));
    $insert = mysqli_query($conn, "INSERT INTO kelas (nama_kelas) VALUES ('$nama')");

    header("Location: ../kelas.php?msg=" . ($insert ? 'added' : 'error'));
}
?>
