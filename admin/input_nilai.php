<?php 
ob_start();
include '../config/auth.php';
cek_role(['admin']);
include '../config/koneksi.php';



// ================= SIMPAN =================
if(isset($_POST['simpan'])){

    $siswa_id = $_POST['siswa_id'] ?? '';
    $mapel_id = $_POST['mapel_id'] ?? '';
    $nilai    = (int)($_POST['nilai'] ?? 0);
    $jenis    = $_POST['jenis_nilai'] ?? '';
    $tanggal  = $_POST['tanggal'] ?? '';
    $desk     = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');

    // VALIDASI
    if(!$siswa_id || !$mapel_id || !$jenis || !$tanggal){
        echo "<script>alert('Data belum lengkap');</script>";
        return;
    }

    if($nilai < 0 || $nilai > 100){
        echo "<script>alert('Nilai harus 0-100');</script>";
        return;
    }

    // ambil guru default (misalnya guru pertama)
    $getGuru = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM guru LIMIT 1"));
    $guru_id = $getGuru['id'] ?? 1; // fallback kalau kosong

    $query = mysqli_query($conn,"INSERT INTO nilai
        (siswa_id,mapel_id,guru_id,nilai,jenis,tanggal,deskripsi)
        VALUES(
        '$siswa_id',
        '$mapel_id',
        '$guru_id',
        '$nilai',
        '$jenis',
        '$tanggal',
        '$desk'
    )");

    if($query){
        echo "<script>
            alert('Data berhasil disimpan');
            location='input_nilai.php';
        </script>";
    }else{
        echo "<script>alert('Gagal simpan: ".mysqli_error($conn)."');</script>";
    }
}

    /* ================= UPDATE ================= */
if(isset($_POST['update'])){

    $id = $_POST['id'];
    $nilai = $_POST['nilai'];
    $jenis = $_POST['jenis'];
    $tanggal = $_POST['tanggal'];
    $desk = mysqli_real_escape_string($conn,$_POST['deskripsi']);

    mysqli_query($conn,"UPDATE nilai SET
        nilai='$nilai',
        jenis='$jenis',
        tanggal='$tanggal',
        deskripsi='$desk'
        WHERE id='$id'
    ");

    echo "<script>location='input_nilai.php';</script>";
}

/* ================= HAPUS ================= */
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];

    mysqli_query($conn,"DELETE FROM nilai WHERE id='$id'");

    echo "<script>location='input_nilai.php';</script>";
}


/* ================= CEK EXCEL ================= */
$excel_ready = false;
if(file_exists('../vendor/autoload.php')){
require '../vendor/autoload.php';
$excel_ready = true;
}

/* ================= FILTER ================= */
$filter_jenis = $_GET['filter_jenis'] ?? '';
$whereJenis = ($filter_jenis!='') ? "WHERE n.jenis='$filter_jenis'" : "";

