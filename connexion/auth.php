<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion/Login.php"); // إعادة التوجيه لصفحة تسجيل الدخول
    exit();
}
?>
