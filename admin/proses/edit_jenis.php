<?php
require '../../koneksi.php';
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id_jenis'];
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_pelanggaran']));
    $poin = (int)$_POST['poin'];
    $page = (int)$_POST['page'];

    $update = mysqli_query($conn, "UPDATE jenis_pelanggaran SET nama_pelanggaran = '$nama', poin = $poin WHERE id_jenis = $id");
    header("Location: ../jenis_pelanggaran.php?page=$page&msg=" . ($update ? 'updated' : 'error'));
}
?>