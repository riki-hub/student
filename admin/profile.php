<?php
require '../koneksi.php';
session_start();

// Cek login (opsional, tambahkan jika belum ada)
if (!isset($_SESSION['id_user'])) {
    header("Location: ../sign-in.php");
    exit;
}

// Proses hapus (lebih aman)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id_user = $id");
    header("Location: profile.php?msg=deleted");
    exit;
}

// Pesan feedback
$alert = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'added') $alert = '<div class="alert alert-success">User berhasil ditambahkan!</div>';
    if ($_GET['msg'] == 'updated') $alert = '<div class="alert alert-success">User berhasil diupdate!</div>';
    if ($_GET['msg'] == 'deleted') $alert = '<div class="alert alert-success">User berhasil dihapus!</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Master User - Material Dashboard</title>

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" rel="stylesheet" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />

  <!-- Material Dashboard CSS -->
  <link href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
  <?php include "sidebar.php"; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include "nav.php"; ?>

    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-lg-12">
          <h3 class="h4 font-weight-bolder mb-0">Master User</h3>
          <p class="text-sm mb-0">Kelola data pengguna sistem.</p>
        </div>
      </div>

      <!-- Alert Feedback -->
      <?= $alert ?>

      <div class="card">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahUser">
            <i class="fas fa-plus me-2"></i> Tambah User
          </button>
        </div>

        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Username</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Lengkap</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                $query = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user DESC");
                while ($u = mysqli_fetch_assoc($query)):
                ?>
                  <tr>
                    <td class="ps-4"><span class="text-secondary text-xs"><?= $no++ ?></span></td>
                    <td><p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($u['username']) ?></p></td>
                    <td><p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($u['nama_lengkap']) ?></p></td>
                    <td><span class="badge badge-sm bg-gradient-<?= $u['role'] == 'admin' ? 'danger' : ($u['role'] == 'guru' ? 'info' : 'secondary') ?>">
                      <?= ucfirst($u['role']) ?>
                    </span></td>
                    <td class="text-center table-actions py-3">
    <!-- Tombol Edit -->
    <button class="btn btn-warning btn-sm me-2 px-4" 
            data-bs-toggle="modal" 
            data-bs-target="#editUser<?= $u['id_user'] ?>">
        Edit
    </button>

    <!-- Tombol Hapus -->
    <a href="?delete=<?= $u['id_user'] ?>" 
       onclick="return confirm('Yakin ingin menghapus user <?= htmlspecialchars($u['username']) ?>?')"
       class="btn btn-danger btn-sm px-4">
        Hapus
    </a>
</td>
                  </tr>

                  <!-- Modal Edit User -->
                  <div class="modal fade" id="editUser<?= $u['id_user'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                      <form method="POST" action="proses/edit.php">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                            
                            <div class="mb-3">
                              <label>Username</label>
                              <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($u['username']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                              <label>Password <small class="text-muted">(Kosongkan jika tidak ingin ubah)</small></label>
                              <input type="password" name="password" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                              <label>Nama Lengkap</label>
                              <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($u['nama_lengkap']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                              <label>Role</label>
                              <select name="role" class="form-select" required>
                                <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="guru" <?= $u['role'] == 'guru' ? 'selected' : '' ?>>Guru</option>
                                <option value="orangtua" <?= $u['role'] == 'orangtua' ? 'selected' : '' ?>>Orang Tua</option>
                              </select>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="edit" class="btn btn-success">Update</button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal Tambah User (sudah bagus, tetap dipertahankan) -->
      <div class="modal fade" id="tambahUser" tabindex="-1" aria-labelledby="tambahUserLabel" aria-hidden="true">
        <div class="modal-dialog">
          <form method="POST" action="proses/tambah.php">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="tambahUserLabel">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label for="username" class="form-label">Username</label>
                  <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                  <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="role" class="form-label">Role</label>
                  <select name="role" id="role" class="form-select" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="orangtua">Orang Tua</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="tambah" class="btn btn-success">
                  <i class="fas fa-save me-2"></i> Simpan
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <!-- Core JS -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>
</html>