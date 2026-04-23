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

/* ================= RANKING ================= */
function getRanking($conn, $siswa_id, $jenis){

    $rank = mysqli_query($conn,"
    SELECT siswa_id, SUM(nilai) as total
    FROM nilai
    WHERE jenis='$jenis'
    GROUP BY siswa_id
    ORDER BY total DESC
    ");

    $no = 1;
    while($r = mysqli_fetch_assoc($rank)){
        if($r['siswa_id'] == $siswa_id){
            return $no;
        }
        $no++;
    }
    return '-';
}

$rank_harian   = getRanking($conn,$siswa_id,'harian');
$rank_bulanan  = getRanking($conn,$siswa_id,'bulanan');
$rank_semester = getRanking($conn,$siswa_id,'semester');
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-4">

        <h4>Dashboard Siswa</h4>
        <p>Nama: <strong><?= $_SESSION['user']['nama']; ?></strong></p>

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

                <a href="print_raport.php" target="_blank" class="btn btn-primary">
                    Print Raport
                </a>

            </div>
        </div>

    </div>

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

</body>

</html>