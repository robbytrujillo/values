<?php
include '../config/koneksi.php';
include '../config/auth.php';

$guru_id = $_SESSION['user']['id'];
$jenis = $_GET['jenis'] ?? '';

// relasi guru
$relasi = mysqli_query($conn,"SELECT mapel_id, kelas_id FROM mengajar WHERE guru_id='$guru_id'");

$mapel_ids=[]; 
$kelas_ids=[];

while($r=mysqli_fetch_assoc($relasi)){
    $mapel_ids[]=$r['mapel_id'];
    $kelas_ids[]=$r['kelas_id'];
}

$mapel_ids_str = $mapel_ids ? implode(',',$mapel_ids) : '0';
$kelas_ids_str = $kelas_ids ? implode(',',$kelas_ids) : '0';

$where = $jenis ? "AND n.jenis='$jenis'" : "";

$q = mysqli_query($conn,"
SELECT s.nama as nama_siswa, n.nilai
FROM nilai n
JOIN siswa s ON n.siswa_id=s.id
WHERE n.mapel_id IN ($mapel_ids_str)
AND s.kelas_id IN ($kelas_ids_str)
$where
");

$data = [];
while($d=mysqli_fetch_assoc($q)){
    $data[] = $d;
}

header('Content-Type: application/json');
echo json_encode($data);