<?php include '../config/koneksi.php'; ?>

<?php
include '../config/auth.php';
cek_role(['guru']);
?>

<form method="POST">

    <select name="siswa_id">
        <?php
$q=mysqli_query($conn,"SELECT * FROM siswa");
while($d=mysqli_fetch_array($q)){
echo "<option value='$d[id]'>$d[nama]</option>";
}
?>
    </select>

    <select name="mapel_id">
        <?php
$q=mysqli_query($conn,"SELECT * FROM mapel");
while($d=mysqli_fetch_array($q)){
echo "<option value='$d[id]'>$d[nama_mapel]</option>";
}
?>
    </select>

    <input type="number" name="nilai" placeholder="Nilai">
    <textarea name="deskripsi"></textarea>

    <button name="simpan">Simpan</button>
</form>

<?php
if(isset($_POST['simpan'])){
mysqli_query($conn,"INSERT INTO nilai 
VALUES(NULL,'$_POST[siswa_id]','$_POST[mapel_id]',1,'Ganjil','Januari','$_POST[nilai]','$_POST[deskripsi]')");
}
?>