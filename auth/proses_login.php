<?php
session_start();
include '../config/koneksi.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

$user = null;

// ================= CEK ADMIN =================
$q = mysqli_query($conn,"SELECT * FROM users WHERE username='$username' LIMIT 1");
if(mysqli_num_rows($q)){
    $user = mysqli_fetch_assoc($q);

    if(md5($password) == $user['password']){
        $_SESSION['user'] = $user;
        $_SESSION['role'] = 'admin';

        header("Location: ../admin/dashboard.php");
        exit;
    }
}

// ================= CEK GURU =================
$q = mysqli_query($conn,"SELECT * FROM guru WHERE username='$username' LIMIT 1");
if(mysqli_num_rows($q)){
    $user = mysqli_fetch_assoc($q);

    if(password_verify($password, $user['password'])){
        $_SESSION['user'] = $user;
        $_SESSION['role'] = 'guru';

        header("Location: ../guru/input_nilai.php");
        exit;
    }
}

// ================= CEK SISWA =================
$q = mysqli_query($conn,"SELECT * FROM siswa WHERE username='$username' LIMIT 1");
if(mysqli_num_rows($q)){
    $user = mysqli_fetch_assoc($q);

    if(password_verify($password, $user['password'])){
        $_SESSION['user'] = $user;
        $_SESSION['role'] = 'siswa';

        header("Location: ../siswa/index.php");
        exit;
    }
}

// ================= GAGAL =================
echo "<script>
alert('Username atau password salah');
window.location='../index.php';
</script>";