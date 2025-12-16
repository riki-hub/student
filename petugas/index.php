<?php
require '../koneksi.php';
session_start();

// Cek login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

if (isset($_SESSION['alert'])) {
    $alertType = $_SESSION['alert'];
    $message = $_SESSION['message'];

    $icon = $alertType === 'success' ? '✓ ' : '✗ ';
    $safeMessage = json_encode($icon . $message, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    echo "<script>
        window.addEventListener('DOMContentLoaded', function () {
            alert(" . $safeMessage . ");
        });
    </script>";

    // Hapus session alert setelah ditampilkan
    unset($_SESSION['alert']);
    unset($_SESSION['message']);
}
// Hitung statistik
$queryTotal = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggaran");
$totalReports = mysqli_fetch_assoc($queryTotal)['total'];

$queryToday = mysqli_query($conn, "SELECT COUNT(*) as total FROM pelanggaran WHERE DATE(tanggal) = CURDATE()");
$todayReports = mysqli_fetch_assoc($queryToday)['total'];

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelaporan Siswa</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="header">
        <div class="header-top">
            <div class="logo-section">
                <div class="logo">🏫</div>
                <div class="school-info">
                    <h1>SMK MADYA DEPOK</h1>
                    <p>Sistem Laporan Siswa</p>
                </div>
            </div>

            <a href="../admin/logout.php" class="btn-logout">Logout</a>

        </div>
        <div class="user-info">
            <div class="user-info-left">
                <span><?= $_SESSION['role'] ?></span>
                <strong><?= $_SESSION['nama']; ?></strong>
            </div>
            <div class="date-info" id="currentDate"></div>
        </div>
    </div>

    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-number" id="totalReports"><?= $totalReports ?></div>
            <div class="stat-label">Total Laporan</div>
        </div>

        <div class="stat-box">
            <div class="stat-number" id="todayReports"><?= $todayReports; ?></div>
            <div class="stat-label">Hari Ini</div>
        </div>
    </div>

    <div class="nav-tabs">
        <button type="button" class="nav-tab active" data-tab="form">Buat Laporan</button>
        <button type="button" class="nav-tab" data-tab="list">Daftar Laporan</button>
    </div>

    <div id="formContent" class="content active">
        <div class="section-header">
            <div class="section-title">Formulir Laporan Baru</div>
            <div class="section-subtitle">Lengkapi data siswa dan pelanggaran yang terjadi</div>
        </div>

        <div class="form-card">
            <form method="POST" action="proses/proses_lapor.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Kelas</label>
                    <select name="kelas">
                        <option value="">Pilih kelas</option>
                        <?php
                        $queryKelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas");
                        while ($row = mysqli_fetch_assoc($queryKelas)) {
                            echo "<option value='{$row['id_kelas']}'>{$row['nama_kelas']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nama Lengkap Siswa</label>
                    <input type="text" id="studentName" placeholder="Ketik nama siswa" autocomplete="off" required>
                    <input type="hidden" id="Idsiswa" name="id_siswa">
                    <div id="siswaList" class="autocomplete-box"></div>
                </div>

                <div class="form-group">
                    <label>Jenis Pelanggaran</label>
                    <select name="pelanggaran" required>
                        <option value="">Pilih Pelanggaran</option>
                        <?php
                        $query = mysqli_query($conn, "SELECT * FROM jenis_pelanggaran");
                        while ($row = mysqli_fetch_assoc($query)) {
                            echo "<option value='{$row['id_jenis']}'>{$row['nama_pelanggaran']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tanggal Kejadian</label>
                    <input type="date" name="tanggal" required>
                </div>

                <div class="form-group">
                    <label>Keterangan / Kronologi</label>
                    <textarea name="keterangan" required></textarea>
                </div>

                <div class="form-group">
                    <label>Bukti (Opsional)</label>
                    <input type="file" name="bukti">
                </div>

                <button type="submit" class="btn-submit">Kirim Laporan</button>
            </form>

        </div>
    </div>

    <div id="listContent" class="content">
        <div class="section-header">
            <div class="section-title">Daftar Laporan</div>
            <div class="section-subtitle">Riwayat laporan siswa bermasalah</div>
        </div>

        <div class="reports-list">
            <?php
            $query = "
    SELECT 
        p.id_pelanggaran,
        s.nama_siswa,
        k.nama_kelas as kelas,
        j.nama_pelanggaran,
        j.poin,
        p.tanggal,
        p.keterangan
    FROM pelanggaran p
    JOIN siswa s ON p.id_siswa = s.id_siswa
    JOIN jenis_pelanggaran j ON p.id_jenis = j.id_jenis
    JOIN kelas k ON s.id_kelas = k.id_kelas
    ORDER BY p.tanggal DESC
";

            $result = mysqli_query($conn, $query);

            while ($row = mysqli_fetch_assoc($result)) {
            ?>
                <div class="report-card">
                    <div class="report-header">
                        <div>
                            <div class="student-name">
                                <?= htmlspecialchars($row['nama_siswa']) ?>
                            </div>
                            <span class="student-class">
                                <?= htmlspecialchars($row['kelas']) ?>
                            </span>
                        </div>

                        <!-- contoh status dari poin -->
                        <span class="status-badge">
                            Poin Berkurang <?= $row['poin'] ?>
                        </span>
                    </div>

                    <div class="issue-tag">
                        <?= htmlspecialchars($row['nama_pelanggaran']) ?>
                    </div>

                    <div class="report-description">
                        <?= htmlspecialchars($row['keterangan']) ?>
                    </div>

                    <div class="report-footer">
                        <span>
                            📅 <?= date('d M Y', strtotime($row['tanggal'])) ?>
                        </span>
                        <span>
                            ID: #<?= str_pad($row['id_pelanggaran'], 4, '0', STR_PAD_LEFT) ?>
                        </span>
                    </div>
                </div>
            <?php } ?>
        </div>

    </div>

    <div class="footer">
        © 2025 SMKS MADYA DEPOK - Sistem Pelaporan Siswa
    </div>



    <script>
        // ==================== VARIABLES ====================
        let siswaInput, siswaList, idSiswa, kelasSelect;

        document.addEventListener('DOMContentLoaded', function() {
            console.log('JS LOADED');

            // ==================== INITIALIZE ELEMENTS ====================
            siswaInput = document.getElementById('studentName');
            siswaList = document.getElementById('siswaList');
            idSiswa = document.getElementById('Idsiswa');
            kelasSelect = document.querySelector('select[name="kelas"]');

            if (!siswaInput || !siswaList || !idSiswa) {
                console.error('Elemen siswa tidak lengkap');
                return;
            }

            // ==================== AUTOCOMPLETE SISWA ====================
            siswaInput.addEventListener('input', function() {
                const keyword = siswaInput.value.trim();
                const kelas = kelasSelect ? kelasSelect.value : '';

                console.log('Ketik:', keyword);

                if (keyword.length < 1) {
                    siswaList.innerHTML = '';
                    siswaList.style.display = 'none';
                    idSiswa.value = ''; // Reset hidden input
                    return;
                }

                fetch('get_siswa.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `keyword=${encodeURIComponent(keyword)}&kelas=${encodeURIComponent(kelas)}`
                    })
                    .then(response => response.text())
                    .then(html => {
                        console.log('Response:', html);

                        siswaList.innerHTML = html;
                        siswaList.style.display = html.trim() ? 'block' : 'none';
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        siswaList.style.display = 'none';
                    });
            });

            // Klik di luar → tutup list
            document.addEventListener('click', function(e) {
                if (!siswaList.contains(e.target) && e.target !== siswaInput) {
                    siswaList.style.display = 'none';
                }
            });

            // ==================== TAB SWITCHING ====================
            const tabButtons = document.querySelectorAll('.nav-tab');
            const tabContents = document.querySelectorAll('.content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');

                    console.log('Switch to tab:', targetTab);

                    // Remove active class from all tabs and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Show corresponding content
                    if (targetTab === 'form') {
                        document.getElementById('formContent').classList.add('active');
                    } else if (targetTab === 'list') {
                        document.getElementById('listContent').classList.add('active');
                    }
                });
            });

            // ==================== UPDATE DATE ====================
            updateDate();

            // ==================== FORM VALIDATION ====================
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const idSiswaValue = idSiswa.value;

                    if (!idSiswaValue) {
                        e.preventDefault();
                        alert('❌ Silakan pilih siswa dari daftar autocomplete!');
                        siswaInput.focus();
                        return false;
                    }
                });
            }

            // ==================== SET DEFAULT DATE ====================
            const dateInput = document.querySelector('input[name="tanggal"]');
            if (dateInput) {
                dateInput.valueAsDate = new Date();
                dateInput.max = new Date().toISOString().split('T')[0]; // Max = today
            }

            // ==================== ALERT NOTIFICATION ====================
            <?php if (isset($_SESSION['alert'])): ?>
                const alertType = '<?= $_SESSION['alert'] ?>';
                const message = <?= json_encode($_SESSION['message'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

                if (alertType === 'success') {
                    alert('✓ ' + message);
                } else {
                    alert('✗ ' + message);
                }

                <?php
                unset($_SESSION['alert']);
                unset($_SESSION['message']);
                ?>
            <?php endif; ?>
        });

        // ==================== HELPER FUNCTIONS ====================

        // Dipanggil dari HTML hasil AJAX
        function pilihSiswa(id, nama) {
            console.log('Pilih siswa:', id, nama);

            siswaInput.value = nama;
            idSiswa.value = id;
            siswaList.style.display = 'none';
        }

        // Update tanggal di header
        function updateDate() {
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const today = new Date().toLocaleDateString('id-ID', options);
            const dateElement = document.getElementById('currentDate');
            if (dateElement) {
                dateElement.textContent = today;
            }
        }
    </script>


</body>

</html>