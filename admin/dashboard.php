<?php
require '../koneksi.php';
session_start();

// Cek login
if (!isset($_SESSION['id_user'])) {
  header("Location: ../sign-in.php");
  exit;
}

// Ambil data statistik
// Total Admin (dari tabel user berdasarkan role admin)
$query_admin = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$total_admin = mysqli_fetch_assoc($query_admin)['total'];

// Total Siswa (dari tabel siswa yang aktif)
$query_siswa = mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa WHERE status = 'aktif'");
$total_siswa = mysqli_fetch_assoc($query_siswa)['total'];

// Total Siswa bulan lalu untuk perbandingan
$bulan_lalu = date('Y-m-d', strtotime('-1 month'));
$query_siswa_lalu = mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa WHERE status = 'aktif'");
$total_siswa_lalu = mysqli_fetch_assoc($query_siswa_lalu)['total'];
$persentase_siswa = $total_siswa_lalu > 0 ? round((($total_siswa - $total_siswa_lalu) / $total_siswa_lalu) * 100, 1) : 0;

// Total Pelanggaran
$query_pelanggaran = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggaran");
$total_pelanggaran = mysqli_fetch_assoc($query_pelanggaran)['total'];

// Total Pelanggaran bulan ini
$bulan_ini = date('Y-m');
$query_pelanggaran_bulan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggaran WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'");
$total_pelanggaran_bulan = mysqli_fetch_assoc($query_pelanggaran_bulan)['total'];

// Total Pelanggaran bulan lalu untuk perbandingan
$bulan_lalu_format = date('Y-m', strtotime('-1 month'));
$query_pelanggaran_lalu = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggaran WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_lalu_format'");
$total_pelanggaran_lalu = mysqli_fetch_assoc($query_pelanggaran_lalu)['total'];
$persentase_pelanggaran = $total_pelanggaran_lalu > 0 ? round((($total_pelanggaran_bulan - $total_pelanggaran_lalu) / $total_pelanggaran_lalu) * 100, 1) : 0;

// Total Guru/Staff (role selain admin dan siswa)
$query_guru = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role IN ('guru', 'admin')");
$total_guru = mysqli_fetch_assoc($query_guru)['total'];

// =============================
// AMBIL FILTER DARI FORM
// =============================
$bulan_awal  = isset($_GET['bulan_awal'])  ? (int)$_GET['bulan_awal']  : 1;
$bulan_akhir = isset($_GET['bulan_akhir']) ? (int)$_GET['bulan_akhir'] : 12;
$tahun       = isset($_GET['tahun'])       ? (int)$_GET['tahun']       : date('Y');

// Validasi sederhana
if ($bulan_awal > $bulan_akhir) {
    $bulan_awal = 1;
    $bulan_akhir = 12;
}

// =============================
// QUERY UNTUK GRAFIK PER BULAN
// =============================
$sql_grafik = "
    SELECT 
        MONTH(tanggal) AS bulan,
        COUNT(*) AS total
    FROM pelanggaran
    WHERE 
        YEAR(tanggal) = $tahun
        AND MONTH(tanggal) BETWEEN $bulan_awal AND $bulan_akhir
    GROUP BY MONTH(tanggal)
    ORDER BY MONTH(tanggal)
";

$result_grafik = mysqli_query($conn, $sql_grafik);

// Siapkan data untuk grafik
$label = [];
$data  = [];

$nama_bulan = [
    1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 
    5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
    9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
];

