<?php
require '../koneksi.php';
session_start();

// Cek login
if (!isset($_SESSION['id_user'])) {
  header("Location: ../sign-in.php");
  exit;
}

// Proses hapus
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $hapus = mysqli_query($conn, "DELETE FROM master_sanksi WHERE id_sanksi = $id");
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  header("Location: sanksi.php?page=$page&msg=" . ($hapus ? 'deleted' : 'error'));
  exit;
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM master_sanksi");
$total = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total / $limit);

// Ambil data sanksi
$query = mysqli_query($conn, "SELECT * FROM master_sanksi ORDER BY batas_poin ASC, id_sanksi DESC LIMIT $limit OFFSET $offset");

// Alert feedback
$alert = '';
if (isset($_GET['msg'])) {
  $messages = [
    'added'   => 'Sanksi berhasil ditambahkan!',
    'updated' => 'Sanksi berhasil diupdate!',
    'deleted' => 'Sanksi berhasil dihapus!',
    'error'   => 'Terjadi kesalahan, silakan coba lagi.'
  ];
  $type = $_GET['msg'];
  $alertClass = ($type === 'error') ? 'danger' : 'success';
  if (isset($messages[$type])) {
    $alert = "<div class='alert alert-$alertClass alert-dismissible fade show' role='alert'>
                    {$messages[$type]}
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                  </div>";
  }
}
?>


<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Master Sanksi - Material Dashboard</title>

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
          <h3 class="h4 font-weight-bolder mb-1">Master Sanksi</h3>
          <p class="text-sm text-muted mb-0">Kelola jenis sanksi berdasarkan batas poin pelanggaran siswa</p>
        </div>
      </div>

      <?= $alert ?>

      <div class="card shadow-sm">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahSanksi">
            <i class="fas fa-plus me-2"></i> Tambah Sanksi
          </button>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-items-center mb-0 table-hover">
              <thead class="bg-light">
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">No</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Sanksi</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Batas Poin</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Deskripsi</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                </tr>
              </thead>
                           <tbody>
                <?php
                $no = $offset + 1;
                // Variabel untuk menampung semua modal edit
                $edit_modals = '';
                while ($s = mysqli_fetch_assoc($query)):
                ?>
                  <tr>
                    <td class="ps-4"><span class="text-secondary text-xs"><?= $no++ ?></span></td>
                    <td>
                      <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($s['nama_sanksi']) ?></p>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-gradient-warning batas-poin-badge"><?= number_format($s['batas_poin']) ?> poin</span>
                    </td>
                    <td>
                      <p class="text-xs deskripsi-text mb-0">
                        <?= htmlspecialchars(strlen($s['deskripsi']) > 80 ? substr($s['deskripsi'], 0, 80) . '...' : $s['deskripsi']) ?>
                      </p>
                    </td>
                    <td class="text-center table-actions py-3">
                      <!-- Tombol Edit -->
                      <button class="btn btn-warning btn-sm me-2 px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#editSanksi<?= $s['id_sanksi'] ?>">
                        Edit
                      </button>

                      <!-- Tombol Hapus -->
                      <a href="?delete=<?= $s['id_sanksi'] ?>&page=<?= $page ?>"
                        onclick="return confirm('Yakin menghapus sanksi \"<?= htmlspecialchars($s['nama_sanksi']) ?>\"?')"
                        class="btn btn-danger btn-sm px-4">
                        Hapus
                      </a>
                    </td>
                  </tr>

                  <?php
                  // Kumpulkan modal edit ke dalam variabel
                  $edit_modals .= '
                  <div class="modal fade" id="editSanksi'. $s['id_sanksi'] .'" tabindex="-1" aria-labelledby="editSanksiLabel'. $s['id_sanksi'] .'" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <form method="POST" action="proses/edit_sanksi.php">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="editSanksiLabel'. $s['id_sanksi'] .'">Edit Sanksi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <input type="hidden" name="id_sanksi" value="'. $s['id_sanksi'] .'">
                            <input type="hidden" name="page" value="'. $page .'">

                            <div class="row">
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Sanksi</label>
                                <input type="text" name="nama_sanksi" class="form-control"
                                  value="'. htmlspecialchars($s['nama_sanksi']) .'" required>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Batas Poin <small class="text-muted">(minimal poin untuk sanksi ini)</small></label>
                                <input type="number" name="batas_poin" class="form-control"
                                  value="'. $s['batas_poin'] .'" min="0" required>
                              </div>
                              <div class="col-12 mb-3">
                                <label class="form-label">Deskripsi Sanksi</label>
                                <textarea name="deskripsi" class="form-control" rows="4" required>'. htmlspecialchars($s['deskripsi']) .'</textarea>
                              </div>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="edit" class="btn btn-success">
                              Update
                            </button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>';
                  ?>

                <?php endwhile; ?>
              </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
              <div class="card-footer py-4">
                <nav>
                  <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                      <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                      <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                      </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                      <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                  </ul>
                </nav>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Tempatkan semua Modal Edit di sini (di luar tabel) -->
      <?= $edit_modals ?>

      <!-- Modal Tambah Sanksi (sudah benar posisinya) -->
      <div class="modal fade" id="tambahSanksi" tabindex="-1" aria-labelledby="tambahSanksiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <form method="POST" action="proses/tambah_sanksi.php">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="tambahSanksiLabel">Tambah Sanksi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Sanksi</label>
                    <input type="text" name="nama_sanksi" class="form-control"
                      placeholder="Contoh: Teguran Lisan" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Batas Poin</label>
                    <input type="number" name="batas_poin" class="form-control" min="0" value="10" required>
                    <small class="text-muted">Sanksi diberikan jika poin siswa ≤ nilai ini</small>
                  </div>
                  <div class="col-12 mb-3">
                    <label class="form-label">Deskripsi Sanksi</label>
                    <textarea name="deskripsi" class="form-control" rows="5" placeholder="Jelaskan detail sanksi yang akan diberikan..." required></textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="tambah" class="btn btn-success">
                  Simpan
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <!-- Scripts -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>



