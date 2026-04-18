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
    $sheet->setCellValue('C1', 'kelas');

    $sheet->setCellValue('A2', 'Budi');
    $sheet->setCellValue('B2', 'Matematika');
    $sheet->setCellValue('C2', 'X IPA 1');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template_relasi.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// ================= SEARCH =================
$search = $_GET['search'] ?? '';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;

$start = ($page - 1) * $limit;

$where = "";
if(!empty($search)){
    $s = mysqli_real_escape_string($conn,$search);
    $where = "WHERE g.nama LIKE '%$s%' 
              OR m.nama_mapel LIKE '%$s%' 
              OR r.kelas LIKE '%$s%'";
}

// ================= QUERY =================
$q = mysqli_query($conn,"
SELECT r.*, g.nama as guru, m.nama_mapel 
FROM guru_mapel_kelas r
JOIN guru g ON r.guru_id = g.id
JOIN mapel m ON r.mapel_id = m.id
$where
LIMIT $start,$limit
");

$total = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as total
FROM guru_mapel_kelas r
JOIN guru g ON r.guru_id = g.id
JOIN mapel m ON r.mapel_id = m.id
$where
"))['total'];

$pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Relasi Guru</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
    /* .modal {
        display: none;
        position: fixed;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .modal {
        z-index: 9999;
    } */

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

        z-index: 99999;
        /* PENTING */
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        width: 350px;
        position: relative;
        z-index: 100000;
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

    .btn-secondary {
        background: #64748b;
    }

    .modal-footer {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .modal-footer .btn {
        flex: 1;
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
            <h3>Relasi Guru - Mapel - Kelas</h3>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <button type="button" class="btn" onclick="openModal()">+ Tambah Relasi</button>

        <br><br>

        <!-- IMPORT -->
        <div class="card">
            <h3>Import Excel</h3>

            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="file" accept=".xlsx" required>
                <button class="btn btn-success" name="import_excel">Import</button>
                <a href="?download_template=1" class="btn btn-purple">Template</a>
            </form>

            <?php if(!$excel_ready): ?>
            <p style="color:red;">PhpSpreadsheet belum terinstall</p>
            <?php endif; ?>
        </div>

        <!-- TABLE -->
        <div class="card">
            <h3>Data Relasi</h3>

            <form method="GET">
                <input type="text" name="search" placeholder="Cari..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn">Cari</button>

                <?php if($search): ?>
                <a href="guru_mapel_kelas.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>

            <table>
                <tr>
                    <th>No</th>
                    <th>Guru</th>
                    <th>Mapel</th>
                    <th>Kelas</th>
                    <th>Aksi</th>
                </tr>

                <?php
            $no = $start + 1;
            while($d=mysqli_fetch_array($q)){
            ?>
                <tr>
                    <td><?= $no ?></td>
                    <td><?= $d['guru'] ?></td>
                    <td><?= $d['nama_mapel'] ?></td>
                    <td><?= $d['kelas'] ?></td>
                    <td>
                        <a href="javascript:void(0)" onclick="editData(
                            '<?= $d['id'] ?>',
                            '<?= $d['guru_id'] ?>',
                            '<?= $d['mapel_id'] ?>',
                            '<?= htmlspecialchars($d['kelas'], ENT_QUOTES) ?>'
                            )">Edit</a> |
                        <a href="?hapus=<?= $d['id'] ?>" onclick="return confirm('Yakin?')">Hapus</a>
                    </td>
                </tr>
                <?php $no++; } ?>
            </table>

            <br>

            <?php for($i=1;$i<=$pages;$i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="btn">
                <?= $i ?>
            </a>
            <?php endfor; ?>

        </div>

    </div>

    <!-- MODAL -->
    <div class="modal" id="modalForm">
        <div class="modal-content">

            <h3 id="modalTitle">Tambah Relasi</h3>

            <form method="POST">

                <input type="hidden" name="id" id="id">

                <select name="guru_id" id="guru_id" required>
                    <option value="">-- Guru --</option>
                    <?php
                $g=mysqli_query($conn,"SELECT * FROM guru");
                while($d=mysqli_fetch_array($g)){
                    echo "<option value='$d[id]'>$d[nama]</option>";
                }
                ?>
                </select>

                <select name="mapel_id" id="mapel_id" required>
                    <option value="">-- Mapel --</option>
                    <?php
                $m=mysqli_query($conn,"SELECT * FROM mapel");
                while($d=mysqli_fetch_array($m)){
                    echo "<option value='$d[id]'>$d[nama_mapel]</option>";
                }
                ?>
                </select>

                <input type="text" name="kelas" id="kelas" placeholder="Kelas (X IPA 1)" required>

                <div class="modal-footer">
                    <button type="submit" name="simpan" id="btnSubmit" class="btn">Simpan</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Kembali</button>
                </div>

            </form>

        </div>
    </div>

    <script>
    function openModal() {
        const modal = document.getElementById('modalForm');
        modal.style.display = 'flex';

        document.getElementById('modalTitle').innerText = 'Tambah Relasi';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'simpan';
        btn.innerText = 'Simpan';
        btn.style.background = '#3b82f6';

        // reset form
        document.getElementById('id').value = '';
        document.getElementById('guru_id').value = '';
        document.getElementById('mapel_id').value = '';
        document.getElementById('kelas').value = '';
    }

    function closeModal() {
        document.getElementById('modalForm').style.display = 'none';
    }

    function editData(id, guru, mapel, kelas) {
        const modal = document.getElementById('modalForm');
        modal.style.display = 'flex';

        document.getElementById('modalTitle').innerText = 'Edit Relasi';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'update';
        btn.innerText = 'Update';
        btn.style.background = '#f59e0b';

        document.getElementById('id').value = id;
        document.getElementById('guru_id').value = guru;
        document.getElementById('mapel_id').value = mapel;
        document.getElementById('kelas').value = kelas;
    }

    // klik luar modal = close
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
    // var_dump($_POST); // DEBUG
    // exit;
$guru = $_POST['guru_id'];
$mapel = $_POST['mapel_id'];
$kelas = $_POST['kelas'];

mysqli_query($conn,"INSERT INTO guru_mapel_kelas 
(guru_id,mapel_id,kelas)
VALUES('$guru','$mapel','$kelas')");
echo "<script>location='guru_mapel_kelas.php';</script>";
}

// UPDATE
if(isset($_POST['update'])){
mysqli_query($conn,"UPDATE guru_mapel_kelas SET
guru_id='$_POST[guru_id]',
mapel_id='$_POST[mapel_id]',
kelas='$_POST[kelas]'
WHERE id='$_POST[id]'");
echo "<script>location='guru_mapel_kelas.php';</script>";
}

// HAPUS
if(isset($_GET['hapus'])){
mysqli_query($conn,"DELETE FROM guru_mapel_kelas WHERE id='$_GET[hapus]'");
echo "<script>location='guru_mapel_kelas.php';</script>";
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

        if(!$guru || !$mapel) continue;

        mysqli_query($conn,"INSERT INTO guru_mapel_kelas 
        (guru_id,mapel_id,kelas)
        VALUES('{$guru['id']}','{$mapel['id']}','$row[2]')");
    }

    echo "<script>alert('Import berhasil');location='guru_mapel_kelas.php';</script>";
}
?>