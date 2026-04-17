<?php include '../config/koneksi.php'; ?>

<h2>RAPORT</h2>

<table border="1">
    <tr>
        <th>Mapel</th>
        <th>Nilai</th>
        <th>Deskripsi</th>
    </tr>

    <?php
$q=mysqli_query($conn,"
SELECT mapel.nama_mapel,nilai.nilai,nilai.deskripsi
FROM nilai
JOIN mapel ON nilai.mapel_id=mapel.id
");

$total=0; $jumlah=0;

while($d=mysqli_fetch_array($q)){
$total+=$d['nilai']; $jumlah++;

echo "<tr>
<td>$d[nama_mapel]</td>
<td>$d[nilai]</td>
<td>$d[deskripsi]</td>
</tr>";
}

$rata=$total/$jumlah;
?>

</table>

<p>Total: <?= $total ?></p>
<p>Rata-rata: <?= round($rata,2) ?></p>