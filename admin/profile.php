<?php require '../koneksi.php' ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
    Material Dashboard 3 by Creative Tim
  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <!-- CSS Files -->
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-gray-100">
  <?php include "sidebar.php" ?>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <?php include "nav.php" ?>
    <!-- End Navbar -->
    <div class="container-fluid py-2">
      <div class="row">
        <h3 class="mb-0 h4 font-weight-bolder">Master User</h3>
        <p class="mb-4">
          Add New User.
        </p>
      </div>

      <div class="card">
        <div class="d-flex">
          <button class="btn btn-primary m-3" data-bs-toggle="modal" data-bs-target="#tambahUser">
            + Tambah User
          </button>
        </div>

        <div class="card-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>No</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Role</th>
                <th width="180">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $data = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user DESC");
              while ($u = mysqli_fetch_assoc($data)) {
              ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= $u['username'] ?></td>
                  <td><?= $u['nama_lengkap'] ?></td>
                  <td><?= ucfirst($u['role']) ?></td>
                  <td>
                    <button class="btn btn-warning btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#edit<?= $u['id_user'] ?>">
                      Edit
                    </button>
                    <a href="?delete=<?= $u['id_user'] ?>"
                      onclick="return confirm('Hapus user ini?')"
                      class="btn btn-danger btn-sm">
                      Hapus
                    </a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal fade" id="tambahUser" tabindex="-1">
        <div class="modal-dialog">
          <form method="POST" class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Tambah User</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="mb-2">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>

              <div class="mb-2">
                <label>Password</label>
                <input type="text" name="password" class="form-control" required>
              </div>

              <div class="mb-2">
                <label>Nama</label>
                <input type="text" name="nama_lengkap" class="form-control" required>
              </div>

              <div class="mb-2">
                <label>Role</label>
                <select name="role" class="form-control" required>
                  <option value="">-- Pilih Role --</option>
                  <option value="admin">Admin</option>
                  <option value="guru">Guru</option>
                  <option value="orangtua">Orang Tua</option>
                </select>
              </div>
            </div>

            <div class="modal-footer">
              <button type="submit" name="tambah" class="btn btn-success">
                Simpan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>