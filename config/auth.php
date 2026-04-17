<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit;
}

function cek_role($roles = []){
    if(!in_array($_SESSION['user']['role'], $roles)){
        header("Location: ../index.php");
        exit;
    }
}
?>