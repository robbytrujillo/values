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

/* ================= HANDLE ACTION ================= */

// SIMPAN
if(isset($_POST['simpan'])){
    $nisn = mysqli_real_escape_string($conn,$_POST['nisn']);
    $nama = mysqli_real_escape_string($conn,$_POST['nama']);
    $kelas_id = $_POST['kelas_id'];

    if(empty($nisn) || empty($nama) || empty($kelas_id)){
        die("Data tidak lengkap");
    }

    $username = $nisn;
    $password = password_hash('password', PASSWORD_DEFAULT);

    mysqli_query($conn,"
        INSERT INTO siswa (nisn,nama,kelas_id,username,password)
        VALUES ('$nisn','$nama','$kelas_id','$username','$password')
    ");

    header("Location: siswa.php");
    exit;
}

// UPDATE
if(isset($_POST['update'])){
    $nisn = mysqli_real_escape_string($conn,$_POST['nisn']);
    $nama = mysqli_real_escape_string($conn,$_POST['nama']);
    $kelas_id = $_POST['kelas_id'];

    mysqli_query($conn,"UPDATE siswa SET 
        nisn='$nisn',
        nama='$nama',
        kelas_id='$kelas_id'
        WHERE id='$_POST[id]'
    ");

    header("Location: siswa.php");
    exit;
}

// HAPUS
if(isset($_GET['hapus'])){
    mysqli_query($conn,"DELETE FROM siswa WHERE id='$_GET[hapus]'");
    header("Location: siswa.php");
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
    $where = "WHERE s.nisn LIKE '%$s%' 
              OR s.nama LIKE '%$s%' 
              OR k.nama_kelas LIKE '%$s%'";
}

$q = mysqli_query($conn,"
SELECT s.*, k.nama_kelas 
FROM siswa s
LEFT JOIN kelas k ON s.kelas_id = k.id
$where
LIMIT $start,$limit
");

$total = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as total 
FROM siswa s
LEFT JOIN kelas k ON s.kelas_id = k.id
$where
"))['total'];

$pages = ceil($total/$limit);

// ================= TEMPLATE EXCEL =================
if(isset($_GET['download_template'])){
    if(!$excel_ready){
        die('PhpSpreadsheet belum terinstall');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1','nisn');
    $sheet->setCellValue('B1','nama');
    $sheet->setCellValue('C1','kelas');

    $sheet->setCellValue('A2','1234567890');
    $sheet->setCellValue('B2','Budi Santoso');
    $sheet->setCellValue('C2','X IPA 1');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template_siswa.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// ================= IMPORT =================
if(isset($_POST['import_excel'])){
    if(!$excel_ready){
        die('Library belum ada');
    }

    $file = $_FILES['file']['tmp_name'];
    $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file)->getActiveSheet()->toArray();

    foreach($sheet as $i => $row){
        if($i == 0) continue;

        $nisn  = mysqli_real_escape_string($conn, $row[0]);
        $nama  = mysqli_real_escape_string($conn, $row[1]);
        $kelas = mysqli_real_escape_string($conn, $row[2]);

        if(empty($nisn) || empty($kelas)) continue;

        $k = mysqli_fetch_assoc(mysqli_query($conn,"
            SELECT id FROM kelas WHERE nama_kelas='$kelas'
        "));

        if(!$k) continue;

        $kelas_id = $k['id'];

        $username = $nisn;
        $password = password_hash('password', PASSWORD_DEFAULT);

        mysqli_query($conn,"
            INSERT INTO siswa (nisn,nama,kelas_id,username,password)
            VALUES ('$nisn','$nama','$kelas_id','$username','$password')
        ");
    }

    header("Location: siswa.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include 'template.php'; ?>

    <div class="container-fluid mt-3">

        <div class="d-flex justify-content-between mb-3">
            <h4>Data Siswa</h4>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalForm">
            + Tambah Siswa
        </button>

        <div class="card">
            <div class="card-body">

                <form method="GET" class="mb-2">
                    <input type="text" name="search" class="form-control d-inline w-25"
                        value="<?= htmlspecialchars($search) ?>" placeholder="Cari...">
                    <button class="btn btn-primary btn-sm">Cari</button>
                </form>

                <!-- IMPORT -->
                <div class="card mb-3">
                    <div class="card-body">

                        <h5>Import Data Siswa</h5>

                        <form method="POST" enctype="multipart/form-data" class="form-inline">

                            <input type="file" name="file" class="form-control mr-2 mb-2" accept=".xlsx" required>

                            <button type="submit" name="import_excel" class="btn btn-success mr-2 mb-2">
                                Import Excel
                            </button>

                            <a href="siswa.php?download_template=1" class="btn btn-info mb-2">
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

                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no=$start+1; while($d=mysqli_fetch_array($q)){ ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $d['nisn'] ?></td>
                            <td><?= $d['nama'] ?></td>
                            <td><?= $d['nama_kelas'] ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editData(
'<?= $d['id'] ?>',
'<?= $d['nisn'] ?>',
'<?= $d['nama'] ?>',
'<?= $d['kelas_id'] ?>'
)">Edit</button>

                                <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin?')">Hapus</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>

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
                        <h5 id="modalTitle">Tambah Siswa</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="id" id="id">

                        <input type="text" name="nisn" id="nisn" class="form-control mb-2" placeholder="NISN" required>
                        <input type="text" name="nama" id="nama" class="form-control mb-2" placeholder="Nama" required>

                        <select name="kelas_id" id="kelas_id" class="form-control" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php
$k = mysqli_query($conn,"SELECT * FROM kelas");
while($d=mysqli_fetch_array($k)){
echo "<option value='$d[id]'>$d[nama_kelas]</option>";
}
?>
                        </select>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="simpan" id="btnSubmit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function editData(id, nisn, nama, kelas) {
        $('#modalForm').modal('show');

        document.getElementById('modalTitle').innerText = 'Edit Siswa';

        document.getElementById('id').value = id;
        document.getElementById('nisn').value = nisn;
        document.getElementById('nama').value = nama;
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