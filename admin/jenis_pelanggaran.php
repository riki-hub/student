<?php
require '../koneksi.php';
session_start();

// Cek login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

// Proses hapus
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $hapus = mysqli_query($conn, "DELETE FROM jenis_pelanggaran WHERE id_jenis = $id");
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    header("Location: jenis_pelanggaran.php?page=$page&msg=" . ($hapus ? 'deleted' : 'error'));
    exit;
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jenis_pelanggaran");
$total = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total / $limit);

// Ambil data jenis pelanggaran
$query = mysqli_query($conn, "SELECT * FROM jenis_pelanggaran ORDER BY id_jenis DESC LIMIT $limit OFFSET $offset");

// Alert feedback
$alert = '';
if (isset($_GET['msg'])) {
    $messages = [
        'added'   => 'Jenis pelanggaran berhasil ditambahkan!',
        'updated' => 'Jenis pelanggaran berhasil diupdate!',
        'deleted' => 'Jenis pelanggaran berhasil dihapus!',
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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Master Jenis Pelanggaran - Material Dashboard</title>

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
          <h3 class="h4 font-weight-bolder mb-1">Master Jenis Pelanggaran</h3>
          <p class="text-sm text-muted mb-0">Kelola jenis-jenis pelanggaran dan poin pengurangannya</p>
        </div>
      </div>

      <?= $alert ?>

      <div class="card shadow-sm">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahJenis">
            <i class="fas fa-plus me-2"></i> Tambah Jenis
          </button>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-items-center mb-0 table-hover">
              <thead class="bg-light">
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">No</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Pelanggaran</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Poin Pengurangan</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                </tr>
              </thead>
                            <tbody>
                <?php
                $no = $offset + 1;
                // Simpan ID untuk modal edit nanti
                $edit_modals = '';
                while ($j = mysqli_fetch_assoc($query)):
                ?>
                  <tr>
                    <td class="ps-4"><span class="text-secondary text-xs"><?= $no++ ?></span></td>
                    <td><p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($j['nama_pelanggaran']) ?></p></td>
                    <td class="text-center">
                      <span class="badge bg-gradient-danger poin-badge">-<?= number_format($j['poin']) ?></span>
                    </td>
                    <td class="text-center table-actions py-3">
                      <!-- Tombol Edit -->
                      <button class="btn btn-warning btn-sm me-2 px-4" 
                              data-bs-toggle="modal" 
                              data-bs-target="#editJenis<?= $j['id_jenis'] ?>">
                          Edit
                      </button>

                      <!-- Tombol Hapus -->
                      <a href="?delete=<?= $j['id_jenis'] ?>&page=<?= $page ?>"
                         onclick="return confirm('Yakin menghapus jenis pelanggaran \"<?= htmlspecialchars($j['nama_pelanggaran']) ?>\"?')"
                         class="btn btn-danger btn-sm px-4">
                          Hapus
                      </a>
                    </td>
                  </tr>

                  <?php
                  // Simpan modal edit ke variabel (akan ditampilkan nanti di luar tabel)
                  $edit_modals .= '
                  <div class="modal fade" id="editJenis'. $j['id_jenis'] .'" tabindex="-1" aria-labelledby="editJenisLabel'. $j['id_jenis'] .'" aria-hidden="true">
                    <div class="modal-dialog">
                      <form method="POST" action="proses/edit_jenis.php">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="editJenisLabel'. $j['id_jenis'] .'">Edit Jenis Pelanggaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <input type="hidden" name="id_jenis" value="'. $j['id_jenis'] .'">
                            <input type="hidden" name="page" value="'. $page .'">

                            <div class="mb-3">
                              <label class="form-label">Nama Pelanggaran</label>
                              <input type="text" name="nama_pelanggaran" class="form-control"
                                     value="'. htmlspecialchars($j['nama_pelanggaran']) .'" required>
                            </div>

                            <div class="mb-3">
                              <label class="form-label">Poin Pengurangan</label>
                              <input type="number" name="poin" class="form-control"
                                     value="'. $j['poin'] .'" min="1" required>
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

      <!-- Semua Modal Edit diletakkan di sini, di luar tabel -->
      <?= $edit_modals ?>

      <!-- Modal Tambah Jenis Pelanggaran (sudah benar posisinya) -->
      <div class="modal fade" id="tambahJenis" tabindex="-1" aria-labelledby="tambahJenisLabel" aria-hidden="true">
        <div class="modal-dialog">
          <form method="POST" action="proses/tambah_jenis.php">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="tambahJenisLabel">Tambah Jenis Pelanggaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Nama Pelanggaran</label>
                  <input type="text" name="nama_pelanggaran" class="form-control"
                         placeholder="Contoh: Terlambat masuk kelas" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Poin Pengurangan</label>
                  <input type="number" name="poin" class="form-control" min="1" value="5" required>
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
