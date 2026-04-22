<?php
include '../config/auth.php';
cek_role(['guru']);
include '../config/koneksi.php';

$guru_id = $_SESSION['user']['id'];

/* ================= CEK EXCEL ================= */
$excel_ready = false;
if(file_exists('../vendor/autoload.php')){
    require '../vendor/autoload.php';
    $excel_ready = true;
}

/* ================= DOWNLOAD TEMPLATE ================= */
if(isset($_GET['download_template'])){
    if(!$excel_ready){
        die('PhpSpreadsheet belum terinstall');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1','nisn');
    $sheet->setCellValue('B1','mapel');
    $sheet->setCellValue('C1','nilai');
    $sheet->setCellValue('D1','jenis');
    $sheet->setCellValue('E1','tanggal');
    $sheet->setCellValue('F1','deskripsi');

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
        echo "<script>alert('Library belum ada');</script>";
        exit;
    }

    $file = $_FILES['file']['tmp_name'];
    $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file)->getActiveSheet()->toArray();

    $berhasil = 0;
    $gagal = 0;

    foreach($sheet as $i=>$row){
        if($i==0) continue;

        $nisn  = mysqli_real_escape_string($conn,$row[0]);
        $mapel = mysqli_real_escape_string($conn,$row[1]);
        $nilai = mysqli_real_escape_string($conn,$row[2]);
        $jenis = mysqli_real_escape_string($conn,$row[3]);
        $tgl   = mysqli_real_escape_string($conn,$row[4]);
        $desk  = mysqli_real_escape_string($conn,$row[5]);

        if(empty($nisn) || empty($mapel)){
            $gagal++; continue;
        }

        $s = mysqli_fetch_assoc(mysqli_query($conn,"
            SELECT id, kelas_id FROM siswa WHERE nisn='$nisn'
        "));

        $m = mysqli_fetch_assoc(mysqli_query($conn,"
            SELECT id FROM mapel WHERE nama_mapel='$mapel'
        "));

        if(!$s || !$m){
            $gagal++; continue;
        }

        // VALIDASI MENGAJAR
        $cek = mysqli_query($conn,"
            SELECT * FROM mengajar
            WHERE guru_id='$guru_id'
            AND mapel_id='{$m['id']}'
            AND kelas_id='{$s['kelas_id']}'
        ");

        if(mysqli_num_rows($cek)==0){
            $gagal++; continue;
        }

        mysqli_query($conn,"
            INSERT INTO nilai
            (siswa_id,mapel_id,guru_id,nilai,jenis_nilai,tanggal,deskripsi)
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

    echo "<script>alert('Import selesai: $berhasil berhasil, $gagal gagal');location='input_nilai.php';</script>";
}

/* ================= RELASI MENGAJAR ================= */
$relasi = mysqli_query($conn,"
SELECT mapel_id, kelas_id 
FROM mengajar 
WHERE guru_id='$guru_id'
");

$mapel_ids = [];
$kelas_ids = [];

while($r = mysqli_fetch_assoc($relasi)){
    $mapel_ids[] = $r['mapel_id'];
    $kelas_ids[] = $r['kelas_id'];
}

$mapel_ids_str = !empty($mapel_ids) ? implode(',', $mapel_ids) : '0';
$kelas_ids_str = !empty($kelas_ids) ? implode(',', $kelas_ids) : '0';

/* ================= QUERY NILAI ================= */
$q = mysqli_query($conn,"
SELECT n.*, s.nama as nama_siswa, k.nama_kelas, m.nama_mapel
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
JOIN kelas k ON s.kelas_id = k.id
JOIN mapel m ON n.mapel_id = m.id
WHERE n.mapel_id IN ($mapel_ids_str)
AND s.kelas_id IN ($kelas_ids_str)
ORDER BY n.id DESC
");

if(!$q){
    die("Query error: ".mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Input Nilai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include 'template.php'; ?>

    <div class="container-fluid mt-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Input Nilai</h4>
            <div class="text-right">
                <strong><?= $_SESSION['user']['nama']; ?></strong><br>
                <small class="text-muted">Guru</small>
            </div>
        </div>

        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalForm">
            + Input Nilai
        </button>

        <!-- IMPORT -->
        <div class="card mb-3">
            <div class="card-body">

                <h5>Import Excel</h5>

                <form method="POST" enctype="multipart/form-data" class="form-inline">
                    <input type="file" name="file" class="form-control mr-2" required>
                    <button class="btn btn-success mr-2" name="import_excel">Import</button>
                    <a href="?download_template=1" class="btn btn-info">Template</a>
                </form>

                <?php if(!$excel_ready): ?>
                <div class="alert alert-danger mt-2">PhpSpreadsheet belum terinstall</div>
                <?php endif; ?>

            </div>
        </div>

        <!-- TABLE -->
        <div class="card">
            <div class="card-body">

                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Mapel</th>
                            <th>Nilai</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no=1; while($d=mysqli_fetch_assoc($q)){ ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $d['nama_siswa'] ?></td>
                            <td><?= $d['nama_kelas'] ?></td>
                            <td><?= $d['nama_mapel'] ?></td>
                            <td><?= $d['nilai'] ?></td>
                            <td><?= $d['jenis_nilai'] ?></td>
                            <td><?= $d['tanggal'] ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editData(
                            '<?= $d['id'] ?>',
                            '<?= $d['siswa_id'] ?>',
                            '<?= $d['mapel_id'] ?>',
                            '<?= $d['nilai'] ?>',
                            '<?= $d['jenis_nilai'] ?>',
                            '<?= $d['tanggal'] ?>',
                            '<?= htmlspecialchars($d['deskripsi'],ENT_QUOTES) ?>'
                            )">Edit</button>
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

                    <div class="modal-header">
                        <h5>Input Nilai</h5>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="id" id="id">

                        <select name="siswa_id" id="siswa_id" class="form-control mb-2" required>
                            <option value="">Pilih Siswa</option>
                            <?php
$siswa = mysqli_query($conn,"SELECT * FROM siswa WHERE kelas_id IN ($kelas_ids_str)");
while($s=mysqli_fetch_assoc($siswa)){
echo "<option value='$s[id]'>$s[nama]</option>";
}
?>
                        </select>

                        <select name="mapel_id" id="mapel_id" class="form-control mb-2" required>
                            <option value="">Pilih Mapel</option>
                            <?php
$mapel = mysqli_query($conn,"SELECT * FROM mapel WHERE id IN ($mapel_ids_str)");
while($m=mysqli_fetch_assoc($mapel)){
echo "<option value='$m[id]'>$m[nama_mapel]</option>";
}
?>
                        </select>

                        <input type="number" name="nilai" id="nilai" class="form-control mb-2" required>

                        <select name="jenis_nilai" id="jenis_nilai" class="form-control mb-2">
                            <option value="harian">Harian</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="semester">Semester</option>
                        </select>

                        <input type="date" name="tanggal" id="tanggal" class="form-control mb-2" required>

                        <textarea name="deskripsi" id="deskripsi" class="form-control"></textarea>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="simpan" id="btnSubmit" class="btn btn-primary">Simpan</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function editData(id, siswa, mapel, nilai, jenis, tanggal, desk) {
        $('#modalForm').modal('show');
        $('#id').val(id);
        $('#siswa_id').val(siswa);
        $('#mapel_id').val(mapel);
        $('#nilai').val(nilai);
        $('#jenis_nilai').val(jenis);
        $('#tanggal').val(tanggal);
        $('#deskripsi').val(desk);

        $('#btnSubmit').attr('name', 'update').text('Update');
    }
    </script>

</body>

</html>

<?php
/* ================= SIMPAN ================= */
if(isset($_POST['simpan'])){

$siswa_id = $_POST['siswa_id'];
$mapel_id = $_POST['mapel_id'];

// VALIDASI
$cek = mysqli_query($conn,"
SELECT * FROM mengajar
WHERE guru_id='$guru_id'
AND mapel_id='$mapel_id'
AND kelas_id=(SELECT kelas_id FROM siswa WHERE id='$siswa_id')
");

if(mysqli_num_rows($cek)==0){
    echo "<script>alert('Tidak diizinkan');</script>";
    exit;
}

mysqli_query($conn,"INSERT INTO nilai
(siswa_id,mapel_id,guru_id,nilai,jenis_nilai,tanggal,deskripsi)
VALUES(
'$_POST[siswa_id]',
'$_POST[mapel_id]',
'$guru_id',
'$_POST[nilai]',
'$_POST[jenis_nilai]',
'$_POST[tanggal]',
'$_POST[deskripsi]'
)");

echo "<script>location='input_nilai.php';</script>";
}

/* ================= UPDATE ================= */
if(isset($_POST['update'])){
mysqli_query($conn,"UPDATE nilai SET
nilai='$_POST[nilai]',
jenis_nilai='$_POST[jenis_nilai]',
tanggal='$_POST[tanggal]',
deskripsi='$_POST[deskripsi]'
WHERE id='$_POST[id]'");

echo "<script>location='input_nilai.php';</script>";
}
?>