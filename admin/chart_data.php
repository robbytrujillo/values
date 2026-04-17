<?php
include '../config/koneksi.php';

$label=[];
$nilai=[];

$q=mysqli_query($conn,"
SELECT mapel.nama_mapel, AVG(nilai.nilai) as rata
FROM nilai
JOIN mapel ON nilai.mapel_id=mapel.id
GROUP BY mapel.id
");

while($d=mysqli_fetch_array($q)){
$label[]=$d['nama_mapel'];
$nilai[]=$d['rata'];
}

echo json_encode([
"label"=>$label,
"nilai"=>$nilai
]);