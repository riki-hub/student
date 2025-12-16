<?php
session_start();
require '../koneksi.php';

// Jika sudah login sebagai siswa, langsung alihkan
if (isset($_SESSION['siswa_id'])) {
  header("Location: index.php");
  exit;
}

$error = '';
$nisValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nisValue = trim($_POST['nis'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($nisValue !== '' && $password !== '') {
    $stmt = $conn->prepare("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id_kelas WHERE s.nis = ? AND s.status = 'aktif' LIMIT 1");
    $stmt->bind_param("s", $nisValue);
    $stmt->execute();
    $result = $stmt->get_result();
    $siswa = $result->fetch_assoc();

    if ($siswa && $password === $siswa['password']) {
      session_regenerate_id(true);
      $_SESSION['siswa_id'] = $siswa['id_siswa'];
      $_SESSION['siswa_nama'] = $siswa['nama_siswa'];
      $_SESSION['siswa_nis'] = $siswa['nis'];
      $_SESSION['siswa_kelas'] = $siswa['nama_kelas'];
      header("Location: index.php");
      exit;
    } else {
      $error = "NIS atau password salah, atau akun belum aktif.";
    }
    $stmt->close();
  } else {
    $error = "NIS dan password harus diisi.";
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>Login Siswa - Sistem Poin</title>

  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />

  <style>
    .auth-hero {
      background: linear-gradient(125deg, rgba(14, 116, 144, 0.9), rgba(12, 74, 110, 0.95)), url('../assets/img/curved-images/curved0.jpg');
      background-size: cover;
      background-position: center;
    }

    .card {
      border-radius: 18px;
    }

    .card .form-label {
      font-weight: 600;
      color: #0f172a;
    }

    .card .btn {
      font-weight: 700;
      letter-spacing: .02em;
    }

    .login-extra {
      font-size: 0.9rem;
    }
  </style>
</head>

<body class="bg-gray-200">
  <main class="main-content mt-0">
    <div class="page-header align-items-start min-vh-100 auth-hero">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container my-auto">
        <div class="row justify-content-center">
          <div class="col-lg-4 col-md-7 col-12 mx-auto">
            <div class="card shadow-lg">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-dark shadow-dark border-radius-lg py-3 px-4">
                  <h4 class="text-white font-weight-bolder text-center mt-2 mb-1">Portal Siswa</h4>
                  <p class="text-sm text-white text-center mb-0 opacity-8">Masuk dengan NIS untuk melihat profil dan poin</p>
                </div>
              </div>
              <div class="card-body px-4 pb-4">
                <?php if (!empty($error)): ?>
                  <div class="alert alert-danger text-white" role="alert">
                    <?= htmlspecialchars($error) ?>
                  </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="text-start">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">NIS</label>
                    <input type="text" name="nis" class="form-control" value="<?= htmlspecialchars($nisValue) ?>" required autofocus>
                  </div>
                  <div class="input-group input-group-outline mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                  </div>

                  <div class="text-center mt-4">
                    <button type="submit" class="btn bg-gradient-dark w-100 my-2">Masuk</button>
                  </div>
                </form>

                <div class="text-center login-extra mt-3">
                  <span class="text-secondary">Petugas/Admin?</span>
                  <a href="../index.php" class="text-dark font-weight-bolder ms-1">Masuk di sini</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>
