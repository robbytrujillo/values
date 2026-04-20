<?php
session_start();

// Jika sudah login → redirect sesuai role
if(isset($_SESSION['user'])){
    $role = $_SESSION['user']['role'];

    switch($role){
        case 'admin':
            header("Location: admin/dashboard.php");
            exit;
        case 'guru':
            header("Location: guru/input_nilai.php");
            exit;
        case 'walas':
            header("Location: walas/dashboard.php");
            exit;
        case 'kurikulum':
            header("Location: kurikulum/dashboard.php");
            exit;
        case 'siswa':
            header("Location: siswa/dashboard.php");
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Analisa Nilai SMA</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
    /* tambahan khusus landing */
    .hero {
        text-align: center;
        padding: 60px 20px;
    }

    .hero h1 {
        font-size: 28px;
        margin-bottom: 10px;
    }

    .hero p {
        color: #555;
        margin-bottom: 20px;
    }

    .btn-login {
        display: inline-block;
        padding: 12px 25px;
        background: #1e293b;
        color: white;
        border-radius: 10px;
        text-decoration: none;
    }

    .features {
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
        padding: 20px;
    }

    .feature-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }
    </style>

</head>

<body>

    <div class="hero">
        <h1>📊 Sistem Analisa Nilai SMA</h1>
        <p>Aplikasi pengolahan nilai, ranking, dan raport siswa</p>
        <a href="auth/login.php" class="btn-login">Login Sekarang</a>
    </div>

    <div class="features">

        <div class="feature-card">
            <h3>📌 Manajemen Nilai</h3>
            <p>Input nilai siswa oleh guru secara mudah dan cepat</p>
        </div>

        <div class="feature-card">
            <h3>🏆 Ranking Otomatis</h3>
            <p>Ranking kelas, angkatan, dan sekolah secara realtime</p>
        </div>

        <div class="feature-card">
            <h3>📄 Raport Digital</h3>
            <p>Cetak raport lengkap dengan deskripsi dan tanda tangan</p>
        </div>

        <div class="feature-card">
            <h3>📈 Grafik Nilai</h3>
            <p>Visualisasi statistik nilai siswa dan mapel</p>
        </div>

    </div>

</body>

</html>