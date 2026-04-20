<?php 
include '../config/auth.php';
cek_role(['admin']);
?>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">

<?php include 'template.php'; ?>

<div class="grid">

    <div class="card">
        <h3>Total Siswa</h3>
        <p>100</p>
    </div>

    <div class="card">
        <h3>Total Guru</h3>
        <p>20</p>
    </div>

    <div class="card">
        <h3>Total Mapel</h3>
        <p>10</p>
    </div>

</div>

</div>