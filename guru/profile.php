<?php
include '../config/auth.php';
cek_role(['guru']);
include '../config/koneksi.php';

$guru_id = $_SESSION['user']['id'];

/* ================= DATA GURU ================= */
$result = mysqli_query($conn, "
    SELECT g.*, u.username
    FROM guru g
    LEFT JOIN user u ON g.id = u.id
    WHERE g.id='$guru_id'
");

if(!$result){
    die("Query Error: " . mysqli_error($conn));
}

$guru = mysqli_fetch_assoc($result);

if(!$guru){
    die("Data guru tidak ditemukan.");
}

// if (!$guru) {
//     die("Data guru tidak ditemukan.");
// }

/* ================= UPDATE PROFILE ================= */
if (isset($_POST['update_profile'])) {

    $nama      = mysqli_real_escape_string($conn, $_POST['nama']);
    $nip       = mysqli_real_escape_string($conn, $_POST['nip']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp     = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat    = mysqli_real_escape_string($conn, $_POST['alamat']);
    $username  = mysqli_real_escape_string($conn, $_POST['username']);

    mysqli_query($conn, "
        UPDATE guru SET
            nama='$nama',
            nip='$nip',
            email='$email',
            no_hp='$no_hp',
            alamat='$alamat'
        WHERE id='$guru_id'
    ");

    mysqli_query($conn, "
        UPDATE user SET
            username='$username'
        WHERE id='$guru_id'
    ");

    echo "<script>alert('Profile berhasil diperbarui');location='profile.php';</script>";
    exit;
}

/* ================= UPDATE PASSWORD ================= */
if (isset($_POST['update_password'])) {

    $password_lama = md5($_POST['password_lama']);
    $password_baru = md5($_POST['password_baru']);
    $konfirmasi    = md5($_POST['konfirmasi_password']);

    $cekUser = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT * FROM user
        WHERE id='{$guru['user_id']}'
        AND password='$password_lama'
    "));

    if (!$cekUser) {
        echo "<script>alert('Password lama salah');</script>";
    } elseif ($password_baru != $konfirmasi) {
        echo "<script>alert('Konfirmasi password tidak cocok');</script>";
    } else {

        mysqli_query($conn, "
            UPDATE user SET
                password='$password_baru'
            WHERE id='{$guru['user_id']}'
        ");

        echo "<script>alert('Password berhasil diperbarui');location='profile.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Profile Guru</title>

    <link rel="icon" type="image/png" href="../assets/images/logo-sma.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .btn {
        border-radius: 30px !important;
    }

    .profile-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
    }

    .profile-avatar {
        width: 90px;
        height: 90px;
        background: white;
        color: #007bff;
        font-size: 36px;
        font-weight: bold;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }

    @media(max-width:767px) {
        .form-control {
            margin-bottom: 10px;
        }
    }
    </style>
</head>

<body>

    <?php include 'template.php'; ?>

    <div class="container-fluid mt-4">

        <div class="profile-header mb-4">
            <div class="profile-avatar">
                <?= strtoupper(substr($guru['nama'],0,1)) ?>
            </div>
            <h3><?= htmlspecialchars($guru['nama']) ?></h3>
            <p class="mb-0">Guru / Pengajar</p>
        </div>

        <div class="row">

            <!-- PROFILE -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-body">

                        <h4 class="mb-4">Data Profile</h4>

                        <form method="POST">

                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control"
                                    value="<?= htmlspecialchars($guru['nama']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label>NIP</label>
                                <input type="text" name="nip" class="form-control"
                                    value="<?= htmlspecialchars($guru['nip']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control"
                                    value="<?= htmlspecialchars($guru['username']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?= htmlspecialchars($guru['email']) ?>">
                            </div>

                            <div class="form-group">
                                <label>No HP</label>
                                <input type="text" name="no_hp" class="form-control"
                                    value="<?= htmlspecialchars($guru['no_hp']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control"
                                    rows="3"><?= htmlspecialchars($guru['alamat']) ?></textarea>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">
                                Simpan Perubahan
                            </button>

                        </form>

                    </div>
                </div>
            </div>

            <!-- PASSWORD -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">

                        <h4 class="mb-4">Ganti Password</h4>

                        <form method="POST">

                            <div class="form-group">
                                <label>Password Lama</label>
                                <input type="password" name="password_lama" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Password Baru</label>
                                <input type="password" name="password_baru" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Konfirmasi Password</label>
                                <input type="password" name="konfirmasi_password" class="form-control" required>
                            </div>

                            <button type="submit" name="update_password" class="btn btn-warning btn-block">
                                Update Password
                            </button>

                        </form>

                    </div>
                </div>
            </div>

        </div>

    </div>

    <?php include 'template_footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>