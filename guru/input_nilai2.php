<?php 
include '../config/auth.php';
cek_role(['guru']);
include '../config/koneksi.php';

$guru_id = $_SESSION['user']['id'];

// ================= AMBIL RELASI =================
$relasi = mysqli_query($conn,"
SELECT mapel_id, kelas 
FROM guru_mapel_kelas 
WHERE guru_id='$guru_id'
");

$mapel_ids = [];
$kelas_list = [];

while($r = mysqli_fetch_assoc($relasi)){
    $mapel_ids[] = $r['mapel_id'];
    $kelas_list[] = "'".$r['kelas']."'";
}

$mapel_ids_str = !empty($mapel_ids) ? implode(',', $mapel_ids) : '0';
$kelas_str = !empty($kelas_list) ? implode(',', $kelas_list) : "''";

// ================= SEARCH =================
$search = $_GET['search'] ?? '';
$s = mysqli_real_escape_string($conn,$search);

$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$start = ($page - 1) * $limit;

// ================= QUERY DATA =================
$q = mysqli_query($conn,"
SELECT n.*, s.nama as nama_siswa, s.kelas, m.nama_mapel 
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
JOIN mapel m ON n.mapel_id = m.id
WHERE n.mapel_id IN ($mapel_ids_str)
AND s.kelas IN ($kelas_str)
".(!empty($search) ? " AND (s.nama LIKE '%$s%' OR m.nama_mapel LIKE '%$s%')" : "")."
LIMIT $start,$limit
");

$total = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as total 
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
JOIN mapel m ON n.mapel_id = m.id
WHERE n.mapel_id IN ($mapel_ids_str)
AND s.kelas IN ($kelas_str)
"))['total'];

$pages = ceil($total / $limit);

// ================= CEK EXCEL =================
$excel_ready = false;
if(file_exists('../vendor/autoload.php')){
    require '../vendor/autoload.php';
    $excel_ready = true;
}

// ================= TEMPLATE =================
if(isset($_GET['download_template'])){
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1','nis');
    $sheet->setCellValue('B1','mapel');
    $sheet->setCellValue('C1','nilai');
    $sheet->setCellValue('D1','deskripsi');

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
    <title>Input Nilai</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
    .modal {
        display: none;
        /* WAJIB */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;

        background: rgba(0, 0, 0, 0.5);

        z-index: 9999;

        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 12px;

        width: 90%;
        max-width: 420px;

        position: relative;
    }
    </style>

</head>

<body>

    <div class="sidebar">
        <h2>Guru</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="input_nilai.php">Input Nilai</a>
        <a href="../auth/logout.php">Logout</a>
    </div>

    <div class="content">

        <div class="navbar">
            <h3>Input Nilai</h3>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <button class="btn" onclick="openModal()">+ Input Nilai</button>

        <br><br>

        <!-- IMPORT -->
        <div class="card">
            <h3>Import Excel</h3>

            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="file" required>
                <button class="btn" name="import_excel">Import</button>
                <a href="?download_template=1" class="btn">Template</a>
            </form>
        </div>

        <!-- TABLE -->
        <div class="card">
            <h3>Data Nilai</h3>

            <table>
                <tr>
                    <th>No</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Mapel</th>
                    <th>Nilai</th>
                    <th>Aksi</th>
                </tr>

                <?php $no=$start+1; while($d=mysqli_fetch_array($q)){ ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $d['nama_siswa'] ?></td>
                    <td><?= $d['kelas'] ?></td>
                    <td><?= $d['nama_mapel'] ?></td>
                    <td><?= $d['nilai'] ?></td>
                    <td>
                        <a href="#" onclick="editData(
'<?= $d['id'] ?>',
'<?= $d['siswa_id'] ?>',
'<?= $d['mapel_id'] ?>',
'<?= $d['nilai'] ?>',
'<?= htmlspecialchars($d['deskripsi'],ENT_QUOTES) ?>',
'<?= $d['kelas'] ?>'
)">Edit</a>


                    </td>
                </tr>
                <?php } ?>
            </table>

        </div>
    </div>

    <!-- MODAL -->
    <div class="modal" id="modalForm">
        <div class="modal-content">

            <h3 id="modalTitle">Input Nilai</h3>

            <form method="POST">

                <input type="hidden" name="id" id="id">

                <select id="kelas_filter" onchange="filterSiswa()" required>
                    <option value="">-- Kelas --</option>

                    <?php
$kelas = mysqli_query($conn,"
SELECT DISTINCT kelas 
FROM siswa 
WHERE kelas IN ($kelas_str)
");
while($k=mysqli_fetch_array($kelas)){
echo "<option value='$k[kelas]'>$k[kelas]</option>";
}
?>
                </select>

                <select name="siswa_id" id="siswa_id" required>
                    <option value="">-- Siswa --</option>
                    <?php
$siswa = mysqli_query($conn,"
SELECT * FROM siswa WHERE kelas IN ($kelas_str)
");
while($s=mysqli_fetch_array($siswa)){
echo "<option value='$s[id]' data-kelas='$s[kelas]'>$s[nama]</option>";
}
?>
                </select>

                <select name="mapel_id" id="mapel_id" required>
                    <option value="">-- Mapel --</option>
                    <?php
$mapel = mysqli_query($conn,"
SELECT * FROM mapel WHERE id IN ($mapel_ids_str)
");
while($m=mysqli_fetch_array($mapel)){
echo "<option value='$m[id]'>$m[nama_mapel]</option>";
}
?>
                </select>

                <input type="number" name="nilai" id="nilai" required>
                <textarea name="deskripsi" id="deskripsi"></textarea>

                <div style="margin-top:10px; display:flex; gap:10px;">
                    <button type="submit" name="simpan" id="btnSubmit">Simpan</button>
                    <button type="button" onclick="closeModal()">Tutup</button>
                </div>

            </form>
        </div>
    </div>

    <script>
    function openModal() {
        const modal = document.getElementById('modalForm');
        modal.style.display = 'flex';

        // reset form
        document.getElementById('id').value = '';
        document.getElementById('kelas_filter').value = '';
        document.getElementById('siswa_id').value = '';
        document.getElementById('mapel_id').value = '';
        document.getElementById('nilai').value = '';
        document.getElementById('deskripsi').value = '';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'simpan';
        btn.innerText = 'Simpan';
    }

    function closeModal() {
        document.getElementById('modalForm').style.display = 'none';
    }

    function editData(id, siswa, mapel, nilai, desk, kelas) {
        const modal = document.getElementById('modalForm');
        modal.style.display = 'flex';

        let btn = document.getElementById('btnSubmit');
        btn.name = 'update';
        btn.innerText = 'Update';

        document.getElementById('id').value = id;

        document.getElementById('kelas_filter').value = kelas;
        filterSiswa();

        setTimeout(() => {
            document.getElementById('siswa_id').value = siswa;
        }, 100);

        document.getElementById('mapel_id').value = mapel;
        document.getElementById('nilai').value = nilai;
        document.getElementById('deskripsi').value = desk;
    }

    function filterSiswa() {
        let kelas = document.getElementById('kelas_filter').value;
        let opt = document.getElementById('siswa_id').options;

        for (let i = 0; i < opt.length; i++) {
            let o = opt[i];

            if (!o.dataset.kelas) continue;

            o.style.display = (o.dataset.kelas === kelas) ? 'block' : 'none';
        }

        document.getElementById('siswa_id').value = '';
    }

    // klik luar modal = close
    window.onclick = function(e) {
        const modal = document.getElementById('modalForm');
        if (e.target === modal) {
            closeModal();
        }
    }
    </script>

</body>

</html>

<?php
// ================= SIMPAN =================
if(isset($_POST['simpan'])){

$cek = mysqli_query($conn,"
SELECT * FROM guru_mapel_kelas
WHERE guru_id='$guru_id'
AND mapel_id='$_POST[mapel_id]'
AND kelas = (
SELECT kelas FROM siswa WHERE id='$_POST[siswa_id]'
)");

if(mysqli_num_rows($cek)==0){
echo "<script>alert('Tidak diizinkan');</script>";
exit;
}

mysqli_query($conn,"INSERT INTO nilai 
(siswa_id,mapel_id,guru_id,semester,bulan,nilai,deskripsi)
VALUES(
'$_POST[siswa_id]',
'$_POST[mapel_id]',
'$guru_id',
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
nilai='$_POST[nilai]',
deskripsi='$_POST[deskripsi]'
WHERE id='$_POST[id]'");

echo "<script>location='input_nilai.php';</script>";
}
?>