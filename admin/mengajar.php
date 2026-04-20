<?php 
include '../config/auth.php';
cek_role(['admin']);
include '../config/koneksi.php';

// ================= CEK EXCEL =================
$excel_ready = false;
if(file_exists('../vendor/autoload.php')){
    require '../vendor/autoload.php';
    $excel_ready = true;
}

// ================= DOWNLOAD TEMPLATE =================
if(isset($_GET['download_template'])){
    if(!$excel_ready){
        die('PhpSpreadsheet belum terinstall');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'nama_guru');
    $sheet->setCellValue('B1', 'nama_mapel');
    $sheet->setCellValue('C1', 'nama_kelas');
    $sheet->setCellValue('D1', 'wali(1/0)');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template_mengajar.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// ================= SEARCH =================
$search = $_GET['search'] ?? '';

$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$start = ($page - 1) * $limit;

$where = "";
if(!empty($search)){
    $s = mysqli_real_escape_string($conn,$search);
    $where = "WHERE g.nama LIKE '%$s%' 
              OR m.nama_mapel LIKE '%$s%' 
              OR k.nama_kelas LIKE '%$s%'";
}

// ================= QUERY =================
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

$pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mengajar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include 'template.php'; ?>

    <div class="container-fluid mt-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Data Mengajar</h4>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <!-- BUTTON -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalForm">
            + Tambah Data
        </button>

        <!-- IMPORT -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>Import Excel</h5>

                <form method="POST" enctype="multipart/form-data" class="form-inline">
                    <input type="file" name="file" class="form-control mr-2 mb-2" required>
                    <button class="btn btn-success mr-2 mb-2" name="import_excel">Import</button>
                    <a href="?download_template=1" class="btn btn-info mb-2">Template</a>
                </form>

                <?php if(!$excel_ready): ?>
                <div class="alert alert-danger mt-2">
                    PhpSpreadsheet belum terinstall
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card">
            <div class="card-body">

                <h5>Data Mengajar</h5>

                <form method="GET" class="form-inline mb-3">
                    <input type="text" name="search" class="form-control mr-2" placeholder="Cari..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary mr-2">Cari</button>

                    <?php if($search): ?>
                    <a href="mengajar.php" class="btn btn-secondary">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Guru</th>
                                <th>Mapel</th>
                                <th>Kelas</th>
                                <th>Wali</th>
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
                                <td><?= $d['is_wali'] ? '<span class="badge badge-success">Ya</span>' : '-' ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="editData(
                                '<?= $d['id'] ?>',
                                '<?= $d['guru_id'] ?>',
                                '<?= $d['mapel_id'] ?>',
                                '<?= $d['kelas_id'] ?>',
                                '<?= $d['is_wali'] ?>'
                                )">Edit</button>

                                    <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin?')">Hapus</a>
                                </td>
                            </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <nav>
                    <ul class="pagination">
                        <?php for($i=1;$i<=$pages;$i++): ?>
                        <li class="page-item <?= ($i==$page)?'active':'' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>

            </div>
        </div>

    </div>

    <!-- MODAL -->
    <div class="modal fade" id="modalForm">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Data</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="id" id="id">

                        <div class="form-group">
                            <label>Guru</label>
                            <select name="guru_id" id="guru_id" class="form-control" required>
                                <option value="">-- Pilih Guru --</option>
                                <?php 
                            $g=mysqli_query($conn,"SELECT * FROM guru");
                            while($d=mysqli_fetch_array($g)){
                                echo "<option value='$d[id]'>$d[nama]</option>";
                            }
                            ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Mapel</label>
                            <select name="mapel_id" id="mapel_id" class="form-control" required>
                                <option value="">-- Pilih Mapel --</option>
                                <?php 
                            $m=mysqli_query($conn,"SELECT * FROM mapel");
                            while($d=mysqli_fetch_array($m)){
                                echo "<option value='$d[id]'>$d[nama_mapel]</option>";
                            }
                            ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Kelas</label>
                            <select name="kelas_id" id="kelas_id" class="form-control" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php 
                            $k=mysqli_query($conn,"SELECT * FROM kelas");
                            while($d=mysqli_fetch_array($k)){
                                echo "<option value='$d[id]'>$d[nama_kelas]</option>";
                            }
                            ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_wali" id="is_wali" value="1">
                                Wali Kelas
                            </label>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="simpan" id="btnSubmit" class="btn btn-primary">
                            Simpan
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Tutup
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <?php include 'template_footer.php'; ?>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function editData(id, guru, mapel, kelas, wali) {
        $('#modalForm').modal('show');

        document.getElementById('modalTitle').innerText = 'Edit Data';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'update';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-warning');
        btn.innerText = 'Update';

        document.getElementById('id').value = id;
        document.getElementById('guru_id').value = guru;
        document.getElementById('mapel_id').value = mapel;
        document.getElementById('kelas_id').value = kelas;
        document.getElementById('is_wali').checked = (wali == 1);
    }
    </script>

</body>

</html>

<?php
// SIMPAN
if(isset($_POST['simpan'])){
$is_wali = isset($_POST['is_wali']) ? 1 : 0;

mysqli_query($conn,"INSERT INTO mengajar
(guru_id,mapel_id,kelas_id,is_wali)
VALUES(
'$_POST[guru_id]',
'$_POST[mapel_id]',
'$_POST[kelas_id]',
'$is_wali'
)");
echo "<script>location='mengajar.php';</script>";
}

// UPDATE
if(isset($_POST['update'])){
$is_wali = isset($_POST['is_wali']) ? 1 : 0;

mysqli_query($conn,"UPDATE mengajar SET
guru_id='$_POST[guru_id]',
mapel_id='$_POST[mapel_id]',
kelas_id='$_POST[kelas_id]',
is_wali='$is_wali'
WHERE id='$_POST[id]'");

echo "<script>location='mengajar.php';</script>";
}

// HAPUS
if(isset($_GET['hapus'])){
mysqli_query($conn,"DELETE FROM mengajar WHERE id='$_GET[hapus]'");
echo "<script>location='mengajar.php';</script>";
}

// IMPORT
if(isset($_POST['import_excel'])){
    if(!$excel_ready){
        echo "<script>alert('Library belum ada');</script>";
        exit;
    }

    $file = $_FILES['file']['tmp_name'];
    $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file)->getActiveSheet()->toArray();

    foreach($sheet as $i=>$row){
        if($i==0) continue;

        $guru = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM guru WHERE nama='$row[0]'"));
        $mapel = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM mapel WHERE nama_mapel='$row[1]'"));
        $kelas = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM kelas WHERE nama_kelas='$row[2]'"));

        if(!$guru || !$mapel || !$kelas) continue;

        $wali = ($row[3] == 1) ? 1 : 0;

        mysqli_query($conn,"INSERT INTO mengajar
        (guru_id,mapel_id,kelas_id,is_wali)
        VALUES('{$guru['id']}','{$mapel['id']}','{$kelas['id']}','$wali')");
    }

    echo "<script>alert('Import berhasil');location='mengajar.php';</script>";
}
?>