<?php
include '../../koneksi.php';
session_start();

// Cek login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

$iduser = $_SESSION['id_user'];
mysqli_begin_transaction($conn);

try {
    // 1️⃣ Ambil data form
    $id_siswa    = mysqli_real_escape_string($conn, $_POST['id_siswa']);
    $id_jenis    = mysqli_real_escape_string($conn, $_POST['pelanggaran']);
    $tanggal     = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $keterangan  = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // 2️⃣ Upload bukti (opsional)
    $bukti = null;

    if (!empty($_FILES['bukti']['name'])) {
        // Validasi tipe file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $fileType = $_FILES['bukti']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Tipe file tidak diizinkan. Hanya JPG, PNG, dan PDF.");
        }

        // Validasi ukuran file (max 5MB)
        if ($_FILES['bukti']['size'] > 5242880) {
            throw new Exception("Ukuran file terlalu besar. Maksimal 5MB.");
        }

        // Path ke folder uploads di ROOT project
        $uploadDir = "../../uploads/";

        // Kalau folder belum ada → buat
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Nama file baru dengan sanitasi
        $fileExtension = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
        $bukti = time() . '_' . uniqid() . '.' . $fileExtension;

        // Pindahkan file
        if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $uploadDir . $bukti)) {
            throw new Exception("Gagal mengupload file bukti.");
        }
    }

    // 3️⃣ Ambil poin pelanggaran
    $qPoin = mysqli_query($conn, "
        SELECT poin FROM jenis_pelanggaran WHERE id_jenis='$id_jenis'
    ");

    if (!$qPoin || mysqli_num_rows($qPoin) == 0) {
        throw new Exception("Data jenis pelanggaran tidak ditemukan.");
    }

    $poin = mysqli_fetch_assoc($qPoin)['poin'];

    // 4️⃣ Insert pelanggaran siswa
    $sqlPelanggaran = "
        INSERT INTO pelanggaran 
        (id_siswa, id_jenis, id_user, tanggal, keterangan, bukti, poin_berkurang)
        VALUES 
        ('$id_siswa', '$id_jenis', '$iduser', '$tanggal', '$keterangan', " . ($bukti ? "'$bukti'" : "NULL") . ", '$poin')
    ";

    if (!mysqli_query($conn, $sqlPelanggaran)) {
        throw new Exception("Gagal menyimpan data pelanggaran: " . mysqli_error($conn));
    }

    // Dapatkan ID pelanggaran yang baru saja diinsert
    $id_pelanggaran = mysqli_insert_id($conn);

    // 5️⃣ Update total poin siswa
    $sqlUpdate = "
        UPDATE siswa 
        SET poin_sisa = poin_sisa - $poin
        WHERE id_siswa = '$id_siswa'
    ";

    if (!mysqli_query($conn, $sqlUpdate)) {
        throw new Exception("Gagal mengupdate poin siswa: " . mysqli_error($conn));
    }

    // 6️⃣ Ambil total poin terbaru
    $qTotal = mysqli_query($conn, "
        SELECT poin_sisa FROM siswa WHERE id_siswa='$id_siswa'
    ");

    if (!$qTotal) {
        throw new Exception("Gagal mengambil data poin siswa.");
    }

    $dataSiswa = mysqli_fetch_assoc($qTotal);
    $poin_sebelum = $dataSiswa['poin_sisa'] + $poin; // Poin sebelum dikurangi
    $total_poin = $dataSiswa['poin_sisa']; // Poin setelah dikurangi

    // 6️⃣.1 Insert riwayat poin (PERBAIKAN: gunakan $id_pelanggaran dari insert sebelumnya)
    $sqlRiwayat = "
        INSERT INTO riwayat_poin 
        (id_siswa, id_pelanggaran, poin_sebelum, poin_berubah, poin_sesudah)
        VALUES 
        ('$id_siswa', '$id_pelanggaran', '$poin_sebelum', '-$poin', '$total_poin')
    ";

    if (!mysqli_query($conn, $sqlRiwayat)) {
        throw new Exception("Gagal menyimpan riwayat poin: " . mysqli_error($conn));
    }

    // 7️⃣ Cari sanksi yang sesuai
    $qSanksi = mysqli_query($conn, "
        SELECT * FROM master_sanksi
        WHERE batas_poin >= $total_poin
        ORDER BY batas_poin ASC
        LIMIT 1
    ");

    if ($qSanksi && mysqli_num_rows($qSanksi) > 0) {
        $sanksi = mysqli_fetch_assoc($qSanksi);
        $id_sanksi = $sanksi['id_sanksi'];

        // 8️⃣ Cek apakah sanksi ini sudah pernah diberikan
        $cek = mysqli_query($conn, "
            SELECT * FROM sanksi_siswa
            WHERE id_siswa='$id_siswa' AND id_sanksi='$id_sanksi'
        ");

        if (mysqli_num_rows($cek) == 0) {
            // 9️⃣ Insert sanksi realtime
            $sqlSanksi = "
                INSERT INTO sanksi_siswa (id_siswa, id_sanksi, tanggal_terbit)
                VALUES ('$id_siswa', '$id_sanksi', NOW())
            ";

            if (!mysqli_query($conn, $sqlSanksi)) {
                throw new Exception("Gagal menyimpan sanksi siswa: " . mysqli_error($conn));
            }
        }
    }

    // 🔟 Commit transaksi
    mysqli_commit($conn);

    // Set session untuk alert success
    $_SESSION['alert'] = 'success';
    $_SESSION['message'] = 'Data pelanggaran berhasil disimpan!';

    // Redirect ke halaman sebelumnya
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);

    // Hapus file bukti jika ada error
    if (isset($bukti) && file_exists("../../uploads/" . $bukti)) {
        unlink("../../uploads/" . $bukti);
    }

    // Set session untuk alert error
    $_SESSION['alert'] = 'error';
    $_SESSION['message'] = 'Gagal menyimpan data: ' . $e->getMessage();

    // Redirect ke halaman sebelumnya
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
