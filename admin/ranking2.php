<?php include '../config/koneksi.php'; ?>

<h2>Ranking Siswa (Sekolah)</h2>

<table border="1">
    <tr>
        <th>Ranking</th>
        <th>Nama</th>
        <th>Total Nilai</th>
    </tr>

    <?php
$q=mysqli_query($conn,"
SELECT siswa.nama,
SUM(nilai.nilai) as total
FROM nilai
JOIN siswa ON nilai.siswa_id=siswa.id
GROUP BY siswa.id
ORDER BY total DESC
");

$rank=1;
while($d=mysqli_fetch_array($q)){
echo "<tr>
<td>$rank</td>
<td>$d[nama]</td>
<td>$d[total]</td>
</tr>";
$rank++;
}
?>
</table>