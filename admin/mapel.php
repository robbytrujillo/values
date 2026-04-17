<?php 
include '../config/auth.php';
cek_role(['admin']);
include '../config/koneksi.php'; 
?>

<div class="content">
    <h2>Data Mata Pelajaran</h2>

    <form method="POST">
        <input type="text" name="nama_mapel" placeholder="Nama Mapel" required>
        <button name="simpan">Tambah Mapel</button>
    </form>

    <table>
        <tr>
            <th>No</th>
            <th>Nama Mapel</th>
            <th>Aksi</th>
        </tr>

        <?php
$no=1;
$q=mysqli_query($conn,"SELECT * FROM mapel");

while($d=mysqli_fetch_array($q)){
echo "<tr>
<td>$no</td>
<td>$d[nama_mapel]</td>
<td>
    <a href='?hapus=$d[id]' class='btn-hapus'>Hapus</a>
</td>
</tr>";
$no++;
}
?>
    </table>
</div>

<?php
// SIMPAN
if(isset($_POST['simpan'])){
mysqli_query($conn,"INSERT INTO mapel VALUES(NULL,'$_POST[nama_mapel]')");
echo "<script>location='mapel.php';</script>";
}

// HAPUS
if(isset($_GET['hapus'])){
mysqli_query($conn,"DELETE FROM mapel WHERE id='$_GET[hapus]'");
echo "<script>location='mapel.php';</script>";
}
?>