/* ================= QUERY ================= */
$q = mysqli_query($conn,"
SELECT n.*, s.nama as nama_siswa, k.nama_kelas, m.nama_mapel
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
JOIN kelas k ON s.kelas_id = k.id
JOIN mapel m ON n.mapel_id = m.id
$whereJenis
ORDER BY n.id DESC
");

/* ================= TEMPLATE EXCEL ================= */
if(isset($_GET['download_template'])){
if(!$excel_ready) die('PhpSpreadsheet belum terinstall');

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1','nisn');
$sheet->setCellValue('B1','mapel');
$sheet->setCellValue('C1','nilai');
$sheet->setCellValue('D1','jenis');
$sheet->setCellValue('E1','tanggal');
$sheet->setCellValue('F1','deskripsi');

// contoh
$sheet->setCellValue('A2','123456');
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
echo "<script>
alert('Install PhpSpreadsheet dulu');
</script>";
exit;
}

$file = $_FILES['file']['tmp_name'];
$rows = \PhpOffice\PhpSpreadsheet\IOFactory::load($file)->getActiveSheet()->toArray();

$berhasil = 0;
$gagal = 0;

foreach($rows as $i=>$row){
if($i==0) continue;

$nisn = mysqli_real_escape_string($conn,$row[0]);
$mapel = mysqli_real_escape_string($conn,$row[1]);
$nilai = (int)$row[2];
$jenis = strtolower(trim($row[3]));
$tgl = $row[4];
$desk = mysqli_real_escape_string($conn,$row[5]);

if(!$nisn || !$mapel){ $gagal++; continue; }
if($nilai < 0 || $nilai> 100){ $gagal++; continue; }

    $s = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM siswa WHERE nisn='$nisn'"));
    $m = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM mapel WHERE nama_mapel='$mapel'"));

    if(!$s || !$m){ $gagal++; continue; }

    mysqli_query($conn,"INSERT INTO nilai
    (siswa_id,mapel_id,guru_id,nilai,jenis,tanggal,deskripsi)
    VALUES(
    '{$s['id']}',
    '{$m['id']}',
    '0',
    '$nilai',
    '$jenis',
    '$tgl',
    '$desk'
    )");

    $berhasil++;
    }

    echo "<script>
    alert('Import: $berhasil berhasil, $gagal gagal');
    location = 'input_nilai.php';
    </script>";
    }
    ?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Input Nilai</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
</head>

<body>

    <?php include 'template.php'; ?>

    <div class="container mt-4">

        <h4>Input Nilai (Admin)</h4>

        <!-- FILTER -->
        <form method="GET" class="form-inline mb-3">
            <select name="filter_jenis" class="form-control mr-2">
                <option value="">Semua</option>
                <option value="harian">Harian</option>
                <option value="bulanan">Bulanan</option>
                <option value="semester">Semester</option>
            </select>
            <button class="btn btn-primary btn-sm rounded-pill">Filter</button>
        </form>

        <!-- IMPORT -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="form-inline">
                    <input type="file" name="file" class="form-control mr-2" required>
                    <button class="btn btn-success mr-2 rounded-pill" name="import_excel">Import</button>
                    <a href="?download_template=1" class="btn btn-info rounded-pill">Template</a>
                </form>

                <?php if(!$excel_ready): ?>
                <div class="alert alert-danger mt-2">
                    PhpSpreadsheet belum terinstall
                </div>
                <?php endif; ?>
            </div>
        </div>

        <button class="btn btn-primary mb-3 rounded-pill" data-toggle="modal" data-target="#modalTambah">
            Input Nilai
        </button>

        <!-- TABLE -->
        <table id="tableNilai" class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>No</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Mapel</th>
                    <th>Nilai</th>
                    <th>Jenis</th>
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
                    <td><?= ucfirst($d['jenis']) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm rounded-pill" onclick="editData(
            '<?= $d['id'] ?>',
            '<?= $d['nilai'] ?>',
            '<?= $d['jenis'] ?>',
            '<?= $d['tanggal'] ?>',
            '<?= htmlspecialchars($d['deskripsi'],ENT_QUOTES) ?>'
        )">
                            Edit
                        </button>

                        <button class="btn btn-danger btn-sm rounded-pill" onclick="hapusData('<?= $d['id'] ?>')">
                            Hapus
                        </button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>

    <!-- MODAL TAMBAH -->
    <div class="modal fade" id="modalTambah">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="">
                    <div class="modal-body">

                        <!-- SISWA -->
                        <select name="siswa_id" class="form-control mb-2" required>
                            <option value="">Pilih Siswa</option>
                            <?php
                        $siswa = mysqli_query($conn,"SELECT * FROM siswa");
                        while($s=mysqli_fetch_assoc($siswa)){
                            echo "<option value='$s[id]'>$s[nama]</option>";
                        }
                        ?>
                        </select>

                        <!-- MAPEL -->
                        <select name="mapel_id" class="form-control mb-2" required>
                            <option value="">Pilih Mapel</option>
                            <?php
                        $mapel = mysqli_query($conn,"SELECT * FROM mapel");
                        while($m=mysqli_fetch_assoc($mapel)){
                            echo "<option value='$m[id]'>$m[nama_mapel]</option>";
                        }
                        ?>
                        </select>

                        <!-- NILAI -->
                        <input type="number" name="nilai" class="form-control mb-2" placeholder="Nilai" required>

                        <!-- JENIS -->
                        <select name="jenis_nilai" class="form-control mb-2" required>
                            <option value="">Pilih Jenis</option>
                            <option value="harian">Harian</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="semester">Semester</option>
                        </select>

                        <!-- TANGGAL -->
                        <input type="date" name="tanggal" class="form-control mb-2" required>

                        <!-- DESKRIPSI -->
                        <textarea name="deskripsi" class="form-control mb-2" placeholder="Deskripsi"></textarea>

                        <button type="submit" name="simpan" class="btn btn-primary btn-block">
                            Simpan
                        </button>

                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- MODAL EDIT -->
    <div class="modal fade" id="modalEdit">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="">
                    <div class="modal-body">

                        <input type="hidden" name="id" id="edit_id">

                        <input type="number" name="nilai" id="edit_nilai" class="form-control mb-2" required>

                        <select name="jenis" id="edit_jenis" class="form-control mb-2">
                            <option value="harian">Harian</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="semester">Semester</option>
                        </select>

                        <input type="date" name="tanggal" id="edit_tanggal" class="form-control mb-2" required>

                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control mb-2"></textarea>

                        <button type="submit" name="update" class="btn btn-warning">Update</button>

                    </div>
                </form>

            </div>
        </div>
    </div>

    <?php include 'template_footer.php'; ?>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#tableNilai').DataTable({
            pageLength: 10,
            responsive: true
        });
    });
    </script>

    <script>
    function editData(id, nilai, jenis, tanggal, deskripsi) {
        $('#modalEdit').modal('show');

        $('#edit_id').val(id);
        $('#edit_nilai').val(nilai);
        $('#edit_jenis').val(jenis);
        $('#edit_tanggal').val(tanggal);
        $('#edit_deskripsi').val(deskripsi);
    }

    function hapusData(id) {
        if (confirm('Yakin hapus data?')) {
            window.location = 'input_nilai.php?hapus=' + id;
        }
    }
    </script>

</body>

</html>