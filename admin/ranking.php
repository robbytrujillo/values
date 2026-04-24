<?php 
include '../config/auth.php';
cek_role(['admin']);
include '../config/koneksi.php';

// 🔥 TAMBAHKAN INI
$jenis = $_GET['jenis'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php include 'template.php'; ?>

    <div>



        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Ranking Siswa (Sekolah)</h4>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <form method="GET" class="form-inline mb-3">
            <select name="jenis" class="form-control mr-2">
                <option value="">Semua</option>
                <option value="harian" <?= ($_GET['jenis'] ?? '')=='harian'?'selected':'' ?>>Harian</option>
                <option value="bulanan" <?= ($_GET['jenis'] ?? '')=='bulanan'?'selected':'' ?>>Bulanan</option>
                <option value="semester" <?= ($_GET['jenis'] ?? '')=='semester'?'selected':'' ?>>Semester</option>
            </select>
            <button class="btn btn-primary btn-sm">Filter</button>
        </form>

        <p>
            Jenis:
            <strong><?= $jenis ? ucfirst($jenis) : 'Semua' ?></strong>
        </p>

        <!-- TABLE -->
        <div class="card">
            <div class="card-body">

                <h5>Data Ranking</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Ranking</th>
                                <th>Nama</th>
                                <th>Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                    // $jenis = $_GET['jenis'] ?? '';
                    $where = ($jenis!='') ? "WHERE nilai.jenis='$jenis'" : "";

                    $q=mysqli_query($conn,"
                    SELECT siswa.nama,
                    SUM(nilai.nilai) as total
                    FROM nilai
                    JOIN siswa ON nilai.siswa_id=siswa.id
                    $where
                    GROUP BY siswa.id
                    ORDER BY total DESC
                    ");

                    $rank=1;
                    while($d=mysqli_fetch_array($q)){
                    ?>
                            <tr>
                                <td><strong><?= $rank ?></strong></td>
                                <td><?= htmlspecialchars($d['nama']) ?></td>
                                <td><?= $d['total'] ?></td>
                            </tr>
                            <?php 
                        $rank++; 
                    } 
                    ?>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>

    <?php include 'template_footer.php'; ?>

</body>

</html>