<?php
session_start();
include '../config/koneksi.php';

$siswa_id = $_SESSION['user']['id'];

$q = mysqli_query($conn,"
SELECT n.*, m.nama_mapel
FROM nilai n
JOIN mapel m ON n.mapel_id = m.id
WHERE n.siswa_id='$siswa_id'
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Raport</title>
</head>

<body onload="window.print()">

    <h3 align="center">RAPORT SISWA</h3>
    <p>Nama: <?= $_SESSION['user']['nama']; ?></p>

    <table border="1" width="100%" cellpadding="5">
        <tr>
            <th>Mapel</th>
            <th>Nilai</th>
            <th>Jenis</th>
        </tr>

        <?php while($d=mysqli_fetch_assoc($q)){ ?>
        <tr>
            <td><?= $d['nama_mapel'] ?></td>
            <td><?= $d['nilai'] ?></td>
            <td><?= $d['jenis'] ?></td>
        </tr>
        <?php } ?>

    </table>

</body>

</html>