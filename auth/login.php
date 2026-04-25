<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="icon" type="image/png" href="../assets/images/logo-sma.png">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #e4e3ec, #d2d6d8);
        height: 100vh;
    }

    .login-container {
        height: 100vh;
    }

    .card {
        border-radius: 15px;
    }
    </style>
</head>

<body>

    <div class="container login-container d-flex justify-content-center align-items-center">
        <div class="col-md-4 col-12">

            <div class="card shadow-lg">
                <div class="card-body p-4">

                    <h3 class="text-center mb-4">
                        <img src="../assets/images/logo-sma.png" alt="Logo" width="30" class="mr-2"><b>Values.</b>
                    </h3>

                    <form action="proses_login.php" method="POST">

                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password"
                                required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block rounded-pill">
                            LogIn
                        </button>

                        <footer class="footer-full text-center mt-auto py-3 bg-light border-top">
                            <div class="small" style="font-weight: bold">
                                Copyright &copy; <?= date('Y'); ?>
                                <a href="https://robbyilham.com/" target="_blank">by</a>
                                IT Develop. IHBS
                            </div>
                        </footer>
                    </form>

                </div>
                <a href="../index.php" style="color: red; text-align: center;">
                    ← Back
                </a>
            </div>

        </div>

    </div>



    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>