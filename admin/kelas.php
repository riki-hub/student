<?php
require '../koneksi.php';
session_start();

// Cek login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../sign-in.php");
    exit;
}

// Proses hapus (lebih aman)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM kelas WHERE id_kelas = $id");
    header("Location: kelas.php?msg=deleted");
    exit;
}

// Pesan feedback
$alert = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'added') $alert = '<div class="alert alert-success">kelas berhasil ditambahkan!</div>';
    if ($_GET['msg'] == 'updated') $alert = '<div class="alert alert-success">kelas berhasil diupdate!</div>';
    if ($_GET['msg'] == 'deleted') $alert = '<div class="alert alert-success">kelas berhasil dihapus!</div>';
}

// --- Pagination ---
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Hitung total kelas
$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kelas");
$total = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total / $limit);

// Ambil data kelas
$query = mysqli_query($conn, "SELECT * FROM kelas ORDER BY id_kelas DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Master Kelas - Material Dashboard</title>

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" rel="stylesheet" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />

  <!-- Material Dashboard CSS -->
  <link href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />

  <style>
    /* Tombol aksi di tabel */
    .table-actions .btn {
      padding: 0.35rem 1rem;
      font-size: 0.875rem;
      min-width: 80px;
    }

    /* Badge poin */
    .poin-badge {
      font-weight: bold;
      font-size: 1.1em;
      padding: 0.5em 1em;
    }

    /* === EFEK GELAP SAAT MODAL TERBUKA (Tambah & Edit) === */
    body.modal-open {
      overflow: hidden;
    }

    body.modal-open .sidenav {
      filter: brightness(0.5);
      transition: filter 0.3s ease;
      pointer-events: none;
    }

    body.modal-open .main-content nav {
      filter: brightness(0.65);
      transition: filter 0.3s ease;
    }

    body.modal-open .card,
    body.modal-open .table-responsive {
      filter: brightness(0.85);
      transition: filter 0.3s ease;
    }

    .modal-backdrop.show {
      opacity: 0.75 !important;
    }

    /* === STYLING INPUT DI MODAL === */
    .modal .form-control {
      background-color: #ffffff;
      border: 2px solid #d1d5db;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 14px;
      color: #344767;
      transition: all 0.2s ease;
    }

    .modal .form-control:hover {
      border-color: #5e72e4;
    }

    .modal .form-control:focus {
      border-color: #5e72e4;
      box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.15);
      outline: none;
    }

    .modal .form-control::placeholder {
      color: #9ca3af;
    }
  </style>
</head>

<body class="g-sidenav-show bg-gray-100">
  <?php include "sidebar.php"; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include "nav.php"; ?>

    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-lg-12">
          <h3 class="h4 font-weight-bolder mb-1">Master Kelas</h3>
          <p class="text-sm text-muted mb-0">Kelola data kelas di sistem</p>
        </div>
      </div>

      <!-- Alert -->
      <?= $alert ?>

      <div class="card shadow-sm">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahKelas">
            <i class="fas fa-plus me-2"></i> Tambah Kelas
          </button>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-items-center mb-0 table-hover">
              <thead class="bg-light">
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">No</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Kelas</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                </tr>
              </thead>
                            <tbody>
                <?php
                $no = $offset + 1;
                // Variabel untuk mengumpulkan semua modal edit
                $edit_modals = '';

                while ($k = mysqli_fetch_assoc($query)):
                  // Kumpulkan modal edit ke dalam string
                  $edit_modals .= '
                  <div class="modal fade" id="editKelas'. $k['id_kelas'] .'" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                      <form method="POST" action="proses/edit_kelas.php">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Edit Kelas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <input type="hidden" name="id_kelas" value="'. $k['id_kelas'] .'">
                            <input type="hidden" name="page" value="'. $page .'">

                            <div class="mb-3">
                              <label class="form-label">Nama Kelas</label>
                              <input type="text" name="nama_kelas" class="form-control"
                                     value="'. htmlspecialchars($k['nama_kelas']) .'" required>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="edit" class="btn btn-success">
                              <i class="fas fa-save me-2"></i> Update
                            </button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>';
                ?>
                  <tr>
                    <td class="ps-4"><span class="text-secondary text-xs"><?= $no++ ?></span></td>
                    <td><p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($k['nama_kelas']) ?></p></td>
                    <td class="text-center table-actions py-3">
                      <!-- Tombol Edit -->
                      <button class="btn btn-warning btn-sm me-2 px-4" 
                              data-bs-toggle="modal" 
                              data-bs-target="#editKelas<?= $k['id_kelas'] ?>">
                        Edit
                      </button>

                      <!-- Tombol Hapus -->
                      <a href="?delete=<?= $k['id_kelas'] ?>&page=<?= $page ?>"
                         onclick="return confirm('Yakin menghapus kelas <?= htmlspecialchars($k['nama_kelas']) ?>?')"
                         class="btn btn-danger btn-sm px-4">
                        Hapus
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
              <div class="card-footer py-4">
                <nav aria-label="Pagination">
                  <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                      <a class="page-link" href="?page=<?= $page - 1 ?>" tabindex="-1">
                        <i class="fas fa-angle-left"></i> Previous
                      </a>
                    </li>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                      <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                      </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                      <a class="page-link" href="?page=<?= $page + 1 ?>">
                        Next <i class="fas fa-angle-right"></i>
                      </a>
                    </li>
                  </ul>
                </nav>
              </div>
            <?php endif; ?>
          </div> <!-- end table-responsive -->
        </div> <!-- end card-body -->
      </div> <!-- end card -->

      <!-- Cetak semua modal edit yang sudah dikumpulkan -->
      <?= $edit_modals ?>

      <!-- Modal Tambah Kelas -->
      <div class="modal fade" id="tambahKelas" tabindex="-1" aria-labelledby="tambahKelasLabel" aria-hidden="true">
        <div class="modal-dialog">
          <form method="POST" action="proses/tambah_kelas.php">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="tambahKelasLabel">Tambah Kelas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Nama Kelas</label>
                  <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: X RPL 1" required>
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
    </div> <!-- end container-fluid -->
  </main>

  <!-- Scripts -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>
</html>