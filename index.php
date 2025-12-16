<?php
session_start();
include 'koneksi.php';

// Inisialisasi variabel pesan error
$error = '';

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil dan bersihkan input
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($username !== '' && $password !== '') {
    // Gunakan prepared statement untuk keamanan (mencegah SQL Injection)
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
      if (($password == $data['password'])) {
        // Login berhasil
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['nama'] = $data['nama_lengkap'];
        $_SESSION['role'] = $data['role'];
        if ($data['role'] != 'admin') {
          header("Location: petugas/index.php");
          exit;
        }
        header("Location: admin/dashboard.php");
        exit;
      } else {
        $error = "Password salah!";
      }
    } else {
      $error = "Username tidak ditemukan!";
    }
    $stmt->close();
  } else {
    $error = "Username dan password harus diisi!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <title>Sign In - Material Dashboard 3</title>

  <!-- Fonts and icons -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

  <!-- CSS Files -->
  <link id="pagestyle" href="assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="bg-gray-200">
  <main class="main-content mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-dark shadow-dark border-radius-lg py-3 pe-1">
                  <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Sign in</h4>
                  <div class="row mt-3">
                    <div class="col-2 text-center ms-auto"><a class="btn btn-link px-3" href="javascript:;"><i class="fa fa-facebook text-white text-lg"></i></a></div>
                    <div class="col-2 text-center px-1"><a class="btn btn-link px-3" href="javascript:;"><i class="fa fa-github text-white text-lg"></i></a></div>
                    <div class="col-2 text-center me-auto"><a class="btn btn-link px-3" href="javascript:;"><i class="fa fa-google text-white text-lg"></i></a></div>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <!-- Tampilkan error jika ada -->
                <?php if (!empty($error)): ?>
                  <div class="alert alert-danger text-white text-center" role="alert">
                    <?= htmlspecialchars($error) ?>
                  </div>
                <?php endif; ?>

                <form role="form" class="text-start" method="POST" action="index.php">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                  </div>
                  <div class="input-group input-group-outline mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2">Sign in</button>
                  </div>
                </form>
                <div class="text-center mt-3">
                  <span class="text-secondary text-sm">Login sebagai siswa?</span>
                  <a href="siswa/login.php" class="text-dark text-sm font-weight-bold ms-1">Buka portal siswa</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Core JS Files -->
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>
