<?php
session_start();
include '../config/koneksi.php';

/* ================= VALIDASI SESSION ================= */
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa'){
    header("Location: ../index.php");
    exit;
}

$siswa_id = intval($_SESSION['user']['id']);

/* ================= STATISTIK ================= */

// rata-rata
$q_avg = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT AVG(nilai) as rata 
FROM nilai 
WHERE siswa_id='$siswa_id'
"));
$rata_siswa = $q_avg['rata'] ? round($q_avg['rata'],2) : 0;

// max
$q_max = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT MAX(nilai) as max 
FROM nilai 
WHERE siswa_id='$siswa_id'
"));
$max_siswa = $q_max['max'] ?? 0;

// min
$q_min = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT MIN(nilai) as min 
FROM nilai 
WHERE siswa_id='$siswa_id'
"));
$min_siswa = $q_min['min'] ?? 0;

// total
$q_sum = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT SUM(nilai) as total 
FROM nilai 
WHERE siswa_id='$siswa_id'
"));
$total_siswa = $q_sum['total'] ?? 0;

/* ================= DATA SISWA ================= */
$sql_siswa = "
SELECT s.*, k.nama_kelas
FROM siswa s
LEFT JOIN kelas k ON s.kelas_id = k.id
WHERE s.id='$siswa_id'
";
$siswa = mysqli_fetch_assoc(mysqli_query($conn, $sql_siswa));
$kelas_id = $siswa['kelas_id'];

