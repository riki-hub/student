<?php
include '../../koneksi.php'; // sesuaikan lokasi koneksi

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username      = mysqli_real_escape_string($conn, $_POST['username']);
    $password      = $_POST['password'];
    $nama_lengkap  = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $role          = mysqli_real_escape_string($conn, $_POST['role']);

    // Cek username sudah ada atau belum
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
                alert('Username sudah digunakan!');
                window.history.back();
              </script>";
        exit;
    }

    // Insert user
    $query = mysqli_query($conn, "
        INSERT INTO users (username, password, nama_lengkap, role)
        VALUES ('$username', '$password', '$nama_lengkap', '$role')
    ");

    if ($query) {
        echo "<script>
                alert('User berhasil ditambahkan');
                window.location='../profile.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menambahkan user');
                window.history.back();
              </script>";
    }
}
?>
