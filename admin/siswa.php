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
  $hapus = mysqli_query($conn, "DELETE FROM siswa WHERE id_siswa = $id");
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  header("Location: siswa.php?page=$page&msg=" . ($hapus ? 'deleted' : 'error'));
  exit;
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM siswa");
$total = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total / $limit);

// Ambil data siswa + nama kelas
$query = mysqli_query($conn, "
    SELECT s.*, k.nama_kelas 
    FROM siswa s 
    LEFT JOIN kelas k ON s.id_kelas = k.id_kelas 
    ORDER BY s.id_siswa DESC 
    LIMIT $limit OFFSET $offset
");

// Ambil semua kelas sekali untuk digunakan di modal tambah & edit
$kelas_result = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas");
$all_kelas = mysqli_fetch_all($kelas_result, MYSQLI_ASSOC);

// Alert feedback
$alert = '';
if (isset($_GET['msg'])) {
  $messages = [
    'added'   => 'Siswa berhasil ditambahkan!',
    'updated' => 'Data siswa berhasil diupdate!',
    'deleted' => 'Siswa berhasil dihapus!',
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
  <title>Master Siswa - Material Dashboard</title>

  <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" rel="stylesheet" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />
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
          <h3 class="h4 font-weight-bolder mb-1">Master Siswa</h3>
          <p class="text-sm text-muted mb-0">Kelola data siswa sekolah</p>
        </div>
      </div>

      <?= $alert ?>

      <div class="card shadow-sm">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahSiswa">
            <i class="fas fa-plus me-2"></i> Tambah Siswa
          </button>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-items-center mb-0 table-hover">
              <thead class="bg-light">
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">No</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">NIS</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Siswa</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">JK</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tgl Lahir</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kelas</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Poin Sisa</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = $offset + 1;
                $edit_modals = ''; // Kumpulkan semua modal edit di sini

                while ($s = mysqli_fetch_assoc($query)):
                  $jk = $s['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan';
                  $tgl_lahir = date('d-m-Y', strtotime($s['tanggal_lahir']));
                  $status_badge = $s['status'] == 'aktif' ? 'aktif' : 'nonaktif';

                  // Bangun modal edit
                  $edit_modals .= '
                  <div class="modal fade" id="editSiswa'. $s['id_siswa'] .'" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <form method="POST" action="proses/edit_siswa.php">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Edit Siswa - '. htmlspecialchars($s['nama_siswa']) .'</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <input type="hidden" name="id_siswa" value="'. $s['id_siswa'] .'">
                            <input type="hidden" name="page" value="'. $page .'">

                            <div class="row">
                              <div class="col-md-6 mb-3">
                                <label class="form-label">NIS</label>
                                <input type="text" name="nis" class="form-control" value="'. htmlspecialchars($s['nis']) .'" required>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Siswa</label>
                                <input type="text" name="nama_siswa" class="form-control" value="'. htmlspecialchars($s['nama_siswa']) .'" required>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select" required>
                                  <option value="L" '. ($s['jenis_kelamin'] == 'L' ? 'selected' : '') .'>Laki-laki</option>
                                  <option value="P" '. ($s['jenis_kelamin'] == 'P' ? 'selected' : '') .'>Perempuan</option>
                                </select>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control" value="'. $s['tanggal_lahir'] .'" required>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Kelas</label>
                                <select name="id_kelas" class="form-select">
                                  <option value="">-- Tanpa Kelas --</option>';
                                  foreach ($all_kelas as $k) {
                                    $selected = $s['id_kelas'] == $k['id_kelas'] ? 'selected' : '';
                                    $edit_modals .= '<option value="'. $k['id_kelas'] .'" '. $selected .'>'. htmlspecialchars($k['nama_kelas']) .'</option>';
                                  }
                  $edit_modals .= '
                                </select>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Poin Awal</label>
                                <input type="number" name="poin_awal" class="form-control" value="'. $s['poin_awal'] .'" min="0" required>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Poin Sisa</label>
                                <input type="number" name="poin_sisa" class="form-control" value="'. $s['poin_sisa'] .'" min="0" required>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                  <option value="aktif" '. ($s['status'] == 'aktif' ? 'selected' : '') .'>Aktif</option>
                                  <option value="nonaktif" '. ($s['status'] == 'nonaktif' ? 'selected' : '') .'>Nonaktif</option>
                                </select>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Password Baru <small class="text-muted">(Kosongkan jika tidak ubah)</small></label>
                                <input type="text" name="password" class="form-control" placeholder="Password baru">
                              </div>
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
                    <td><p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($s['nis']) ?></p></td>
                    <td><p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($s['nama_siswa']) ?></p></td>
                    <td><span class="text-xs"><?= $jk ?></span></td>
                    <td><span class="text-xs"><?= $tgl_lahir ?></span></td>
                    <td><span class="text-xs"><?= htmlspecialchars($s['nama_kelas'] ?? '-') ?></span></td>
                    <td><span class="text-xs font-weight-bold"><?= number_format($s['poin_sisa']) ?></span></td>
                    <td><span class="badge badge-sm badge-status-<?= $status_badge ?>"><?= ucfirst($s['status']) ?></span></td>
                    <td class="text-center table-actions py-3">
                      <button class="btn btn-warning btn-sm me-2 px-3" data-bs-toggle="modal" data-bs-target="#editSiswa<?= $s['id_siswa'] ?>">Edit</button>
                      <a href="?delete=<?= $s['id_siswa'] ?>&page=<?= $page ?>" 
                         onclick="return confirm('Yakin menghapus siswa <?= htmlspecialchars($s['nama_siswa']) ?>?')"
                         class="btn btn-danger btn-sm px-3">Hapus</a>
                    </td>
                  </tr>
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

      <!-- Semua Modal Edit Siswa -->
      <?= $edit_modals ?>

      <!-- Modal Tambah Siswa -->
      <div class="modal fade" id="tambahSiswa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <form method="POST" action="proses/tambah_siswa.php">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Tambah Siswa Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">NIS</label>
                    <input type="text" name="nis" class="form-control" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Siswa</label>
                    <input type="text" name="nama_siswa" class="form-control" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="text" name="password" class="form-control" placeholder="Masukkan password" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select" required>
                      <option value="L">Laki-laki</option>
                      <option value="P">Perempuan</option>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Kelas</label>
                    <select name="id_kelas" class="form-select">
                      <option value="">-- Tanpa Kelas --</option>
                      <?php foreach ($all_kelas as $k): ?>
                        <option value="<?= $k['id_kelas'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Poin Awal</label>
                    <input type="number" name="poin_awal" class="form-control" value="100" min="0" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Poin Sisa</label>
                    <input type="number" name="poin_sisa" class="form-control" value="100" min="0" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                      <option value="aktif">Aktif</option>
                      <option value="nonaktif">Nonaktif</option>
                    </select>
                  </div>
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

  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>
</html>