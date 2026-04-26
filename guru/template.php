<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body {
        overflow-x: hidden;
    }

    /* SIDEBAR */
    .sidebar {
        min-height: 100vh;
        background: #1e293b;
        color: #fff;
    }

    .sidebar a {
        color: #cbd5f5;
        display: block;
        padding: 12px;
        text-decoration: none;
    }

    .sidebar a:hover {
        background: #334155;
        color: #fff;
    }

    /* CONTENT */
    .content {
        padding: 20px;
    }
    </style>
</head>

<!-- 🔥 TAMBAHAN FLEX -->

<body class="d-flex flex-column min-vh-100">

    <!-- 🔥 flex-grow agar dorong footer -->
    <div class="container-fluid flex-grow-1">
        <div class="row">

            <!-- SIDEBAR -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <h5 class="text-center mt-3">📊 Guru</h5>

                <a href="dashboard.php">Dashboard</a>
                <a class="nav-link text-white" href="input_nilai.php">
                    📝 Input Nilai
                </a>
                <a class="nav-link text-white" href="ranking.php">
                    📚 Peringkat
                </a>

                <a class="nav-link text-white" href="profil.php">
                    👤 Profil
                </a>
                <a href="../auth/logout.php">Logout</a>
            </nav>

            <!-- MAIN CONTENT -->
            <!-- 🔥 flex-column biar footer bisa dorong -->
            <main class="col-md-10 ml-sm-auto col-lg-10 content d-flex flex-column">

                <!-- NAVBAR TOP -->
                <nav class="navbar navbar-light bg-light mb-4">
                    <button class="btn btn-primary d-md-none" onclick="toggleSidebar()">
                        ☰
                    </button>
                </nav>