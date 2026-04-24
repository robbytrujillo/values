<?php 
include '../config/auth.php';
cek_role(['guru']);
include '../config/koneksi.php';

// ================= FILTER =================
$jenis = $_GET['jenis'] ?? '';
$where = ($jenis!='') ? "WHERE jenis='$jenis'" : "";

// ================= DATA =================
$total_siswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa"))['total'];
$total_guru  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM guru"))['total'];
$total_mapel = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mapel"))['total'];

// ================= STATISTIK NILAI =================
$q1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT AVG(nilai) as rerata FROM nilai $where"));
$rerata = round($q1['rerata'] ?? 0,2);

$q2 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT MAX(nilai) as max_nilai FROM nilai $where"));
$max = $q2['max_nilai'] ?? 0;

$q3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT MIN(nilai) as min_nilai FROM nilai $where"));
$min = $q3['min_nilai'] ?? 0;

// ================= CHART MAPEL =================
$data_mapel = mysqli_query($conn,"
SELECT m.nama_mapel, AVG(n.nilai) as rata
FROM nilai n
JOIN mapel m ON n.mapel_id = m.id
$where
GROUP BY m.id
");

$labels = [];
$values = [];

while($d = mysqli_fetch_assoc($data_mapel)){
    $labels[] = $d['nama_mapel'];
    $values[] = round($d['rata'],2);
}

$guru_id = $_SESSION['user']['id'];

// total siswa yang dia ajar
$total_siswa = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(DISTINCT siswa_id) as total
FROM nilai
WHERE guru_id='$guru_id'
"))['total'];

// rata-rata nilai
$rata = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT AVG(nilai) as rata
FROM nilai
WHERE guru_id='$guru_id'
"))['rata'] ?? 0;

// nilai tertinggi
$max = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT MAX(nilai) as max
FROM nilai
WHERE guru_id='$guru_id'
"))['max'] ?? 0;

// nilai terendah
$min = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT MIN(nilai) as min
FROM nilai
WHERE guru_id='$guru_id'
"))['min'] ?? 0;
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

<?php include 'template.php'; ?>

<div class="container-fluid mt-4">

    <!-- FILTER -->
    <form method="GET" class="form-inline mb-3">
        <select name="jenis" class="form-control mr-2">
            <option value="">Semua</option>
            <option value="harian" <?= $jenis=='harian'?'selected':'' ?>>Harian</option>
            <option value="bulanan" <?= $jenis=='bulanan'?'selected':'' ?>>Bulanan</option>
            <option value="semester" <?= $jenis=='semester'?'selected':'' ?>>Semester</option>
        </select>
        <button class="btn btn-primary btn-sm">Filter</button>
    </form>

    <p>
        Jenis:
        <strong><?= $jenis ? ucfirst($jenis) : 'Semua' ?></strong>
    </p>

    <!-- CARD UTAMA -->
    <div class="row">

        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <h6>Total Siswa</h6>
                    <h3><?= $total_siswa ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    <h6>Total Guru</h6>
                    <h3><?= $total_guru ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white shadow">
                <div class="card-body">
                    <h6>Total Mapel</h6>
                    <h3><?= $total_mapel ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-dark text-white shadow">
                <div class="card-body">
                    <h6>Rerata Nilai</h6>
                    <h3><?= $rerata ?></h3>
                </div>
            </div>
        </div>

    </div>

    <!-- CARD NILAI -->
    <div class="row">

        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Siswa Diajar</h6>
                    <h4><?= $total_siswa ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Rata-rata Nilai</h6>
                    <h4><?= number_format($rata,2) ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Nilai Tertinggi</h6>
                    <h4><?= $max ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Nilai Terendah</h6>
                    <h4><?= $min ?></h4>
                </div>
            </div>
        </div>

    </div>


    <!-- CHART -->
    <div class="card mt-3">
        <div class="card-body">
            <h5>Rata-rata Nilai per Mapel</h5>
            <canvas id="chartMapel"></canvas>
        </div>
    </div>

</div>

<?php include 'template_footer.php'; ?>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
var ctx = document.getElementById('chartMapel').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Rata-rata Nilai',
            data: <?= json_encode($values) ?>,
        }]
    },
    options: {
        responsive: true
    }
});
</script>