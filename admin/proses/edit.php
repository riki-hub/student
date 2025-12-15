<?php
include '../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id           = (int) $_POST['id_user'];
    $username     = mysqli_real_escape_string($conn, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $role         = mysqli_real_escape_string($conn, $_POST['role']);
    $password     = $_POST['password'];

    // Cek username dipakai user lain
    $cek = mysqli_query($conn, "
        SELECT * FROM users 
        WHERE username='$username' AND id_user != '$id'
    ");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
                alert('Username sudah digunakan user lain!');
                window.history.back();
              </script>";
        exit;
    }

    // Jika password diisi → update password
    if (!empty($password)) {
        $password_hash = ($password == PASSWORD_DEFAULT);

        $query = mysqli_query($conn, "
            UPDATE users SET
            username='$username',
            password='$password_hash',
            nama_lengkap='$nama_lengkap',
            role='$role'
            WHERE id_user='$id'
        ");
    } else {
        // Jika password kosong → tidak diubah
        $query = mysqli_query($conn, "
            UPDATE users SET
            username='$username',
            nama_lengkap='$nama_lengkap',
            role='$role'
            WHERE id_user='$id'
        ");
    }

    if ($query) {
        echo "<script>
                alert('User berhasil diperbarui');
                window.location='../profile.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal memperbarui user');
                window.history.back();
              </script>";
    }
}
?>
