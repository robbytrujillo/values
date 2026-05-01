<?php 
ob_start();
include '../config/auth.php';
cek_role(['guru']);
include '../config/koneksi.php';

$guru_id = $_SESSION['user']['id'];

/* ================= FILTER ================= */
$filter_jenis = $_GET['filter_jenis'] ?? '';
$whereJenis = ($filter_jenis!='') ? "AND n.jenis='$filter_jenis'" : "";

/* ================= CEK EXCEL ================= */
$excel_ready = false;
if(file_exists('../vendor/autoload.php')){
    require '../vendor/autoload.php';
    $excel_ready = true;
}

/* ================= RELASI ================= */
$relasi = mysqli_query($conn,"SELECT mapel_id, kelas_id FROM mengajar WHERE guru_id='$guru_id'");

$mapel_ids = [];
$kelas_ids = [];

while($r = mysqli_fetch_assoc($relasi)){
    $mapel_ids[] = $r['mapel_id'];
    $kelas_ids[] = $r['kelas_id'];
}

$mapel_ids_str = $mapel_ids ? implode(',', $mapel_ids) : '0';
$kelas_ids_str = $kelas_ids ? implode(',', $kelas_ids) : '0';

/* ================= QUERY ================= */
$q = mysqli_query($conn,"
SELECT n.*, s.nama as nama_siswa, k.nama_kelas, m.nama_mapel
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
JOIN kelas k ON s.kelas_id = k.id
JOIN mapel m ON n.mapel_id = m.id
WHERE n.mapel_id IN ($mapel_ids_str)
AND s.kelas_id IN ($kelas_ids_str)
$whereJenis
ORDER BY n.id DESC
");

/* ================= TEMPLATE EXCEL ================= */
if(isset($_GET['download_template'])){
    if(!$excel_ready){
        die('PhpSpreadsheet belum terinstall');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1','nisn');
    $sheet->setCellValue('B1','mapel');
    $sheet->setCellValue('C1','nilai');
    $sheet->setCellValue('D1','jenis');
    $sheet->setCellValue('E1','tanggal');
    $sheet->setCellValue('F1','deskripsi');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="template_nilai.xlsx"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

/* ================= IMPORT ================= */
if(isset($_POST['import_excel'])){

    if(!$excel_ready){
        echo "<script>alert('PhpSpreadsheet belum terinstall');</script>";
        exit;
    }

    $file = $_FILES['file']['tmp_name'];
    $rows = \PhpOffice\PhpSpreadsheet\IOFactory::load($file)->getActiveSheet()->toArray();

    $berhasil = 0;
    $gagal = 0;

    foreach($rows as $i=>$row){
        if($i==0) continue;

        $nisn  = mysqli_real_escape_string($conn,$row[0]);
        $mapel = mysqli_real_escape_string($conn,$row[1]);
        $nilai = (int)$row[2];
        $jenis = strtolower(trim($row[3]));
        $tgl   = $row[4];
        $desk  = mysqli_real_escape_string($conn,$row[5]);

        if(!$nisn || !$mapel){ $gagal++; continue; }
        if($nilai < 0 || $nilai > 100){ $gagal++; continue; }

        $s = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id, kelas_id FROM siswa WHERE nisn='$nisn'"));
        $m = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM mapel WHERE nama_mapel='$mapel'"));

        if(!$s || !$m){ $gagal++; continue; }

        $cek = mysqli_query($conn,"SELECT * FROM mengajar 
        WHERE guru_id='$guru_id'
        AND mapel_id='{$m['id']}'
        AND kelas_id='{$s['kelas_id']}'");

        if(mysqli_num_rows($cek)==0){ $gagal++; continue; }

        mysqli_query($conn,"INSERT INTO nilai
        (siswa_id,mapel_id,guru_id,nilai,jenis,tanggal,deskripsi)
        VALUES(
        '{$s['id']}',
        '{$m['id']}',
        '$guru_id',
        '$nilai',
        '$jenis',
        '$tgl',
        '$desk'
        )");

        $berhasil++;
    }

    echo "<script>alert('Import: $berhasil berhasil, $gagal gagal');location='input_nilai.php';</script>";
}

/* ======================
   HARI INDO
====================== */
function hariIndonesia($tanggal) {
    $hari = date('l', strtotime($tanggal));

    $hariIndo = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu'
    ];

    return $hariIndo[$hari];
}

function tanggalIndonesia($tanggal) {

    $bulan = [
        1 => 'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];

    $tanggalExplode = explode('-', date('Y-m-d', strtotime($tanggal)));

    return $tanggalExplode[2] . ' ' .
           $bulan[(int)$tanggalExplode[1]] . ' ' .
           $tanggalExplode[0];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Input Nilai</title>

    <link rel="icon" type="image/png" href="../assets/images/logo-sma.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <style>
    .form-inline select {
        min-width: 180px;
    }

    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .btn {
        border-radius: 30px !important;
    }

    .modal-content {
        border-radius: 15px;
    }

    .table {
        width: 100% !important;
    }

    table.dataTable {
        width: 100% !important;
        margin-top: 15px !important;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px;
        padding: 5px 10px;
        width: 220px !important;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 20px;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        width: 50%;
        margin-bottom: 15px;
    }

    .dataTables_wrapper .dataTables_length {
        float: left;
        text-align: left;
    }

    .dataTables_wrapper .dataTables_filter {
        float: right;
        text-align: right;
    }

    .dataTables_wrapper .dataTables_info {
        float: left;
        padding-top: 15px;
    }

    .dataTables_wrapper .dataTables_paginate {
        float: right;
        padding-top: 10px;
    }

    .dataTables_wrapper .row {
        display: flex;
        align-items: center;
        width: 100%;
        margin: 0;
    }

    /* TABLE MOBILE */
    #tableNilai th,
    #tableNilai td {
        vertical-align: middle;
        white-space: nowrap;
    }

    #tableNilai th:last-child,
    #tableNilai td:last-child {
        min-width: 150px;
        text-align: center;
    }

    #tableNilai td .btn {
        min-width: 70px;
    }

    /* MOBILE */
    @media(max-width:767px) {
        .form-inline {
            display: block !important;
        }

        .form-inline .form-control,
        .form-inline .btn {
            width: 100%;
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            width: 100%;
            float: none;
            text-align: left !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin-left: 0;
            margin-top: 8px;
        }

        .dataTables_wrapper .row {
            display: block;
        }

        .pagination {
            flex-wrap: wrap;
        }

        #tableNilai td:last-child {
            min-width: 120px;
        }

        #tableNilai td .btn {
            width: 100%;
            margin-bottom: 5px;
        }
    }
    </style>
</head>

<body>

    <?php include 'template.php'; ?>

    <div class="container-fluid mt-4">

        <div class="d-flex justify-content-between mb-3 text-center">
            <h4>INPUT NILAI</h4>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="form-inline">

                    <select name="filter_jenis" class="form-control mr-2">
                        <option value="">Semua Jenis</option>
                        <option value="harian" <?= $filter_jenis=='harian'?'selected':'' ?>>Harian</option>
                        <option value="bulanan" <?= $filter_jenis=='bulanan'?'selected':'' ?>>Bulanan</option>
                        <option value="semester" <?= $filter_jenis=='semester'?'selected':'' ?>>Semester</option>
                    </select>

                    <button class="btn btn-primary btn-sm mr-2 rounded-pill">Filter</button>
                    <a href="input_nilai.php" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>

                </form>
            </div>
        </div>

        <button class="btn btn-primary mb-3 rounded-pill" data-toggle="modal" data-target="#modalForm">
            Tambah Nilai
        </button>

        <div class="card mb-3">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="form-inline">
                    <input type="file" name="file" class="form-control mr-2" required>
                    <button class="btn btn-success mr-2 rounded-pill" name="import_excel">Import</button>
                    <a href="?download_template=1" class="btn btn-info rounded-pill">Template</a>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table id="tableNilai" class="table table-bordered table-striped text-nowrap"
                        style="width:100%; min-width:1200px;">

                        <thead class="thead-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Waktu</th>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Mapel</th>
                                <th>Nilai</th>
                                <th>Jenis</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $no=1; while($d=mysqli_fetch_assoc($q)){ ?>
                            <tr>
                                <td><?= $no++ ?></td>

                                <td>
                                    <?= hariIndonesia($d['tanggal']); ?>,
                                    <?= tanggalIndonesia($d['tanggal']); ?>
                                </td>

                                <td><?= $d['nama_siswa'] ?></td>
                                <td><?= $d['nama_kelas'] ?></td>
                                <td><?= $d['nama_mapel'] ?></td>
                                <td><?= $d['nilai'] ?></td>

                                <td class="text-center">
                                    <span class="badge badge-<?=
                                                $d['jenis']=='harian' ? 'info' :
                                                ($d['jenis']=='bulanan' ? 'warning' : 'success')
                                            ?> px-3 py-2" style="border-radius: 30px">
                                        <?= ucfirst($d['jenis']) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class=" d-flex flex-column flex-md-row">
                                        <button class="btn btn-warning btn-sm mb-1 mr-md-1" onclick="editData(
                                        '<?= $d['id'] ?>',
                                        '<?= $d['nilai'] ?>',
                                        '<?= $d['jenis'] ?>',
                                        '<?= $d['tanggal'] ?>',
                                        '<?= htmlspecialchars($d['deskripsi'],ENT_QUOTES) ?>'
                                    )">
                                            Edit
                                        </button>

                                        <button class="btn btn-danger btn-sm" onclick="hapusData('<?= $d['id'] ?>')">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>

    <div class="modal fade" id="modalForm">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST">
                    <div class="modal-body">

                        <select name="tipe_input" id="tipe_input" class="form-control mb-2" onchange="toggleTipeInput()"
                            required>
                            <option value="per_orang">Input Per Siswa</option>
                            <option value="per_kelas">Input Sekaligus Per Kelas</option>
                        </select>

                        <select name="kelas_filter" id="kelas_filter" class="form-control mb-2" onchange="filterSiswa()"
                            required>
                            <option value="">Pilih Kelas</option>
                            <?php
                        $kelas = mysqli_query($conn,"SELECT * FROM kelas WHERE id IN ($kelas_ids_str)");
                        while($k=mysqli_fetch_assoc($kelas)){
                            echo "<option value='$k[id]'>$k[nama_kelas]</option>";
                        }
                        ?>
                        </select>

                        <select name="siswa_id" id="siswa_id" class="form-control mb-2" required>
                            <option value="">Pilih Siswa</option>
                            <?php
                        $siswa = mysqli_query($conn,"SELECT * FROM siswa WHERE kelas_id IN ($kelas_ids_str)");
                        while($s=mysqli_fetch_assoc($siswa)){
                            echo "<option value='$s[id]' data-kelas='$s[kelas_id]'>$s[nama]</option>";
                        }
                        ?>
                        </select>

                        <select name="mapel_id" id="mapel_id" class="form-control mb-2" required>
                            <option value="">Pilih Mapel</option>
                            <?php
                        $mapel = mysqli_query($conn,"SELECT * FROM mapel WHERE id IN ($mapel_ids_str)");
                        while($m=mysqli_fetch_assoc($mapel)){
                            echo "<option value='$m[id]'>$m[nama_mapel]</option>";
                        }
                        ?>
                        </select>

                        <select name="jenis_nilai" id="jenis_nilai" class="form-control mb-2" required>
                            <option value="">Pilih Jenis</option>
                            <option value="harian">Harian</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="semester">Semester</option>
                        </select>

                        <input type="number" name="nilai" class="form-control mb-2" placeholder="Masukan Nilai"
                            required>
                        <input type="date" name="tanggal" class="form-control mb-2" required>

                        <textarea name="deskripsi" id="deskripsi" class="form-control mb-2"
                            placeholder="Deskripsi (Otomatis menyesuaikan)"></textarea>

                        <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>

                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST">
                    <div class="modal-body">

                        <input type="hidden" name="id" id="edit_id">

                        <input type="number" name="nilai" id="edit_nilai" class="form-control mb-2" required>

                        <select name="jenis" id="edit_jenis" class="form-control mb-2">
                            <option value="harian">Harian</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="semester">Semester</option>
                        </select>

                        <input type="date" name="tanggal" id="edit_tanggal" class="form-control mb-2" required>

                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control mb-2"></textarea>

                        <button type="submit" name="update" class="btn btn-warning">Update</button>

                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <canvas id="chartNilai" style="height:300px;"></canvas>
        </div>
    </div>

    <?php include 'template_footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function filterSiswa() {
        let kelas = $('#kelas_filter').val();
        $('#siswa_id option').each(function() {
            let k = $(this).data('kelas');
            $(this).toggle(!k || kelas == "" || k == kelas);
        });
        $('#siswa_id').val(''); // Reset pilihan siswa setiap ganti kelas
        generateDeskripsi(); // Panggil fungsi auto-deskripsi
    }

    // TAMBAHAN: Fungsi untuk menyembunyikan pilihan siswa jika memilih input "Per Kelas"
    function toggleTipeInput() {
        let tipe = $('#tipe_input').val();
        if (tipe === 'per_kelas') {
            $('#siswa_id').hide().removeAttr('required');
        } else {
            $('#siswa_id').show().attr('required', 'required');
        }
    }

    // TAMBAHAN: Fungsi Auto Generate Deskripsi
    function generateDeskripsi() {
        let namaKelas = $('#kelas_filter option:selected').text();
        let namaMapel = $('#mapel_id option:selected').text();
        let namaJenis = $('#jenis_nilai option:selected').text();

        // Pastikan bukan option default "Pilih..." yang terambil
        if ($('#kelas_filter').val() !== "" && $('#mapel_id').val() !== "" && $('#jenis_nilai').val() !== "") {
            let deskripsiOtomatis = `Penilaian ${namaJenis} untuk Mata Pelajaran ${namaMapel} Kelas ${namaKelas}`;
            $('#deskripsi').val(deskripsiOtomatis);
        }
    }

    // Event listener untuk trigger Auto-Deskripsi
    $('#mapel_id, #jenis_nilai').change(function() {
        generateDeskripsi();
    });
    </script>

    <script>
    function editData(id, nilai, jenis, tanggal, deskripsi) {
        $('#modalEdit').modal('show');

        $('#edit_id').val(id);
        $('#edit_nilai').val(nilai);
        $('#edit_jenis').val(jenis);
        $('#edit_tanggal').val(tanggal);
        $('#edit_deskripsi').val(deskripsi);
    }

    function hapusData(id) {
        if (confirm('Yakin hapus data?')) {
            window.location = 'input_nilai.php?hapus=' + id;
        }
    }
    </script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#tableNilai').DataTable({
            responsive: false,
            autoWidth: false,
            pageLength: 10,
            scrollX: true,
            columnDefs: [{
                targets: -1,
                orderable: false
            }],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan data: _MENU_",
                zeroRecords: "Data tidak ditemukan",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                paginate: {
                    previous: "Sebelumnya",
                    next: "Berikutnya"
                }
            }
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    let chart;

    function loadChart(jenis = '') {

        $.ajax({
            url: 'ajax_nilai.php',
            method: 'GET',
            data: {
                jenis: jenis
            },
            dataType: 'json',
            success: function(data) {

                let labels = [];
                let nilai = [];

                data.forEach(d => {
                    labels.push(d.nama_siswa);
                    nilai.push(d.nilai);
                });

                let ctx = document.getElementById('chartNilai').getContext('2d');

                if (chart) {
                    chart.destroy();
                }

                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Nilai Siswa',
                            data: nilai
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            },
            error: function(xhr) {
                console.log("ERROR AJAX:", xhr.responseText);
            }
        });
    }

    // LOAD AWAL
    $(document).ready(function() {
        loadChart("<?= $filter_jenis ?>");
    });
    </script>

    <script>
    $('form').submit(function() {
        let jenis = $('[name=filter_jenis]').val();
        loadChart(jenis);
    });
    </script>