if (mysqli_num_rows($result_grafik) > 0) {
    while ($row = mysqli_fetch_assoc($result_grafik)) {
        $label[] = $nama_bulan[$row['bulan']];
        $data[]  = (int)$row['total'];
    }
} else {
    $label = ['Belum ada data'];
    $data  = [0];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>Dashboard - Material Dashboard</title>
  
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  
  <style>
    .filter-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
    }
    
    .filter-card h6 {
      color: white;
      margin-bottom: 15px;
      font-weight: 600;
    }
    
    .filter-box {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
    }
    
    .filter-box select,
    .filter-box button {
      padding: 8px 15px;
      border-radius: 8px;
      border: none;
      font-size: 14px;
    }
    
    .filter-box select {
      background: white;
      color: #344767;
      cursor: pointer;
    }
    
    .filter-box button {
      background: #1a73e8;
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .filter-box button:hover {
      background: #1557b0;
      transform: translateY(-2px);
    }
    
    .filter-box span {
      color: white;
      font-weight: 500;
    }
  </style>
</head>

<body class="g-sidenav-show bg-gray-100">
  <?php include "sidebar.php"; ?>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include "nav.php"; ?>

    <div class="container-fluid py-2">
      <div class="row">
        <div class="ms-3">
          <h3 class="mb-0 h4 font-weight-bolder">Dashboard</h3>
          <p class="mb-4">Statistik dan ringkasan sistem poin pelanggaran siswa</p>
        </div>

        <!-- Card Total Admin -->
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Total Admin</p>
                  <h4 class="mb-0"><?= number_format($total_admin) ?></h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">admin_panel_settings</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm">
                <span class="text-secondary font-weight-bolder">Admin aktif </span>
                dalam sistem
              </p>
            </div>
          </div>
        </div>

        <!-- Card Total Siswa -->
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Total Siswa</p>
                  <h4 class="mb-0"><?= number_format($total_siswa) ?></h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-primary shadow-primary shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">person</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm">
                <?php if ($persentase_siswa > 0): ?>
                  <span class="text-success font-weight-bolder">+<?= abs($persentase_siswa) ?>% </span>dari bulan lalu
                <?php elseif ($persentase_siswa < 0): ?>
                  <span class="text-danger font-weight-bolder"><?= $persentase_siswa ?>% </span>dari bulan lalu
                <?php else: ?>
                  <span class="text-secondary font-weight-bolder">Tidak ada perubahan</span>
                <?php endif; ?>
              </p>
            </div>
          </div>
        </div>

        <!-- Card Total Pelanggaran -->
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Total Pelanggaran</p>
                  <h4 class="mb-0"><?= number_format($total_pelanggaran) ?></h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-danger shadow-danger shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">warning</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm">
                <?php if ($persentase_pelanggaran > 0): ?>
                  <span class="text-danger font-weight-bolder">+<?= abs($persentase_pelanggaran) ?>% </span>dari bulan lalu
                <?php elseif ($persentase_pelanggaran < 0): ?>
                  <span class="text-success font-weight-bolder"><?= $persentase_pelanggaran ?>% </span>dari bulan lalu
                <?php else: ?>
                  <span class="text-secondary font-weight-bolder">Tidak ada perubahan</span>
                <?php endif; ?>
              </p>
            </div>
          </div>
        </div>

        <!-- Card Total Guru/Staff -->
        <div class="col-xl-3 col-sm-6">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Total Guru/Staff</p>
                  <h4 class="mb-0"><?= number_format($total_guru) ?></h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-success shadow-success shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">school</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm">
                <span class="text-secondary font-weight-bolder">Guru & staff </span>
                terdaftar
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Grafik -->
      <div class="row mt-4">
        <div class="col-lg-12">
          <div class="filter-card">
            <h6><i class="material-symbols-rounded" style="vertical-align: middle;">tune</i> Filter Grafik Pelanggaran</h6>
            <form method="GET" class="filter-box">
              <span>Periode:</span>
              <select name="bulan_awal">
                <?php
                $bulan_short = [
                  1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'Mei', 6=>'Jun',
                  7=>'Jul', 8=>'Agu', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des'
                ];
                foreach ($bulan_short as $key => $value) {
                  $selected = ($key == $bulan_awal) ? 'selected' : '';
                  echo "<option value='$key' $selected>$value</option>";
                }
                ?>
              </select>

              <span>s/d</span>

              <select name="bulan_akhir">
                <?php
                foreach ($bulan_short as $key => $value) {
                  $selected = ($key == $bulan_akhir) ? 'selected' : '';
                  echo "<option value='$key' $selected>$value</option>";
                }
                ?>
              </select>

              <select name="tahun">
                <?php
                for ($i = date('Y') - 5; $i <= date('Y'); $i++) {
                  $selected = ($i == $tahun) ? 'selected' : '';
                  echo "<option value='$i' $selected>$i</option>";
                }
                ?>
              </select>

              <button type="submit">
                <i class="material-symbols-rounded" style="font-size: 18px; vertical-align: middle;">search</i>
                Tampilkan
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Grafik Pelanggaran -->
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Grafik Pelanggaran Per Bulan</h6>
              <p class="text-sm">Periode: <?= $nama_bulan[$bulan_awal] ?> - <?= $nama_bulan[$bulan_akhir] ?> <?= $tahun ?></p>
            </div>
            <div class="card-body">
              <canvas id="grafikPelanggaran" style="max-height: 400px;"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabel Pelanggaran Terbaru -->
      <div class="row mt-4">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Pelanggaran Terbaru</h6>
              <p class="text-sm">10 Data pelanggaran terakhir</p>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Siswa</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pelanggaran</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Poin</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pelapor</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $query_recent = mysqli_query($conn, "
                      SELECT p.*, 
                             s.nama_siswa, s.nis,
                             jp.nama_pelanggaran,
                             u.nama_lengkap
                      FROM pelanggaran p
                      LEFT JOIN siswa s ON p.id_siswa = s.id_siswa
                      LEFT JOIN jenis_pelanggaran jp ON p.id_jenis = jp.id_jenis
                      LEFT JOIN users u ON p.id_user = u.id_user
                      ORDER BY p.tanggal DESC, p.id_pelanggaran DESC
                      LIMIT 10
                    ");

                    while ($r = mysqli_fetch_assoc($query_recent)):
                      $tgl = date('d M Y', strtotime($r['tanggal']));
                    ?>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?= htmlspecialchars($r['nama_siswa'] ?? '-') ?></h6>
                            <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($r['nis'] ?? '-') ?></p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($r['nama_pelanggaran'] ?? '-') ?></p>
                      </td>
                      <td>
                        <p class="text-xs text-secondary mb-0"><?= $tgl ?></p>
                      </td>
                      <td class="align-middle text-center">
                        <span class="badge badge-sm bg-gradient-danger">-<?= $r['poin_berkurang'] ?></span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold"><?= htmlspecialchars($r['nama_lengkap'] ?? '-') ?></span>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
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
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    // Data dari PHP
    const labels = <?= json_encode($label); ?>;
    const dataPelanggaran = <?= json_encode($data); ?>;

    // Buat grafik
    new Chart(document.getElementById('grafikPelanggaran'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Jumlah Pelanggaran',
          data: dataPelanggaran,
          fill: true,
          backgroundColor: 'rgba(255, 99, 132, 0.1)',
          borderColor: 'rgb(255, 99, 132)',
          tension: 0.4,
          borderWidth: 3,
          pointRadius: 5,
          pointBackgroundColor: 'rgb(255, 99, 132)',
          pointBorderColor: '#fff',
          pointBorderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        },
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: {
              size: 14
            },
            bodyFont: {
              size: 13
            }
          }
        }
      }
    });
  </script>

</body>
</html>