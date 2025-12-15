<?php
require '../../koneksi.php';
if (isset($_POST['tambah'])) {
    $nis            = mysqli_real_escape_string($conn, $_POST['nis']);
    $password       = mysqli_real_escape_string($conn, $_POST['password']); // plain text
    $nama           = mysqli_real_escape_string($conn, $_POST['nama_siswa']);
    $jk             = $_POST['jenis_kelamin'];
    $tgl_lahir      = $_POST['tanggal_lahir'];
    $id_kelas       = $_POST['id_kelas'] ?: NULL;
    $poin_awal      = (int)$_POST['poin_awal'];
    $poin_sisa      = (int)$_POST['poin_sisa'];
    $status         = $_POST['status'];

    $insert = mysqli_query($conn, "INSERT INTO siswa 
        (nis, password, nama_siswa, jenis_kelamin, tanggal_lahir, id_kelas, poin_awal, poin_sisa, status)
        VALUES ('$nis', '$password', '$nama', '$jk', '$tgl_lahir', ".($id_kelas ? "'$id_kelas'" : "NULL").", $poin_awal, $poin_sisa, '$status')");

    header("Location: ../siswa.php?msg=" . ($insert ? 'added' : 'error'));
}
?>