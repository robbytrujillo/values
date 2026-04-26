<?php
session_start();
include '../config/koneksi.php';

$siswa_id = $_SESSION['user']['id'];

/* ================= FILTER JENIS ================= */
$jenis = $_GET['jenis'] ?? 'bulanan'; // default bulanan

/* ================= DATA SISWA ================= */
$siswa = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT s.*, k.nama_kelas
FROM siswa s
JOIN kelas k ON s.kelas_id = k.id
WHERE s.id='$siswa_id'
"));

/* ================= DATA NILAI ================= */
$q = mysqli_query($conn,"
SELECT m.nama_mapel, AVG(n.nilai) as nilai, MAX(n.deskripsi) as deskripsi
FROM nilai n
JOIN mapel m ON n.mapel_id = m.id
WHERE n.siswa_id='$siswa_id'
AND n.jenis='$jenis'
GROUP BY n.mapel_id
");

/* ================= HITUNG RATA ================= */
$total = 0;
$jumlah = 0;
$data_nilai = [];

while($d = mysqli_fetch_assoc($q)){
    $total += $d['nilai'];
    $jumlah++;
    $data_nilai[] = $d;
}

$rata = $jumlah ? round($total/$jumlah,2) : 0;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Raport</title>
    <style>
    body {
        font-family: Arial;
        font-size: 14px;
    }

    .header {
        text-align: center;
    }

    .judul {
        font-weight: bold;
        margin-top: 10px;
    }

    .table {
        width: 100%;
        margin-top: 10px;
    }

    .table td {
        padding: 4px;
        vertical-align: top;
    }

    .bold {
        font-weight: bold;
    }

    body {
        font-family: Arial;
        font-size: 13px;
    }

    .header {
        text-align: center;
        line-height: 1.3;
    }

    .table {
        width: 100%;
        margin-top: 5px;
    }

    .table td {
        padding: 4px;
    }

    .table-nilai {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .table-nilai th {
        background: #eee;
        text-align: center;
    }

    .table-nilai td,
    .table-nilai th {
        border: 1px solid #000;
        padding: 6px;
    }

    .judul {
        font-weight: bold;
        margin-top: 10px;
    }
    </style>
</head>

<body onload="window.print()">

    <!-- <div class="header">
        <h3>YAYASAN DAKWAH ISLAM CAHAYA ILMU</h3>
        <h4>SMA BOARDING SCHOOL</h4>
        <p>LAPORAN <?= strtoupper($jenis) ?></p>
    </div> -->

    <div class="header">
        <h3>YAYASAN DAKWAH ISLAM CAHAYA ILMU</h3>
        <h4>SMA BOARDING SCHOOL</h4>
        <h4><b>LAPORAN HASIL BELAJAR SISWA</b></h4>
        <p>Periode: <?= ucfirst($jenis) ?></p>
    </div>

    <hr>

    <!-- BIODATA -->
    <!-- <table class="table">
        <tr>
            <td width="200">Nama Siswa</td>
            <td>: <?= $siswa['nama'] ?></td>
        </tr>
        <tr>
            <td>Kelas</td>
            <td>: <?= $siswa['nama_kelas'] ?></td>
        </tr>
        <tr>
            <td>Jenis Nilai</td>
            <td>: <b><?= ucfirst($jenis) ?></b></td>
        </tr>
    </table> -->

    <table class="table">
        <tr>
            <td width="150">Nama</td>
            <td>: <?= $siswa['nama'] ?></td>
        </tr>
        <tr>
            <td>Kelas</td>
            <td>: <?= $siswa['nama_kelas'] ?></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: <?= date('d-m-Y') ?></td>
        </tr>
    </table>

    <br>

    <!-- NILAI -->
    <div class="judul">NILAI MATA PELAJARAN</div>

    <?php
$no = 1;
foreach($data_nilai as $d){
?>
    <!-- <table class="table">
        <tr>
            <td width="30"><?= $no++ ?></td>
            <td width="200"><?= $d['nama_mapel'] ?></td>
            <td width="50"><b><?= round($d['nilai']) ?></b></td>
            <td><?= $d['deskripsi'] ?: '-' ?></td>
        </tr>
    </table> -->
    <table class="table-nilai" border="1" cellspacing="0" cellpadding="5">
        <tr>
            <th>No</th>
            <th>Mata Pelajaran</th>
            <th>Nilai</th>
            <th>Predikat</th>
            <th>Deskripsi</th>
        </tr>

        <?php 
    $no=1;
    foreach($data_nilai as $d):

        $nilai = round($d['nilai']);

        // hitung predikat
        if($nilai >= 92) $predikat = 'A';
        elseif($nilai >= 83) $predikat = 'B';
        elseif($nilai >= 76) $predikat = 'C';
        else $predikat = 'D';
    ?>
        <tr>
            <td align="center"><?= $no++ ?></td>
            <td><?= $d['nama_mapel'] ?></td>
            <td align="center"><?= $nilai ?></td>
            <td align="center"><?= $predikat ?></td>
            <td><?= $d['deskripsi'] ?: '-' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php } ?>

    <br>

    <!-- RATA -->
    <p class="bold">Rata-rata: <?= $rata ?></p>

    <br><br>

    <!-- KETERANGAN -->
    <p>
        Kriteria :<br>
        A : 92 - 100<br>
        B : 83 - 91<br>
        C : 76 - 82<br>
        D : < 76 </p>

            <br><br>

            <!-- TTD -->
            <table width="100%">
                <tr>
                    <td></td>
                    <td align="center">
                        Depok, <?= date('d-m-Y') ?><br>
                        Wali Kelas,<br><br><br>
                        <b>____________________</b>
                    </td>
                </tr>
            </table>

</body>

</html>