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

    $sheet->getStyle('A1')->getFont()->setBold(true);
    $sheet->getColumnDimension('A')->setAutoSize(true);

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
<html>

<head>
    <title>Data Guru</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
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
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        width: 350px;
    }

    .btn {
        padding: 8px 16px;
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

    .modal-footer {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 15px;
    }

    .modal-footer .btn {
        flex: 1;
        padding: 10px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
    }

    .btn-secondary {
        background: #64748b;
        color: white;
    }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="siswa.php">Data Siswa</a>
        <a href="guru.php">Data Guru</a>
        <a href="mapel.php">Data Mapel</a>
        <a href="guru_mapel_kelas.php">Relasi Guru</a>
        <a href="ranking.php">Ranking</a>
        <a href="../auth/logout.php">Logout</a>
    </div>

    <div class="content">

        <div class="navbar">
            <h3>Data Guru</h3>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <!-- BUTTON -->
        <button class="btn" onclick="openModal()">+ Tambah Guru</button>


        <br><br>

        <!-- IMPORT -->
        <div class="card">
            <h3>Import Excel (.xlsx)</h3>

            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="file" accept=".xlsx" required>
                <button class="btn btn-success" name="import_excel">Import</button>
                <a href="guru.php?download_template=1" class="btn btn-purple">Template</a>
            </form>

            <?php if(!$excel_ready): ?>
            <p style="color:red;">PhpSpreadsheet belum terinstall</p>
            <?php endif; ?>
        </div>

        <!-- TABLE -->
        <div class="card">
            <h3>Daftar Guru</h3>

            <!-- SEARCH -->
            <form method="GET" style="margin-bottom:15px;">
                <input type="text" name="search" placeholder="Cari nama guru" value="<?= htmlspecialchars($search) ?>"
                    style="padding:8px;border-radius:8px;border:1px solid #ccc;width:250px;">

                <button class="btn">Cari</button>

                <?php if(!empty($search)): ?>
                <a href="guru.php" class="btn" style="background:#64748b;">Reset</a>
                <?php endif; ?>
            </form>

            <table>
                <tr>
                    <th>No</th>
                    <th>Nama Guru</th>
                    <th>Aksi</th>
                </tr>

                <?php
$no = $start + 1;
while($d=mysqli_fetch_array($q)){
echo "<tr>
<td>$no</td>
<td>$d[nama]</td>
<td>
<a href='#' onclick=\"editData('$d[id]','$d[nama]')\" class='badge'>Edit</a>
<a href='?hapus=$d[id]' onclick=\"return confirm('Yakin?')\">Hapus</a>
</td>
</tr>";
$no++;
}
?>
            </table>

            <!-- PAGINATION -->
            <div style="margin-top:15px;">
                <?php for($i=1;$i<=$pages;$i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="btn"
                    style="<?= ($i==$page)?'background:#1e293b;':'' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
            </div>

        </div>

    </div>

    <!-- MODAL -->
    <div class="modal" id="modalForm">
        <div class="modal-content">

            <h3 id="modalTitle">Tambah Guru</h3>

            <form method="POST">
                <input type="hidden" name="id" id="id">

                <input type="text" name="nama" id="nama" placeholder="Nama Guru" required>

                <div class="modal-footer">
                    <button type="submit" name="simpan" id="btnSubmit" class="btn">Simpan</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Kembali</button>
                </div>
            </form>

        </div>
    </div>

    <script>
    // function openModal() {
    //     document.getElementById('modalForm').style.display = 'flex';
    //     document.getElementById('modalTitle').innerText = 'Tambah Guru';
    //     document.getElementById('btnSubmit').name = 'simpan';
    //     document.getElementById('id').value = '';
    //     document.getElementById('nama').value = '';
    // }

    function openModal() {
        document.getElementById('modalForm').style.display = 'flex';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'simpan';
        btn.innerText = 'Simpan';
        btn.style.background = '#3b82f6';
    }

    function closeModal() {
        document.getElementById('modalForm').style.display = 'none';
    }

    // function editData(id, nama) {
    //     document.getElementById('modalForm').style.display = 'flex';
    //     document.getElementById('modalTitle').innerText = 'Edit Guru';
    //     document.getElementById('btnSubmit').name = 'update';
    //     document.getElementById('id').value = id;
    //     document.getElementById('nama').value = nama;
    // }

    function editData(id, nama) {
        document.getElementById('modalForm').style.display = 'flex';

        document.getElementById('modalTitle').innerText = 'Edit Guru';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'update';
        btn.innerText = 'Update';
        btn.style.background = '#f59e0b'; // warna edit

        document.getElementById('id').value = id;
        document.getElementById('nama').value = nama;
    }
    </script>

</body>

</html>

<?php
// SIMPAN
if(isset($_POST['simpan'])){
mysqli_query($conn,"INSERT INTO guru (nama) VALUES ('$_POST[nama]')");
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

// IMPORT XLSX
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