<?php
include '../config/auth.php';
cek_role(['guru']);
include '../config/koneksi.php';

$guru_id = $_SESSION['user']['id'];

// ================= RELASI MENGAJAR =================
$relasi = mysqli_query($conn,"
SELECT mapel_id, kelas_id 
FROM mengajar 
WHERE guru_id='$guru_id'
");

$mapel_ids = [];
$kelas_ids = [];

while($r = mysqli_fetch_assoc($relasi)){
    $mapel_ids[] = $r['mapel_id'];
    $kelas_ids[] = $r['kelas_id'];
}

$mapel_ids_str = !empty($mapel_ids) ? implode(',', $mapel_ids) : '0';
$kelas_ids_str = !empty($kelas_ids) ? implode(',', $kelas_ids) : '0';

// ================= PAGINATION =================
$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$start = ($page - 1) * $limit;

// ================= QUERY =================
$q = mysqli_query($conn,"
SELECT n.*, s.nama as nama_siswa, k.nama_kelas, m.nama_mapel
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
JOIN kelas k ON s.kelas_id = k.id
JOIN mapel m ON n.mapel_id = m.id
WHERE n.mapel_id IN ($mapel_ids_str)
AND s.kelas_id IN ($kelas_ids_str)
ORDER BY n.created_at DESC
LIMIT $start,$limit
");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Input Nilai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-4">

        <div class="d-flex justify-content-between mb-3">
            <h4>Input Nilai</h4>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalForm">
            + Input Nilai
        </button>

        <div class="card">
            <div class="card-body">

                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Mapel</th>
                            <th>Nilai</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no=$start+1; while($d=mysqli_fetch_assoc($q)){ ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $d['nama_siswa'] ?></td>
                            <td><?= $d['nama_kelas'] ?></td>
                            <td><?= $d['nama_mapel'] ?></td>
                            <td><?= $d['nilai'] ?></td>
                            <td><?= $d['jenis_nilai'] ?></td>
                            <td><?= $d['tanggal'] ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editData(
'<?= $d['id'] ?>',
'<?= $d['siswa_id'] ?>',
'<?= $d['mapel_id'] ?>',
'<?= $d['nilai'] ?>',
'<?= $d['jenis_nilai'] ?>',
'<?= $d['tanggal'] ?>',
'<?= htmlspecialchars($d['deskripsi'],ENT_QUOTES) ?>'
)">Edit</button>
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
                        <h5 class="modal-title">Input Nilai</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="id" id="id">

                        <div class="form-group">
                            <label>Kelas</label>
                            <select id="kelas_filter" class="form-control" onchange="filterSiswa()">
                                <option value="">-- Pilih --</option>
                                <?php
$kelas = mysqli_query($conn,"SELECT * FROM kelas WHERE id IN ($kelas_ids_str)");
while($k=mysqli_fetch_array($kelas)){
echo "<option value='$k[id]'>$k[nama_kelas]</option>";
}
?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Siswa</label>
                            <select name="siswa_id" id="siswa_id" class="form-control">
                                <option value="">-- Pilih --</option>
                                <?php
$siswa = mysqli_query($conn,"SELECT * FROM siswa WHERE kelas_id IN ($kelas_ids_str)");
while($s=mysqli_fetch_array($siswa)){
echo "<option value='$s[id]' data-kelas='$s[kelas_id]'>$s[nama]</option>";
}
?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Mapel</label>
                            <select name="mapel_id" id="mapel_id" class="form-control">
                                <option value="">-- Pilih --</option>
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
                            <input type="number" name="nilai" id="nilai" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Jenis Nilai</label>
                            <select name="jenis_nilai" id="jenis_nilai" class="form-control">
                                <option value="harian">Harian</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="semester">Semester</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="simpan" id="btnSubmit" class="btn btn-primary">Simpan</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function filterSiswa() {
        let kelas = document.getElementById('kelas_filter').value;
        let opt = document.getElementById('siswa_id').options;

        for (let i = 0; i < opt.length; i++) {
            let o = opt[i];
            if (!o.dataset.kelas) continue;
            o.style.display = (kelas === "" || o.dataset.kelas === kelas) ? 'block' : 'none';
        }
    }

    function editData(id, siswa, mapel, nilai, jenis, tanggal, desk) {
        $('#modalForm').modal('show');

        $('#modalForm').on('shown.bs.modal', function() {
            $('#siswa_id').val(siswa);
            $(this).off('shown.bs.modal');
        });

        $('#id').val(id);
        $('#mapel_id').val(mapel);
        $('#nilai').val(nilai);
        $('#jenis_nilai').val(jenis);
        $('#tanggal').val(tanggal);
        $('#deskripsi').val(desk);

        $('#btnSubmit').attr('name', 'update')
            .removeClass('btn-primary')
            .addClass('btn-warning')
            .text('Update');
    }
    </script>

</body>

</html>

<?php
// ================= SIMPAN =================
if(isset($_POST['simpan'])){

// validasi mengajar
$cek = mysqli_query($conn,"
SELECT * FROM mengajar
WHERE guru_id='$guru_id'
AND mapel_id='$_POST[mapel_id]'
AND kelas_id=(SELECT kelas_id FROM siswa WHERE id='$_POST[siswa_id]')
");

if(mysqli_num_rows($cek)==0){
echo "<script>alert('Tidak diizinkan');</script>"; exit;
}

mysqli_query($conn,"INSERT INTO nilai
(siswa_id,mapel_id,guru_id,nilai,jenis_nilai,tanggal,deskripsi)
VALUES(
'$_POST[siswa_id]',
'$_POST[mapel_id]',
'$guru_id',
'$_POST[nilai]',
'$_POST[jenis_nilai]',
'$_POST[tanggal]',
'$_POST[deskripsi]'
)");

echo "<script>location='input_nilai.php';</script>";
}

// ================= UPDATE =================
if(isset($_POST['update'])){
mysqli_query($conn,"UPDATE nilai SET
nilai='$_POST[nilai]',
jenis_nilai='$_POST[jenis_nilai]',
tanggal='$_POST[tanggal]',
deskripsi='$_POST[deskripsi]'
WHERE id='$_POST[id]'");

echo "<script>location='input_nilai.php';</script>";
}
?>