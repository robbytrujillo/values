<?php
session_start();
include '../config/koneksi.php';

if($_SESSION['role'] != 'siswa'){
    header("Location: ../index.php");
    exit;
}

$siswa_id = $_SESSION['user']['id'];

/* ================= DATA NILAI ================= */
$q = mysqli_query($conn,"
SELECT n.*, m.nama_mapel
FROM nilai n
JOIN mapel m ON n.mapel_id = m.id
WHERE n.siswa_id='$siswa_id'
ORDER BY n.tanggal DESC
");

if(!$q){
    die("Query nilai error: " . mysqli_error($conn));
}

/* ================= DATA SISWA + KELAS ================= */
$sql_siswa = "
SELECT s.*, k.nama_kelas
FROM siswa s
LEFT JOIN kelas k ON s.kelas_id = k.id
WHERE s.id='$siswa_id'
";

$q_siswa = mysqli_query($conn, $sql_siswa);

if(!$q_siswa){
    die("Query siswa error: " . mysqli_error($conn));
}

$siswa = mysqli_fetch_assoc($q_siswa);

/* ================= RANKING ================= */
function getRanking($conn, $siswa_id, $jenis){

    // ambil kelas siswa
    $q = mysqli_query($conn,"SELECT kelas_id FROM siswa WHERE id='$siswa_id'");
    if(!$q){
        die(mysqli_error($conn));
    }

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

    if(!$rank){
        die("Query Ranking Error: " . mysqli_error($conn));
    }

    $no = 1;
    while($r = mysqli_fetch_assoc($rank)){
        if($r['siswa_id'] == $siswa_id){
            return $no;
        }
        $no++;
    }

    return '-';
}

/* ================= HITUNG RANKING ================= */
$rank_harian = getRanking($conn,$siswa_id,'harian');
$rank_bulanan = getRanking($conn,$siswa_id,'bulanan');
$rank_semester = getRanking($conn,$siswa_id,'semester');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>

    <link rel="icon" type="image/png" href="../assets/images/logo-sma.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f5f7fb;
        color: #000;
        transition: 0.3s;
    }

    /* DARK MODE */
    body.dark {
        background: #0f172a;
        color: #fff;
    }

    /* semua text ikut berubah */
    body.dark h1,
    body.dark h2,
    body.dark h3,
    body.dark p,
    body.dark span,
    body.dark a {
        color: #fff !important;
    }

    /* navbar */
    body.dark .navbar {
        background: #1e293b !important;
    }

    /* card */
    body.dark .card {
        background: #1e293b;
        color: #fff;
    }

    /* footer tetap terang */
    footer {
        color: #333 !important;
    }

    body.dark footer {
        background: #f8f9fa !important;
        color: #333 !important;
    }

    /* hero */
    .hero {
        padding: 100px 20px;
        text-align: center;
    }

    .hero h1 {
        font-weight: 600;
    }

    /* card animasi */
    .feature-card {
        border-radius: 15px;
        transition: 0.3s;
    }

    .feature-card:hover {
        transform: translateY(-8px);
    }

    /* button */
    .btn-main {
        border-radius: 50px;
        padding: 10px 25px;
    }

    /* toggle icon */
    .dark-toggle {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
    }

    footer a {
        color: #007bff !important;
        /* tetap biru */
    }

    body.dark footer a {
        color: #007bff !important;
        /* paksa tetap biru saat dark */
    }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand font-weight-bold" href="#"><img src="../assets/images/logo-sma.png" alt="Logo"
                    width="30" class="mr-2">Values.</a>

            <div class="ml-auto d-flex align-items-center">
                <button onclick="toggleDark()" id="themeToggle" class="dark-toggle mr-3">
                    🌙
                </button>

                <!-- <a href="auth/login.php" class="btn btn-primary btn-sm btn-main">
                    Login
                </a> -->
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <h4>Dashboard Siswa</h4>
        <p>Nama: <strong><?= $_SESSION['user']['nama']; ?></strong></p>
        <!-- <p>Kelas: <strong><?= $_SESSION['kelas']['nama_kelas']; ?></strong></p> -->
        <!-- <p>Nama: <strong><?= $siswa['nama']; ?></strong></p> -->
        <p>Kelas: <strong><?= $siswa['nama_kelas']; ?></strong></p>

        <!-- RANKING -->
        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        Ranking Harian
                        <h3><?= $rank_harian ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        Ranking Bulanan
                        <h3><?= $rank_bulanan ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        Ranking Semester
                        <h3><?= $rank_semester ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHART -->
        <div class="card mb-4">
            <div class="card-body">
                <canvas id="chartNilai"></canvas>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card">
            <div class="card-body">

                <table class="table table-bordered">
                    <tr>
                        <th>Tanggal</th>
                        <th>Mapel</th>
                        <th>Nilai</th>
                        <th>Jenis</th>
                    </tr>

                    <?php while($d=mysqli_fetch_assoc($q)){ ?>
                    <tr>
                        <td><?= $d['tanggal'] ?></td>
                        <td><?= $d['nama_mapel'] ?></td>
                        <td><?= $d['nilai'] ?></td>
                        <td><?= ucfirst($d['jenis']) ?></td>
                    </tr>
                    <?php } ?>

                </table>

                <!-- <a href="print_raport.php" target="_blank" class="btn btn-primary">
                    Print Raport
                </a> -->
                <a href="print_raport.php?jenis=harian" target="_blank" class="btn btn-info">Print Harian</a>
                <a href="print_raport.php?jenis=bulanan" target="_blank" class="btn btn-warning">Print Bulanan</a>
                <a href="print_raport.php?jenis=semester" target="_blank" class="btn btn-success">Print Semester</a>

            </div>
        </div>

    </div>

    <?php include 'template_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    fetch('chart_siswa.php')
        .then(res => res.json())
        .then(data => {

            let labels = [];
            let nilai = [];

            data.forEach(d => {
                labels.push(d.mapel);
                nilai.push(d.nilai);
            });

            new Chart(document.getElementById('chartNilai'), {
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
    </script>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <script>
    AOS.init({
        duration: 800,
        once: true
    });

    /* TOGGLE DARK MODE */
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

    /* LOAD THEME */
    window.onload = function() {
        let theme = localStorage.getItem('theme');
        let btn = document.getElementById('themeToggle');

        if (theme === 'dark') {
            document.body.classList.add('dark');
            btn.innerHTML = '☀️';
        } else {
            btn.innerHTML = '🌙';
        }
    }
    </script>

</body>

</html>