</body>

</html>

<?php
if(isset($_POST['simpan'])){

// TAMBAHAN: Penyesuaian variabel tangkapan
$tipe_input = $_POST['tipe_input'];
$kelas_id = $_POST['kelas_filter']; // Didapat dari dropdown kelas yang sekarang diberi atribut name
$mapel_id = $_POST['mapel_id'];
$nilai    = (int)$_POST['nilai'];
$jenis    = $_POST['jenis_nilai'];
$tanggal  = $_POST['tanggal'];
$desk     = mysqli_real_escape_string($conn,$_POST['deskripsi']);

if($nilai < 0 || $nilai > 100){
    echo "<script>alert('Nilai 0-100');</script>"; exit;
}

// LOGIKA INPUT BARU (Mengecek tipe input)
if($tipe_input == 'per_kelas') {
    
    // Validasi relasi mengajar khusus per_kelas
    $cek = mysqli_query($conn,"
    SELECT * FROM mengajar
    WHERE guru_id='$guru_id'
    AND mapel_id='$mapel_id'
    AND kelas_id='$kelas_id'
    ");

    if(mysqli_num_rows($cek)==0){
        echo "<script>alert('Tidak diizinkan mengajar di kelas ini');</script>"; exit;
    }

    // Ambil semua siswa di kelas yang dipilih, dan looping query insert-nya
    $get_siswa = mysqli_query($conn, "SELECT id FROM siswa WHERE kelas_id='$kelas_id'");
    while($row_siswa = mysqli_fetch_assoc($get_siswa)){
        $s_id = $row_siswa['id'];
        mysqli_query($conn,"INSERT INTO nilai
        (siswa_id,mapel_id,guru_id,nilai,jenis,tanggal,deskripsi)
        VALUES('$s_id','$mapel_id','$guru_id','$nilai','$jenis','$tanggal','$desk')
        ");
    }

} else {
    // LOGIKA LAMA: Input untuk perorangan/siswa spesifik
    $siswa_id = $_POST['siswa_id'];
    
    $cek = mysqli_query($conn,"
    SELECT * FROM mengajar
    WHERE guru_id='$guru_id'
    AND mapel_id='$mapel_id'
    AND kelas_id=(SELECT kelas_id FROM siswa WHERE id='$siswa_id')
    ");

    if(mysqli_num_rows($cek)==0){
        echo "<script>alert('Tidak diizinkan');</script>"; exit;
    }

    mysqli_query($conn,"INSERT INTO nilai
    (siswa_id,mapel_id,guru_id,nilai,jenis,tanggal,deskripsi)
    VALUES('$siswa_id','$mapel_id','$guru_id','$nilai','$jenis','$tanggal','$desk')
    ");
}

echo "<script>location='input_nilai.php';</script>";
}

// Edit
if(isset($_POST['update'])){

$id       = $_POST['id'];
$nilai    = (int)$_POST['nilai'];
$jenis    = $_POST['jenis'];
$tanggal  = $_POST['tanggal'];
$desk     = mysqli_real_escape_string($conn,$_POST['deskripsi']);

mysqli_query($conn,"UPDATE nilai SET
nilai='$nilai',
jenis='$jenis',
tanggal='$tanggal',
deskripsi='$desk'
WHERE id='$id'
");

echo "<script>location='input_nilai.php';</script>";
}

// hapus
if(isset($_GET['hapus'])){

$id = $_GET['hapus'];

mysqli_query($conn,"DELETE FROM nilai WHERE id='$id'");

echo "<script>location='input_nilai.php';</script>";
}
?>