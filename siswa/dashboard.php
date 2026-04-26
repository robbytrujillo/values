<?php
session_start();
include '../config/koneksi.php';

if($_SESSION['role'] != 'siswa'){
    header("Location: ../index.php");
    exit;
}

$siswa_id = $_SESSION['user']['id'];

/* ================= STATISTIK ================= */

// rata-rata nilai siswa
$q_avg = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT AVG(nilai) as rata 
FROM nilai 
WHERE siswa_id='$siswa_id'
"));
$rata_siswa = round($q_avg['rata'],2);

// nilai tertinggi
$q_max = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT MAX(nilai) as max 
FROM nilai 
WHERE siswa_id='$siswa_id'
"));
$max_siswa = $q_max['max'] ?? 0;

// nilai terendah
$q_min = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT MIN(nilai) as min 
FROM nilai 
WHERE siswa_id='$siswa_id'
"));
$min_siswa = $q_min['min'] ?? 0;

// total nilai
$q_sum = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT SUM(nilai) as total 
FROM nilai 
WHERE siswa_id='$siswa_id'
"));
$total_siswa = $q_sum['total'] ?? 0;


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

/* ================= TOP 5 SISWA DI KELAS ================= */

// ambil kelas siswa
$qk = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT kelas_id FROM siswa WHERE id='$siswa_id'
"));
$kelas_id = $qk['kelas_id'];

