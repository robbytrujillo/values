<?php 
include '../config/auth.php';
cek_role(['admin']);
include '../config/koneksi.php';

// ================= FILTER =================
$ta     = $_GET['ta'] ?? '';
$kelas  = $_GET['kelas'] ?? '';
$search = $_GET['search'] ?? '';

// ================= PAGINATION =================
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = ($page < 1) ? 1 : $page;
$start = ($page - 1) * $limit;

// ================= WHERE =================
$where = "WHERE 1=1";

if($kelas){
    $where .= " AND n.kelas='$kelas'";
}

if($ta){
    $where .= " AND n.tahun_ajaran_id='$ta'";
}

if($search){
    $s = mysqli_real_escape_string($conn,$search);
    $where .= " AND s.nama LIKE '%$s%'";
}

// ================= QUERY =================
$query = "
SELECT 
s.id,
s.nama,

AVG(CASE WHEN n.jenis='harian' THEN n.nilai END) as harian,
AVG(CASE WHEN n.jenis='uts' THEN n.nilai END) as uts,
AVG(CASE WHEN n.jenis='semester' THEN n.nilai END) as semester,

(
    AVG(CASE WHEN n.jenis='harian' THEN n.nilai END)*0.3 +
    AVG(CASE WHEN n.jenis='uts' THEN n.nilai END)*0.3 +
    AVG(CASE WHEN n.jenis='semester' THEN n.nilai END)*0.4
) as total

FROM nilai n
JOIN siswa s ON n.siswa_id = s.id

$where
GROUP BY s.id
ORDER BY total DESC
LIMIT $start,$limit
";

$q = mysqli_query($conn,$query);

// ================= TOTAL =================
$total = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(DISTINCT s.id) as total
FROM nilai n
JOIN siswa s ON n.siswa_id = s.id
$where
"))['total'];

$pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Ranking Siswa</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
    .btn {
        padding: 8px 12px;
        border-radius: 8px;
        background: #3b82f6;
        color: #fff;
        border: none;
        cursor: pointer;
    }

    .btn-secondary {
        background: #64748b;
    }

    .card {
        margin-top: 15px;
    }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>📊 Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="siswa.php">Data Siswa</a>
        <a href="guru.php">Data Guru</a>
        <a href="mapel.php">Data Mapel</a>
        <a href="guru_mapel_kelas.php">Relasi Guru</a>
        <a href="ranking.php">Ranking</a>
        <a href="../auth/logout.php">Logout</a>
    </div>

    <div class="content">

        <div class="navbar">
            <h3>Ranking Siswa</h3>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <!-- FILTER -->
        <div class="card">
            <form method="GET">

                <select name="ta">
                    <option value="">-- Tahun Ajaran --</option>
                    <?php
                    $ta_q = mysqli_query($conn,"SELECT * FROM tahun_ajaran");
                    while($d=mysqli_fetch_array($ta_q)){
                        $selected = ($ta==$d['id'])?'selected':'';
                        echo "<option value='$d[id]' $selected>$d[nama]</option>";
                    }
                    ?>
                </select>

                <select name="kelas">
                    <option value="">-- Kelas --</option>
                    <?php
                    $kelas_q = mysqli_query($conn,"SELECT DISTINCT kelas FROM siswa");
                    while($k=mysqli_fetch_array($kelas_q)){
                        $selected = ($kelas==$k['kelas'])?'selected':'';
                        echo "<option $selected>$k[kelas]</option>";
                    }
                    ?>
                </select>

                <input type="text" name="search" placeholder="Cari siswa" value="<?= htmlspecialchars($search) ?>">

                <button class="btn">Filter</button>

                <?php if($kelas || $ta || $search): ?>
                <a href="ranking.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>

            </form>
        </div>

        <!-- TABLE -->
        <div class="card">
            <h3>Data Ranking</h3>

            <table>
                <tr>
                    <th>Ranking</th>
                    <th>Nama</th>
                    <th>Harian</th>
                    <th>UTS</th>
                    <th>Semester</th>
                    <th>Total</th>
                </tr>

                <?php 
                $no = $start + 1;
                while($d=mysqli_fetch_array($q)){
                ?>
                <tr>
                    <td><?= $no ?></td>
                    <td><?= $d['nama'] ?></td>
                    <td><?= round($d['harian'],1) ?></td>
                    <td><?= round($d['uts'],1) ?></td>
                    <td><?= round($d['semester'],1) ?></td>
                    <td><b><?= round($d['total'],1) ?></b></td>
                </tr>
                <?php $no++; } ?>
            </table>

            <!-- PAGINATION -->
            <div style="margin-top:15px;">
                <?php for($i=1;$i<=$pages;$i++): ?>
                <a href="?page=<?= $i ?>&kelas=<?= $kelas ?>&ta=<?= $ta ?>&search=<?= urlencode($search) ?>" class="btn"
                    style="<?= ($i==$page)?'background:#1e293b;':'' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
            </div>

        </div>

    </div>

</body>

</html>