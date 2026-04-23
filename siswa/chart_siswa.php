<?php
session_start();
include '../config/koneksi.php';

$siswa_id = $_SESSION['user']['id'];

$q = mysqli_query($conn,"
SELECT m.nama_mapel as mapel, AVG(n.nilai) as nilai
FROM nilai n
JOIN mapel m ON n.mapel_id = m.id
WHERE n.siswa_id='$siswa_id'
GROUP BY n.mapel_id
");

$data = [];

while($d=mysqli_fetch_assoc($q)){
    $data[] = $d;
}

echo json_encode($data);