<?php
require '../../koneksi.php';

if (isset($_POST['edit'])) {

    $id   = (int) $_POST['id_kelas'];
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_kelas']));

    $update = mysqli_query($conn, "
        UPDATE kelas 
        SET nama_kelas = '$nama'
        WHERE id_kelas = '$id'
    ");

    header("Location: ../kelas.php?msg=" . ($update ? 'updated' : 'error'));
}
?>
