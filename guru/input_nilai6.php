<?php
ob_start();
include '../config/auth.php';
cek_role(['guru']);
include '../config/koneksi.php';

$guru_id = $_SESSION['user']['id'];

/* ================= FILTER ================= */
$filter_jenis = $_GET['filter_jenis'] ?? '';
$whereJenis = ($filter_jenis!='') ? "AND n.jenis='$filter_jenis'" : "";

/* ================= CEK EXCEL ================= */
$excel_ready = false;
if(file_exists('../vendor/autoload.php')){
    require '../vendor/autoload.php';
    $excel_ready = true;
}

/* ================= RELASI ================= */
$relasi = mysqli_query($conn,"SELECT mapel_id, kelas_id FROM mengajar WHERE guru_id='$guru_id'");

$mapel_ids = [];
$kelas_ids = [];

while($r = mysqli_fetch_assoc($relasi)){
    $mapel_ids[] = $r['mapel_id'];
    $kelas_ids[] = $r['kelas_id'];
}

$mapel_ids_str = $mapel_ids ? implode(',', $mapel_ids) : '0';
$kelas_ids_str = $kelas_ids ? implode(',', $kelas_ids) : '0';

/* ================= QUERY ================= */
$q = mysqli_query($conn,"
SELECT n.*, s.nama as nama_siswa, k.nama_kelas, m.nama_mapel
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
JOIN kelas k ON s.kelas_id = k.id
JOIN mapel m ON n.mapel_id = m.id
WHERE n.mapel_id IN ($mapel_ids_str)
AND s.kelas_id IN ($kelas_ids_str)
$whereJenis
ORDER BY n.id DESC
");

/* ================= TEMPLATE EXCEL ================= */
if(isset($_GET['download_template'])){
    if(!$excel_ready){
        die('PhpSpreadsheet belum terinstall');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // HEADER
    $sheet->setCellValue('A1','nisn');
    $sheet->setCellValue('B1','mapel');
    $sheet->setCellValue('C1','nilai');
    $sheet->setCellValue('D1','jenis');
    $sheet->setCellValue('E1','tanggal');
    $sheet->setCellValue('F1','deskripsi');

    // CONTOH
    $sheet->setCellValue('A2','1234567890');
    $sheet->setCellValue('B2','Matematika');
    $sheet->setCellValue('C2','90');
    $sheet->setCellValue('D2','harian');
    $sheet->setCellValue('E2','2026-01-01');
    $sheet->setCellValue('F2','Bagus');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template_nilai.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

/* ================= IMPORT ================= */
if(isset($_POST['import_excel'])){

    if(!$excel_ready){
        echo "<script>alert('PhpSpreadsheet belum terinstall');</script>";
        exit;
    }

    if($_FILES['file']['error'] != 0){
        echo "<script>alert('File gagal upload');</script>";
        exit;
    }

    $file = $_FILES['file']['tmp_name'];

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
    $rows = $spreadsheet->getActiveSheet()->toArray();

    $berhasil = 0;
    $gagal = 0;

    foreach($rows as $i=>$row){

        if($i == 0) continue;

        $nisn  = mysqli_real_escape_string($conn,$row[0]);
        $mapel = mysqli_real_escape_string($conn,$row[1]);
        $nilai = (int)$row[2];
        $jenis = strtolower(trim($row[3]));
        $tgl   = $row[4];
        $desk  = mysqli_real_escape_string($conn,$row[5]);

        if(!$nisn || !$mapel){
            $gagal++; continue;
        }

        if($nilai < 0 || $nilai > 100){
            $gagal++; continue;
        }

        // siswa
        $s = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT id, kelas_id FROM siswa WHERE nisn='$nisn'
        "));

        // mapel
        $m = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT id FROM mapel WHERE nama_mapel='$mapel'
        "));

        if(!$s || !$m){
            $gagal++; continue;
        }

        // VALIDASI GURU
        $cek = mysqli_query($conn,"
        SELECT * FROM mengajar
        WHERE guru_id='$guru_id'
        AND mapel_id='{$m['id']}'
        AND kelas_id='{$s['kelas_id']}'
        ");

        if(mysqli_num_rows($cek)==0){
            $gagal++; continue;
        }

        // DUPLIKAT
        $cek2 = mysqli_query($conn,"
        SELECT id FROM nilai
        WHERE siswa_id='{$s['id']}'
        AND mapel_id='{$m['id']}'
        AND tanggal='$tgl'
        AND jenis='$jenis'
        ");

        if(mysqli_num_rows($cek2)>0){
            $gagal++; continue;
        }

        mysqli_query($conn,"
        INSERT INTO nilai
        (siswa_id,mapel_id,guru_id,nilai,jenis,tanggal,deskripsi)
        VALUES(
        '{$s['id']}',
        '{$m['id']}',
        '$guru_id',
        '$nilai',
        '$jenis',
        '$tgl',
        '$desk'
        )
        ");

        $berhasil++;
    }

    echo "<script>alert('Import: $berhasil berhasil, $gagal gagal');location='input_nilai.php';</script>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Input Nilai</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
</head>

<body>

    <?php include 'template.php'; ?>

    <div class="container-fluid mt-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between mb-3">
            <h4>Input Nilai</h4>
            <strong><?= $_SESSION['user']['nama']; ?></strong>
        </div>

        <!-- FILTER -->
        <div class="mb-3">
            <a href="?" class="btn btn-secondary <?= $filter_jenis==''?'active':'' ?>">Semua</a>
            <a href="?filter_jenis=harian" class="btn btn-info <?= $filter_jenis=='harian'?'active':'' ?>">Harian</a>
            <a href="?filter_jenis=bulanan"
                class="btn btn-warning <?= $filter_jenis=='bulanan'?'active':'' ?>">Bulanan</a>
            <a href="?filter_jenis=semester"
                class="btn btn-success <?= $filter_jenis=='semester'?'active':'' ?>">Semester</a>
        </div>

        <!-- BUTTON -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalForm">
            + Input Nilai
        </button>

        <div class="card mb-3">
            <div class="card-body">

                <h5>Import Excel</h5>

                <form method="POST" enctype="multipart/form-data" class="form-inline">
                    <input type="file" name="file" class="form-control mr-2" required>
                    <button class="btn btn-success mr-2" name="import_excel">Import</button>
                    <a href="?download_template=1" class="btn btn-info">Download Template</a>
                </form>

                <?php if(!$excel_ready): ?>
                <div class="alert alert-danger mt-2">
                    PhpSpreadsheet belum terinstall (composer require phpoffice/phpspreadsheet)
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- TABLE -->
        <div class="card">
            <div class="card-body">

                <table id="tableNilai" class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Mapel</th>
                            <th>Nilai</th>
                            <th>Jenis</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no=1; while($d=mysqli_fetch_assoc($q)){ ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $d['tanggal'] ?></td>
                            <td><?= $d['nama_siswa'] ?></td>
                            <td><?= $d['nama_kelas'] ?></td>
                            <td><?= $d['nama_mapel'] ?></td>
                            <td><?= $d['nilai'] ?></td>
                            <td>
                                <?php
                            if($d['jenis']=='harian'){
                                echo "<span class='badge badge-info'>Harian</span>";
                            }elseif($d['jenis']=='bulanan'){
                                echo "<span class='badge badge-warning'>Bulanan</span>";
                            }else{
                                echo "<span class='badge badge-success'>Semester</span>";
                            }
                            ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>

                </table>

            </div>
        </div>

    </div>

    <!-- MODAL -->
    <div class="modal fade" id="modalForm">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST">
                    <div class="modal-body">

                        <!-- KELAS -->
                        <select id="kelas_filter" class="form-control mb-2" onchange="filterSiswa()" required>
                            <option value="">Pilih Kelas</option>
                            <?php
                        $kelas = mysqli_query($conn,"SELECT * FROM kelas WHERE id IN ($kelas_ids_str)");
                        while($k=mysqli_fetch_assoc($kelas)){
                            echo "<option value='$k[id]'>$k[nama_kelas]</option>";
                        }
                        ?>
                        </select>

                        <!-- SISWA -->
                        <select name="siswa_id" id="siswa_id" class="form-control mb-2" required>
                            <option value="">Pilih Siswa</option>
                            <?php
                        $siswa = mysqli_query($conn,"SELECT * FROM siswa WHERE kelas_id IN ($kelas_ids_str)");
                        while($s=mysqli_fetch_assoc($siswa)){
                            echo "<option value='$s[id]' data-kelas='$s[kelas_id]'>$s[nama]</option>";
                        }
                        ?>
                        </select>

                        <!-- MAPEL -->
                        <select name="mapel_id" class="form-control mb-2" required>
                            <option value="">Pilih Mapel</option>
                            <?php
                        $mapel = mysqli_query($conn,"SELECT * FROM mapel WHERE id IN ($mapel_ids_str)");
                        while($m=mysqli_fetch_assoc($mapel)){
                            echo "<option value='$m[id]'>$m[nama_mapel]</option>";
                        }
                        ?>
                        </select>

                        <input type="number" name="nilai" class="form-control mb-2" placeholder="Nilai" required>

                        <select name="jenis_nilai" class="form-control mb-2" required>
                            <option value="">Pilih Jenis</option>
                            <option value="harian">Harian</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="semester">Semester</option>
                        </select>

                        <input type="date" name="tanggal" class="form-control mb-2" required>

                        <textarea name="deskripsi" class="form-control mb-2" placeholder="Deskripsi"></textarea>

                        <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>

                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- JS WAJIB -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function filterSiswa() {
        let kelas = document.getElementById('kelas_filter').value;
        let opt = document.getElementById('siswa_id').options;

        for (let i = 0; i < opt.length; i++) {
            let o = opt[i];
            if (!o.dataset.kelas) continue;
            o.style.display = (kelas == "" || o.dataset.kelas == kelas) ? 'block' : 'none';
        }
    }
    </script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#tableNilai').DataTable();
    });
    </script>

</body>

</html>

<?php
/* ================= SIMPAN ================= */
if(isset($_POST['simpan'])){

$siswa_id = $_POST['siswa_id'];
$mapel_id = $_POST['mapel_id'];
$nilai    = (int)$_POST['nilai'];
$jenis    = $_POST['jenis_nilai'];
$tanggal  = $_POST['tanggal'];
$desk     = mysqli_real_escape_string($conn,$_POST['deskripsi']);

if($nilai < 0 || $nilai > 100){
    echo "<script>alert('Nilai 0-100');</script>"; exit;
}

// VALIDASI MENGAJAR
$cek = mysqli_query($conn,"
SELECT * FROM mengajar
WHERE guru_id='$guru_id'
AND mapel_id='$mapel_id'
AND kelas_id=(SELECT kelas_id FROM siswa WHERE id='$siswa_id')
");

if(mysqli_num_rows($cek)==0){
    echo "<script>alert('Tidak diizinkan');</script>"; exit;
}

mysqli_query($conn,"INSERT INTO nilai
(siswa_id,mapel_id,guru_id,nilai,jenis,tanggal,deskripsi)
VALUES('$siswa_id','$mapel_id','$guru_id','$nilai','$jenis','$tanggal','$desk')
");

echo "<script>location='input_nilai.php';</script>";
}
?>