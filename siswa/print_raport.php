<?php
session_start();
require '../vendor/autoload.php';
include '../config/koneksi.php';

use Dompdf\Dompdf;

$siswa_id = $_SESSION['user']['id'];
$jenis = $_GET['jenis'] ?? 'bulanan';

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

$total=0; $jumlah=0;
$data_nilai=[];

while($d=mysqli_fetch_assoc($q)){
    $total += $d['nilai'];
    $jumlah++;
    $data_nilai[] = $d;
}

$rata = $jumlah ? round($total/$jumlah,2) : 0;

/* ================= HTML ================= */
$logo_path = __DIR__ . '/../assets/images/ihbs-Logo.png';

$type = pathinfo($logo_path, PATHINFO_EXTENSION);
$data = file_get_contents($logo_path);

$logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

ob_start();
?>

<style>
@page {
    margin: 25px;
}

body {
    font-family: Arial, sans-serif;
    font-size: 12px;
}

/* ================= HEADER ================= */
.header {
    width: 100%;
    border-bottom: 3px solid #000;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.header table {
    width: 100%;
}

.logo {
    width: 80px;
}

.text-header {
    text-align: center;
}

.text-header h3,
.text-header h4,
.text-header p {
    margin: 2px;
}

/* ================= BIODATA ================= */
.biodata {
    margin-top: 10px;
}

.biodata td {
    padding: 3px;
}

/* ================= TABLE NILAI ================= */
.table-nilai {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.table-nilai th,
.table-nilai td {
    border: 1px solid #000;
    padding: 6px;
}

.table-nilai th {
    background: #f2f2f2;
    text-align: center;
}

/* ================= FOOTER ================= */
.ttd {
    margin-top: 40px;
}

.ttd td {
    text-align: center;
    padding-top: 40px;
}

/* cap */
.cap {
    position: absolute;
    bottom: 120px;
    left: 50%;
    opacity: 0.15;
}
</style>

<!-- ================= HEADER ================= -->
<div class="header">
    <table>
        <tr>
            <td width="90">
                <!-- <img src="../assets/images/logo-sma.png" class="logo"> -->
                <img src="<?= $logo_base64 ?>" width="80">
            </td>
            <td class="text-header">
                <h2>YAYASAN DAKWAH ISLAM CAHAYA ILMU</h3>
                    <h3>SMA BOARDING SCHOOL</h3>
                    <h4><b>LAPORAN HASIL BELAJAR SISWA</b></h4>
                    <p>Periode: <?= strtoupper($jenis) ?></p>
            </td>
        </tr>
    </table>
</div>
<!-- <div>
    <tr style="text-align: center">
        <h4><b>LAPORAN HASIL BELAJAR SISWA</b></h4>
        <p>Periode: <?= strtoupper($jenis) ?></p>
    </tr>
</div> -->


<!-- ================= BIODATA ================= -->
<table class="biodata">


    <tr>
        <td width="50">Nama</td>
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

<!-- ================= TABLE NILAI ================= -->
<table class="table-nilai">
    <tr>
        <th width="40">No</th>
        <th>Mata Pelajaran</th>
        <th width="60">Nilai</th>
        <th width="80">Predikat</th>
        <th>Deskripsi</th>
    </tr>

    <?php 
$no=1;
foreach($data_nilai as $d):

$nilai = round($d['nilai']);

if($nilai >= 92) $predikat='A';
elseif($nilai >= 83) $predikat='B';
elseif($nilai >= 76) $predikat='C';
else $predikat='D';
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

<p align="right"><b>Rata-rata: <?= $rata ?></b></p>

<div class="box-kriteria">
    <b>Kriteria Penilaian</b>

    <table class="table-kriteria" style="border: 1px">
        <tr>
            <td width="30">A</td>
            <td>: 92 - 100</td>
        </tr>
        <tr>
            <td>B</td>
            <td>: 83 - 91</td>
        </tr>
        <tr>
            <td>C</td>
            <td>: 76 - 82</td>
        </tr>
        <tr>
            <td>D</td>
            <td>: &lt; 76</td>
        </tr>
    </table>
</div>

<!-- ================= TTD ================= -->
<table width="100%" class="ttd">
    <tr>
        <td>
            Mengetahui,<br>
            Kepala Sekolah<br><br><br><br>
            <b>________________</b>
        </td>

        <td>
            <?= date('d-m-Y') ?><br>
            Wali Kelas<br><br><br><br>
            <b>________________</b>
        </td>
    </tr>
</table>

<!-- ================= CAP (OPSIONAL) ================= -->
<img src="../assets/images/cap.png" class="cap" width="150">

<?php
$html = ob_get_clean();

/* ================= DOMPDF ================= */
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

// SET A4
$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

// OUTPUT
$dompdf->stream("raport.pdf", ["Attachment"=>false]);