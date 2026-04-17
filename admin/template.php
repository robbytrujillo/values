<div class="sidebar">
    <h2>📊 Admin</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="siswa.php">Data Siswa</a>
    <a href="guru.php">Data Guru</a>
    <a href="mapel.php">Data Mapel</a>
    <a href="ranking.php">Ranking</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="content">

    <div class="navbar">
        <div>Dashboard Admin</div>
        <div><?= $_SESSION['user']['nama']; ?></div>
    </div>