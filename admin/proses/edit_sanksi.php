<?php
require '../../koneksi.php';
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id_sanksi'];
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_sanksi']));
    $batas = (int)$_POST['batas_poin'];
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $page = (int)$_POST['page'];

    $update = mysqli_query($conn, "UPDATE master_sanksi SET nama_sanksi = '$nama', batas_poin = $batas, deskripsi = '$deskripsi' WHERE id_sanksi = $id");
    header("Location: ../sanksi.php?page=$page&msg=" . ($update ? 'updated' : 'error'));
}
?>