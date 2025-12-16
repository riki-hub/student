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
  
  // Ambil data pelanggaran untuk mengembalikan poin
  $get_pelang = mysqli_query($conn, "SELECT id_siswa, poin_berkurang FROM pelanggaran WHERE id_pelanggaran = $id");
  $pelang = mysqli_fetch_assoc($get_pelang);
  
  if ($pelang) {
    // Kembalikan poin ke siswa
    mysqli_query($conn, "UPDATE siswa SET poin_sisa = poin_sisa + {$pelang['poin_berkurang']} WHERE id_siswa = {$pelang['id_siswa']}");
    
    // Hapus pelanggaran
    $hapus = mysqli_query($conn, "DELETE FROM pelanggaran WHERE id_pelanggaran = $id");
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    header("Location: pelanggaran.php?page=$page&msg=" . ($hapus ? 'deleted' : 'error'));
  } else {
    header("Location: pelanggaran.php?msg=error");
  }
  exit;
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pelanggaran");
$total = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total / $limit);

// Ambil data pelanggaran + JOIN tabel siswa, jenis_pelanggaran, dan user
$query = mysqli_query($conn, "
    SELECT 
        p.*,
        s.nama_siswa,
        s.nis,
        jp.nama_pelanggaran,
        jp.poin AS poin_jenis,
        u.nama_lengkap
    FROM pelanggaran p
    LEFT JOIN siswa s 
        ON p.id_siswa = s.id_siswa
    LEFT JOIN jenis_pelanggaran jp 
        ON p.id_jenis = jp.id_jenis
    LEFT JOIN `users` u 
        ON p.id_user = u.id_user
    ORDER BY p.tanggal DESC, p.id_pelanggaran DESC
    LIMIT $limit OFFSET $offset
");


// Ambil data untuk dropdown modal
$siswa_result = mysqli_query($conn, "SELECT id_siswa, nis, nama_siswa FROM siswa WHERE status = 'aktif' ORDER BY nama_siswa");
$all_siswa = mysqli_fetch_all($siswa_result, MYSQLI_ASSOC);

$jenis_result = mysqli_query($conn, "SELECT id_jenis, nama_pelanggaran, poin FROM jenis_pelanggaran ORDER BY nama_pelanggaran");
$all_jenis = mysqli_fetch_all($jenis_result, MYSQLI_ASSOC);

// Alert feedback
$alert = '';
if (isset($_GET['msg'])) {
  $messages = [
    'added'   => 'Pelanggaran berhasil ditambahkan!',
    'updated' => 'Data pelanggaran berhasil diupdate!',
    'deleted' => 'Pelanggaran berhasil dihapus!',
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
  <title>Master Pelanggaran - Material Dashboard</title>

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
      font-size: 0.9em;
      padding: 0.4em 0.8em;
    }

    /* Preview bukti gambar */
    .bukti-preview {
      max-width: 60px;
      max-height: 60px;
      object-fit: cover;
      border-radius: 6px;
      cursor: pointer;
    }

    /* === EFEK GELAP SAAT MODAL TERBUKA === */
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
    .modal .form-control, .modal .form-select {
      background-color: #ffffff;
      border: 2px solid #d1d5db;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 14px;
      color: #344767;
      transition: all 0.2s ease;
    }

    .modal .form-control:hover, .modal .form-select:hover {
      border-color: #5e72e4;
    }

    .modal .form-control:focus, .modal .form-select:focus {
      border-color: #5e72e4;
      box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.15);
      outline: none;
    }

    .modal .form-control::placeholder {
      color: #9ca3af;
    }

    .badge-status-aktif {
      background-color: #4caf50;
      color: white;
    }

    .badge-status-nonaktif {
      background-color: #f44336;
      color: white;
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
          <h3 class="h4 font-weight-bolder mb-1">Master Pelanggaran</h3>
          <p class="text-sm text-muted mb-0">Kelola data pelanggaran siswa</p>
        </div>
      </div>

      <?= $alert ?>


        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-items-center mb-0 table-hover">
              <thead class="bg-light">
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">No</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Siswa</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Jenis Pelanggaran</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Poin</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pelapor</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Bukti</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = $offset + 1;
                $edit_modals = ''; // Kumpulkan semua modal edit di sini

                while ($p = mysqli_fetch_assoc($query)):
                  $tgl = date('d/m/Y', strtotime($p['tanggal']));
                  $bukti_display = '-';
                  if (!empty($p['bukti'])) {
                    $bukti_display = "<img src='../uploads/{$p['bukti']}' class='bukti-preview' 
                                       onclick=\"window.open(this.src, '_blank')\" 
                                       title='Klik untuk memperbesar'>";
                  }

                  // Bangun modal edit
                  $edit_modals .= '
                  <div class="modal fade" id="editPelanggaran'. $p['id_pelanggaran'] .'" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <form method="POST" action="proses/edit_pelanggaran.php" enctype="multipart/form-data">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Edit Pelanggaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <input type="hidden" name="id_pelanggaran" value="'. $p['id_pelanggaran'] .'">
                            <input type="hidden" name="page" value="'. $page .'">
                            <input type="hidden" name="bukti_lama" value="'. $p['bukti'] .'">

                            <div class="row">
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="'. $p['tanggal'] .'" required>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Siswa</label>
                                <select name="id_siswa" class="form-select" required>
                                  <option value="">-- Pilih Siswa --</option>';
                                  foreach ($all_siswa as $s) {
                                    $selected = $p['id_siswa'] == $s['id_siswa'] ? 'selected' : '';
                                    $edit_modals .= '<option value="'. $s['id_siswa'] .'" '. $selected .'>'. htmlspecialchars($s['nis']) .' - '. htmlspecialchars($s['nama_siswa']) .'</option>';
                                  }
                  $edit_modals .= '
                                </select>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Pelanggaran</label>
                                <select name="id_jenis" class="form-select" id="editJenis'. $p['id_pelanggaran'] .'" onchange="updatePoinEdit'. $p['id_pelanggaran'] .'()" required>
                                  <option value="">-- Pilih Jenis --</option>';
                                  foreach ($all_jenis as $j) {
                                    $selected = $p['id_jenis'] == $j['id_jenis'] ? 'selected' : '';
                                    $edit_modals .= '<option value="'. $j['id_jenis'] .'" data-poin="'. $j['poin'] .'" '. $selected .'>'. htmlspecialchars($j['nama_pelanggaran']) .' ('. $j['poin'] .' poin)</option>';
                                  }
                  $edit_modals .= '
                                </select>
                              </div>
                              <div class="col-md-6 mb-3">
                                <label class="form-label">Poin Berkurang</label>
                                <input type="number" name="poin_berkurang" id="editPoin'. $p['id_pelanggaran'] .'" class="form-control" value="'. $p['poin_berkurang'] .'" min="0" required>
                              </div>
                              <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3">'. htmlspecialchars($p['keterangan']) .'</textarea>
                              </div>
                              <div class="col-md-12 mb-3">
                                <label class="form-label">Bukti (Upload baru jika ingin mengganti)</label>
                                <input type="file" name="bukti" class="form-control" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, JPEG (Max 2MB)</small>';
                  if (!empty($p['bukti'])) {
                    $edit_modals .= '<div class="mt-2">
                                      <img src="../uploads/bukti/'. $p['bukti'] .'" style="max-width: 150px; border-radius: 8px;">
                                      <p class="text-xs mt-1">Bukti saat ini</p>
                                    </div>';
                  }
                  $edit_modals .= '
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
                  </div>
                  
                  <script>
                  function updatePoinEdit'. $p['id_pelanggaran'] .'() {
                    var select = document.getElementById("editJenis'. $p['id_pelanggaran'] .'");
                    var poinInput = document.getElementById("editPoin'. $p['id_pelanggaran'] .'");
                    var selectedOption = select.options[select.selectedIndex];
                    var poin = selectedOption.getAttribute("data-poin");
                    if (poin) {
                      poinInput.value = poin;
                    }
                  }
                  </script>';
                ?>
                  <tr>
                    <td class="ps-4"><span class="text-secondary text-xs"><?= $no++ ?></span></td>
                    <td><p class="text-xs font-weight-bold mb-0"><?= $tgl ?></p></td>
                    <td>
                      <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($p['nama_siswa'] ?? '-') ?></p>
                      <span class="text-xxs text-muted"><?= htmlspecialchars($p['nis'] ?? '-') ?></span>
                    </td>
                    <td><p class="text-xs mb-0"><?= htmlspecialchars($p['nama_pelanggaran'] ?? '-') ?></p></td>
                    <td><span class="text-xs font-weight-bold mb-0">-<?= $p['poin_berkurang'] ?></span></td>
                    <td><span class="text-xs"><?= htmlspecialchars($p['nama_lengkap'] ?? '-') ?></span></td>
                    <td><?= $bukti_display ?></td>
                    <td class="text-center table-actions py-3">
                      <button class="btn btn-warning btn-sm me-2 px-3" data-bs-toggle="modal" data-bs-target="#editPelanggaran<?= $p['id_pelanggaran'] ?>">Edit</button>
                      <a href="?delete=<?= $p['id_pelanggaran'] ?>&page=<?= $page ?>" 
                         onclick="return confirm('Yakin menghapus pelanggaran ini? Poin akan dikembalikan ke siswa.')"
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

      <!-- Semua Modal Edit -->
      <?= $edit_modals ?>
    </div>
  </main>

  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  
  <script>
    function updatePoin() {
      var select = document.getElementById('jenisSelect');
      var poinInput = document.getElementById('poinInput');
      var selectedOption = select.options[select.selectedIndex];
      var poin = selectedOption.getAttribute('data-poin');
      
      if (poin) {
        poinInput.value = poin;
      }
    }
  </script>
</body>
</html>