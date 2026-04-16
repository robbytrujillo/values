<?php
session_start();
include '../config/koneksi.php';

$username=$_POST['username'];
$password=md5($_POST['password']);

$q=mysqli_query($conn,"SELECT * FROM users WHERE username='$username' AND password='$password'");
$data=mysqli_fetch_array($q);

if($data){
$_SESSION['user']=$data;

switch($data['role']){
case 'admin': header("Location: ../admin/dashboard.php"); break;
case 'guru': header("Location: ../guru/input_nilai.php"); break;
}
}else{
echo "Login gagal";
}
?>