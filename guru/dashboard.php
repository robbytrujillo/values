<?php
include '../config/auth.php';
cek_role(['guru']);
include '../config/koneksi.php';

$guru_id = $_SESSION['user']['id'];

// ================= FILTER =================
$jenis = $_GET['jenis'] ?? '';
$where_jenis = ($jenis != '') ? "AND n.jenis='$jenis'" : "";

// ================= DATA GURU =================
$total_guru = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM guru"))['total'];
$total_mapel = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mapel"))['total'];

// ================= DATA KHUSUS GURU =================
$total_siswa = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(DISTINCT n.siswa_id) as total
    FROM nilai n
    WHERE n.guru_id='$guru_id' $where_jenis
"))['total'];

$q1 = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT AVG(n.nilai) as rerata
    FROM nilai n
    WHERE n.guru_id='$guru_id' $where_jenis
"));
$rerata = round($q1['rerata'] ?? 0, 2);

$q2 = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT MAX(n.nilai) as max_nilai
    FROM nilai n
    WHERE n.guru_id='$guru_id' $where_jenis
"));
$max = $q2['max_nilai'] ?? 0;

$q3 = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT MIN(n.nilai) as min_nilai
    FROM nilai n
    WHERE n.guru_id='$guru_id' $where_jenis
"));
$min = $q3['min_nilai'] ?? 0;

// ================= CHART MAPEL =================
$data_mapel = mysqli_query($conn,"
    SELECT m.nama_mapel, AVG(n.nilai) as rata
    FROM nilai n
    JOIN mapel m ON n.mapel_id = m.id
    WHERE n.guru_id='$guru_id' $where_jenis
    GROUP BY m.id
");

$labels = [];
$values = [];

while($d = mysqli_fetch_assoc($data_mapel)){
    $labels[] = $d['nama_mapel'];
    $values[] = round($d['rata'], 2);
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

<style>
.card-modern {
    border-radius: 15px;
    border: none;
    transition: 0.3s;
}

.card-modern:hover {
    transform: translateY(-5px);
}

.card-modern .card-body {
    display: flex;
    align-items: center;
}

.card-icon {
    font-size: 40px;
    flex-shrink: 0;
}

.card-text {
    margin-left: 15px;
}

/* FILTER RESPONSIVE */
.filter-wrapper {
    width: 100%;
    margin-bottom: 25px;
}

.filter-group {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    width: 100%;
}

.filter-select {
    flex: 1;
    min-width: 200px;
}

.btn-filter {
    min-width: 120px;
}

/* MOBILE */
@media (max-width: 768px) {
    .filter-group {
        flex-direction: column;
    }

    .filter-select {
        width: 100%;
        margin-bottom: 12px;
    }

    .btn-filter {
        width: 100%;
    }

    .card-modern .card-body {
        flex-direction: row;
    }
}
</style>

<?php include 'template.php'; ?>

<div class="container-fluid mt-4">

    <h4><strong style="font-weight: bold">Dashboard Guru</strong></h4>
    <br>

    <!-- FILTER -->
    <form method="GET" class="filter-wrapper mb-4">
        <div class="filter-group">
            <select name="jenis" class="form-control filter-select">
                <option value="">Semua</option>
                <option value="harian" <?= $jenis=='harian'?'selected':'' ?>>Harian</option>
                <option value="bulanan" <?= $jenis=='bulanan'?'selected':'' ?>>Bulanan</option>
                <option value="semester" <?= $jenis=='semester'?'selected':'' ?>>Semester</option>
            </select>
            <button class="btn btn-primary btn-filter">Filter</button>
        </div>
    </form>

    <p>
        Jenis:
        <strong><?= $jenis ? ucfirst($jenis) : 'Semua' ?></strong>
    </p>

    <!-- CARD UTAMA -->
    <div class="row">

        <!-- <div class="col-md-3 mb-3">
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
        </div> -->

        <div class="col-md-3 mb-3" data-aos="fade-up">
            <div class="card shadow card-modern">
                <div class="card-body">
                    <i class='bx bx-group text-primary card-icon'></i>
                    <div class="card-text">
                        <small>Total Siswa</small>
                        <h4><?= $total_siswa ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="100">
            <div class="card shadow card-modern">
                <div class="card-body">
                    <i class='bx bx-user text-success card-icon'></i>
                    <div class="card-text">
                        <small>Total Guru</small>
                        <h4><?= $total_guru ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="200">
            <div class="card shadow card-modern">
                <div class="card-body">
                    <i class='bx bx-book text-warning card-icon'></i>
                    <div class="card-text">
                        <small>Total Mapel</small>
                        <h4><?= $total_mapel ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="300">
            <div class="card shadow card-modern">
                <div class="card-body">
                    <i class='bx bx-line-chart text-dark card-icon'></i>
                    <div class="card-text">
                        <small>Rerata Nilai</small>
                        <h4><?= $rerata ?></h4>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- CARD NILAI -->
    <div class="row">

        <!-- <div class="col-md-3 mb-3">
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
        </div> -->

        <div class="col-md-3 mb-3" data-aos="fade-up">
            <div class="card shadow card-modern">
                <div class="card-body">
                    <i class='bx bx-user-check text-info card-icon'></i>
                    <div class="card-text">
                        <small>Siswa Diajar</small>
                        <h4><?= $total_siswa ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="100">
            <div class="card shadow card-modern">
                <div class="card-body">
                    <i class='bx bx-line-chart text-primary card-icon'></i>
                    <div class="card-text">
                        <small>Rata-rata Nilai</small>
                        <h4><?= number_format($rata,2) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="200">
            <div class="card shadow card-modern">
                <div class="card-body">
                    <i class='bx bx-trophy text-success card-icon'></i>
                    <div class="card-text">
                        <small>Nilai Tertinggi</small>
                        <h4><?= $max ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="300">
            <div class="card shadow card-modern">
                <div class="card-body">
                    <i class='bx bx-down-arrow text-danger card-icon'></i>
                    <div class="card-text">
                        <small>Nilai Terendah</small>
                        <h4><?= $min ?></h4>
                    </div>
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

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
AOS.init();
</script>