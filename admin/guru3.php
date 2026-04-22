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

    $sheet->setCellValue('A1', 'nama');
    $sheet->setCellValue('A2', 'Budi Santoso');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template_guru.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// ================= SEARCH & PAGINATION =================
$search = $_GET['search'] ?? '';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;

$start = ($page - 1) * $limit;

$where = "";
if(!empty($search)){
    $search_safe = mysqli_real_escape_string($conn, $search);
    $where = "WHERE nama LIKE '%$search_safe%'";
}

$q = mysqli_query($conn,"SELECT * FROM guru $where LIMIT $start,$limit");

$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM guru $where"))['total'];
$pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include 'template.php'; ?>

    <div>

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Data Guru</h4>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <!-- BUTTON -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalForm">
            + Tambah Guru
        </button>

        <!-- IMPORT -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>Import Excel</h5>

                <form method="POST" enctype="multipart/form-data" class="form-inline">
                    <input type="file" name="file" class="form-control mr-2 mb-2" required>
                    <button class="btn btn-success mr-2 mb-2" name="import_excel">Import</button>
                    <a href="guru.php?download_template=1" class="btn btn-info mb-2">Template</a>
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

                <h5>Daftar Guru</h5>

                <form method="GET" class="form-inline mb-3">
                    <input type="text" name="search" class="form-control mr-2" placeholder="Cari nama guru"
                        value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary mr-2">Cari</button>

                    <?php if(!empty($search)): ?>
                    <a href="guru.php" class="btn btn-secondary">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama Guru</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                    $no = $start + 1;
                    while($d=mysqli_fetch_array($q)){
                    ?>
                            <tr>
                                <td><?= $no ?></td>
                                <td><?= htmlspecialchars($d['nama']) ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="editData(
                                    '<?= $d['id'] ?>',
                                    '<?= htmlspecialchars($d['nama'],ENT_QUOTES) ?>'
                                )">Edit</button>

                                    <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin?')">Hapus</a>
                                </td>
                            </tr>
                            <?php $no++; } ?>

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
                        <h5 class="modal-title" id="modalTitle">Tambah Guru</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="id" id="id">

                        <div class="form-group">
                            <label>Nama Guru</label>
                            <input type="text" name="nama" id="nama" class="form-control" required>
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

    <script>
    function editData(id, nama) {
        $('#modalForm').modal('show');

        document.getElementById('modalTitle').innerText = 'Edit Guru';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'update';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-warning');
        btn.innerText = 'Update';

        document.getElementById('id').value = id;
        document.getElementById('nama').value = nama;
    }
    </script>

</body>

</html>

<?php
// SIMPAN
if(isset($_POST['simpan'])){
$nama = mysqli_real_escape_string($conn,$_POST['nama']);
mysqli_query($conn,"INSERT INTO guru (nama) VALUES ('$nama')");
echo "<script>location='guru.php';</script>";
}

// UPDATE
if(isset($_POST['update'])){
mysqli_query($conn,"UPDATE guru SET nama='$_POST[nama]' WHERE id='$_POST[id]'");
echo "<script>location='guru.php';</script>";
}

// HAPUS
if(isset($_GET['hapus'])){
mysqli_query($conn,"DELETE FROM guru WHERE id='$_GET[hapus]'");
echo "<script>location='guru.php';</script>";
}

// IMPORT
if(isset($_POST['import_excel'])){
    if(!$excel_ready){
        echo "<script>alert('Library belum ada');</script>";
        exit;
    }

    $file = $_FILES['file']['tmp_name'];
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray();

    foreach($sheet as $i=>$row){
        if($i==0) continue;

        $nama = mysqli_real_escape_string($conn,$row[0]);
        if(empty($nama)) continue;

        mysqli_query($conn,"INSERT INTO guru (nama) VALUES ('$nama')");
    }

    echo "<script>alert('Import berhasil');location='guru.php';</script>";
}
?>