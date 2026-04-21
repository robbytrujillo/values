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
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">

    <div class="container-fluid flex-grow-1">
        <div class="row">

            <!-- SIDEBAR -->
            <nav class="col-md-2 d-none d-md-block bg-dark text-white" style="min-height:100vh;">
                <h5 class="text-center mt-3">👨‍🏫 Guru</h5>

                <a href="dashboard.php" class="d-block text-light p-2">Dashboard</a>
                <a href="input_nilai.php" class="d-block text-light p-2 bg-secondary">Input Nilai</a>
                <a href="../auth/logout.php" class="d-block text-light p-2">Logout</a>
            </nav>

            <!-- CONTENT -->
            <main class="col-md-10 p-4 d-flex flex-column">

                <!-- HEADER -->
                <div class="d-flex justify-content-between mb-3">
                    <h4>Input Nilai</h4>
                    <div><?= $_SESSION['user']['nama']; ?></div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <!-- BUTTON -->
                        <button class="btn btn-primary mb-3 mr-2 rounded-pill" data-toggle="modal"
                            data-target="#modalForm">
                            Input Nilai
                        </button>
                    </div>
                </div>

                <!-- IMPORT -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5>Import Excel</h5>

                        <form method="POST" enctype="multipart/form-data" class="form-inline">
                            <input type="file" name="file" class="form-control mr-2 mb-2" required>
                            <button class="btn btn-success mr-2 mb-2" name="import_excel">Import</button>
                            <a href="?download_template=1" class="btn btn-info mb-2">Template</a>
                        </form>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="card">
                    <div class="card-body">

                        <h5>Data Nilai</h5>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th>Mapel</th>
                                        <th>Nilai</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php $no=$start+1; while($d=mysqli_fetch_array($q)){ ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $d['nama_siswa'] ?></td>
                                        <td><?= $d['kelas'] ?></td>
                                        <td><?= $d['nama_mapel'] ?></td>
                                        <td><?= $d['nilai'] ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" onclick="editData(
                                                '<?= $d['id'] ?>',
                                                '<?= $d['siswa_id'] ?>',
                                                '<?= $d['mapel_id'] ?>',
                                                '<?= $d['nilai'] ?>',
                                                '<?= htmlspecialchars($d['deskripsi'],ENT_QUOTES) ?>',
                                                '<?= $d['kelas'] ?>'
                                                )">Edit</button>
                                        </td>
                                    </tr>
                                    <?php } ?>

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- FOOTER -->
                <footer class="mt-auto text-center py-3 border-top mt-4">
                    <small>© <?= date('Y') ?> IT Development IHBS</small>
                </footer>

            </main>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="modalForm">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Input Nilai</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="id" id="id">

                        <div class="form-group">
                            <label>Kelas</label>
                            <select id="kelas_filter" class="form-control" onchange="filterSiswa()" required>
                                <option value="">-- Kelas --</option>
                                <?php
                            $kelas = mysqli_query($conn,"SELECT DISTINCT kelas FROM siswa WHERE kelas IN ($kelas_str)");
                            while($k=mysqli_fetch_array($kelas)){
                                echo "<option value='$k[kelas]'>$k[kelas]</option>";
                            }
                            ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Siswa</label>
                            <select name="siswa_id" id="siswa_id" class="form-control" required>
                                <option value="">-- Siswa --</option>
                                <?php
                            $siswa = mysqli_query($conn,"SELECT * FROM siswa WHERE kelas IN ($kelas_str)");
                            while($s=mysqli_fetch_array($siswa)){
                                echo "<option value='$s[id]' data-kelas='$s[kelas]'>$s[nama]</option>";
                            }
                            ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Mapel</label>
                            <select name="mapel_id" id="mapel_id" class="form-control" required>
                                <option value="">-- Mapel --</option>
                                <?php
                            $mapel = mysqli_query($conn,"SELECT * FROM mapel WHERE id IN ($mapel_ids_str)");
                            while($m=mysqli_fetch_array($mapel)){
                                echo "<option value='$m[id]'>$m[nama_mapel]</option>";
                            }
                            ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Nilai</label>
                            <input type="number" name="nilai" id="nilai" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="simpan" id="btnSubmit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

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
        $('#modalForm').modal('show');

        // isi dulu data basic
        document.getElementById('id').value = id;
        document.getElementById('kelas_filter').value = kelas;

        // jalankan filter setelah modal benar-benar tampil
        $('#modalForm').on('shown.bs.modal', function() {

            filterSiswa();

            // set value siswa setelah filter
            document.getElementById('siswa_id').value = siswa;

            // remove event supaya tidak double
            $(this).off('shown.bs.modal');
        });

        document.getElementById('mapel_id').value = mapel;
        document.getElementById('nilai').value = nilai;
        document.getElementById('deskripsi').value = desk;

        let btn = document.getElementById('btnSubmit');
        btn.name = 'update';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-warning');
        btn.innerText = 'Update';
    }

    function filterSiswa() {
        let kelas = document.getElementById('kelas_filter').value;
        let opt = document.getElementById('siswa_id').options;

        for (let i = 0; i < opt.length; i++) {
            let o = opt[i];

            if (!o.dataset.kelas) continue;

            if (kelas === "") {
                o.style.display = 'block';
            } else {
                o.style.display = (o.dataset.kelas === kelas) ? 'block' : 'none';
            }
        }
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