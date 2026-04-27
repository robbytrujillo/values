<?php
session_start();
include '../config/koneksi.php';

$siswa_id = $_SESSION['user']['id'];
$mapel_id = $_GET['mapel_id'] ?? '';

$where = "WHERE n.siswa_id='$siswa_id'";
if($mapel_id != ''){
    $where .= " AND n.mapel_id='$mapel_id'";
}

$q = mysqli_query($conn,"
SELECT m.nama_mapel as mapel, AVG(n.nilai) as nilai
FROM nilai n
JOIN mapel m ON n.mapel_id = m.id
$where
GROUP BY n.mapel_id
");

$data = [];
while($d = mysqli_fetch_assoc($q)){
    $data[] = $d;
}

echo json_encode($data);