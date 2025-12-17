<?php
session_start();
require '../koneksi.php';

// Wajib login sebagai siswa
if (!isset($_SESSION['siswa_id'])) {
  header("Location: login.php");
  exit;
}

$idSiswa = (int)$_SESSION['siswa_id'];

// Ambil data profil siswa
$profilStmt = $conn->prepare("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id_kelas WHERE s.id_siswa = ? LIMIT 1");
$profilStmt->bind_param("i", $idSiswa);
$profilStmt->execute();
$profilRes = $profilStmt->get_result();
$siswa = $profilRes->fetch_assoc();
$profilStmt->close();

if (!$siswa) {
  session_unset();
  session_destroy();
  header("Location: login.php?msg=notfound");
  exit;
}

$poinAwal = (int)$siswa['poin_awal'];
$poinSisa = (int)$siswa['poin_sisa'];
$poinTerpakai = max(0, $poinAwal - $poinSisa);
$poinPersen = $poinAwal > 0 ? max(0, min(100, round(($poinSisa / $poinAwal) * 100))) : 0;
$jkLabel = $siswa['jenis_kelamin'] === 'P' ? 'Perempuan' : ($siswa['jenis_kelamin'] === 'L' ? 'Laki-laki' : '-');
$tglLahir = !empty($siswa['tanggal_lahir']) ? date('d M Y', strtotime($siswa['tanggal_lahir'])) : '-';
$kelas = $siswa['nama_kelas'] ?: '-';
$statusSiswa = strtolower($siswa['status']) === 'aktif' ? 'aktif' : 'nonaktif';

