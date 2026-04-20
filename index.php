<?php
session_start();

if(isset($_SESSION['user'])){
    $role = $_SESSION['user']['role'];

    switch($role){
        case 'admin': header("Location: admin/dashboard.php"); exit;
        case 'guru': header("Location: guru/input_nilai.php"); exit;
        case 'walas': header("Location: walas/dashboard.php"); exit;
        case 'kurikulum': header("Location: kurikulum/dashboard.php"); exit;
        case 'siswa': header("Location: siswa/dashboard.php"); exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Analisa Nilai SMA</title>

    <link rel="icon" type="image/png" href="assets/images/logo-sma.png">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f5f7fb;
        color: #000;
        transition: 0.3s;
    }

    /* DARK MODE */
    body.dark {
        background: #0f172a;
        color: #fff;
    }

    /* semua text ikut berubah */
    body.dark h1,
    body.dark h2,
    body.dark h3,
    body.dark p,
    body.dark span,
    body.dark a {
        color: #fff !important;
    }

    /* navbar */
    body.dark .navbar {
        background: #1e293b !important;
    }

    /* card */
    body.dark .card {
        background: #1e293b;
        color: #fff;
    }

    /* footer tetap terang */
    footer {
        color: #333 !important;
    }

    body.dark footer {
        background: #f8f9fa !important;
        color: #333 !important;
    }

    /* hero */
    .hero {
        padding: 100px 20px;
        text-align: center;
    }

    .hero h1 {
        font-weight: 600;
    }

    /* card animasi */
    .feature-card {
        border-radius: 15px;
        transition: 0.3s;
    }

    .feature-card:hover {
        transform: translateY(-8px);
    }

    /* button */
    .btn-main {
        border-radius: 50px;
        padding: 10px 25px;
    }

    /* toggle icon */
    .dark-toggle {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
    }

    footer a {
        color: #007bff !important;
        /* tetap biru */
    }

    body.dark footer a {
        color: #007bff !important;
        /* paksa tetap biru saat dark */
    }
    </style>

</head>

<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand font-weight-bold" href="#"><img src="assets/images/logo-sma.png" alt="Logo"
                    width="30" class="mr-2">
                Values.</a>

            <div class="ml-auto d-flex align-items-center">
                <button onclick="toggleDark()" id="themeToggle" class="dark-toggle mr-3">
                    🌙
                </button>

                <a href="auth/login.php" class="btn btn-primary btn-main">
                    Login
                </a>
            </div>
        </div>
    </nav>

    <!-- CONTENT -->
    <div class="flex-grow-1">

        <!-- HERO -->
        <section class="hero container">
            <h1 data-aos="fade-up">Sistem Analisa Nilai SMA</h1>

            <p class="text-muted mt-3" data-aos="fade-up" data-aos-delay="100">
                Kelola nilai siswa, ranking, dan raport secara otomatis & modern
            </p>

            <a href="auth/login.php" class="btn btn-primary btn-main mt-3" data-aos="fade-up" data-aos-delay="200">
                Mulai Sekarang
            </a>
        </section>

        <!-- FEATURES -->
        <section class="container mb-5">
            <div class="row">

                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up">
                    <div class="card feature-card shadow-sm h-100 text-center">
                        <div class="card-body">
                            <h5>📌 Manajemen Nilai</h5>
                            <p class="text-muted">Input nilai siswa cepat</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card feature-card shadow-sm h-100 text-center">
                        <div class="card-body">
                            <h5>🏆 Ranking Otomatis</h5>
                            <p class="text-muted">Ranking realtime</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card feature-card shadow-sm h-100 text-center">
                        <div class="card-body">
                            <h5>📄 Raport Digital</h5>
                            <p class="text-muted">Cetak raport modern</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card feature-card shadow-sm h-100 text-center">
                        <div class="card-body">
                            <h5>📈 Grafik Nilai</h5>
                            <p class="text-muted">Statistik visual</p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </div>

    <!-- FOOTER -->
    <footer class="text-center py-3 bg-light border-top mt-auto">
        <div class="small" style="font-weight: bold;">
            Copyright &copy; <?= date('Y'); ?>
            <a href="https://robbyilham.com/" target="_blank">by</a>
            IT Development IHBS
        </div>
    </footer>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <script>
    AOS.init({
        duration: 800,
        once: true
    });

    /* TOGGLE DARK MODE */
    function toggleDark() {
        document.body.classList.toggle('dark');

        let btn = document.getElementById('themeToggle');

        if (document.body.classList.contains('dark')) {
            localStorage.setItem('theme', 'dark');
            btn.innerHTML = '☀️';
        } else {
            localStorage.setItem('theme', 'light');
            btn.innerHTML = '🌙';
        }
    }

    /* LOAD THEME */
    window.onload = function() {
        let theme = localStorage.getItem('theme');
        let btn = document.getElementById('themeToggle');

        if (theme === 'dark') {
            document.body.classList.add('dark');
            btn.innerHTML = '☀️';
        } else {
            btn.innerHTML = '🌙';
        }
    }
    </script>

</body>

</html>