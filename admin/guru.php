<?php 
include '../config/auth.php';
cek_role(['admin']);
include '../config/koneksi.php'; 
?>

<div class="content">
    <h2>Data Guru</h2>

    <form method="POST">
        <input type="text" name="nama" placeholder="Nama Guru" required>
        <button name="simpan">Tambah Guru</button>
    </form>

    <table>
        <tr>
            <th>No</th>
            <th>Nama Guru</th>
            <th>Aksi</th>
        </tr>

        <?php
$no=1;
$q=mysqli_query($conn,"SELECT * FROM guru");

while($d=mysqli_fetch_array($q)){
echo "<tr>
<td>$no</td>
<td>$d[nama]</td>
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
mysqli_query($conn,"INSERT INTO guru VALUES(NULL,'$_POST[nama]')");
echo "<script>location='guru.php';</script>";
}

// HAPUS
if(isset($_GET['hapus'])){
mysqli_query($conn,"DELETE FROM guru WHERE id='$_GET[hapus]'");
echo "<script>location='guru.php';</script>";
}
?>