<?php
require '../../koneksi.php';
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../index.php");
    exit;
}

if (!isset($_POST['import'])) {
    header("Location: ../siswa.php");
    exit;
}

if ($_FILES['file_excel']['error'] !== 0) {
    header("Location: ../siswa.php?msg=import_error");
    exit;
}

$file = $_FILES['file_excel']['tmp_name'];

// PATH AUTLOAD BENAR
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;


try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // Ambil mapping kelas
    $kelas_map = [];
    $q = mysqli_query($conn, "SELECT id_kelas, nama_kelas FROM kelas");
    while ($k = mysqli_fetch_assoc($q)) {
        $kelas_map[strtoupper(trim($k['nama_kelas']))] = $k['id_kelas'];
    }

    $success = 0;
    $errors = 0;
    $error_details = [];

    // DATA MULAI BARIS 8 (index 7)
    for ($i = 7; $i < count($rows); $i++) {

        $row_number = $i + 1;
        $row = $rows[$i];

        if (empty($row[0]) && empty($row[1])) continue;

        $nis        = trim($row[0]);
        $nama       = trim($row[1]);
        $jk_excel   = strtoupper(trim($row[2]));
        $tgl_excel  = $row[3];
        $kelas_excel = strtoupper(trim($row[4]));
        $password_plain = trim($row[5]);

        // Validasi wajib
        if (!$nis || !$nama) {
            $errors++;
            $error_details[] = "Baris $row_number: NIS/Nama/Password kosong";
            continue;
        }

        // Konversi jenis kelamin
        if ($jk_excel === 'LAKI-LAKI') {
            $jk = 'L';
        } elseif ($jk_excel === 'PEREMPUAN') {
            $jk = 'P';
        } else {
            $errors++;
            $error_details[] = "Baris $row_number: Jenis kelamin tidak valid";
            continue;
        }

        // Konversi tanggal
        if (is_numeric($tgl_excel)) {
            $tanggal_lahir = Date::excelToDateTimeObject($tgl_excel)->format('Y-m-d');
        } else {
            $tanggal_lahir = date('Y-m-d', strtotime(str_replace('/', '-', $tgl_excel)));
        }

        // Cek kelas
        if (!isset($kelas_map[$kelas_excel])) {
            $errors++;
            $error_details[] = "Baris $row_number: Kelas '$kelas_excel' tidak ditemukan";
            continue;
        }
        $id_kelas = $kelas_map[$kelas_excel];

        // Hash password
        $password = ($password_plain);

        $passwordDefault = "12345";

        // Default
        $poin_awal = 300;
        $poin_sisa = 300;
        $status = 'aktif';

        $sql = "INSERT INTO siswa
                (nis, nama_siswa, jenis_kelamin, tanggal_lahir, id_kelas, poin_awal, poin_sisa, status, password)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                nama_siswa = VALUES(nama_siswa),
                jenis_kelamin = VALUES(jenis_kelamin),
                tanggal_lahir = VALUES(tanggal_lahir),
                id_kelas = VALUES(id_kelas),
                poin_awal = 300,
                poin_sisa = 300,
                status = 'aktif',
                password = VALUES(password)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "ssssiiiss",
            $nis,
            $nama,
            $jk,
            $tanggal_lahir,
            $id_kelas,
            $poin_awal,
            $poin_sisa,
            $status,
            $passwordDefault
        );

        if (mysqli_stmt_execute($stmt)) {
            $success++;
        } else {
            $errors++;
            $error_details[] = "Baris $row_number: Gagal simpan data";
        }
        mysqli_stmt_close($stmt);
    }

    $_SESSION['import_error_details'] = $error_details;
    header("Location: ../siswa.php?msg=imported&success=$success&errors=$errors");
    exit;
} catch (Exception $e) {
    $_SESSION['import_error_details'] = [$e->getMessage()];
    header("Location: ../siswa.php?msg=import_error");
    exit;
}
