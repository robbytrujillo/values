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

    $sheet->setCellValue('A1', 'nama_mapel');
    $sheet->setCellValue('A2', 'Matematika');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template_mapel.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// ================= SEARCH & PAGINATION =================
$search = $_GET['search'] ?? '';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;

$start = ($page - 1) * $limit;

$where = "";
if(!empty($search)){
    $s = mysqli_real_escape_string($conn,$search);
    $where = "WHERE nama_mapel LIKE '%$s%'";
}

$q = mysqli_query($conn,"SELECT * FROM mapel $where LIMIT $start,$limit");

$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM mapel $where"))['total'];
$pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mapel</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
    .btn {
        padding: 8px 12px;
        border-radius: 8px;
        background: #3b82f6;
        color: #fff;
        border: none;
        cursor: pointer;
    }

    .btn-success {
        background: #10b981;
    }

    .btn-purple {
        background: #6366f1;
    }

    .btn-secondary {
        background: #64748b;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        width: 90%;
        max-width: 400px;
        animation: fadeIn 0.2s ease;
    }

    @keyframes fadeIn {
        from {
            transform: scale(0.9);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .modal-footer {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .modal-footer .btn {
        flex: 1;
    }

    .form-inline {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>📊 Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="siswa.php">Data Siswa</a>
        <a href="guru.php">Data Guru</a>
        <a href="mapel.php">Data Mapel</a>
        <a href="ranking.php">Ranking</a>
        <a href="../auth/logout.php">Logout</a>
    </div>

    <div class="content">

        <div class="navbar">
            <h3>Data Mata Pelajaran</h3>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <br>

        <button class="btn" onclick="openModal()">+ Tambah Mapel</button>

        <br><br>

        <!-- IMPORT -->
        <div class="card">
            <h3>Import Excel</h3>

            <form method="POST" enctype="multipart/form-data" class="form-inline">
                <input type="file" name="file" accept=".xlsx" required>
                <button class="btn btn-success" name="import_excel">Import</button>
                <a href="mapel.php?download_template=1" class="btn btn-purple">Template</a>
            </form>

            <?php if(!$excel_ready): ?>
            <p style="color:red;">PhpSpreadsheet belum terinstall</p>
            <?php endif; ?>

        </div>

        <!-- TABLE -->
        <div class="card">
            <h3>Daftar Mapel</h3>

            <form method="GET">
                <input type="text" name="search" placeholder="Cari mapel" value="<?= htmlspecialchars($search) ?>">
                <button class="btn">Cari</button>

                <?php if($search): ?>
                <a href="mapel.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>

            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>No</th>
                        <th>Nama Mapel</th>
                        <th>Aksi</th>
                    </tr>

                    <?php
$no = $start + 1;
while($d=mysqli_fetch_array($q)){
?>
                    <tr>
                        <td><?= $no ?></td>
                        <td><?= htmlspecialchars($d['nama_mapel']) ?></td>
                        <td>
                            <a href="javascript:void(0)"
                                onclick="editData('<?= $d['id'] ?>','<?= htmlspecialchars($d['nama_mapel'],ENT_QUOTES) ?>')">
                                Edit
                            </a> |
                            <a href="?hapus=<?= $d['id'] ?>" onclick="return confirm('Yakin?')">Hapus</a>
                        </td>
                    </tr>
                    <?php $no++; } ?>
                </table>
            </div>

            <br>

            <?php for($i=1;$i<=$pages;$i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="btn"
                style="<?= ($i==$page)?'background:#1e293b;':'' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

        </div>

    </div>

    <!-- MODAL -->
    <div class="modal" id="modalForm">
        <div class="modal-content">

            <h3 id="modalTitle">Tambah Mapel</h3>

            <form method="POST">
                <input type="hidden" name="id" id="id">

                <input type="text" name="nama_mapel" id="nama_mapel" placeholder="Nama Mapel" required>

                <div class="modal-footer">
                    <button type="submit" name="simpan" id="btnSubmit" class="btn">Simpan</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Kembali</button>
                </div>

            </form>

        </div>
    </div>

    <script>
    function openModal() {
        document.getElementById('modalForm').style.display = 'flex';

        document.getElementById('modalTitle').innerText = 'Tambah Mapel';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'simpan';
        btn.innerText = 'Simpan';
        btn.style.background = '#3b82f6';

        document.getElementById('id').value = '';
        document.getElementById('nama_mapel').value = '';
    }

    function closeModal() {
        document.getElementById('modalForm').style.display = 'none';
    }

    function editData(id, nama) {
        document.getElementById('modalForm').style.display = 'flex';

        document.getElementById('modalTitle').innerText = 'Edit Mapel';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'update';
        btn.innerText = 'Update';
        btn.style.background = '#f59e0b';

        document.getElementById('id').value = id;
        document.getElementById('nama_mapel').value = nama;
    }

    window.onclick = function(e) {
        const modal = document.getElementById('modalForm');
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    }
    </script>

</body>

</html>

<?php
// SIMPAN
if(isset($_POST['simpan'])){
mysqli_query($conn,"INSERT INTO mapel (nama_mapel) VALUES ('$_POST[nama_mapel]')");
echo "<script>location='mapel.php';</script>";
}

// UPDATE
if(isset($_POST['update'])){
mysqli_query($conn,"UPDATE mapel SET nama_mapel='$_POST[nama_mapel]' WHERE id='$_POST[id]'");
echo "<script>location='mapel.php';</script>";
}

// HAPUS
if(isset($_GET['hapus'])){
mysqli_query($conn,"DELETE FROM mapel WHERE id='$_GET[hapus]'");
echo "<script>location='mapel.php';</script>";
}

// IMPORT
if(isset($_POST['import_excel'])){
    if(!$excel_ready){
        echo "<script>alert('Library belum ada');</script>";
        exit;
    }

    $file=$_FILES['file']['tmp_name'];
    $spreadsheet=\PhpOffice\PhpSpreadsheet\IOFactory::load($file);
    $sheet=$spreadsheet->getActiveSheet()->toArray();

    foreach($sheet as $i=>$row){
        if($i==0) continue;

        $nama=mysqli_real_escape_string($conn,$row[0]);
        if(empty($nama)) continue;

        mysqli_query($conn,"INSERT INTO mapel (nama_mapel) VALUES ('$nama')");
    }

    echo "<script>alert('Import berhasil');location='mapel.php';</script>";
}
?>