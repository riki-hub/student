<?php
require '../../koneksi.php';
if (isset($_POST['edit'])) {
    $id             = (int)$_POST['id_siswa'];
    $nis            = mysqli_real_escape_string($conn, $_POST['nis']);
    $nama           = mysqli_real_escape_string($conn, $_POST['nama_siswa']);
    $jk             = $_POST['jenis_kelamin'];
    $tgl_lahir      = $_POST['tanggal_lahir'];
    $id_kelas       = $_POST['id_kelas'] ?: NULL;
    $poin_awal      = (int)$_POST['poin_awal'];
    $poin_sisa      = (int)$_POST['poin_sisa'];
    $status         = $_POST['status'];
    $page           = (int)$_POST['page'];

    $sql = "UPDATE siswa SET 
            nis = '$nis',
            nama_siswa = '$nama',
            jenis_kelamin = '$jk',
            tanggal_lahir = '$tgl_lahir',
            id_kelas = ".($id_kelas ? "'$id_kelas'" : "NULL").",
            poin_awal = $poin_awal,
            poin_sisa = $poin_sisa,
            status = '$status'";

    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $sql .= ", password = '$password'";
    }

    $sql .= " WHERE id_siswa = $id";

    $update = mysqli_query($conn, $sql);
    header("Location: ../siswa.php?page=$page&msg=" . ($update ? 'updated' : 'error'));
}
?>