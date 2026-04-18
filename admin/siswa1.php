<?php 
include '../config/auth.php';
cek_role(['admin']);
include '../config/koneksi.php'; 
?>

<!DOCTYPE html>
<html>

<head>
    <title>Data Siswa</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>📊 Admin</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="siswa.php">Data Siswa</a>
        <a href="guru.php">Data Guru</a>
        <a href="mapel.php">Data Mapel</a>
        <a href="ranking.php">Ranking</a>
        <a href="../auth/logout.php">Logout</a>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <!-- NAVBAR -->
        <div class="navbar">
            <div>Data Siswa</div>
            <div><?= $_SESSION['user']['nama']; ?></div>
        </div>

        <!-- FORM -->
        <div class="card">
            <h3>Tambah Siswa</h3>

            <form method="POST">
                <input type="text" name="nis" placeholder="NIS" required>
                <input type="text" name="nama" placeholder="Nama Siswa" required>
                <input type="text" name="kelas" placeholder="Kelas" required>
                <input type="text" name="angkatan" placeholder="Angkatan" required>
                <button name="simpan">Simpan</button>
            </form>
        </div>

        <!-- TABLE -->
        <div class="card">
            <h3>Daftar Siswa</h3>

            <table>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Angkatan</th>
                    <th>Aksi</th>
                </tr>

                <?php
$no=1;
$q=mysqli_query($conn,"SELECT * FROM siswa");

while($d=mysqli_fetch_array($q)){
echo "<tr>
<td>$no</td>
<td>$d[nis]</td>
<td>$d[nama]</td>
<td><span class='badge'>$d[kelas]</span></td>
<td>$d[angkatan]</td>
<td>
    <a href='?hapus=$d[id]' class='btn-hapus'>Hapus</a>
</td>
</tr>";
$no++;
}
?>
            </table>
        </div>

    </div>

</body>

</html>

<?php
// SIMPAN
if(isset($_POST['simpan'])){
mysqli_query($conn,"INSERT INTO siswa VALUES(NULL,'$_POST[nis]','$_POST[nama]','$_POST[kelas]','$_POST[angkatan]')");
echo "<script>location='siswa.php';</script>";
}

// HAPUS
if(isset($_GET['hapus'])){
mysqli_query($conn,"DELETE FROM siswa WHERE id='$_GET[hapus]'");
echo "<script>location='siswa.php';</script>";
}
?>