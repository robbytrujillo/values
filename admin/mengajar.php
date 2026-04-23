<?php 
include '../config/auth.php';
cek_role(['admin']);
include '../config/koneksi.php';

/* ================= CEK EXCEL ================= */
$excel_ready = false;
if(file_exists('../vendor/autoload.php')){
    require '../vendor/autoload.php';
    $excel_ready = true;
}

/* ================= HANDLE ACTION ================= */

// SIMPAN
if(isset($_POST['simpan'])){
    $guru_id  = $_POST['guru_id'];
    $mapel_id = $_POST['mapel_id'];
    $kelas_id = $_POST['kelas_id'];

    // CEK DUPLIKAT
    $cek = mysqli_query($conn,"
        SELECT * FROM mengajar 
        WHERE guru_id='$guru_id' 
        AND mapel_id='$mapel_id' 
        AND kelas_id='$kelas_id'
    ");

    if(mysqli_num_rows($cek) > 0){
        echo "<script>alert('Data sudah ada');location='mengajar.php';</script>";
        exit;
    }

    mysqli_query($conn,"
        INSERT INTO mengajar (guru_id,mapel_id,kelas_id)
        VALUES ('$guru_id','$mapel_id','$kelas_id')
    ");

    header("Location: mengajar.php");
    exit;
}

// UPDATE
if(isset($_POST['update'])){
    mysqli_query($conn,"
        UPDATE mengajar SET
        guru_id='$_POST[guru_id]',
        mapel_id='$_POST[mapel_id]',
        kelas_id='$_POST[kelas_id]'
        WHERE id='$_POST[id]'
    ");

    header("Location: mengajar.php");
    exit;
}

// HAPUS
if(isset($_GET['hapus'])){
    mysqli_query($conn,"DELETE FROM mengajar WHERE id='$_GET[hapus]'");
    header("Location: mengajar.php");
    exit;
}

// IMPORT
if(isset($_POST['import_excel'])){
    if(!$excel_ready){
        echo "<script>alert('PhpSpreadsheet belum ada');</script>";
        exit;
    }

    $file = $_FILES['file']['tmp_name'];
    $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file)->getActiveSheet()->toArray();

    foreach($sheet as $i=>$row){
        if($i==0) continue;

        $guru = mysqli_fetch_assoc(mysqli_query($conn,"
            SELECT id FROM guru WHERE nama='$row[0]'
        "));
        $mapel = mysqli_fetch_assoc(mysqli_query($conn,"
            SELECT id FROM mapel WHERE nama_mapel='$row[1]'
        "));
        $kelas = mysqli_fetch_assoc(mysqli_query($conn,"
            SELECT id FROM kelas WHERE nama_kelas='$row[2]'
        "));

        if(!$guru || !$mapel || !$kelas) continue;

        mysqli_query($conn,"
            INSERT INTO mengajar (guru_id,mapel_id,kelas_id)
            VALUES ('{$guru['id']}','{$mapel['id']}','{$kelas['id']}')
        ");
    }

    header("Location: mengajar.php");
    exit;
}

// TEMPLATE
if(isset($_GET['download_template'])){
    if(!$excel_ready){
        die('PhpSpreadsheet belum terinstall');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'nama_guru');
    $sheet->setCellValue('B1', 'nama_mapel');
    $sheet->setCellValue('C1', 'nama_kelas');

    $sheet->setCellValue('A2', 'Budi');
    $sheet->setCellValue('B2', 'Matematika');
    $sheet->setCellValue('C2', 'X IPA 1');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template_mengajar.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

/* ================= DATA ================= */

$search = $_GET['search'] ?? '';
$limit = 10;
$page = max(1,(int)($_GET['page'] ?? 1));
$start = ($page-1)*$limit;

$where = "";
if($search){
    $s = mysqli_real_escape_string($conn,$search);
    $where = "WHERE g.nama LIKE '%$s%' 
              OR m.nama_mapel LIKE '%$s%' 
              OR k.nama_kelas LIKE '%$s%'";
}

$q = mysqli_query($conn,"
SELECT mg.*, g.nama as guru, m.nama_mapel, k.nama_kelas
FROM mengajar mg
JOIN guru g ON mg.guru_id = g.id
JOIN mapel m ON mg.mapel_id = m.id
JOIN kelas k ON mg.kelas_id = k.id
$where
LIMIT $start,$limit
");

$total = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as total
FROM mengajar mg
JOIN guru g ON mg.guru_id = g.id
JOIN mapel m ON mg.mapel_id = m.id
JOIN kelas k ON mg.kelas_id = k.id
$where
"))['total'];

$pages = ceil($total/$limit);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Mengajar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include 'template.php'; ?>

    <div class="container-fluid mt-4">

        <div class="d-flex justify-content-between mb-3">
            <h4>Data Mengajar</h4>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalForm">
            + Tambah Data
        </button>

        <!-- IMPORT -->
        <div class="card mb-3">
            <div class="card-body">
                <h5>Import Data</h5>

                <form method="POST" enctype="multipart/form-data" class="form-inline">
                    <input type="file" name="file" class="form-control mr-2 mb-2" required>
                    <button class="btn btn-success mr-2 mb-2" name="import_excel">Import</button>
                    <a href="?download_template=1" class="btn btn-info mb-2">Template</a>
                </form>

                <?php if(!$excel_ready): ?>
                <div class="alert alert-danger mt-2">PhpSpreadsheet belum terinstall</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card">
            <div class="card-body">

                <form method="GET" class="mb-2">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari...">
                    <button class="btn btn-primary btn-sm">Cari</button>
                </form>

                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Guru</th>
                            <th>Mapel</th>
                            <th>Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no=$start+1; while($d=mysqli_fetch_array($q)){ ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $d['guru'] ?></td>
                            <td><?= $d['nama_mapel'] ?></td>
                            <td><?= $d['nama_kelas'] ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editData(
                            '<?= $d['id'] ?>',
                            '<?= $d['guru_id'] ?>',
                            '<?= $d['mapel_id'] ?>',
                            '<?= $d['kelas_id'] ?>'
                        )">Edit</button>

                                <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin?')">Hapus</a>
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
                        <h5 id="modalTitle">Tambah Data</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="id" id="id">

                        <select name="guru_id" id="guru_id" class="form-control mb-2" required>
                            <option value="">-- Guru --</option>
                            <?php 
                        $g=mysqli_query($conn,"SELECT * FROM guru");
                        while($d=mysqli_fetch_array($g)){
                            echo "<option value='$d[id]'>$d[nama]</option>";
                        }
                        ?>
                        </select>

                        <select name="mapel_id" id="mapel_id" class="form-control mb-2" required>
                            <option value="">-- Mapel --</option>
                            <?php 
                        $m=mysqli_query($conn,"SELECT * FROM mapel");
                        while($d=mysqli_fetch_array($m)){
                            echo "<option value='$d[id]'>$d[nama_mapel]</option>";
                        }
                        ?>
                        </select>

                        <select name="kelas_id" id="kelas_id" class="form-control" required>
                            <option value="">-- Kelas --</option>
                            <?php 
                        $k=mysqli_query($conn,"SELECT * FROM kelas");
                        while($d=mysqli_fetch_array($k)){
                            echo "<option value='$d[id]'>$d[nama_kelas]</option>";
                        }
                        ?>
                        </select>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="simpan" id="btnSubmit" class="btn btn-primary">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <?php include 'template_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function editData(id, guru, mapel, kelas) {
        $('#modalForm').modal('show');

        document.getElementById('modalTitle').innerText = 'Edit Data';

        document.getElementById('id').value = id;
        document.getElementById('guru_id').value = guru;
        document.getElementById('mapel_id').value = mapel;
        document.getElementById('kelas_id').value = kelas;

        let btn = document.getElementById('btnSubmit');
        btn.name = 'update';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-warning');
        btn.innerText = 'Update';
    }
    </script>

</body>

</html>