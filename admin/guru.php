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
    $nip  = mysqli_real_escape_string($conn,$_POST['nip']);
    $nama = mysqli_real_escape_string($conn,$_POST['nama']);

    $username = $nip;
    $password = password_hash('gurudanmudaris', PASSWORD_DEFAULT);

    mysqli_query($conn,"
        INSERT INTO guru (nip,nama,username,password,role)
        VALUES ('$nip','$nama','$username','$password','guru')
    ");

    header("Location: guru.php");
    exit;
}

// UPDATE
if(isset($_POST['update'])){
    $nip  = mysqli_real_escape_string($conn,$_POST['nip']);
    $nama = mysqli_real_escape_string($conn,$_POST['nama']);

    mysqli_query($conn,"
        UPDATE guru SET 
        nip='$nip',
        nama='$nama',
        username='$nip'
        WHERE id='$_POST[id]'
    ");

    header("Location: guru.php");
    exit;
}

// HAPUS
if(isset($_GET['hapus'])){
    mysqli_query($conn,"DELETE FROM guru WHERE id='$_GET[hapus]'");
    header("Location: guru.php");
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

        $nip  = mysqli_real_escape_string($conn,$row[0]);
        $nama = mysqli_real_escape_string($conn,$row[1]);

        if(empty($nip) || empty($nama)) continue;

        $username = $nip;
        $password = password_hash('gurudanmudaris', PASSWORD_DEFAULT);

        mysqli_query($conn,"
            INSERT INTO guru (nip,nama,username,password,role)
            VALUES ('$nip','$nama','$username','$password','guru')
        ");
    }

    header("Location: guru.php");
    exit;
}

// TEMPLATE
if(isset($_GET['download_template'])){
    if(!$excel_ready){
        die('PhpSpreadsheet belum terinstall');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1','nip');
    $sheet->setCellValue('B1','nama');

    $sheet->setCellValue('A2','1987654321');
    $sheet->setCellValue('B2','Budi Santoso');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template_guru.xlsx"');

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
    $where = "WHERE nama LIKE '%$s%' OR nip LIKE '%$s%'";
}

$q = mysqli_query($conn,"SELECT * FROM guru $where LIMIT $start,$limit");

$total = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as total FROM guru $where
"))['total'];

$pages = ceil($total/$limit);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include 'template.php'; ?>

    <div class="container-fluid mt-3">

        <div class="d-flex justify-content-between mb-3">
            <h4>Data Guru</h4>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalForm">
            + Tambah Guru
        </button>

        <!-- IMPORT -->
        <div class="card mb-3">
            <div class="card-body">
                <h5>Import Data Guru</h5>

                <form method="POST" enctype="multipart/form-data" class="form-inline">
                    <input type="file" name="file" class="form-control mr-2 mb-2" accept=".xlsx" required>

                    <button class="btn btn-success mr-2 mb-2" name="import_excel">
                        Import Excel
                    </button>

                    <a href="?download_template=1" class="btn btn-info mb-2">
                        Download Template
                    </a>
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

                <form method="GET" class="mb-2">
                    <input type="text" name="search" class="form-control d-inline w-25"
                        value="<?= htmlspecialchars($search) ?>" placeholder="Cari...">
                    <button class="btn btn-primary btn-sm">Cari</button>
                </form>

                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no=$start+1; while($d=mysqli_fetch_array($q)){ ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $d['nip'] ?></td>
                            <td><?= $d['nama'] ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editData(
                                '<?= $d['id'] ?>',
                                '<?= $d['nip'] ?>',
                                '<?= $d['nama'] ?>'
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
                        <h5 id="modalTitle">Tambah Guru</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="id" id="id">

                        <input type="text" name="nip" id="nip" class="form-control mb-2" placeholder="NIP" required>
                        <input type="text" name="nama" id="nama" class="form-control" placeholder="Nama" required>

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
    function editData(id, nip, nama) {
        $('#modalForm').modal('show');

        document.getElementById('modalTitle').innerText = 'Edit Guru';

        document.getElementById('id').value = id;
        document.getElementById('nip').value = nip;
        document.getElementById('nama').value = nama;

        let btn = document.getElementById('btnSubmit');
        btn.name = 'update';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-warning');
        btn.innerText = 'Update';
    }
    </script>

</body>

</html>