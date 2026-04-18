<?php 
include '../config/auth.php';
cek_role(['guru']);
include '../config/koneksi.php';

$kelas_wali = $_SESSION['user']['kelas_wali'] ?? null;

// ================= SEARCH & PAGINATION =================
$search = $_GET['search'] ?? '';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;

$start = ($page - 1) * $limit;

$where = "";
if(!empty($search)){
    $s = mysqli_real_escape_string($conn,$search);
    $where = "WHERE s.nama LIKE '%$s%' OR m.nama_mapel LIKE '%$s%'";
}

// JOIN biar tampil nama siswa & mapel
$q = mysqli_query($conn,"
SELECT n.*, s.nama as nama_siswa, s.kelas, m.nama_mapel 
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
JOIN mapel m ON n.mapel_id = m.id
$where
LIMIT $start,$limit
");

$total = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as total 
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
JOIN mapel m ON n.mapel_id = m.id
$where
"))['total'];

$pages = ceil($total / $limit);

// ================= CEK EXCEL =================
$excel_ready = false;
if(file_exists('../vendor/autoload.php')){
    require '../vendor/autoload.php';
    $excel_ready = true;
}

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

    $sheet->setCellValue('A1','nis');
    $sheet->setCellValue('B1','mapel');
    $sheet->setCellValue('C1','nilai');
    $sheet->setCellValue('D1','deskripsi');

    $sheet->setCellValue('A2','12345');
    $sheet->setCellValue('B2','Matematika');
    $sheet->setCellValue('C2','90');
    $sheet->setCellValue('D2','Bagus');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template_nilai.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai</title>

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

    .btn-warning {
        background: #f59e0b;
    }

    .btn-danger {
        background: #ef4444;
    }

    .btn-secondary {
        background: #64748b;
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
        max-width: 420px;
    }

    .modal-footer {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .modal-footer .btn {
        flex: 1;
    }

    .table-wrapper {
        overflow-x: auto;
    }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>👨‍🏫 Guru</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="input_nilai.php">Input Nilai</a>
        <a href="../auth/logout.php">Logout</a>
    </div>

    <div class="content">

        <div class="navbar">
            <h3>Input Nilai Siswa</h3>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <br>

        <button class="btn" onclick="openModal()">+ Input Nilai</button>

        <br><br>

        <!-- TABLE -->
        <div class="card">
            <h3>Data Nilai</h3>

            <form method="GET">
                <input type="text" name="search" placeholder="Cari siswa / mapel"
                    value="<?= htmlspecialchars($search) ?>">
                <button class="btn">Cari</button>

                <?php if($search): ?>
                <a href="input_nilai.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>

            <div class="card">
                <h3>Import Excel Nilai</h3>

                <form method="POST" enctype="multipart/form-data" class="form-inline">
                    <input type="file" name="file" accept=".xlsx" required>

                    <button class="btn btn-success" name="import_excel">Import</button>
                    <a href="input_nilai.php?download_template=1" class="btn btn-purple">Template</a>
                </form>

                <?php if(!$excel_ready): ?>
                <p style="color:red;">PhpSpreadsheet belum terinstall</p>
                <?php endif; ?>
            </div>

            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>No</th>
                        <th>Siswa</th>
                        <th>Mapel</th>
                        <th>Nilai</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>

                    <?php
$no = $start + 1;
while($d=mysqli_fetch_array($q)){
?>
                    <tr>
                        <td><?= $no ?></td>
                        <td><?= htmlspecialchars($d['nama_siswa']) ?></td>
                        <td><?= htmlspecialchars($d['nama_mapel']) ?></td>
                        <td><?= $d['nilai'] ?></td>
                        <td><?= htmlspecialchars($d['deskripsi']) ?></td>
                        <td>
                            <a href="javascript:void(0)" onclick="editData(
'<?= $d['id'] ?>',
'<?= $d['siswa_id'] ?>',
'<?= $d['mapel_id'] ?>',
'<?= $d['nilai'] ?>',
'<?= htmlspecialchars($d['deskripsi'],ENT_QUOTES) ?>',
'<?= $d['kelas'] ?>'
)">Edit</a> |
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

            <h3 id="modalTitle">Input Nilai</h3>

            <form method="POST">

                <input type="hidden" name="id" id="id">

                <select id="kelas_filter" onchange="filterSiswa()" required>
                    <option value="">-- Pilih Kelas --</option>

                    <?php
                        // $kelas = mysqli_query($conn,"SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");
                        if($kelas_wali){
    $kelas = mysqli_query($conn,"SELECT DISTINCT kelas FROM siswa WHERE kelas='$kelas_wali'");
}else{
    $kelas = mysqli_query($conn,"SELECT DISTINCT kelas FROM siswa");
}
                        while($k=mysqli_fetch_array($kelas)){
                            echo "<option value='$k[kelas]'>$k[kelas]</option>";
                        }
                    ?>
                </select>

                <select name="siswa_id" id="siswa_id" required>
                    <option value="">-- Pilih Siswa --</option>

                    <?php
    $siswa = mysqli_query($conn,"SELECT * FROM siswa");
    while($s=mysqli_fetch_array($siswa)){
        echo "<option value='$s[id]' data-kelas='$s[kelas]'>$s[nama]</option>";
    }
    ?>
                </select>

                <select name="mapel_id" id="mapel_id" required>
                    <option value="">-- Pilih Mapel --</option>
                    <?php
$mapel = mysqli_query($conn,"SELECT * FROM mapel");
while($m=mysqli_fetch_array($mapel)){
echo "<option value='$m[id]'>$m[nama_mapel]</option>";
}
?>
                </select>

                <input type="number" name="nilai" id="nilai" placeholder="Nilai" required>
                <textarea name="deskripsi" id="deskripsi" placeholder="Deskripsi"></textarea>

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

        document.getElementById('modalTitle').innerText = 'Input Nilai';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'simpan';
        btn.innerText = 'Simpan';
        btn.style.background = '#3b82f6';

        document.getElementById('id').value = '';
        document.getElementById('nilai').value = '';
        document.getElementById('deskripsi').value = '';
    }

    function closeModal() {
        document.getElementById('modalForm').style.display = 'none';
    }

    function editData(id, siswa_id, mapel_id, nilai, deskripsi, kelas) {
        openModal();

        document.getElementById('modalTitle').innerText = 'Edit Nilai';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'update';
        btn.innerText = 'Update';
        btn.style.background = '#f59e0b';

        document.getElementById('id').value = id;

        // set kelas
        document.getElementById('kelas_filter').value = kelas;

        // filter siswa berdasarkan kelas
        filterSiswa();

        // set siswa setelah filter
        document.getElementById('siswa_id').value = siswa_id;

        document.getElementById('mapel_id').value = mapel_id;
        document.getElementById('nilai').value = nilai;
        document.getElementById('deskripsi').value = deskripsi;
    }

    window.onclick = function(e) {
        if (e.target == document.getElementById('modalForm')) {
            closeModal();
        }
    }

    function filterSiswa() {
        let kelas = document.getElementById('kelas_filter').value;
        let siswa = document.getElementById('siswa_id').options;

        for (let i = 0; i < siswa.length; i++) {
            let opt = siswa[i];

            if (!opt.dataset.kelas) {
                opt.style.display = 'block';
                continue;
            }

            if (opt.dataset.kelas === kelas) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        }

        document.getElementById('siswa_id').value = '';
    }

    // function loadSiswa(kelas, selected_id = null) {
    //     fetch('get_siswa.php?kelas=' + kelas)
    //         .then(res => res.text())
    //         .then(data => {
    //             document.getElementById('siswa_id').innerHTML = data;

    //             if (selected_id) {
    //                 document.getElementById('siswa_id').value = selected_id;
    //             }
    //         });
    // }
    </script>

</body>

</html>

<?php
// ================= SIMPAN =================
if(isset($_POST['simpan'])){
mysqli_query($conn,"INSERT INTO nilai 
(siswa_id,mapel_id,guru_id,semester,bulan,nilai,deskripsi)
VALUES (
'$_POST[siswa_id]',
'$_POST[mapel_id]',
'1',
'Ganjil',
'Januari',
'$_POST[nilai]',
'$_POST[deskripsi]'
)");
echo "<script>location='input_nilai.php';</script>";
}

// ================= UPDATE =================
if(isset($_POST['update'])){
mysqli_query($conn,"UPDATE nilai SET
siswa_id='$_POST[siswa_id]',
mapel_id='$_POST[mapel_id]',
nilai='$_POST[nilai]',
deskripsi='$_POST[deskripsi]'
WHERE id='$_POST[id]'");
echo "<script>location='input_nilai.php';</script>";
}

// ================= HAPUS =================
if(isset($_GET['hapus'])){
mysqli_query($conn,"DELETE FROM nilai WHERE id='$_GET[hapus]'");
echo "<script>location='input_nilai.php';</script>";
}

// ================= IMPORT XLSX =================
if(isset($_POST['import_excel'])){
    if(!$excel_ready){
        echo "<script>alert('Library belum ada');</script>";
        exit;
    }

    $file = $_FILES['file']['tmp_name'];

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray();

    foreach($sheet as $i => $row){
        if($i == 0) continue;

        $nis   = mysqli_real_escape_string($conn,$row[0]);
        $mapel = mysqli_real_escape_string($conn,$row[1]);
        $nilai = $row[2];
        $desk  = mysqli_real_escape_string($conn,$row[3]);

        // cari siswa berdasarkan NIS
        $siswa = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM siswa WHERE nis='$nis'"));
        if(!$siswa) continue;

        // cari mapel berdasarkan nama
        $mapel_q = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM mapel WHERE nama_mapel='$mapel'"));
        if(!$mapel_q) continue;

        mysqli_query($conn,"INSERT INTO nilai 
        (siswa_id,mapel_id,guru_id,semester,bulan,nilai,deskripsi)
        VALUES (
        '{$siswa['id']}',
        '{$mapel_q['id']}',
        '1',
        'Ganjil',
        'Januari',
        '$nilai',
        '$desk'
        )");
    }

    echo "<script>alert('Import berhasil');location='input_nilai.php';</script>";
}
?>