/* ================= DATA NILAI ================= */
$q = mysqli_query($conn,"
SELECT n.*, m.nama_mapel
FROM nilai n
JOIN mapel m ON n.mapel_id = m.id
WHERE n.siswa_id='$siswa_id'
ORDER BY n.tanggal DESC
");

/* ================= RANKING ================= */
function getRanking($conn, $siswa_id, $jenis){
    $q = mysqli_query($conn,"SELECT kelas_id FROM siswa WHERE id='$siswa_id'");
    $s = mysqli_fetch_assoc($q);
    $kelas_id = $s['kelas_id'];

    $sql = "
    SELECT n.siswa_id, SUM(n.nilai) as total
    FROM nilai n
    JOIN siswa s ON n.siswa_id = s.id
    WHERE n.jenis='$jenis'
    AND s.kelas_id='$kelas_id'
    GROUP BY n.siswa_id
    ORDER BY total DESC
    ";

    $rank = mysqli_query($conn, $sql);

    $no = 1;
    while($r = mysqli_fetch_assoc($rank)){
        if($r['siswa_id'] == $siswa_id){
            return $no;
        }
        $no++;
    }
    return '-';
}

$rank_harian = getRanking($conn,$siswa_id,'harian');
$rank_bulanan = getRanking($conn,$siswa_id,'bulanan');
$rank_semester = getRanking($conn,$siswa_id,'semester');

/* ================= TOP 5 ================= */
$q_top = mysqli_query($conn,"
SELECT s.nama, SUM(n.nilai) as total
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
WHERE s.kelas_id='$kelas_id'
GROUP BY s.id
ORDER BY total DESC
LIMIT 5
");

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
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>

    <link rel="icon" href="../assets/images/logo-sma.png">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

    <style>
    :root {
        --bg: #f5f7fb;
        --card: #ffffff;
        --text: #222;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--bg);
        color: var(--text);
        transition: 0.3s;
    }

    /* DARK MODE */
    body.dark {
        --bg: #0f172a;
        --card: #1e293b;
        --text: #ffffff;
    }

    /* GLOBAL */
    body.dark,
    body.dark p,
    body.dark span,
    body.dark h1,
    body.dark h2,
    body.dark h3,
    body.dark h4,
    body.dark h5,
    body.dark a {
        color: #fff !important;
    }

    /* NAVBAR */
    body.dark .navbar {
        background: #1e293b !important;
    }

    /* CARD */
    .card {
        border-radius: 15px;
        margin-bottom: 15px;
        background: var(--card);
        transition: 0.3s;
    }

    body.dark .card {
        background: #1e293b;
    }

    /* BUTTON */
    .btn-main {
        border-radius: 50px;
    }

    /* DARK TOGGLE */
    .dark-toggle {
        border: none;
        background: none;
        font-size: 20px;
    }

    /* ================= TABLE ================= */
    .table {
        background: #fff;
    }

    body.dark .table {
        background: #1e293b;
    }

    body.dark .table th,
    body.dark .table td {
        color: #fff !important;
        border-color: #444 !important;
    }

    body.dark .table thead {
        background: #334155;
    }

    body.dark .table tbody tr {
        background: #1e293b;
    }

    body.dark .table tbody tr:hover {
        background: #334155;
    }

    /* ================= TOP 5 ================= */
    #topSiswaContainer .d-flex {
        background: #f8fafc;
        border-radius: 10px;
        transition: 0.3s;
    }

    body.dark #topSiswaContainer .d-flex {
        background: #334155 !important;
    }

    body.dark #topSiswaContainer div {
        color: #fff !important;
    }

    body.dark #topSiswaContainer small {
        color: #cbd5e1 !important;
    }

    /* TEXT MUTED FIX */
    body.dark .text-muted {
        color: #cbd5e1 !important;
    }

    /* PROGRESS */
    body.dark .progress {
        background: #334155;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .row>div {
            margin-bottom: 15px;
        }
    }

    table.dataTable td {
        white-space: nowrap;
    }

    table.dataTable td {
        white-space: nowrap;
    }

    @media (max-width: 576px) {
        table.dataTable td {
            white-space: normal;
        }
    }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand font-weight-bold" href="#">
                <img src="../assets/images/logo-sma.png" width="30" class="mr-2">
                Values.
            </a>

            <div class="ml-auto d-flex align-items-center">
                <button onclick="toggleDark()" id="themeToggle" class="dark-toggle mr-3">🌙</button>
                <a href="../auth/logout.php" class="btn btn-primary btn-sm rounded-pill">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <!-- HEADER -->
        <div class="card text-center" data-aos="fade-down">
            <div class="card-body">
                <h4><strong><?= $_SESSION['user']['nama']; ?></strong></h4>
                <p>Kelas: <strong><?= $siswa['nama_kelas']; ?></strong></p>
            </div>
        </div>

        <!-- FILTER -->
        <div class="d-flex justify-content-center align-items-center mb-3" style="gap:10px" data-aos="fade-up">
            <label><strong>Mapel:</strong></label>
            <select id="filterMapel" class="form-control" style="max-width:220px;">
                <option value="">Semua</option>
                <?php
            $mapel = mysqli_query($conn,"
                SELECT DISTINCT m.id, m.nama_mapel
                FROM nilai n
                JOIN mapel m ON n.mapel_id = m.id
                WHERE n.siswa_id='$siswa_id'
            ");
            while($m=mysqli_fetch_assoc($mapel)){
                echo "<option value='{$m['id']}'>{$m['nama_mapel']}</option>";
            }
            ?>
            </select>
        </div>

        <!-- STATISTIK -->
        <div class="row text-center mb-4">

            <div class="col-md-3" data-aos="zoom-in">
                <div class="card shadow">
                    <div class="card-body d-flex align-items-center">
                        <i class='bx bx-line-chart text-primary' style="font-size:40px;"></i>
                        <div class="ml-3 text-left">
                            <small>Rata-rata</small>
                            <h4 id="rata"><?= $rata_siswa ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3" data-aos="zoom-in" data-aos-delay="100">
                <div class="card shadow">
                    <div class="card-body d-flex align-items-center">
                        <i class='bx bx-trophy text-success' style="font-size:40px;"></i>
                        <div class="ml-3 text-left">
                            <small>Tertinggi</small>
                            <h4 id="max"><?= $max_siswa ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3" data-aos="zoom-in" data-aos-delay="200">
                <div class="card shadow">
                    <div class="card-body d-flex align-items-center">
                        <i class='bx bx-down-arrow text-danger' style="font-size:40px;"></i>
                        <div class="ml-3 text-left">
                            <small>Terendah</small>
                            <h4 id="min"><?= $min_siswa ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3" data-aos="zoom-in" data-aos-delay="300">
                <div class="card shadow">
                    <div class="card-body d-flex align-items-center">
                        <i class='bx bx-bar-chart text-dark' style="font-size:40px;"></i>
                        <div class="ml-3 text-left">
                            <small>Total</small>
                            <h4 id="total"><?= $total_siswa ?></h4>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RANKING -->
        <div class="row mb-4 text-white">

            <div class="col-md-4" data-aos="fade-up">
                <div class="card shadow" style="background:linear-gradient(135deg,#3b82f6,#6366f1);">
                    <div class="card-body">
                        <small>Ranking Harian</small>
                        <h2 id="rank_harian"><?= $rank_harian ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="150">
                <div class="card shadow" style="background:linear-gradient(135deg,#f59e0b,#f97316);">
                    <div class="card-body">
                        <small>Ranking Bulanan</small>
                        <h2 id="rank_bulanan"><?= $rank_bulanan ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card shadow" style="background:linear-gradient(135deg,#10b981,#059669);">
                    <div class="card-body">
                        <small>Ranking Semester</small>
                        <h2 id="rank_semester"><?= $rank_semester ?></h2>
                    </div>
                </div>
            </div>

        </div>

        <!-- TOP 5 -->
        <div class="card shadow" data-aos="fade-up">
            <div class="card-body">
                <h5 class="text-center" id="judulTop">🏆 Top 5 Siswa</h5>

                <div id="topSiswaContainer">
                    <?php $no=1; while($t=mysqli_fetch_assoc($q_top)){ ?>
                    <div class="d-flex align-items-center mb-3 p-2" style="background:#f8fafc; border-radius:10px;">
                        <div style="width:40px;"><?= $no ?></div>

                        <?php $avatar = "https://api.dicebear.com/7.x/adventurer/svg?seed=" . urlencode($t['nama']); ?>
                        <img src="<?= $avatar ?>" width="45" height="45" style="border-radius:50%;">

                        <div class="ml-3 flex-grow-1">
                            <div><?= $t['nama'] ?></div>
                            <small class="text-muted">Total: <?= $t['total'] ?></small>
                        </div>
                    </div>
                    <?php $no++; } ?>
                </div>
            </div>
        </div>

        <!-- CHART -->
        <div class="card">
            <div class="card-body">
                <canvas id="chartNilai"></canvas>
            </div>
        </div>

        <!-- PROGRESS -->
        <div class="card">
            <div class="card-body">
                <h6>Progress Nilai</h6>
                <div class="progress">
                    <div class="progress-bar bg-success" id="progressBar" style="width: <?= $rata_siswa ?>%">
                        <?= $rata_siswa ?>%
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table id="tableNilai" class="table table-bordered table-hover nowrap" style="width:100%">

                        <!--<table class="table table-bordered">-->
                        <thead class="thead-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Mapel</th>
                                <th>Nilai</th>
                                <th>Jenis</th>
                            </tr>
                        </thead>
                        <!--<tbody id="tableBody">-->
                        <tbody>
                            <?php while($d=mysqli_fetch_assoc($q)){ ?>
                            <tr>
                                <!-- <td><?= date('d-m-Y', strtotime($d['tanggal'])) ?></td> -->
                                <td>
                                    <?= hariIndonesia($d['tanggal']); ?>,
                                    <?= tanggalIndonesia($d['tanggal']); ?>
                                </td>
                                <td><?= $d['nama_mapel'] ?></td>
                                <td><?= $d['nilai'] ?></td>
                                <td><?= ucfirst($d['jenis']) ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-center">
                    <a href="print_raport.php?jenis=harian" class="btn btn-info rounded-pill" target="_blank">Harian</a>
                    <a href="print_raport.php?jenis=bulanan" class="btn btn-warning rounded-pill"
                        target="_blank">Bulanan</a>
                    <a href="print_raport.php?jenis=semester" class="btn btn-success rounded-pill"
                        target="_blank">Semester</a>
                </div>

            </div>
        </div>

    </div>

    <?php include 'template_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <script>
    AOS.init({
        duration: 800,
        once: true
    });

    function toggleDark() {
        document.body.classList.toggle('dark');
        let btn = document.getElementById('themeToggle');

        if (document.body.classList.contains('dark')) {
            localStorage.setItem('theme', 'dark');
            btn.innerHTML = '☀️';
        } else {
            localStorage.setItem('theme', 'light');
            btn.innerHTML = '🌙';
        }
    }

    window.onload = function() {
        let theme = localStorage.getItem('theme');
        let btn = document.getElementById('themeToggle');

        if (theme === 'dark') {
            document.body.classList.add('dark');
            btn.innerHTML = '☀️';
        }
    };

    /* ================= CHART ================= */
    let chart;

    function loadChart(mapel_id = '') {
        fetch('chart_siswa.php?mapel_id=' + mapel_id)
            .then(res => res.json())
            .then(data => {

                let labels = [];
                let nilai = [];

                data.forEach(d => {
                    labels.push(d.mapel);
                    nilai.push(d.nilai);
                });

                if (chart) chart.destroy();

                chart = new Chart(document.getElementById('chartNilai'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Nilai',
                            data: nilai
                        }]
                    }
                });
            });
    }

    // load awal
    loadChart();

    /* ================= FILTER ================= */
    document.getElementById('filterMapel').addEventListener('change', function() {

        let mapel_id = this.value;

        fetch('filter_siswa.php?mapel_id=' + mapel_id)
            .then(res => res.json())
            .then(res => {

                let namaMapel = this.options[this.selectedIndex].text;
                document.getElementById('judulTop').innerText =
                    "🏆 Top 5 Siswa Kelas (" + namaMapel + ")";

                document.getElementById('rata').innerText = res.rata;
                document.getElementById('max').innerText = res.max;
                document.getElementById('min').innerText = res.min;
                document.getElementById('total').innerText = res.total;

                document.getElementById('rank_harian').innerText = res.rank_harian;
                document.getElementById('rank_bulanan').innerText = res.rank_bulanan;
                document.getElementById('rank_semester').innerText = res.rank_semester;

                let progress = document.getElementById('progressBar');
                progress.style.width = res.rata + '%';
                progress.innerText = res.rata + '%';

                // table
                // let html = '';
                // res.data.forEach(d => {
                //     html += `
                //     <tr>
                //         <td>${d.tanggal}</td>
                //         <td>${d.nama_mapel}</td>
                //         <td>${d.nilai}</td>
                //         <td>${d.jenis}</td>
                //     </tr>`;
                // });
                // document.getElementById('tableBody').innerHTML = html;

                // CLEAR + RELOAD DATATABLE
                table.clear();

                res.data.forEach(d => {
                    table.row.add([
                        d.tanggal,
                        d.nama_mapel,
                        d.nilai,
                        d.jenis.charAt(0).toUpperCase() + d.jenis.slice(1)
                    ]);
                });

                table.draw();

                // table.clear();

                // res.data.forEach(d => {
                //     table.row.add([
                //         d.tanggal,
                //         d.nama_mapel,
                //         d.nilai,
                //         d.jenis
                //     ]);
                // });

                // table.draw();

                // top 5
                let topHtml = '';
                res.top_siswa.forEach((siswa, index) => {

                    let medal = '';
                    if (index === 0) medal = '🥇';
                    else if (index === 1) medal = '🥈';
                    else if (index === 2) medal = '🥉';

                    let avatar =
                        `https://api.dicebear.com/7.x/adventurer/svg?seed=${encodeURIComponent(siswa.nama)}`;

                    topHtml += `
            <div class="d-flex align-items-center mb-3 p-2" style="border-radius:10px; background:#f8fafc;">
                <div style="width:40px; font-weight:bold;">${index + 1}</div>
                <img src="${avatar}" width="45" height="45" style="border-radius:50%;">
                <div class="ml-3 flex-grow-1">
                    <div>${siswa.nama}</div>
                    <small class="text-muted">Total: ${siswa.total}</small>
                </div>
                <div>${medal}</div>
            </div>`;
                });

                document.getElementById('topSiswaContainer').innerHTML = topHtml;

                loadChart(mapel_id);
            });

    });

    AOS.init({
        duration: 800,
        once: true
    });

    // FIX supaya jalan setelah load & dynamic content
    window.addEventListener('load', () => {
        AOS.refresh();
    });
    </script>

    <!-- DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

    <script>
    let table;

    $(document).ready(function() {
        table = $('#tableNilai').DataTable({
            responsive: true,
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            autoWidth: false,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                paginate: {
                    next: "→",
                    previous: "←"
                }
            }
        });
    });
    </script>


</body>

</html>