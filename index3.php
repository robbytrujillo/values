<?php
session_start();

if(isset($_SESSION['user'])){
    $role = $_SESSION['user']['role'];

    switch($role){
        case 'admin':
            header("Location: admin/dashboard.php"); exit;
        case 'guru':
            header("Location: guru/input_nilai.php"); exit;
        case 'walas':
            header("Location: walas/dashboard.php"); exit;
        case 'kurikulum':
            header("Location: kurikulum/dashboard.php"); exit;
        case 'siswa':
            header("Location: siswa/dashboard.php"); exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Analisa Nilai SMA</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f5f7fb;
    }

    .hero {
        padding: 80px 20px;
        text-align: center;
    }

    .hero h1 {
        font-weight: 600;
    }

    .feature-card {
        border-radius: 15px;
        transition: 0.3s;
    }

    .feature-card:hover {
        transform: translateY(-5px);
    }
    </style>

</head>

<body class="d-flex flex-column min-vh-100">

    <!-- CONTENT -->
    <div class="flex-grow-1">

        <!-- HERO -->
        <section class="hero container">
            <h1 class="mb-3">📊 Sistem Analisa Nilai SMA</h1>
            <p class="text-muted mb-4">
                Aplikasi pengolahan nilai, ranking, dan raport siswa
            </p>
            <a href="auth/login.php" class="btn btn-primary px-4 rounded-pill">
                Login Sekarang
            </a>
        </section>

        <!-- FEATURES -->
        <section class="container mb-5">
            <div class="row">

                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card feature-card shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5>📌 Manajemen Nilai</h5>
                            <p class="text-muted">
                                Input nilai siswa oleh guru secara cepat
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card feature-card shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5>🏆 Ranking Otomatis</h5>
                            <p class="text-muted">
                                Ranking kelas & sekolah realtime
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card feature-card shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5>📄 Raport Digital</h5>
                            <p class="text-muted">
                                Cetak raport lengkap & profesional
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card feature-card shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5>📈 Grafik Nilai</h5>
                            <p class="text-muted">
                                Statistik nilai dalam bentuk visual
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </div>

    <!-- FOOTER -->
    <footer class="text-center mt-auto py-3 bg-light border-top">
        <div class="small">
            Copyright &copy; <?= date('Y'); ?>
            <a href="https://robbyilham.com/" target="_blank">by</a>
            IT Development IHBS
        </div>
    </footer>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>