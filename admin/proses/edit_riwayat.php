<?php
require '../../koneksi.php';
session_start();

if (!isset($_SESSION['id_user'])) {
  header("Location: ../../index.php");
  exit;
}

if (isset($_POST['edit'])) {
  $id_pelanggaran = (int)$_POST['id_pelanggaran'];
  $id_siswa = mysqli_real_escape_string($conn, $_POST['id_siswa']);
  $id_jenis = mysqli_real_escape_string($conn, $_POST['id_jenis']);
  $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
  $poin_berkurang = (int)$_POST['poin_berkurang'];
  $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
  $bukti_lama = $_POST['bukti_lama'];
  $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
  
  // Ambil data lama untuk mengembalikan poin
  $get_old = mysqli_query($conn, "SELECT id_siswa, poin_berkurang FROM pelanggaran WHERE id_pelanggaran = $id_pelanggaran");
  $old_data = mysqli_fetch_assoc($get_old);
  
  // Handle upload bukti baru
  $nama_bukti = $bukti_lama;
  if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png'];
    $filename = $_FILES['bukti']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed) && $_FILES['bukti']['size'] <= 2097152) { // 2MB
      $nama_bukti = 'bukti_' . time() . '_' . uniqid() . '.' . $ext;
      $upload_path = '../../uploads/bukti/';
      
      if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
      }
      
      move_uploaded_file($_FILES['bukti']['tmp_name'], $upload_path . $nama_bukti);
      
      // Hapus bukti lama
      if (!empty($bukti_lama) && file_exists($upload_path . $bukti_lama)) {
        unlink($upload_path . $bukti_lama);
      }
    }
  }
  
  // Update pelanggaran
  $sql = "UPDATE pelanggaran SET 
          id_siswa = '$id_siswa',
          id_jenis = '$id_jenis',
          tanggal = '$tanggal',
          poin_berkurang = $poin_berkurang,
          keterangan = '$keterangan',
          bukti = '$nama_bukti'
          WHERE id_pelanggaran = $id_pelanggaran";
  
  $update = mysqli_query($conn, $sql);
  
  if ($update) {
    // Kembalikan poin lama ke siswa lama
    if ($old_data) {
      mysqli_query($conn, "UPDATE siswa SET poin_sisa = poin_sisa + {$old_data['poin_berkurang']} WHERE id_siswa = {$old_data['id_siswa']}");
    }
    
    // Kurangi poin baru dari siswa baru
    mysqli_query($conn, "UPDATE siswa SET poin_sisa = poin_sisa - $poin_berkurang WHERE id_siswa = '$id_siswa'");
    
    header("Location: ../riwayat.php?page=$page&msg=updated");
  } else {
    header("Location: ../riwayat.php?page=$page&msg=error");
  }
  exit;
}
?>