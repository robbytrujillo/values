<?php include '../config/koneksi.php'; ?>

<form method="POST">
    <input type="text" name="nis" placeholder="NIS">
    <input type="text" name="nama" placeholder="Nama">
    <input type="text" name="kelas" placeholder="Kelas">
    <input type="text" name="angkatan" placeholder="Angkatan">
    <button name="simpan">Simpan</button>
</form>

<?php
if(isset($_POST['simpan'])){
mysqli_query($conn,"INSERT INTO siswa VALUES(NULL,'$_POST[nis]','$_POST[nama]','$_POST[kelas]','$_POST[angkatan]')");
}
?>

<table border="1">
    <tr>
        <th>Nama</th>
        <th>Kelas</th>
    </tr>

    <?php
$q=mysqli_query($conn,"SELECT * FROM siswa");
while($d=mysqli_fetch_array($q)){
echo "<tr><td>$d[nama]</td><td>$d[kelas]</td></tr>";
}
?>
</table>