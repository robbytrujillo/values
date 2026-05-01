<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>

    <link rel="icon" type="image/png" href="../assets/images/logo-sma.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body {
        overflow-x: hidden;
        background: #f1f5f9;
        margin: 0;
    }

    .sidebar {
        min-height: 100vh;
        background: #0f172a;
        color: white;
        padding-top: 20px;
    }

    .sidebar a {
        color: #cbd5e1;
        display: block;
        padding: 12px 20px;
        text-decoration: none;
        margin: 4px 10px;
        border-radius: 10px;
        transition: 0.3s;
    }

    .sidebar a:hover {
        background: #1d4ed8;
        color: white;
    }

    .content {
        padding: 20px;
        min-height: 100vh;
    }

    /* MOBILE SIDEBAR */
    .mobile-sidebar {
        position: fixed;
        top: 0;
        left: -280px;
        width: 260px;
        height: 100%;
        background: #0f172a;
        z-index: 9999;
        transition: 0.3s;
        padding-top: 20px;
        overflow-y: auto;
    }

    .mobile-sidebar.show {
        left: 0;
    }

    .mobile-sidebar a {
        display: block;
        color: #cbd5e1;
        padding: 14px 20px;
        text-decoration: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .mobile-sidebar a:hover {
        background: #1d4ed8;
        color: white;
    }

    .overlay {
        position: fixed;
        display: none;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9998;
    }

    .overlay.show {
        display: block;
    }

    @media(max-width:767px) {
        .content {
            padding: 15px;
        }
    }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- MOBILE -->
    <div class="mobile-sidebar d-md-none" id="mobileSidebar">
        <h4 class="text-center text-white mb-4"><img src="../assets/images/logo-sma.png" width="35" class="mb-2">
            Admin</h4>

        <a href="dashboard.php">Dashboard</a>
        <a href="siswa.php">Data Siswa</a>
        <a href="guru.php">Data Guru</a>
        <a href="mapel.php">Data Mapel</a>
        <a href="mengajar.php">Data Mengajar</a>
        <a href="input_nilai.php">Input Nilai</a>
        <a href="ranking.php">Ranking</a>
        <a href="../auth/logout.php">Logout</a>
    </div>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <div class="container-fluid flex-grow-1">
        <div class="row">

            <!-- DESKTOP -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <h4 class="text-center text-white mb-4"><img src="../assets/images/logo-sma.png" width="35"
                        class="mb-2"> Panel Admin</h4>

                <a href="dashboard.php">Dashboard</a>
                <a href="siswa.php">Data Siswa</a>
                <a href="guru.php">Data Guru</a>
                <a href="mapel.php">Data Mapel</a>
                <a href="mengajar.php">Data Mengajar</a>
                <a href="input_nilai.php">Input Nilai</a>
                <a href="ranking.php">Ranking</a>
                <a href="../auth/logout.php">Logout</a>
            </nav>

            <!-- CONTENT -->
            <main class="col-md-10 content d-flex flex-column">

                <nav class="navbar navbar-light bg-white shadow-sm rounded mb-4">
                    <button class="btn btn-primary d-md-none" onclick="toggleSidebar()">
                        ☰
                    </button>
                    <span class="font-weight-bold h4 mb-0">Panel Admin</span>
                </nav>

                <script>
                function toggleSidebar() {
                    document.getElementById('mobileSidebar').classList.toggle('show');
                    document.getElementById('overlay').classList.toggle('show');
                }
                </script>