// Ambil riwayat pelanggaran siswa
$pelanggaran = [];
$histStmt = $conn->prepare("SELECT p.id_pelanggaran, p.tanggal, p.keterangan, p.bukti, p.poin_berkurang, jp.nama_pelanggaran, jp.poin AS poin_jenis, u.nama_lengkap AS petugas, u.role AS role_petugas FROM pelanggaran p LEFT JOIN jenis_pelanggaran jp ON p.id_jenis = jp.id_jenis LEFT JOIN users u ON p.id_user = u.id_user WHERE p.id_siswa = ? ORDER BY p.tanggal DESC, p.id_pelanggaran DESC");
$histStmt->bind_param("i", $idSiswa);
$histStmt->execute();
$histRes = $histStmt->get_result();
while ($row = $histRes->fetch_assoc()) {
  $pelanggaran[] = $row;
}
$histStmt->close();
$totalPelanggaran = count($pelanggaran);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>Dashboard Siswa - Sistem Poin</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />

  <style>
    * {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    body {
      background: #f8fafc;
    }

    .main-content {
      padding: 0;
    }

    /* Header Section */
    .header-section {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 2rem 0 8rem 0;
      position: relative;
      overflow: hidden;
    }

    .header-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
      opacity: 0.4;
    }

    .header-content {
      position: relative;
      z-index: 1;
    }

    .user-welcome {
      color: rgba(255, 255, 255, 0.95);
    }

    .user-welcome h2 {
      font-size: 1.75rem;
      font-weight: 700;
      margin: 0.5rem 0;
      color: white;
    }

    .user-welcome .subtitle {
      font-size: 0.95rem;
      opacity: 0.9;
      font-weight: 500;
    }

    .avatar-modern {
      width: 60px;
      height: 60px;
      border-radius: 20px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: grid;
      place-items: center;
      font-weight: 800;
      font-size: 24px;
      color: white;
      box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
      border: 3px solid rgba(255, 255, 255, 0.3);
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-size: 0.875rem;
      font-weight: 600;
      backdrop-filter: blur(10px);
    }

    .status-badge.aktif {
      background: rgba(255, 255, 255, 0.25);
      color: white;
    }

    .status-badge.nonaktif {
      background: rgba(239, 68, 68, 0.25);
      color: white;
    }

    .status-badge i {
      font-size: 1rem;
    }

    .btn-logout {
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.3);
      padding: 0.65rem 1.5rem;
      border-radius: 12px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-logout:hover {
      background: rgba(255, 255, 255, 0.3);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    /* Cards Container */
    .cards-container {
      margin-top: -5rem;
      position: relative;
      z-index: 2;
    }

    /* Stats Cards */
    .stat-card {
      background: white;
      border-radius: 20px;
      padding: 1.75rem;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(0, 0, 0, 0.04);
      transition: all 0.3s ease;
      height: 100%;
    }

    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
      width: 56px;
      height: 56px;
      border-radius: 16px;
      display: grid;
      place-items: center;
      font-size: 24px;
      margin-bottom: 1rem;
    }

    .stat-icon.primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .stat-icon.success {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: white;
    }

    .stat-icon.warning {
      background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
      color: #d97706;
    }

    .stat-label {
      font-size: 0.875rem;
      color: #64748b;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.5rem;
    }

    .stat-value {
      font-size: 2rem;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 1rem;
    }

    .stat-description {
      font-size: 0.875rem;
      color: #94a3b8;
      font-weight: 500;
    }

    .progress-modern {
      height: 8px;
      border-radius: 50px;
      background: #e2e8f0;
      overflow: hidden;
      margin: 1rem 0 0.5rem 0;
    }

    .progress-modern .progress-bar {
      background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
      border-radius: 50px;
      transition: width 1s ease;
    }

    /* Content Cards */
    .content-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(0, 0, 0, 0.04);
      overflow: hidden;
    }

    .card-header-modern {
      padding: 1.75rem;
      border-bottom: 1px solid #f1f5f9;
      background: #fafbfc;
    }

    .card-header-modern h6 {
      font-size: 1.125rem;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 0.25rem;
    }

    .card-header-modern .subtitle {
      font-size: 0.875rem;
      color: #64748b;
      margin: 0;
    }

    .card-body-modern {
      padding: 1.75rem;
    }

    /* Profile Info */
    .profile-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 0;
      border-bottom: 1px solid #f1f5f9;
    }

    .profile-item:last-child {
      border-bottom: none;
    }

    .profile-label {
      font-size: 0.875rem;
      color: #64748b;
      font-weight: 600;
    }

    .profile-value {
      font-size: 0.9375rem;
      color: #0f172a;
      font-weight: 700;
    }

    /* Table */
    .table-modern {
      width: 100%;
      font-size: 0.875rem;
    }

    .table-modern thead th {
      background: #f8fafc;
      color: #64748b;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.5px;
      padding: 1rem;
      border-bottom: 2px solid #e2e8f0;
    }

    .table-modern tbody td {
      padding: 1.25rem 1rem;
      vertical-align: middle;
      border-bottom: 1px solid #f1f5f9;
      color: #475569;
    }

    .table-modern tbody tr:hover {
      background: #fafbfc;
    }

    .violation-name {
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 0.25rem;
    }

    .violation-id {
      font-size: 0.75rem;
      color: #94a3b8;
    }

    .petugas-name {
      font-weight: 600;
      color: #0f172a;
      margin-bottom: 0.25rem;
    }

    .petugas-role {
      font-size: 0.75rem;
      color: #94a3b8;
      text-transform: capitalize;
    }

    .badge-poin {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-weight: 700;
      font-size: 0.875rem;
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: white;
    }

    .badge-count {
      display: inline-flex;
      align-items: center;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      background: #667eea;
      color: white;
      font-weight: 700;
      font-size: 0.875rem;
    }

    .link-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: #f1f5f9;
      color: #667eea;
      transition: all 0.3s ease;
    }

    .link-icon:hover {
      background: #667eea;
      color: white;
      transform: scale(1.1);
    }

    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: #94a3b8;
    }

    .empty-state i {
      font-size: 4rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    /* Responsive */
    @media (max-width: 991px) {
      .header-section {
        padding: 1.5rem 0 6rem 0;
      }

      .cards-container {
        margin-top: -4rem;
      }

      .user-welcome h2 {
        font-size: 1.5rem;
      }
    }

    @media (max-width: 767px) {
      .stat-value {
        font-size: 1.75rem;
      }

      .table-responsive {
        border-radius: 12px;
      }

      .table-modern {
        font-size: 0.8125rem;
      }

      .table-modern thead th {
        padding: 0.75rem;
        font-size: 0.7rem;
      }

      .table-modern tbody td {
        padding: 1rem 0.75rem;
      }

      .avatar-modern {
        width: 50px;
        height: 50px;
        font-size: 20px;
      }
    }
  </style>
</head>

<body>
  <main class="main-content">
    <!-- Header Section -->
    <section class="header-section">
      <div class="container-fluid px-4 header-content">
        <div class="row align-items-center">
          <div class="col-lg-8 col-md-7 col-12">
            <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
              <div class="avatar-modern text-uppercase">
                <?= htmlspecialchars(substr($siswa['nama_siswa'], 0, 2)) ?>
              </div>
              <div class="user-welcome">
                <p class="subtitle mb-1">Selamat datang kembali,</p>
                <h2><?= htmlspecialchars($siswa['nama_siswa']) ?></h2>
                <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                  <span class="status-badge <?= $statusSiswa ?>">
                    <i class="material-symbols-rounded" style="font-size: 16px;">
                      <?= $statusSiswa === 'aktif' ? 'check_circle' : 'cancel' ?>
                    </i>
                    Status <?= ucfirst($statusSiswa) ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-5 col-12 text-md-end">
            <a href="logout.php" class="btn btn-logout">
              <i class="material-symbols-rounded align-middle me-2" style="font-size: 18px;">logout</i>
              Keluar
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <div class="container-fluid px-4 cards-container">
      <!-- Stats Cards -->
      <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6">
          <div class="stat-card">
            <div class="stat-icon primary">
              <i class="material-symbols-rounded">account_balance_wallet</i>
            </div>
            <div class="stat-label">Poin Sisa</div>
            <div class="stat-value"><?= number_format($poinSisa) ?></div>
            <div class="progress-modern">
              <div class="progress-bar" role="progressbar" style="width: <?= $poinPersen ?>%;" aria-valuenow="<?= $poinPersen ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="stat-description"><?= $poinPersen ?>% dari poin awal (<?= number_format($poinAwal) ?>)</div>
          </div>
        </div>

        <div class="col-xl-4 col-md-6">
          <div class="stat-card">
            <div class="stat-icon success">
              <i class="material-symbols-rounded">military_tech</i>
            </div>
            <div class="stat-label">Poin Awal</div>
            <div class="stat-value"><?= number_format($poinAwal) ?></div>
            <div class="stat-description">Total poin yang diberikan sekolah</div>
          </div>
        </div>

        <div class="col-xl-4 col-md-6">
          <div class="stat-card">
            <div class="stat-icon warning">
              <i class="material-symbols-rounded">warning</i>
            </div>
            <div class="stat-label">Total Pelanggaran</div>
            <div class="stat-value"><?= $totalPelanggaran ?></div>
            <div class="stat-description">Poin berkurang: <?= number_format($poinTerpakai) ?></div>
          </div>
        </div>
      </div>

      <!-- Content Cards -->
      <div class="row g-4 pb-4">
        <!-- Profile Card -->
        <div class="col-xl-4 col-lg-5">
          <div class="content-card h-100">
            <div class="card-header-modern">
              <h6>Profil Siswa</h6>
              <p class="subtitle">Data identitas terbaru</p>
            </div>
            <div class="card-body-modern">
              <div class="profile-item">
                <span class="profile-label">Nama Lengkap</span>
                <span class="profile-value"><?= htmlspecialchars($siswa['nama_siswa']) ?></span>
              </div>
              <div class="profile-item">
                <span class="profile-label">NIS</span>
                <span class="profile-value"><?= htmlspecialchars($siswa['nis']) ?></span>
              </div>
              <div class="profile-item">
                <span class="profile-label">Kelas</span>
                <span class="profile-value"><?= htmlspecialchars($kelas) ?></span>
              </div>
              <div class="profile-item">
                <span class="profile-label">Jenis Kelamin</span>
                <span class="profile-value"><?= htmlspecialchars($jkLabel) ?></span>
              </div>
              <div class="profile-item">
                <span class="profile-label">Tanggal Lahir</span>
                <span class="profile-value"><?= htmlspecialchars($tglLahir) ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Violation History Card -->
        <div class="col-xl-8 col-lg-7">
          <div class="content-card h-100">
            <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3">
              <div>
                <h6>Riwayat Pelanggaran</h6>
                <p class="subtitle">Catatan poin yang pernah dikurangi</p>
              </div>
              <span class="badge-count">
                <i class="material-symbols-rounded me-1" style="font-size: 16px;">format_list_numbered</i>
                <?= $totalPelanggaran ?> Catatan
              </span>
            </div>
            <div class="card-body-modern">
              <?php if ($totalPelanggaran === 0): ?>
                <div class="empty-state">
                  <i class="material-symbols-rounded">sentiment_satisfied</i>
                  <p class="mb-0">Belum ada pelanggaran tercatat.<br><small>Pertahankan prestasi yang baik!</small></p>
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table-modern">
                    <thead>
                      <tr>
                        <th>Tanggal</th>
                        <th>Pelanggaran</th>
                        <th class="text-center">Poin</th>
                        <th>Petugas</th>
                        <th>Keterangan</th>
                        <th class="text-center">Bukti</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($pelanggaran as $row): ?>
                        <tr>
                          <td>
                            <div style="white-space: nowrap;">
                              <?= htmlspecialchars(date('d M Y', strtotime($row['tanggal']))) ?>
                            </div>
                          </td>
                          <td>
                            <div class="violation-name"><?= htmlspecialchars($row['nama_pelanggaran'] ?? 'Pelanggaran') ?></div>
                            <div class="violation-id">ID: <?= (int)$row['id_pelanggaran'] ?></div>
                          </td>
                          <td class="text-center">
                            <span class="badge-poin">
                              <i class="material-symbols-rounded" style="font-size: 14px;">remove</i>
                              <?= (int)$row['poin_berkurang'] ?>
                            </span>
                          </td>
                          <td>
                            <div class="petugas-name"><?= htmlspecialchars($row['petugas'] ?: 'Petugas') ?></div>
                            <div class="petugas-role"><?= htmlspecialchars($row['role_petugas'] ?: '-') ?></div>
                          </td>
                          <td>
                            <div style="max-width: 200px; white-space: normal;">
                              <?= htmlspecialchars($row['keterangan'] ?: '-') ?>
                            </div>
                          </td>
                          <td class="text-center">
                            <?php if (!empty($row['bukti'])): ?>
                              <a href="../uploads/<?= htmlspecialchars($row['bukti']) ?>" target="_blank" class="link-icon" title="Lihat bukti">
                                <i class="material-symbols-rounded" style="font-size: 18px;">link</i>
                              </a>
                            <?php else: ?>
                              <span style="color: #cbd5e1;">-</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
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

  <script>
    // Animate progress bar on load
    window.addEventListener('load', function() {
      const progressBar = document.querySelector('.progress-bar');
      if (progressBar) {
        const width = progressBar.style.width;
        progressBar.style.width = '0%';
        setTimeout(() => {
          progressBar.style.width = width;
        }, 100);
      }
    });
  </script>
</body>

</html>