// ambil top 5
$q_top = mysqli_query($conn,"
SELECT s.nama, s.foto, SUM(n.nilai) as total
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
WHERE s.kelas_id='$kelas_id'
GROUP BY s.id
ORDER BY total DESC
LIMIT 5
");

/* ================= TOP 5 ================= */
$kelas_id = $siswa['kelas_id'];

$q_top = mysqli_query($conn,"
SELECT s.nama, SUM(n.nilai) as total
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
WHERE s.kelas_id='$kelas_id'
GROUP BY s.id
ORDER BY total DESC
LIMIT 5
");

if(!$q_top){
    die("Query TOP ERROR: ".mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>

    <link rel="icon" type="image/png" href="../assets/images/logo-sma.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ICON -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- APEX CHART -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">


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

    /* TABLE DARK MODE */
    body.dark table {
        color: #fff !important;
    }

    body.dark table th,
    body.dark table td {
        color: #fff !important;
    }

    /* header table */
    body.dark table thead {
        background: #1e293b;
    }

    /* border table */
    body.dark table,
    body.dark table th,
    body.dark table td {
        border-color: #444 !important;
    }

    body.dark table tbody tr:hover {
        background: #334155;
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

                <a href="../auth/logout.php" class="btn btn-primary btn-sm btn-main">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <h4><strong style="font-weight: bold">Dashboard Siswa</strong></h4>
        <div class="card">
            <div class="col-md-12 text-center">
                <div class="body">
                    <br>
                    <h4><strong><?= $_SESSION['user']['nama']; ?></strong></h2>
                        <!-- <p>Kelas: <strong><?= $_SESSION['kelas']['nama_kelas']; ?></strong></p> -->
                        <!-- <p>Nama: <strong><?= $siswa['nama']; ?></strong></p> -->
                        <p>Kelas: <strong><?= $siswa['nama_kelas']; ?></strong></p>
                </div>
            </div>
        </div>
        <br>

        <!-- STATISTIK SISWA -->
        <!-- <div class="row text-center mb-4">

            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        Rata-rata
                        <h4><?= $rata_siswa ?></h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        Nilai Tertinggi
                        <h4><?= $max_siswa ?></h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        Nilai Terendah
                        <h4><?= $min_siswa ?></h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        Total Nilai
                        <h4><?= $total_siswa ?></h4>
                    </div>
                </div>
            </div>

        </div> -->


        <!-- <br> -->
        <div class="row text-center mb-4">

            <div class="col-md-3" data-aos="fade-up">
                <div class="card shadow border-0" style="border-radius:15px;">
                    <div class="card-body d-flex align-items-center">
                        <i class='bx bx-line-chart text-primary' style="font-size:40px;"></i>
                        <div class="ml-3 text-left">
                            <small>Rata-rata</small>
                            <h4><?= $rata_siswa ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card shadow border-0" style="border-radius:15px;">
                    <div class="card-body d-flex align-items-center">
                        <i class='bx bx-trophy text-success' style="font-size:40px;"></i>
                        <div class="ml-3 text-left">
                            <small>Tertinggi</small>
                            <h4><?= $max_siswa ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="card shadow border-0" style="border-radius:15px;">
                    <div class="card-body d-flex align-items-center">
                        <i class='bx bx-down-arrow text-danger' style="font-size:40px;"></i>
                        <div class="ml-3 text-left">
                            <small>Terendah</small>
                            <h4><?= $min_siswa ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="card shadow border-0" style="border-radius:15px;">
                    <div class="card-body d-flex align-items-center">
                        <i class='bx bx-bar-chart text-dark' style="font-size:40px;"></i>
                        <div class="ml-3 text-left">
                            <small>Total</small>
                            <h4><?= $total_siswa ?></h4>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <br>
        <!-- RANKING -->
        <!-- <div class="row text-center mb-4">
            <div class="col-md-4 ">
                <div class="card bg-info text-white" style="border-radius: 10px;">
                    <div class="card-body">
                        Ranking Harian
                        <h3><?= $rank_harian ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-warning text-white" style="border-radius: 10px;">
                    <div class="card-body">
                        Ranking Bulanan
                        <h3><?= $rank_bulanan ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-success text-white" style="border-radius: 10px;">
                    <div class="card-body">
                        Ranking Semester
                        <h3><?= $rank_semester ?></h3>
                    </div>
                </div>
            </div>
        </div> -->

        <div class="row mb-4 text-white">

            <!-- HARIAN -->
            <div class="col-md-4" data-aos="fade-up">
                <div class="card border-0 shadow"
                    style="border-radius:15px; background: linear-gradient(135deg,#3b82f6,#6366f1);">
                    <div class="card-body">

                        <div class="d-flex justify-content-between">
                            <div>
                                <small>Ranking Harian</small>
                                <h2><?= $rank_harian ?></h2>
                            </div>
                            <i class='bx bx-trending-up' style="font-size:40px;"></i>
                        </div>

                        <!-- progress dummy -->
                        <div class="progress mt-3" style="height:6px;">
                            <div class="progress-bar bg-white" style="width: <?= (100 - ($rank_harian*10)) ?>%"></div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- BULANAN -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow"
                    style="border-radius:15px; background: linear-gradient(135deg,#f59e0b,#f97316);">
                    <div class="card-body">

                        <div class="d-flex justify-content-between">
                            <div>
                                <small>Ranking Bulanan</small>
                                <h2><?= $rank_bulanan ?></h2>
                            </div>
                            <i class='bx bx-calendar' style="font-size:40px;"></i>
                        </div>

                        <div class="progress mt-3" style="height:6px;">
                            <div class="progress-bar bg-white" style="width: <?= (100 - ($rank_bulanan*10)) ?>%"></div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SEMESTER -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow"
                    style="border-radius:15px; background: linear-gradient(135deg,#10b981,#059669);">
                    <div class="card-body">

                        <div class="d-flex justify-content-between">
                            <div>
                                <small>Ranking Semester</small>
                                <h2><?= $rank_semester ?></h2>
                            </div>
                            <i class='bx bx-award' style="font-size:40px;"></i>
                        </div>

                        <div class="progress mt-3" style="height:6px;">
                            <div class="progress-bar bg-white" style="width: <?= (100 - ($rank_semester*10)) ?>%"></div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="card mb-4 shadow border-0" style="border-radius:15px;">
            <div class="card-body">

                <h5 class="mb-3">🏆 Top 5 Siswa Kelas</h5>

                <?php $no=1; while($t=mysqli_fetch_assoc($q_top)){ ?>
                <div class="d-flex align-items-center mb-3 p-2" style="border-radius:10px; background:#f8fafc;">

                    <!-- RANK -->
                    <div style="width:40px; font-weight:bold;">
                        <?= $no ?>
                    </div>

                    <?php
// generate avatar unik dari nama
$avatar = "https://api.dicebear.com/7.x/adventurer/svg?seed=" . urlencode($t['nama']);
?>

                    <!-- FOTO -->
                    <!-- <img src="../assets/foto/<?= $t['foto'] ?? 'default.png' ?>" width="45" height="45"
                        style="border-radius:50%; object-fit:cover;"> -->

                    <img src="<?= $avatar ?>" width="45" height="45" style="border-radius:50%;">

                    <!-- NAMA -->
                    <div class="ml-3 flex-grow-1">
                        <div><?= $t['nama'] ?></div>
                        <small class="text-muted">Total: <?= $t['total'] ?></small>
                    </div>

                    <!-- BADGE -->
                    <div>
                        <?php if($no==1){ ?>
                        <span class="badge badge-warning">🥇</span>
                        <?php }elseif($no==2){ ?>
                        <span class="badge badge-secondary">🥈</span>
                        <?php }elseif($no==3){ ?>
                        <span class="badge badge-dark">🥉</span>
                        <?php } ?>
                    </div>

                </div>
                <?php $no++; } ?>

            </div>
        </div>

        <div class="row mb-4">

            <!-- ANALISA AI -->
            <!-- <div class="col-md-6">
                <div class="card shadow border-0" style="border-radius:15px;">
                    <div class="card-body">
                        <h5>🤖 Analisa AI</h5>
                        <div id="aiInsight">Loading...</div>
                    </div>
                </div>
            </div> -->

            <!-- REKOMENDASI -->
            <!-- <div class="col-md-6">
                <div class="card shadow border-0" style="border-radius:15px;">
                    <div class="card-body">
                        <h5>📚 Rekomendasi Belajar</h5>
                        <div id="aiRekomendasi">Loading...</div>
                    </div>
                </div>
            </div> -->

        </div>

        <!-- CHAT -->
        <!-- <div class="card shadow mb-4">
            <div class="card-body">
                <h5>💬 Tanya AI</h5>

                <div id="chatBox" style="height:200px; overflow:auto; font-size:14px;"></div>

                <input id="chatInput" class="form-control mt-2" placeholder="Tanya nilai kamu...">
                <button onclick="kirimAI()" class="btn btn-primary mt-2 btn-sm">Kirim</button>
            </div>
        </div> -->

        <!-- CHART -->
        <div class="card mb-4">
            <div class="card-body">
                <canvas id="chartNilai"></canvas>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h6>Progress Nilai</h6>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: <?= $rata_siswa ?>%">
                        <?= $rata_siswa ?>%
                    </div>
                </div>
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
                <a href="print_raport.php?jenis=harian" target="_blank" class="btn btn-info rounded-pill">Print
                    Harian</a>
                <a href="print_raport.php?jenis=bulanan" target="_blank" class="btn btn-warning rounded-pill">Print
                    Bulanan</a>
                <a href="print_raport.php?jenis=semester" target="_blank" class="btn btn-success rounded-pill">Print
                    Semester</a>

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

            var options = {
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },

                series: [{
                    name: 'Nilai',
                    data: nilai
                }],

                xaxis: {
                    categories: labels
                },

                stroke: {
                    curve: 'smooth',
                    width: 3
                },

                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.6,
                        opacityTo: 0.1
                    }
                },

                colors: ['#6366f1'],

                markers: {
                    size: 5
                },

                tooltip: {
                    theme: 'light'
                }

            };

            var chart = new ApexCharts(document.querySelector("#chartNilai"), options);
            chart.render();

        });
    </script>


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

            var options = {
                chart: {
                    type: 'bar',
                    height: 350
                },
                series: [{
                    name: 'Nilai',
                    data: nilai
                }],
                xaxis: {
                    categories: labels
                },
                colors: ['#4f46e5'],
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '50%'
                    }
                },
                dataLabels: {
                    enabled: false
                }
            };

            var chart = new ApexCharts(document.querySelector("#chartNilai"), options);
            chart.render();
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

    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
    AOS.init();
    </script>

    <script>
    /* ================= LOAD AI ================= */

    // ANALISA
    fetch('ai.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'mode=insight&siswa_id=<?= $siswa_id ?>'
        })
        .then(res => res.json())
        .then(d => {
            document.getElementById('aiInsight').innerHTML =
                // d.choices[0].message.content;
                d.response;
        })
        .catch(() => {
            document.getElementById('aiInsight').innerHTML = "Gagal load AI";
        });

    // REKOMENDASI
    fetch('ai.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'mode=rekomendasi&siswa_id=<?= $siswa_id ?>'
        })
        .then(res => res.json())
        .then(d => {
            document.getElementById('aiRekomendasi').innerHTML =
                // d.choices[0].message.content;
                d.response;
        })
        .catch(() => {
            document.getElementById('aiRekomendasi').innerHTML = "Gagal load AI";
        });


    /* ================= CHATBOT ================= */

    function kirimAI() {
        let input = document.getElementById('chatInput');
        let q = input.value;

        if (!q) return;

        document.getElementById('chatBox').innerHTML += `
    <div><b>Kamu:</b> ${q}</div>
    `;

        fetch('ai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `mode=chat&siswa_id=<?= $siswa_id ?>&question=${encodeURIComponent(q)}`
            })
            .then(res => res.json())
            .then(d => {
                // let jawab = d.choices[0].message.content;
                let jawab = d.response;

                document.getElementById('chatBox').innerHTML += `
    <div><b>AI:</b> ${jawab}</div>
    <hr>
    `;

                input.value = "";
            })
            .catch(() => {
                document.getElementById('chatBox').innerHTML += `
    <div style="color:red;">AI error</div>
    `;
            });
    }
    </script>

</body>

</html>