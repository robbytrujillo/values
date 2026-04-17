<?php
include '../config/koneksi.php';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=nilai.xls");

echo "<table border='1'>";

$q=mysqli_query($conn,"
SELECT siswa.nama,mapel.nama_mapel,nilai.nilai
FROM nilai
JOIN siswa ON nilai.siswa_id=siswa.id
JOIN mapel ON nilai.mapel_id=mapel.id
");

while($d=mysqli_fetch_array($q)){
echo "<tr>
<td>$d[nama]</td>
<td>$d[nama_mapel]</td>
<td>$d[nilai]</td>
</tr>";
}

echo "</table>";