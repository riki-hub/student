<?php
session_start();
unset(
  $_SESSION['siswa_id'],
  $_SESSION['siswa_nama'],
  $_SESSION['siswa_nis'],
  $_SESSION['siswa_kelas']
);
session_destroy();
header("Location: login.php");
exit;
