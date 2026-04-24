<?php 
include '../config/auth.php';
cek_role(['admin']);
include '../config/koneksi.php';

// contoh ambil data (opsional, bisa disesuaikan)
$total_siswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa"))['total'];
$total_guru  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM guru"))['total'];
$total_mapel = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mapel"))['total'];
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

<?php include 'template.php'; ?>

<div class="container-fluid mt-4">

    <div class="row">

        <!-- CARD SISWA -->
        <div class="col-md-4 col-12 mb-4">
            <div class="card shadow-sm border-left-primary">
                <div class="card-body">
                    <h6 class="text-muted">Total Siswa</h6>
                    <h3><?php echo $total_siswa; ?></h3>
                </div>
            </div>
        </div>

        <!-- CARD GURU -->
        <div class="col-md-4 col-12 mb-4">
            <div class="card shadow-sm border-left-success">
                <div class="card-body">
                    <h6 class="text-muted">Total Guru</h6>
                    <h3><?php echo $total_guru; ?></h3>
                </div>
            </div>
        </div>

        <!-- CARD MAPEL -->
        <div class="col-md-4 col-12 mb-4">
            <div class="card shadow-sm border-left-warning">
                <div class="card-body">
                    <h6 class="text-muted">Total Mapel</h6>
                    <h3><?php echo $total_mapel; ?></h3>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include 'template_footer.php'; ?>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>