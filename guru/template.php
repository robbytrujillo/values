<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Guru</title>

    <link rel="icon" type="image/png" href="../assets/images/logo-sma.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    body {
        overflow-x: hidden;
        background: #f8fafc;
        font-family: 'Segoe UI', sans-serif;
    }

    /* SIDEBAR */
    .sidebar {
        min-height: 100vh;
        background: linear-gradient(180deg, #1e293b, #0f172a);
        color: white;
        transition: all 0.3s ease;
        padding-top: 15px;
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        z-index: 9999;
    }

    .sidebar h5 {
        font-weight: bold;
        letter-spacing: 1px;
    }

    .sidebar a {
        color: #cbd5e1;
        display: block;
        padding: 12px 20px;
        margin: 4px 10px;
        border-radius: 10px;
        text-decoration: none;
        transition: 0.3s;
        font-size: 15px;
    }

    .sidebar a:hover,
    .sidebar a.active {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        transform: translateX(5px);
    }

    .sidebar i {
        width: 20px;
        margin-right: 8px;
    }

    /* CONTENT */
    .content {
        padding: 20px;
        min-height: 100vh;
        transition: 0.3s;
    }

    /* TOPBAR */
    .topbar {
        background: white;
        border-radius: 15px;
        padding: 12px 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .toggle-btn {
        border-radius: 10px;
    }

    /* OVERLAY */
    #sidebarOverlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        z-index: 9998;
    }

    /* MOBILE */
    @media(max-width:767px) {
        .sidebar {
            position: fixed;
            top: 0;
            left: -260px;
            width: 250px;
            height: 100%;
        }

        .sidebar.show {
            left: 0;
        }

        .content {
            width: 100%;
            padding: 12px;
            margin-left: 0 !important;
        }

        .topbar {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 10px;
        }

        .topbar h5 {
            font-size: 18px;
        }

        .topbar span {
            font-size: 14px;
        }
    }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- OVERLAY MOBILE -->
    <div id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="container-fluid flex-grow-1">
        <div class="row">

            <!-- SIDEBAR -->
            <nav id="sidebarMenu" class="col-md-2 sidebar">

                <h5 class="text-center text-white mb-4">
                    <img src="../assets/images/logo-sma.png" width="35" class="mb-2">
                    PANEL GURU
                </h5>

                <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])=='dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>

                <a href="input_nilai.php"
                    class="<?= basename($_SERVER['PHP_SELF'])=='input_nilai.php' ? 'active' : '' ?>">
                    <i class="fas fa-edit"></i> Input Nilai
                </a>

                <a href="ranking.php" class="<?= basename($_SERVER['PHP_SELF'])=='ranking.php' ? 'active' : '' ?>">
                    <i class="fas fa-trophy"></i> Peringkat
                </a>

                <a href="profil.php" class="<?= basename($_SERVER['PHP_SELF'])=='profil.php' ? 'active' : '' ?>">
                    <i class="fas fa-user"></i> Profil
                </a>

                <a href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>

            </nav>

            <!-- MAIN CONTENT -->
            <main class="col-md-10 ml-sm-auto col-lg-10 content">

                <!-- TOPBAR -->
                <div class="topbar d-flex justify-content-between align-items-center mb-4">

                    <button class="btn btn-primary d-md-none toggle-btn" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>

                    <h5 class="mb-0 font-weight-bold text-dark">
                        Panel Guru
                    </h5>

                    <span class="text-muted">
                        Welcome,
                        <strong class="text-danger">
                            <?= $_SESSION['user']['nama']; ?>
                        </strong>
                    </span>

                </div>

                <script>
                function toggleSidebar() {
                    const sidebar = document.getElementById("sidebarMenu");
                    const overlay = document.getElementById("sidebarOverlay");

                    sidebar.classList.toggle("show");

                    if (sidebar.classList.contains("show")) {
                        overlay.style.display = "block";
                    } else {
                        overlay.style.display = "none";
                    }
                }
                </script>